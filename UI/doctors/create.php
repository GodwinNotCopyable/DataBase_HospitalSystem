<?php
$title = "Tambah Dokter";
require_once __DIR__ . "/../partials/header.php";
$counts = ['patients'=>0,'doctors'=>0,'rooms'=>0,'medications'=>0,'appointments'=>0,'admissions'=>0,'dash'=>0];
require __DIR__ . "/../partials/sidebar.php";

if($_SERVER['REQUEST_METHOD']==='POST'){
  $DoctorID = trim($_POST['DoctorID'] ?? '');
  if($DoctorID==='') $DoctorID = next_id($pdo,'doctordata','DoctorID','DO');

  try{
    q($pdo,"INSERT INTO doctordata (DoctorID, DoctorName, Specialization, DoctorPhone, DoctorEmail, DoctorAddress) VALUES (?,?,?,?,?,?)",[
      $DoctorID,
      trim($_POST['DoctorName'] ?? ''),
      trim($_POST['Specialization'] ?? ''),
      trim($_POST['DoctorPhone'] ?? ''),
      trim($_POST['DoctorEmail'] ?? ''),
      trim($_POST['DoctorAddress'] ?? ''),
    ]);
    set_flash('','Dokter ditambahkan: '.$DoctorID);
    redirect("/hospitalsystem/doctors/index.php");
  }catch(Exception $e){
    set_flash('error',"Gagal menambah dokter: ".$e->getMessage());
  }
}

$next = next_id($pdo,'doctordata','DoctorID','DO');
$flash = get_flash();
?>
<main class="main">
  <div class="topbar">
    <div>
      <h2>Tambah Dokter</h2>
    </div>
    <div class="actions">
      <a class="btn" href="/hospitalsystem/doctors/index.php">← Kembali</a>
    </div>
  </div>

  <div class="container">
    <?php if($flash): ?><div class="flash <?= h($flash['type']) ?>" data-auto-dismiss="5200"><?= h($flash['msg']) ?></div><?php endif; ?>
    <div class="card">
      <form class="form" method="post">
        <div class="grid cols-2">
          <div class="field">
            <label>DoctorID</label>
            <input name="DoctorID" value="<?= h($_POST['DoctorID'] ?? $next) ?>" placeholder="DO001" />
            <div class="help">Kosongkan untuk auto-generate.</div>
          </div>
          <div class="field">
            <label>Nama</label>
            <input required name="DoctorName" value="<?= h($_POST['DoctorName'] ?? '') ?>" />
            <div class="help" style="color: transparent;">_</div>
          </div>
        </div>
        <div class="grid cols-2">
          <div class="field">
            <label>Spesialisasi</label>
            <input required name="Specialization" value="<?= h($_POST['Specialization'] ?? '') ?>" placeholder="Cardiologist" />
          </div>
          <div class="field">
            <label>No. HP</label>
            <input required name="DoctorPhone" value="<?= h($_POST['DoctorPhone'] ?? '') ?>" />
          </div>
        </div>
        <div class="field">
          <label>Email</label>
          <input required type="email" name="DoctorEmail" value="<?= h($_POST['DoctorEmail'] ?? '') ?>" />
        </div>
        <div class="field">
          <label>Alamat</label>
          <textarea required name="DoctorAddress"><?= h($_POST['DoctorAddress'] ?? '') ?></textarea>
        </div>

        <div class="actions">
          <button class="btn primary" type="submit">Simpan</button>
          <a class="btn" href="/hospitalsystem/doctors/index.php">Batal</a>
        </div>
      </form>
    </div>
  </div>
</main>
<?php require_once __DIR__ . "/../partials/footer.php"; ?>
