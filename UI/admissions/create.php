<?php
$title = "Tambah Admission";
require_once __DIR__ . "/../partials/header.php";
$counts = ['patients'=>0,'doctors'=>0,'rooms'=>0,'medications'=>0,'appointments'=>0,'admissions'=>0,'dash'=>0];
require __DIR__ . "/../partials/sidebar.php";

$patients = q($pdo,"SELECT PatientID, PatientName FROM patientgeneralinfo ORDER BY PatientName ASC")->fetchAll();
$doctors = q($pdo,"SELECT DoctorID, DoctorName, Specialization FROM doctordata ORDER BY DoctorName ASC")->fetchAll();
$rooms = q($pdo,"SELECT RoomNumber, RoomType FROM room WHERE RoomStatus='Available' ORDER BY RoomNumber ASC")->fetchAll();

if($_SERVER['REQUEST_METHOD']==='POST'){
  $AdmissionID = trim($_POST['AdmissionID'] ?? '');
  if($AdmissionID==='') $AdmissionID = next_id($pdo,'admission','AdmissionID','AD');

  try{
    // gunakan stored procedure InsertNewAdmission (DischargeDate NULL dan RoomStatus otomatis Occupied)
    q($pdo,"CALL InsertNewAdmission(?,?,?,?,?,?)",[
      $AdmissionID,
      $_POST['AdmissionDate'] ?? '',
      trim($_POST['Diagnosis'] ?? ''),
      $_POST['PatientID'] ?? '',
      $_POST['DoctorID'] ?? '',
      $_POST['RoomNumber'] ?? '',
    ]);
    set_flash('','Admission dibuat: '.$AdmissionID);
    redirect("/hospitalsystem/admissions/index.php");
  }catch(Exception $e){
    set_flash('error',"Gagal membuat admission: ".$e->getMessage());
  }
}

$next = next_id($pdo,'admission','AdmissionID','AD');
$flash = get_flash();
?>
<main class="main">
  <div class="topbar">
    <div>
      <h2>Tambah Admission</h2>
    </div>
    <div class="actions">
      <a class="btn" href="/hospitalsystem/admissions/index.php">← Kembali</a>
    </div>
  </div>

  <div class="container">
    <?php if($flash): ?><div class="flash <?= h($flash['type']) ?>" data-auto-dismiss="5200"><?= h($flash['msg']) ?></div><?php endif; ?>
    <div class="card">
      <form class="form" method="post">
        <div class="grid cols-2">
          <div class="field">
            <label>AdmissionID</label>
            <input name="AdmissionID" value="<?= h($_POST['AdmissionID'] ?? $next) ?>" placeholder="AD001" />
            <div class="help">Kosongkan untuk auto-generate.</div>
          </div>
          <div class="field">
            <label>Tanggal Masuk</label>
            <input required type="date" name="AdmissionDate" value="<?= h($_POST['AdmissionDate'] ?? date('Y-m-d')) ?>" />
            <div class="help" style="color: transparent;">_</div>
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
          <label>Ruangan (hanya Available)</label>
          <select required name="RoomNumber">
            <option value="">-- pilih ruangan --</option>
            <?php foreach($rooms as $r): ?>
              <option value="<?= h($r['RoomNumber']) ?>" <?= (($_POST['RoomNumber'] ?? '')===$r['RoomNumber']?'selected':'') ?>>
                <?= h($r['RoomNumber']) ?> - <?= h($r['RoomType']) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <?php if(!$rooms): ?><div class="help">Tidak ada ruangan Available. Ubah status di menu Ruangan.</div><?php endif; ?>
        </div>

        <div class="field">
          <label>Diagnosis</label>
          <textarea required name="Diagnosis"><?= h($_POST['Diagnosis'] ?? '') ?></textarea>
        </div>

        <div class="actions">
          <button class="btn primary" type="submit" <?= (!$rooms ? 'disabled' : '') ?>>Simpan</button>
          <a class="btn" href="/hospitalsystem/admissions/index.php">Batal</a>
        </div>
      </form>
    </div>
  </div>
</main>
<?php require_once __DIR__ . "/../partials/footer.php"; ?>
