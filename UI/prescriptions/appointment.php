<?php
$title = "Appointment Prescriptions";
require_once __DIR__ . "/../partials/header.php";
$counts = ['patients'=>0,'doctors'=>0,'rooms'=>0,'medications'=>0,'appointments'=>0,'admissions'=>0,'dash'=>0];
require __DIR__ . "/../partials/sidebar.php";

// cek apakah kolom Quantity ada
$hasQty = false;
try{
  $col = q($pdo, "SHOW COLUMNS FROM appointmentprescription LIKE 'Quantity'")->fetch();
  $hasQty = $col ? true : false;
}catch(Exception $e){ $hasQty=false; }

$appointments = q($pdo,"SELECT a.AppointmentID, g.PatientName, d.DoctorName, a.AppointmentDate, a.AppointmentStatus
  FROM appointment a
  JOIN patientgeneralinfo g ON a.PatientID=g.PatientID
  JOIN doctordata d ON a.DoctorID=d.DoctorID
  ORDER BY a.AppointmentDate DESC, a.AppointmentID DESC")->fetchAll();

$meds = q($pdo,"SELECT MedicationID, MedicationName, Stock, MedicationPrice
  FROM medication ORDER BY MedicationName ASC")->fetchAll();

if($_SERVER['REQUEST_METHOD']==='POST'){
  $id = trim($_POST['AppointmentPrescriptionID'] ?? '');
  if($id==='') $id = next_id($pdo,'appointmentprescription','AppointmentPrescriptionID','APPR');

  $appointmentID = $_POST['AppointmentID'] ?? '';
  $medID = $_POST['MedicationID'] ?? '';
  $dosage = trim($_POST['DosageDetail'] ?? '');
  $duration = trim($_POST['Duration'] ?? '');

  $qty = 1;
  if($hasQty){
    $qty = (int)($_POST['Quantity'] ?? 1);
    if($qty < 1) $qty = 1;
  }

  try{
    if($hasQty){
      q($pdo,"INSERT INTO appointmentprescription (AppointmentPrescriptionID, DosageDetail, Duration, MedicationID, Quantity, AppointmentID)
             VALUES (?,?,?,?,?,?)",[$id,$dosage,$duration,$medID,$qty,$appointmentID]);
    } else {
      q($pdo,"INSERT INTO appointmentprescription (AppointmentPrescriptionID, DosageDetail, Duration, MedicationID, AppointmentID)
             VALUES (?,?,?,?,?)",[$id,$dosage,$duration,$medID,$appointmentID]);
    }
    set_flash('','Prescription appointment ditambahkan: '.$id);
    redirect("/hospitalsystem/prescriptions/appointment.php");
  }catch(Exception $e){
    set_flash('error',"Gagal menambah prescription: ".$e->getMessage());
  }
}

$rows = q($pdo,"SELECT ap.AppointmentPrescriptionID, ap.DosageDetail, ap.Duration, ap.MedicationID, ap.AppointmentID"
  . ($hasQty ? ", ap.Quantity" : "") .
  ", m.MedicationName, m.MedicationPrice,
     a.AppointmentDate, a.AppointmentStatus, g.PatientName, d.DoctorName
  FROM appointmentprescription ap
  JOIN medication m ON ap.MedicationID=m.MedicationID
  JOIN appointment a ON ap.AppointmentID=a.AppointmentID
  JOIN patientgeneralinfo g ON a.PatientID=g.PatientID
  JOIN doctordata d ON a.DoctorID=d.DoctorID
  ORDER BY ap.AppointmentPrescriptionID DESC")->fetchAll();

$next = next_id($pdo,'appointmentprescription','AppointmentPrescriptionID','APPR');
$flash = get_flash();
?>
<main class="main">
  <div class="topbar">
    <div>
      <h2>Appointment Prescriptions</h2>
      <div class="sub">Tambah obat untuk pasien konsultasi/appointment.</div>
    </div>
    <div class="actions">
      <a class="btn" href="/hospitalsystem/prescriptions/index.php">← Kembali</a>
      <a class="btn" href="/hospitalsystem/reports/billing.php?type=appointment">Billing Appointment →</a>
    </div>
  </div>

  <div class="container">
    <?php if($flash): ?><div class="flash <?= h($flash['type']) ?>" data-auto-dismiss="5200"><?= h($flash['msg']) ?></div><?php endif; ?>

    <?php if(!$hasQty): ?>
      <div class="flash warn">
        Kolom <b>Quantity</b> belum ada. Kalau ingin qty & hitung biaya lebih akurat, jalankan patch:
        <span class="mono">/hospitalsystem/sql/patch_prescription_quantity.sql</span>
      </div>
    <?php endif; ?>

    <div class="grid cols-2">
      <div class="card">
        <h3 style="margin:0 0 10px 0;">Tambah Prescription</h3>
        <form class="form" method="post">
          <div class="field">
            <label>AppointmentPrescriptionID</label>
            <input name="AppointmentPrescriptionID" value="<?= h($_POST['AppointmentPrescriptionID'] ?? $next) ?>" placeholder="APPR001"/>
            <div class="help">Kosongkan untuk auto-generate.</div>
          </div>

          <div class="field">
            <label>Appointment</label>
            <select required name="AppointmentID">
              <option value="">-- pilih appointment --</option>
              <?php foreach($appointments as $a): ?>
                <option value="<?= h($a['AppointmentID']) ?>">
                  <?= h($a['AppointmentID']) ?> • <?= h($a['PatientName']) ?> • <?= h($a['DoctorName']) ?> • <?= h($a['AppointmentDate']) ?> • <?= h($a['AppointmentStatus']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="field">
            <label>Medication</label>
            <select required name="MedicationID">
              <option value="">-- pilih obat --</option>
              <?php foreach($meds as $m): ?>
                <option value="<?= h($m['MedicationID']) ?>">
                  <?= h($m['MedicationName']) ?> (<?= h($m['MedicationID']) ?>) • Stok <?= h($m['Stock']) ?> • Rp <?= number_format((float)$m['MedicationPrice'],2,',','.') ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <?php if($hasQty): ?>
          <div class="field">
            <label>Quantity</label>
            <input type="number" min="1" name="Quantity" value="<?= h($_POST['Quantity'] ?? 1) ?>"/>
          </div>
          <?php endif; ?>

          <div class="field">
            <label>Dosage Detail</label>
            <textarea required name="DosageDetail"><?= h($_POST['DosageDetail'] ?? '') ?></textarea>
          </div>
          <div class="field">
            <label>Duration</label>
            <input required name="Duration" value="<?= h($_POST['Duration'] ?? '') ?>" placeholder="7 days" />
          </div>

          <div class="actions">
            <button class="btn primary" type="submit">Simpan</button>
          </div>
        </form>
      </div>

      <div class="card">
        <h3 style="margin:0 0 10px 0;">Daftar Prescription</h3>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>ID</th><th>Appointment</th><th>Pasien</th><th>Obat</th><?php if($hasQty): ?><th>Qty</th><?php endif; ?><th>Detail</th><th>Harga</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($rows as $r): ?>
                <tr>
                  <td class="mono"><?= h($r['AppointmentPrescriptionID']) ?></td>
                  <td class="mono"><?= h($r['AppointmentID']) ?><br/><small><?= h($r['AppointmentDate']) ?> • <?= h($r['AppointmentStatus']) ?></small></td>
                  <td><?= h($r['PatientName']) ?><br/><small><?= h($r['DoctorName']) ?></small></td>
                  <td><b><?= h($r['MedicationName']) ?></b><br/><small class="mono"><?= h($r['MedicationID']) ?></small></td>
                  <?php if($hasQty): ?><td class="mono"><?= h($r['Quantity']) ?></td><?php endif; ?>
                  <td><small><?= h($r['DosageDetail']) ?><br/>Durasi: <?= h($r['Duration']) ?></small></td>
                  <td class="mono">Rp <?= number_format((float)$r['MedicationPrice'],2,',','.') ?></td>
                </tr>
              <?php endforeach; ?>
              <?php if(!$rows): ?><tr><td colspan="<?= $hasQty ? 7 : 6 ?>"><small>Tidak ada data.</small></td></tr><?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</main>
<?php require_once __DIR__ . "/../partials/footer.php"; ?>
