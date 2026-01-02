<?php
$title = "Billing Report";
require_once __DIR__ . "/../partials/header.php";
$counts = ['patients'=>0,'doctors'=>0,'rooms'=>0,'medications'=>0,'appointments'=>0,'admissions'=>0,'dash'=>0];
require __DIR__ . "/../partials/sidebar.php";

$type = $_GET['type'] ?? 'admission';
if(!in_array($type, ['admission','appointment'], true)) $type = 'admission';

$flash = get_flash();

// POST: Mark bill as Paid
if($_SERVER['REQUEST_METHOD']==='POST' && (($_POST['action'] ?? '')==='mark_paid')){
  $t = $_POST['type'] ?? 'admission';
  $id = $_POST['id'] ?? '';
  if(!in_array($t, ['admission','appointment'], true) || $id===''){
    set_flash('error','Request tidak valid.');
    redirect('/hospitalsystem/reports/billing.php?type='.$type);
  }

  try{
    if($t==='admission'){
      $exists = q($pdo,"SELECT AdmissionBillsID FROM admissionbills WHERE AdmissionID=? LIMIT 1",[$id])->fetch();
      if(!$exists){
        // fallback: create bill then set paid
        $newId = next_id($pdo,'admissionbills','AdmissionBillsID','ADBI');
        q($pdo,"INSERT INTO admissionbills (AdmissionBillsID, TotalAmount, PaymentStatus, PaymentMethod, CreationDate, StaffID, AdmissionID, AdmissionPrescriptionID, BankName)
               VALUES (?,?,?,?,?,?,?,?,?)",
          [$newId, CalculateTotalAdmissionCost($id), 'Paid', 'Cash', date('Y-m-d'), 'ST001', $id, null, null]);
      } else {
        q($pdo,"UPDATE admissionbills
               SET PaymentStatus='Paid',
                   TotalAmount=CalculateTotalAdmissionCost(AdmissionID)
               WHERE AdmissionID=?",[$id]);
      }
      set_flash('', 'Billing Admission '.$id.' berhasil di-set Paid.');
      redirect('/hospitalsystem/reports/billing.php?type=admission');
    } else {
      $exists = q($pdo,"SELECT AppointmentBillsID FROM appointmentbills WHERE AppointmentID=? LIMIT 1",[$id])->fetch();
      if(!$exists){
        $newId = next_id($pdo,'appointmentbills','AppointmentBillsID','APBI');
        q($pdo,"INSERT INTO appointmentbills (AppointmentBillsID, TotalAmount, PaymentStatus, PaymentMethod, CreationDate, StaffID, AppointmentID, AppointmentPrescriptionID, BankName)
               VALUES (?,?,?,?,?,?,?,?,?)",
          [$newId, CalculateTotalAppointmentCost($id), 'Paid', 'Cash', date('Y-m-d'), 'ST001', $id, null, null]);
      } else {
        q($pdo,"UPDATE appointmentbills
               SET PaymentStatus='Paid',
                   TotalAmount=CalculateTotalAppointmentCost(AppointmentID)
               WHERE AppointmentID=?",[$id]);
      }
      set_flash('', 'Billing Appointment '.$id.' berhasil di-set Paid.');
      redirect('/hospitalsystem/reports/billing.php?type=appointment');
    }
  }catch(Exception $e){
    set_flash('error','Gagal update billing: '.$e->getMessage());
    redirect('/hospitalsystem/reports/billing.php?type='.$t);
  }
}

// detect Quantity columns (optional patch)
$hasQtyAdmission = false;
$hasQtyAppointment = false;
try{ $hasQtyAdmission = (bool)q($pdo, "SHOW COLUMNS FROM admissionprescriptions LIKE 'Quantity'")->fetch(); }catch(Exception $e){}
try{ $hasQtyAppointment = (bool)q($pdo, "SHOW COLUMNS FROM appointmentprescription LIKE 'Quantity'")->fetch(); }catch(Exception $e){}

$rxMap = [];

if($type==='admission'){
  $rows = q($pdo,"SELECT a.AdmissionID, a.AdmissionDate, a.DischargeDate, g.PatientName, d.DoctorName, a.RoomNumber,
    CalculateTotalAdmissionCost(a.AdmissionID) AS TotalCost,
    b.PaymentStatus, b.PaymentMethod, b.BankName, b.CreationDate
    FROM admission a
    JOIN patientgeneralinfo g ON a.PatientID=g.PatientID
    JOIN doctordata d ON a.DoctorID=d.DoctorID
    LEFT JOIN admissionbills b ON a.AdmissionID=b.AdmissionID
    ORDER BY a.AdmissionDate DESC, a.AdmissionID DESC")->fetchAll();

  // build unique ids (compatible with PHP < 7.4)
  $tmp = [];
  foreach($rows as $row){ $tmp[$row['AdmissionID']] = true; }
  $ids = array_keys($tmp);

  if(count($ids) > 0){
    $ph = implode(',', array_fill(0, count($ids), '?'));
    $sql = "SELECT ap.AdmissionID, ap.AdmissionPrescriptionID, ap.AdmissionDosageUsed AS DosageInfo,
              ap.MedicationID, m.MedicationName, m.MedicationPrice AS UnitPrice"
            . ($hasQtyAdmission ? ", ap.Quantity AS Quantity, (m.MedicationPrice*ap.Quantity) AS LineTotal" : ", 1 AS Quantity, m.MedicationPrice AS LineTotal")
            . " FROM admissionprescriptions ap
               JOIN medication m ON ap.MedicationID=m.MedicationID
               WHERE ap.AdmissionID IN ($ph)
               ORDER BY ap.AdmissionID, ap.AdmissionPrescriptionID DESC";
    $rxRows = q($pdo, $sql, $ids)->fetchAll();
    foreach($rxRows as $x){
      $k = $x['AdmissionID'];
      if(!isset($rxMap[$k])) $rxMap[$k] = ['items'=>[], 'subtotal'=>0.0];
      $rxMap[$k]['items'][] = $x;
      $rxMap[$k]['subtotal'] += (float)$x['LineTotal'];
    }
  }
} else {
  $rows = q($pdo,"SELECT a.AppointmentID, a.AppointmentDate, a.AppointmentStatus, g.PatientName, d.DoctorName,
    CalculateTotalAppointmentCost(a.AppointmentID) AS TotalCost,
    b.PaymentStatus, b.PaymentMethod, b.BankName, b.CreationDate
    FROM appointment a
    JOIN patientgeneralinfo g ON a.PatientID=g.PatientID
    JOIN doctordata d ON a.DoctorID=d.DoctorID
    LEFT JOIN appointmentbills b ON a.AppointmentID=b.AppointmentID
    ORDER BY a.AppointmentDate DESC, a.AppointmentID DESC")->fetchAll();

  $tmp = [];
  foreach($rows as $row){ $tmp[$row['AppointmentID']] = true; }
  $ids = array_keys($tmp);

  if(count($ids) > 0){
    $ph = implode(',', array_fill(0, count($ids), '?'));
    $sql = "SELECT ap.AppointmentID, ap.AppointmentPrescriptionID, ap.DosageDetail AS DosageInfo, ap.Duration AS Duration,
              ap.MedicationID, m.MedicationName, m.MedicationPrice AS UnitPrice"
            . ($hasQtyAppointment ? ", ap.Quantity AS Quantity, (m.MedicationPrice*ap.Quantity) AS LineTotal" : ", 1 AS Quantity, m.MedicationPrice AS LineTotal")
            . " FROM appointmentprescription ap
               JOIN medication m ON ap.MedicationID=m.MedicationID
               WHERE ap.AppointmentID IN ($ph)
               ORDER BY ap.AppointmentID, ap.AppointmentPrescriptionID DESC";
    $rxRows = q($pdo, $sql, $ids)->fetchAll();
    foreach($rxRows as $x){
      $k = $x['AppointmentID'];
      if(!isset($rxMap[$k])) $rxMap[$k] = ['items'=>[], 'subtotal'=>0.0];
      $rxMap[$k]['items'][] = $x;
      $rxMap[$k]['subtotal'] += (float)$x['LineTotal'];
    }
  }
}
?>
<main class="main">
  <div class="topbar">
    <div>
      <h2>Billing Report</h2>
      <div class="sub">Detail obat (prescription), subtotal, dan total yang harus dibayar.</div>
    </div>
    <div class="actions">
      <a class="btn <?= $type==='admission'?'primary':'' ?>" href="/hospitalsystem/reports/billing.php?type=admission">Admission</a>
      <a class="btn <?= $type==='appointment'?'primary':'' ?>" href="/hospitalsystem/reports/billing.php?type=appointment">Appointment</a>
    </div>
  </div>

  <div class="container">
    <?php if($flash): ?><div class="flash <?= h($flash['type']) ?>" data-auto-dismiss="4200"><?= h($flash['msg']) ?></div><?php endif; ?>

    <div class="card">
      <div class="help" style="margin-bottom:10px;">
        <?php if($type==='admission' && !$hasQtyAdmission): ?>
          <br/><span class="tag">Qty=1</span> Quantity belum ada di <span class="mono">admissionprescriptions</span> (optional patch).
        <?php endif; ?>
        <?php if($type==='appointment' && !$hasQtyAppointment): ?>
          <br/><span class="tag">Qty=1</span> Quantity belum ada di <span class="mono">appointmentprescription</span> (optional patch).
        <?php endif; ?>
      </div>

      <div class="table-wrap">
        <table>
          <thead>
            <?php if($type==='admission'): ?>
              <tr>
                <th>AdmissionID</th><th>Tgl Masuk</th><th>Tgl Keluar</th><th>Pasien</th><th>Dokter</th><th>Room</th>
                <th>Detail obat</th>
                <th>Subtotal obat</th>
                <th>Total (Rp)</th>
                <th>Payment</th>
              </tr>
            <?php else: ?>
              <tr>
                <th>AppointmentID</th><th>Tanggal</th><th>Status</th><th>Pasien</th><th>Dokter</th>
                <th>Detail obat</th>
                <th>Subtotal obat</th>
                <th>Total (Rp)</th>
                <th>Payment</th>
              </tr>
            <?php endif; ?>
          </thead>
          <tbody>
          <?php foreach($rows as $r): ?>
            <?php if($type==='admission'):
              $k = $r['AdmissionID'];
              $rx = $rxMap[$k]['items'] ?? [];
              $subtotal = $rxMap[$k]['subtotal'] ?? 0.0;
            ?>
              <tr>
                <td class="mono"><?= h($r['AdmissionID']) ?></td>
                <td><?= h($r['AdmissionDate']) ?></td>
                <td><?= h($r['DischargeDate'] ?? '-') ?></td>
                <td><?= h($r['PatientName']) ?></td>
                <td><?= h($r['DoctorName']) ?></td>
                <td class="mono"><?= h($r['RoomNumber']) ?></td>
                <td>
                  <details>
                    <summary class="btn" style="display:inline-flex;">Detail obat (<?= count($rx) ?>)</summary>
                    <div style="margin-top:10px;">
                      <div class="table-wrap">
                        <table>
                          <thead>
                            <tr>
                              <th>RxID</th><th>Obat</th><th>Qty</th><th>Harga</th><th>Total</th><th>Dosage</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php foreach($rx as $x): ?>
                              <tr>
                                <td class="mono"><?= h($x['AdmissionPrescriptionID']) ?></td>
                                <td><b><?= h($x['MedicationName']) ?></b><br/><small class="mono"><?= h($x['MedicationID']) ?></small></td>
                                <td class="mono"><?= h($x['Quantity']) ?></td>
                                <td class="mono">Rp <?= number_format((float)$x['UnitPrice'],2,',','.') ?></td>
                                <td class="mono">Rp <?= number_format((float)$x['LineTotal'],2,',','.') ?></td>
                                <td><small><?= h($x['DosageInfo']) ?></small></td>
                              </tr>
                            <?php endforeach; ?>
                            <?php if(!$rx): ?><tr><td colspan="6"><small>Tidak ada prescription.</small></td></tr><?php endif; ?>
                          </tbody>
                        </table>
                      </div>
                      <div class="help" style="margin-top:8px;">
                        Subtotal obat: <b class="mono">Rp <?= number_format((float)$subtotal,2,',','.') ?></b>
                      </div>
                    </div>
                  </details>
                </td>
                <td class="mono">Rp <?= number_format((float)$subtotal,2,',','.') ?></td>
                <td class="mono">Rp <?= number_format((float)$r['TotalCost'],2,',','.') ?></td>
                <td>
                  <?php if($r['PaymentStatus']): ?>
                    <span class="status"><span class="dot <?= h(status_dot_class($r['PaymentStatus'])) ?>"></span><?= h($r['PaymentStatus']) ?></span>
                    <small style="display:block;margin-top:4px;">
                      <?= h($r['PaymentMethod']) ?><?= $r['BankName'] ? ' • '.h($r['BankName']) : '' ?><br/>
                      <?= h($r['CreationDate']) ?>
                    </small>
                    <?php if($r['PaymentStatus']!=='Paid'): ?>
                      <form method="post" style="margin-top:8px;">
                        <input type="hidden" name="action" value="mark_paid"/>
                        <input type="hidden" name="type" value="admission"/>
                        <input type="hidden" name="id" value="<?= h($r['AdmissionID']) ?>"/>
                        <button class="btn primary" type="submit">Mark Paid</button>
                      </form>
                    <?php endif; ?>
                  <?php else: ?>
                    <small>Belum ada bill.</small>
                  <?php endif; ?>
                </td>
              </tr>
            <?php else:
              $k = $r['AppointmentID'];
              $rx = $rxMap[$k]['items'] ?? [];
              $subtotal = $rxMap[$k]['subtotal'] ?? 0.0;
            ?>
              <tr>
                <td class="mono"><?= h($r['AppointmentID']) ?></td>
                <td><?= h($r['AppointmentDate']) ?></td>
                <td><span class="status"><span class="dot <?= h(status_dot_class($r['AppointmentStatus'])) ?>"></span><?= h($r['AppointmentStatus']) ?></span></td>
                <td><?= h($r['PatientName']) ?></td>
                <td><?= h($r['DoctorName']) ?></td>
                <td>
                  <details>
                    <summary class="btn" style="display:inline-flex;">Detail obat (<?= count($rx) ?>)</summary>
                    <div style="margin-top:10px;">
                      <div class="table-wrap">
                        <table>
                          <thead>
                            <tr>
                              <th>RxID</th><th>Obat</th><th>Qty</th><th>Harga</th><th>Total</th><th>Detail</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php foreach($rx as $x): ?>
                              <tr>
                                <td class="mono"><?= h($x['AppointmentPrescriptionID']) ?></td>
                                <td><b><?= h($x['MedicationName']) ?></b><br/><small class="mono"><?= h($x['MedicationID']) ?></small></td>
                                <td class="mono"><?= h($x['Quantity']) ?></td>
                                <td class="mono">Rp <?= number_format((float)$x['UnitPrice'],2,',','.') ?></td>
                                <td class="mono">Rp <?= number_format((float)$x['LineTotal'],2,',','.') ?></td>
                                <td><small><?= h($x['DosageInfo']) ?><?php if(isset($x['Duration']) && $x['Duration']): ?><br/>Durasi: <?= h($x['Duration']) ?><?php endif; ?></small></td>
                              </tr>
                            <?php endforeach; ?>
                            <?php if(!$rx): ?><tr><td colspan="6"><small>Tidak ada prescription.</small></td></tr><?php endif; ?>
                          </tbody>
                        </table>
                      </div>
                      <div class="help" style="margin-top:8px;">
                        Subtotal obat: <b class="mono">Rp <?= number_format((float)$subtotal,2,',','.') ?></b>
                      </div>
                    </div>
                  </details>
                </td>
                <td class="mono">Rp <?= number_format((float)$subtotal,2,',','.') ?></td>
                <td class="mono">Rp <?= number_format((float)$r['TotalCost'],2,',','.') ?></td>
                <td>
                  <?php if($r['PaymentStatus']): ?>
                    <span class="status"><span class="dot <?= h(status_dot_class($r['PaymentStatus'])) ?>"></span><?= h($r['PaymentStatus']) ?></span>
                    <small style="display:block;margin-top:4px;">
                      <?= h($r['PaymentMethod']) ?><?= $r['BankName'] ? ' • '.h($r['BankName']) : '' ?><br/>
                      <?= h($r['CreationDate']) ?>
                    </small>
                    <?php if($r['PaymentStatus']!=='Paid'): ?>
                      <form method="post" style="margin-top:8px;">
                        <input type="hidden" name="action" value="mark_paid"/>
                        <input type="hidden" name="type" value="appointment"/>
                        <input type="hidden" name="id" value="<?= h($r['AppointmentID']) ?>"/>
                        <button class="btn primary" type="submit">Mark Paid</button>
                      </form>
                    <?php endif; ?>
                  <?php else: ?>
                    <small>Belum ada bill.</small>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endif; ?>
          <?php endforeach; ?>
          <?php if(!$rows): ?>
            <tr><td colspan="12"><small>Tidak ada data.</small></td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</main>
<?php require __DIR__ . "/../partials/footer.php"; ?>
