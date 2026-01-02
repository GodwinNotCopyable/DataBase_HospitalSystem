<?php
$title = "Dokter";
require_once __DIR__ . "/../partials/header.php";
$counts = ['patients'=>0,'doctors'=>(int)q($pdo,"SELECT COUNT(*) c FROM doctordata")->fetch()['c'],'rooms'=>0,'medications'=>0,'appointments'=>0,'admissions'=>0,'dash'=>0];
require __DIR__ . "/../partials/sidebar.php";

$rows = q($pdo,"SELECT * FROM doctordata ORDER BY DoctorID DESC")->fetchAll();
$flash = get_flash();
?>
<main class="main">
  <div class="topbar">
    <div>
      <h2>Dokter</h2>
      <div class="sub">Data dokter & spesialisasi.</div>
    </div>
    <div class="actions">
      <a class="btn primary" href="/hospitalsystem/doctors/create.php">+ Tambah</a>
    </div>
  </div>

  <div class="container">
    <?php if($flash): ?>
      <div class="flash <?= h($flash['type']) ?>" data-auto-dismiss="4200"><?= h($flash['msg']) ?></div>
    <?php endif; ?>

    <div class="card">
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>ID</th><th>Nama</th><th>Spesialisasi</th><th>Kontak</th><th>Alamat</th><th>Aksi</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach($rows as $r): ?>
            <tr>
              <td class="mono"><?= h($r['DoctorID']) ?></td>
              <td><b><?= h($r['DoctorName']) ?></b><br/><small><?= h($r['DoctorEmail']) ?></small></td>
              <td><span class="tag"><?= h($r['Specialization']) ?></span></td>
              <td><?= h($r['DoctorPhone']) ?></td>
              <td><small><?= h($r['DoctorAddress']) ?></small></td>
              <td>
                <div class="actions">
                  <a class="btn" href="/hospitalsystem/doctors/edit.php?id=<?= h($r['DoctorID']) ?>">Edit</a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if(!$rows): ?><tr><td colspan="6"><small>Tidak ada data.</small></td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</main>
<?php require_once __DIR__ . "/../partials/footer.php"; ?>
