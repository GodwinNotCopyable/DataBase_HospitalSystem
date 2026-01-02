<?php
$title = "Edit Dokter";
require_once __DIR__ . "/../partials/header.php";
$counts = ['patients'=>0,'doctors'=>0,'rooms'=>0,'medications'=>0,'appointments'=>0,'admissions'=>0,'dash'=>0];
require __DIR__ . "/../partials/sidebar.php";

$id = $_GET['id'] ?? '';
if($id===''){ set_flash('error','DoctorID tidak valid'); redirect('/hospitalsystem/doctors/index.php'); }

$row = q($pdo,"SELECT * FROM doctordata WHERE DoctorID=?",[$id])->fetch();
if(!$row){ set_flash('error','Data tidak ditemukan'); redirect('/hospitalsystem/doctors/index.php'); }

if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    q($pdo,"UPDATE doctordata SET DoctorName=?, Specialization=?, DoctorPhone=?, DoctorEmail=?, DoctorAddress=? WHERE DoctorID=?",[
      trim($_POST['DoctorName'] ?? ''),
      trim($_POST['Specialization'] ?? ''),
      trim($_POST['DoctorPhone'] ?? ''),
      trim($_POST['DoctorEmail'] ?? ''),
      trim($_POST['DoctorAddress'] ?? ''),
      $id
    ]);
    set_flash('','Perubahan disimpan.');
    redirect("/hospitalsystem/doctors/edit.php?id=".urlencode($id));
  }catch(Exception $e){
    set_flash('error',"Gagal update: ".$e->getMessage());
  }
  $row = q($pdo,"SELECT * FROM doctordata WHERE DoctorID=?",[$id])->fetch();
}

$flash = get_flash();
?>
<main class="main">
  <div class="topbar">
    <div>
      <h2>Edit Dokter</h2>
      <div class="sub">ID: <span class="mono"><?= h($id) ?></span></div>
    </div>
    <div class="actions">
      <a class="btn" href="/hospitalsystem/doctors/index.php">← Kembali</a>
    </div>
  </div>

  <div class="container">
    <?php if($flash): ?><div class="flash <?= h($flash['type']) ?>" data-auto-dismiss="5200"><?= h($flash['msg']) ?></div><?php endif; ?>
    <div class="card">
      <form class="form" method="post">
        <div class="field">
          <label>Nama</label>
          <input required name="DoctorName" value="<?= h($row['DoctorName']) ?>" />
        </div>
        <div class="grid cols-2">
          <div class="field">
            <label>Spesialisasi</label>
            <input required name="Specialization" value="<?= h($row['Specialization']) ?>" />
          </div>
          <div class="field">
            <label>No. HP</label>
            <input required name="DoctorPhone" value="<?= h($row['DoctorPhone']) ?>" />
          </div>
        </div>
        <div class="field">
          <label>Email</label>
          <input required type="email" name="DoctorEmail" value="<?= h($row['DoctorEmail']) ?>" />
        </div>
        <div class="field">
          <label>Alamat</label>
          <textarea required name="DoctorAddress"><?= h($row['DoctorAddress']) ?></textarea>
        </div>
        <div class="actions">
          <button class="btn primary" type="submit">Simpan</button>
          <a class="btn" href="/hospitalsystem/doctors/index.php">Kembali</a>
        </div>
      </form>
    </div>
  </div>
</main>
<?php require_once __DIR__ . "/../partials/footer.php"; ?>
