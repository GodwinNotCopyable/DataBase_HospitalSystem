<?php
$title = "Pasien";
require_once __DIR__ . "/../partials/header.php";
$counts = [
  'patients' => (int)q($pdo,"SELECT COUNT(*) c FROM patientgeneralinfo")->fetch()['c'],
  'doctors' => (int)q($pdo,"SELECT COUNT(*) c FROM doctordata")->fetch()['c'],
  'rooms' => (int)q($pdo,"SELECT COUNT(*) c FROM room")->fetch()['c'],
  'medications' => (int)q($pdo,"SELECT COUNT(*) c FROM medication")->fetch()['c'],
  'appointments' => (int)q($pdo,"SELECT COUNT(*) c FROM appointment")->fetch()['c'],
  'admissions' => (int)q($pdo,"SELECT COUNT(*) c FROM admission")->fetch()['c'],
  'dash' => 0
];
require __DIR__ . "/../partials/sidebar.php";

$search = trim($_GET['q'] ?? "");
$params = [];
$sql = "SELECT g.*, m.WeightInKg, m.HeightInCm, m.BloodType, m.Allergies
        FROM patientgeneralinfo g
        JOIN patientmedicalinfo m ON g.PatientID=m.PatientID";
if($search !== ""){
  $sql .= " WHERE g.PatientID LIKE ? OR g.PatientName LIKE ? OR g.PatientPhone LIKE ?";
  $params = ["%$search%","%$search%","%$search%"];
}
$sql .= " ORDER BY g.PatientID DESC";
$rows = q($pdo, $sql, $params)->fetchAll();

$flash = get_flash();
?>
<main class="main">
  <div class="topbar">
    <div>
      <h2>Pasien</h2>
      <div class="sub">Data pasien.</div>
    </div>
    <div class="actions">
      <form method="get" style="display:flex; gap:10px; align-items:center;">
        <input name="q" value="<?= h($search) ?>" placeholder="Cari ID / Nama / No HP..." />
        <button class="btn" type="submit">Cari</button>
      </form>
      <a class="btn primary" href="/hospitalsystem/patients/create.php">+ Tambah</a>
    </div>
  </div>

  <?php if($flash): ?>
    <div class="container">
      <div class="flash <?= h($flash['type']) ?>" data-auto-dismiss="4200"><?= h($flash['msg']) ?></div>
    </div>
  <?php endif; ?>

  <div class="container">
    <div class="card">
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Nama</th>
              <th>Gender</th>
              <th>DOB</th>
              <th>Kontak</th>
              <th>Medis</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach($rows as $r): ?>
            <tr>
              <td class="mono"><?= h($r['PatientID']) ?></td>
              <td>
                <b><?= h($r['PatientName']) ?></b><br/>
                <small><?= h($r['PatientEmail'] ?? '-') ?></small>
              </td>
              <td><?= h($r['PatientGender']) ?></td>
              <td><?= h($r['PatientDOB']) ?></td>
              <td>
                <?= h($r['PatientPhone']) ?><br/>
                <small>EC: <?= h($r['PatientEmergencyContact']) ?></small>
              </td>
              <td>
                <span class="tag"><?= h($r['BloodType'] ?? '-') ?></span>
                <small style="display:block;margin-top:6px;">
                  <?= h($r['WeightInKg']) ?> kg • <?= h($r['HeightInCm']) ?> cm<br/>
                  Alergi: <?= h($r['Allergies'] ?? '-') ?>
                </small>
              </td>
              <td>
                <div class="actions">
                  <a class="btn" href="/hospitalsystem/patients/edit.php?id=<?= h($r['PatientID']) ?>">Edit</a>
                  <a class="btn" href="/hospitalsystem/patients/medications.php?id=<?= h($r['PatientID']) ?>">Riwayat Obat</a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if(!$rows): ?>
            <tr><td colspan="7"><small>Tidak ada data.</small></td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</main>
<?php require_once __DIR__ . "/../partials/footer.php"; ?>
