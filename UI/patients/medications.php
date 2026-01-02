<?php
$title = "Obat Pasien";
require_once __DIR__ . "/../partials/header.php";
$counts = ['patients'=>0,'doctors'=>0,'rooms'=>0,'medications'=>0,'appointments'=>0,'admissions'=>0,'dash'=>0];
require __DIR__ . "/../partials/sidebar.php";

$patientID = $_GET['id'] ?? '';
if($patientID===''){
  set_flash('error','PatientID tidak valid.');
  redirect('/hospitalsystem/patients/index.php');
}

$flash = get_flash();

$patient = null;
$rows = [];
$summary = null;
$view_ok = true;

try{
  $patient = q($pdo,"SELECT PatientID, PatientName, PatientGender, PatientDOB, PatientPhone, PatientEmail
    FROM patientgeneralinfo WHERE PatientID=?",[$patientID])->fetch();

  if(!$patient){
    set_flash('error','Pasien tidak ditemukan.');
    redirect('/hospitalsystem/patients/index.php');
  }

  // view dari patch SQL
  $rows = q($pdo,"SELECT *
    FROM vw_patient_medications
    WHERE PatientID=?
    ORDER BY VisitDate DESC, VisitID DESC, PrescriptionID DESC",[$patientID])->fetchAll();

  $summary = q($pdo,"SELECT *
    FROM vw_patient_medication_summary
    WHERE PatientID=?",[$patientID])->fetch();

}catch(Exception $e){
  $view_ok = false;
  $err = $e->getMessage();
}

?>
<main class="main">
  <div class="topbar">
    <div>
      <h2>Obat yang Diberikan</h2>
      <div class="sub">
        Pasien: <b><?= h($patientID) ?></b>
        <?php if($patient): ?> • <?= h($patient['PatientName']) ?><?php endif; ?>
      </div>
    </div>
    <div class="actions">
      <a class="btn" href="/hospitalsystem/patients/index.php">← Pasien</a>
      <a class="btn" href="/hospitalsystem/reports/billing.php">Billing →</a>
    </div>
  </div>

  <div class="container">
    <?php if($flash): ?>
      <div class="flash <?= h($flash['type']) ?>" data-auto-dismiss="5200"><?= h($flash['msg']) ?></div>
    <?php endif; ?>

    <?php if(!$view_ok): ?>
      <div class="flash warn">
        View untuk riwayat obat belum ada / belum di-import.
        Jalankan patch SQL: <span class="mono">patch_patient_medications_views.sql</span><br/>
        Error: <span class="mono"><?= h($err ?? '') ?></span>
      </div>
    <?php endif; ?>

    <?php if($patient): ?>
      <div class="grid cols-3">
        <div class="card">
          <div class="label">Nama</div>
          <div style="font-size:18px;font-weight:800;margin-top:6px;"><?= h($patient['PatientName']) ?></div>
          <div class="help" style="margin-top:6px;">
            <?= h($patient['PatientGender']) ?> • <?= h($patient['PatientDOB']) ?>
          </div>
        </div>

        <div class="card">
          <div class="label">Kontak</div>
          <div style="margin-top:8px;">
            <div><b><?= h($patient['PatientPhone']) ?></b></div>
            <small><?= h($patient['PatientEmail'] ?? '-') ?></small>
          </div>
        </div>

        <div class="card">
          <div class="label">Ringkasan Obat</div>
          <?php if($summary): ?>
            <div class="kpi" style="margin-top:8px;">
              <div>
                <div class="label">Total baris prescription</div>
                <div class="num" style="font-size:26px;"><?= (int)$summary['TotalPrescriptionRows'] ?></div>
              </div>
              <div class="pill">vw_patient_medications</div>
            </div>
            <div class="hr"></div>
            <div class="help">
              Total biaya obat: <b class="mono">Rp <?= number_format((float)$summary['TotalMedicationCost'],2,',','.') ?></b>
            </div>
          <?php else: ?>
            <div class="help" style="margin-top:8px;">Belum ada prescription.</div>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>

    <div class="card" style="margin-top:14px;">
      <h3 style="margin:0 0 10px 0;">Daftar Obat</h3>
      <div class="help" style="margin-bottom:10px;">
        Data diambil dari view <span class="mono">vw_patient_medications</span> (gabungan admission + appointment).
      </div>

      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Sumber</th>
              <th>Visit</th>
              <th>Tanggal</th>
              <th>Obat</th>
              <th>Info</th>
              <th>Qty</th>
              <th>Harga</th>
              <th>Total</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($rows as $r): ?>
              <tr>
                <td><span class="tag"><?= h($r['SourceType']) ?></span></td>
                <td class="mono"><?= h($r['VisitID']) ?></td>
                <td>
                  <?= h($r['VisitDate']) ?>
                  <?php if($r['EndDate']): ?><br/><small>Selesai: <?= h($r['EndDate']) ?></small><?php endif; ?>
                </td>
                <td>
                  <b><?= h($r['MedicationName']) ?></b><br/>
                  <small class="mono"><?= h($r['MedicationID']) ?></small>
                </td>
                <td>
                  <small>
                    <?= h($r['DosageInfo'] ?? '-') ?>
                    <?php if($r['Duration']): ?><br/>Durasi: <?= h($r['Duration']) ?><?php endif; ?>
                  </small>
                </td>
                <td class="mono"><?= h($r['Quantity']) ?></td>
                <td class="mono">Rp <?= number_format((float)$r['UnitPrice'],2,',','.') ?></td>
                <td class="mono">Rp <?= number_format((float)$r['LineTotal'],2,',','.') ?></td>
              </tr>
            <?php endforeach; ?>
            <?php if(!$rows): ?>
              <tr><td colspan="8"><small>Tidak ada data prescription untuk pasien ini.</small></td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="card" style="margin-top:14px;">
      <h3 style="margin:0 0 10px 0;">Tambah Prescription</h3>
      <div class="help">
        Tambahkan prescription melalui menu:
        <a class="btn" style="margin-top:10px;" href="/hospitalsystem/prescriptions/index.php">Prescriptions (Rx) →</a>
      </div>
    </div>

  </div>
</main>
<?php require_once __DIR__ . "/../partials/footer.php"; ?>
