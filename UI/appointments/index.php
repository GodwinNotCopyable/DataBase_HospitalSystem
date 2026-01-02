<?php
$title = "Appointment";
require_once __DIR__ . "/../partials/header.php";
$counts = ['patients'=>0,'doctors'=>0,'rooms'=>0,'medications'=>0,'appointments'=>(int)q($pdo,"SELECT COUNT(*) c FROM appointment")->fetch()['c'],'admissions'=>0,'dash'=>0];
require __DIR__ . "/../partials/sidebar.php";

if(isset($_GET['set'])){
  // set status
  $id = $_GET['id'] ?? '';
  $st = $_GET['set'] ?? '';
  if($id!=='' && in_array($st,['Scheduled','Completed','Cancelled'], true)){
    try{
      q($pdo,"UPDATE appointment SET AppointmentStatus=? WHERE AppointmentID=?",[$st,$id]);
      set_flash('','Status appointment diubah.');
    }catch(Exception $e){
      set_flash('error',"Gagal update status: ".$e->getMessage());
    }
  }
  redirect("/hospitalsystem/appointments/index.php");
}

$rows = q($pdo,"SELECT a.*, g.PatientName, d.DoctorName, d.Specialization
  FROM appointment a
  JOIN patientgeneralinfo g ON a.PatientID=g.PatientID
  JOIN doctordata d ON a.DoctorID=d.DoctorID
  ORDER BY a.AppointmentDate DESC, a.AppointmentID DESC")->fetchAll();

$flash = get_flash();
?>
<main class="main">
  <div class="topbar">
    <div>
      <h2>Appointment</h2>
      <div class="sub">Janji temu pasien dengan dokter.</div>
    </div>
    <div class="actions">
      <a class="btn primary" href="/hospitalsystem/appointments/create.php">+ Tambah</a>
    </div>
  </div>

  <div class="container">
    <?php if($flash): ?><div class="flash <?= h($flash['type']) ?>" data-auto-dismiss="4200"><?= h($flash['msg']) ?></div><?php endif; ?>
    <div class="card">
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>ID</th><th>Tanggal</th><th>Pasien</th><th>Dokter</th><th>Status</th><th>Catatan</th><th>Aksi</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach($rows as $r): ?>
            <tr>
              <td class="mono"><?= h($r['AppointmentID']) ?></td>
              <td><?= h($r['AppointmentDate']) ?></td>
              <td><b><?= h($r['PatientName']) ?></b><br/><small class="mono"><?= h($r['PatientID']) ?></small></td>
              <td><b><?= h($r['DoctorName']) ?></b><br/><small><?= h($r['Specialization']) ?> • <span class="mono"><?= h($r['DoctorID']) ?></span></small></td>
              <td>
                <span class="status"><span class="dot <?= h(status_dot_class($r['AppointmentStatus'])) ?>"></span><?= h($r['AppointmentStatus']) ?></span>
              </td>
              <td><small><?= h($r['AppointmentNote']) ?></small></td>
              <td>
                <div class="actions">
                  <a class="btn" href="/hospitalsystem/appointments/edit.php?id=<?= h($r['AppointmentID']) ?>">Edit</a>
                  <a class="btn" href="/hospitalsystem/appointments/index.php?set=Scheduled&id=<?= h($r['AppointmentID']) ?>">Scheduled</a>
                  <a class="btn" href="/hospitalsystem/appointments/index.php?set=Completed&id=<?= h($r['AppointmentID']) ?>">Completed</a>
                  <a class="btn danger" href="/hospitalsystem/appointments/index.php?set=Cancelled&id=<?= h($r['AppointmentID']) ?>" data-confirm="Batalkan appointment <?= h($r['AppointmentID']) ?>?">Cancel</a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if(!$rows): ?><tr><td colspan="7"><small>Tidak ada data.</small></td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</main>
<?php require_once __DIR__ . "/../partials/footer.php"; ?>
