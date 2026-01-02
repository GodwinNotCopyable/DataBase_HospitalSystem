<?php
$title = "Admission Prescriptions";
require_once __DIR__ . "/../partials/header.php";
$counts = ['patients'=>0,'doctors'=>0,'rooms'=>0,'medications'=>0,'appointments'=>0,'admissions'=>0,'dash'=>0];
require __DIR__ . "/../partials/sidebar.php";

// cek apakah kolom Quantity ada
$hasQty = false;
try{
  $col = q($pdo, "SHOW COLUMNS FROM admissionprescriptions LIKE 'Quantity'")->fetch();
  $hasQty = $col ? true : false;
}catch(Exception $e){ $hasQty=false; }

$admissions = q($pdo,"SELECT a.AdmissionID, g.PatientName, a.AdmissionDate, a.DischargeDate
  FROM admission a JOIN patientgeneralinfo g ON a.PatientID=g.PatientID
  ORDER BY a.AdmissionDate DESC, a.AdmissionID DESC")->fetchAll();

$meds = q($pdo,"SELECT MedicationID, MedicationName, Stock, MedicationPrice
  FROM medication ORDER BY MedicationName ASC")->fetchAll();

if($_SERVER['REQUEST_METHOD']==='POST'){
  $id = trim($_POST['AdmissionPrescriptionID'] ?? '');
  if($id==='') $id = next_id($pdo,'admissionprescriptions','AdmissionPrescriptionID','ADPR');

  $admissionID = $_POST['AdmissionID'] ?? '';
  $medID = $_POST['MedicationID'] ?? '';
  $dosage = trim($_POST['AdmissionDosageUsed'] ?? '');

  $qty = 1;
  if($hasQty){
    $qty = (int)($_POST['Quantity'] ?? 1);
    if($qty < 1) $qty = 1;
  }

  try{
    if($hasQty){
      q($pdo,"INSERT INTO admissionprescriptions (AdmissionPrescriptionID, AdmissionDosageUsed, MedicationID, Quantity, AdmissionID)
             VALUES (?,?,?,?,?)",[$id,$dosage,$medID,$qty,$admissionID]);
    } else {
      q($pdo,"INSERT INTO admissionprescriptions (AdmissionPrescriptionID, AdmissionDosageUsed, MedicationID, AdmissionID)
             VALUES (?,?,?,?)",[$id,$dosage,$medID,$admissionID]);
    }
    set_flash('','Prescription admission ditambahkan: '.$id);
    redirect("/hospitalsystem/prescriptions/admission.php");
  }catch(Exception $e){
    set_flash('error',"Gagal menambah prescription: ".$e->getMessage());
  }
}

$rows = q($pdo,"SELECT ap.AdmissionPrescriptionID, ap.AdmissionDosageUsed, ap.MedicationID, ap.AdmissionID"
  . ($hasQty ? ", ap.Quantity" : "") .
  ", m.MedicationName, m.MedicationPrice,
     a.AdmissionDate, a.DischargeDate, g.PatientName
  FROM admissionprescriptions ap
  JOIN medication m ON ap.MedicationID=m.MedicationID
  JOIN admission a ON ap.AdmissionID=a.AdmissionID
  JOIN patientgeneralinfo g ON a.PatientID=g.PatientID
  ORDER BY ap.AdmissionPrescriptionID DESC")->fetchAll();

$next = next_id($pdo,'admissionprescriptions','AdmissionPrescriptionID','ADPR');
$flash = get_flash();
?>
<main class="main">
  <div class="topbar">
    <div>
      <h2>Admission Prescriptions</h2>
      <div class="sub">Tambah obat untuk pasien rawat inap.</div>
    </div>
    <div class="actions">
      <a class="btn" href="/hospitalsystem/prescriptions/index.php">← Kembali</a>
      <a class="btn" href="/hospitalsystem/reports/billing.php?type=admission">Billing Admission →</a>
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
            <label>AdmissionPrescriptionID</label>
            <input name="AdmissionPrescriptionID" value="<?= h($_POST['AdmissionPrescriptionID'] ?? $next) ?>" placeholder="ADPR001"/>
            <div class="help">Kosongkan untuk auto-generate.</div>
          </div>

          <div class="field">
            <label>Admission</label>
            <select required name="AdmissionID">
              <option value="">-- pilih admission --</option>
              <?php foreach($admissions as $a): ?>
                <option value="<?= h($a['AdmissionID']) ?>">
                  <?= h($a['AdmissionID']) ?> • <?= h($a['PatientName']) ?> • <?= h($a['AdmissionDate']) ?><?= $a['DischargeDate'] ? ' (Closed)' : '' ?>
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
            <label>Dosage Used</label>
            <textarea required name="AdmissionDosageUsed"><?= h($_POST['AdmissionDosageUsed'] ?? '') ?></textarea>
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
                <th>ID</th><th>Admission</th><th>Pasien</th><th>Obat</th><?php if($hasQty): ?><th>Qty</th><?php endif; ?><th>Dosage</th><th>Harga</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($rows as $r): ?>
                <tr>
                  <td class="mono"><?= h($r['AdmissionPrescriptionID']) ?></td>
                  <td class="mono"><?= h($r['AdmissionID']) ?><br/><small><?= h($r['AdmissionDate']) ?></small></td>
                  <td><?= h($r['PatientName']) ?></td>
                  <td><b><?= h($r['MedicationName']) ?></b><br/><small class="mono"><?= h($r['MedicationID']) ?></small></td>
                  <?php if($hasQty): ?><td class="mono"><?= h($r['Quantity']) ?></td><?php endif; ?>
                  <td><small><?= h($r['AdmissionDosageUsed']) ?></small></td>
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
