<?php
$title = "Admission";
require_once __DIR__ . "/../partials/header.php";
$counts = ['patients'=>0,'doctors'=>0,'rooms'=>0,'medications'=>0,'appointments'=>0,'admissions'=>(int)q($pdo,"SELECT COUNT(*) c FROM admission")->fetch()['c'],'dash'=>0];
require __DIR__ . "/../partials/sidebar.php";

if(isset($_GET['discharge'])){
  $id = $_GET['discharge'] ?? '';
  if($id!==''){
    try{
      // discharge today and set room available again
      $ad = q($pdo,"SELECT RoomNumber FROM admission WHERE AdmissionID=?",[$id])->fetch();
      q($pdo,"UPDATE admission SET DischargeDate=? WHERE AdmissionID=?", [date('Y-m-d'), $id]);
      if($ad && $ad['RoomNumber']){
        q($pdo,"UPDATE room SET RoomStatus='Available' WHERE RoomNumber=?",[$ad['RoomNumber']]);
      }
      set_flash('','Pasien discharged: '.$id);
    }catch(Exception $e){
      set_flash('error',"Gagal discharge: ".$e->getMessage());
    }
  }
  redirect("/hospitalsystem/admissions/index.php");
}

$rows = q($pdo,"SELECT a.*, g.PatientName, d.DoctorName, r.RoomType, r.RoomStatus
  FROM admission a
  JOIN patientgeneralinfo g ON a.PatientID=g.PatientID
  JOIN doctordata d ON a.DoctorID=d.DoctorID
  JOIN room r ON a.RoomNumber=r.RoomNumber
  ORDER BY a.AdmissionDate DESC, a.AdmissionID DESC")->fetchAll();

$flash = get_flash();
?>
<main class="main">
  <div class="topbar">
    <div>
      <h2>Admission</h2>
      <div class="sub">Pasien rawat inap.</div>
    </div>
    <div class="actions">
      <a class="btn primary" href="/hospitalsystem/admissions/create.php">+ Admission</a>
    </div>
  </div>

  <div class="container">
    <?php if($flash): ?><div class="flash <?= h($flash['type']) ?>" data-auto-dismiss="4200"><?= h($flash['msg']) ?></div><?php endif; ?>
    <div class="card">
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>ID</th><th>Masuk</th><th>Keluar</th><th>Pasien</th><th>Dokter</th><th>Ruangan</th><th>Diagnosis</th><th>Aksi</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach($rows as $r): ?>
            <tr>
              <td class="mono"><?= h($r['AdmissionID']) ?></td>
              <td><?= h($r['AdmissionDate']) ?></td>
              <td><?= h($r['DischargeDate'] ?? '-') ?></td>
              <td><b><?= h($r['PatientName']) ?></b><br/><small class="mono"><?= h($r['PatientID']) ?></small></td>
              <td><b><?= h($r['DoctorName']) ?></b><br/><small class="mono"><?= h($r['DoctorID']) ?></small></td>
              <td>
                <b class="mono"><?= h($r['RoomNumber']) ?></b> <span class="tag"><?= h($r['RoomType']) ?></span><br/>
                <small>Status: <?= h($r['RoomStatus']) ?></small>
              </td>
              <td><small><?= h($r['Diagnosis']) ?></small></td>
              <td>
                <div class="actions">
                  <?php if($r['DischargeDate']===null): ?>
                    <a class="btn primary" data-confirm="Discharge pasien untuk admission <?= h($r['AdmissionID']) ?> (tanggal hari ini)?" href="/hospitalsystem/admissions/index.php?discharge=<?= h($r['AdmissionID']) ?>">Discharge</a>
                  <?php else: ?>
                    <span class="tag">Closed</span>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if(!$rows): ?><tr><td colspan="8"><small>Tidak ada data.</small></td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</main>
<?php require_once __DIR__ . "/../partials/footer.php"; ?>
