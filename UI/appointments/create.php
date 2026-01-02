<?php
$title = "Tambah Appointment";
require_once __DIR__ . "/../partials/header.php";
$counts = ['patients'=>0,'doctors'=>0,'rooms'=>0,'medications'=>0,'appointments'=>0,'admissions'=>0,'dash'=>0];
require __DIR__ . "/../partials/sidebar.php";

$patients = q($pdo,"SELECT PatientID, PatientName FROM patientgeneralinfo ORDER BY PatientName ASC")->fetchAll();
$doctors = q($pdo,"SELECT DoctorID, DoctorName, Specialization FROM doctordata ORDER BY DoctorName ASC")->fetchAll();

if($_SERVER['REQUEST_METHOD']==='POST'){
  $AppointmentID = trim($_POST['AppointmentID'] ?? '');
  if($AppointmentID==='') $AppointmentID = next_id($pdo,'appointment','AppointmentID','AP');

  try{
    // gunakan stored procedure InsertNewAppointment (status otomatis 'Scheduled')
    q($pdo,"CALL InsertNewAppointment(?,?,?,?,?)",[
      $AppointmentID,
      $_POST['AppointmentDate'] ?? '',
      trim($_POST['AppointmentNote'] ?? ''),
      $_POST['PatientID'] ?? '',
      $_POST['DoctorID'] ?? '',
    ]);
    set_flash('','Appointment dibuat: '.$AppointmentID);
    redirect("/hospitalsystem/appointments/index.php");
  }catch(Exception $e){
    set_flash('error',"Gagal membuat appointment: ".$e->getMessage());
  }
}

$next = next_id($pdo,'appointment','AppointmentID','AP');
$flash = get_flash();
?>
<main class="main">
  <div class="topbar">
    <div>
      <h2>Tambah Appointment</h2>
    </div>
    <div class="actions">
      <a class="btn" href="/hospitalsystem/appointments/index.php">← Kembali</a>
    </div>
  </div>

  <div class="container">
    <?php if($flash): ?><div class="flash <?= h($flash['type']) ?>" data-auto-dismiss="5200"><?= h($flash['msg']) ?></div><?php endif; ?>
    <div class="card">
      <form class="form" method="post">
        <div class="grid cols-2">
          <div class="field">
            <label>AppointmentID</label>
            <input name="AppointmentID" value="<?= h($_POST['AppointmentID'] ?? $next) ?>" placeholder="AP001" />
            <div class="help">Kosongkan untuk auto-generate.</div>
          </div>
          <div class="field">
            <label>Tanggal</label>
            <input required type="date" name="AppointmentDate" value="<?= h($_POST['AppointmentDate'] ?? date('Y-m-d')) ?>" />
            <div class="help" style="color: transparent;">Kosongkan untuk auto-generate.</div>
          </div>
        </div>

        <div class="grid cols-2">
          <div class="field">
            <label>Pasien</label>
            <select required name="PatientID">
              <option value="">-- pilih pasien --</option>
              <?php foreach($patients as $p): ?>
                <option value="<?= h($p['PatientID']) ?>" <?= (($_POST['PatientID'] ?? '')===$p['PatientID']?'selected':'') ?>>
                  <?= h($p['PatientName']) ?> (<?= h($p['PatientID']) ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field">
            <label>Dokter</label>
            <select required name="DoctorID">
              <option value="">-- pilih dokter --</option>
              <?php foreach($doctors as $d): ?>
                <option value="<?= h($d['DoctorID']) ?>" <?= (($_POST['DoctorID'] ?? '')===$d['DoctorID']?'selected':'') ?>>
                  <?= h($d['DoctorName']) ?> - <?= h($d['Specialization']) ?> (<?= h($d['DoctorID']) ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="field">
          <label>Catatan</label>
          <textarea required name="AppointmentNote"><?= h($_POST['AppointmentNote'] ?? '') ?></textarea>
        </div>

        <div class="actions">
          <button class="btn primary" type="submit">Simpan</button>
          <a class="btn" href="/hospitalsystem/appointments/index.php">Batal</a>
        </div>
      </form>
    </div>
  </div>
</main>
<?php require_once __DIR__ . "/../partials/footer.php"; ?>
