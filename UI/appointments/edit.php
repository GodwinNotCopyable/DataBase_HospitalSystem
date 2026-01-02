<?php
$title = "Edit Appointment";
require_once __DIR__ . "/../partials/header.php";
$counts = ['patients'=>0,'doctors'=>0,'rooms'=>0,'medications'=>0,'appointments'=>0,'admissions'=>0,'dash'=>0];
require __DIR__ . "/../partials/sidebar.php";

$id = $_GET['id'] ?? '';
if($id===''){ set_flash('error','AppointmentID tidak valid'); redirect('/hospitalsystem/appointments/index.php'); }

$row = q($pdo,"SELECT * FROM appointment WHERE AppointmentID=?",[$id])->fetch();
if(!$row){ set_flash('error','Data tidak ditemukan'); redirect('/hospitalsystem/appointments/index.php'); }

$patients = q($pdo,"SELECT PatientID, PatientName FROM patientgeneralinfo ORDER BY PatientName ASC")->fetchAll();
$doctors = q($pdo,"SELECT DoctorID, DoctorName, Specialization FROM doctordata ORDER BY DoctorName ASC")->fetchAll();

if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    q($pdo,"UPDATE appointment SET AppointmentDate=?, AppointmentNote=?, AppointmentStatus=?, PatientID=?, DoctorID=? WHERE AppointmentID=?",[
      $_POST['AppointmentDate'] ?? '',
      trim($_POST['AppointmentNote'] ?? ''),
      $_POST['AppointmentStatus'] ?? 'Scheduled',
      $_POST['PatientID'] ?? '',
      $_POST['DoctorID'] ?? '',
      $id
    ]);
    set_flash('','Perubahan disimpan.');
    redirect("/hospitalsystem/appointments/edit.php?id=".urlencode($id));
  }catch(Exception $e){
    set_flash('error',"Gagal update: ".$e->getMessage());
  }
  $row = q($pdo,"SELECT * FROM appointment WHERE AppointmentID=?",[$id])->fetch();
}

$flash = get_flash();
?>
<main class="main">
  <div class="topbar">
    <div>
      <h2>Edit Appointment</h2>
      <div class="sub">ID: <span class="mono"><?= h($id) ?></span></div>
    </div>
    <div class="actions">
      <a class="btn" href="/hospitalsystem/appointments/index.php">← Kembali</a>
    </div>
  </div>

  <div class="container">
    <?php if($flash): ?><div class="flash <?= h($flash['type']) ?>" data-auto-dismiss="5200"><?= h($flash['msg']) ?></div><?php endif; ?>
    <div class="card">
      <form class="form" method="post">
        <div class="grid cols-3">
          <div class="field">
            <label>Tanggal</label>
            <input required type="date" name="AppointmentDate" value="<?= h($row['AppointmentDate']) ?>" />
          </div>
          <div class="field">
            <label>Status</label>
            <select name="AppointmentStatus">
              <?php foreach(['Scheduled','Completed','Cancelled'] as $s): ?>
                <option value="<?= h($s) ?>" <?= ($row['AppointmentStatus']===$s?'selected':'') ?>><?= h($s) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field">
            <label>Dokter</label>
            <select required name="DoctorID">
              <?php foreach($doctors as $d): ?>
                <option value="<?= h($d['DoctorID']) ?>" <?= ($row['DoctorID']===$d['DoctorID']?'selected':'') ?>>
                  <?= h($d['DoctorName']) ?> - <?= h($d['Specialization']) ?> (<?= h($d['DoctorID']) ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="field">
          <label>Pasien</label>
          <select required name="PatientID">
            <?php foreach($patients as $p): ?>
              <option value="<?= h($p['PatientID']) ?>" <?= ($row['PatientID']===$p['PatientID']?'selected':'') ?>>
                <?= h($p['PatientName']) ?> (<?= h($p['PatientID']) ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="field">
          <label>Catatan</label>
          <textarea required name="AppointmentNote"><?= h($row['AppointmentNote']) ?></textarea>
        </div>

        <div class="actions">
          <button class="btn primary" type="submit">Simpan</button>
          <a class="btn" href="/hospitalsystem/appointments/index.php">Kembali</a>
        </div>
      </form>
    </div>
  </div>
</main>
<?php require_once __DIR__ . "/../partials/footer.php"; ?>
