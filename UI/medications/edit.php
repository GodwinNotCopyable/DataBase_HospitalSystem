<?php
$title = "Edit Obat";
require_once __DIR__ . "/../partials/header.php";
$counts = ['patients'=>0,'doctors'=>0,'rooms'=>0,'medications'=>0,'appointments'=>0,'admissions'=>0,'dash'=>0];
require __DIR__ . "/../partials/sidebar.php";

$id = $_GET['id'] ?? '';
if($id===''){ set_flash('error','MedicationID tidak valid'); redirect('/hospitalsystem/medications/index.php'); }

$row = q($pdo,"SELECT * FROM medication WHERE MedicationID=?",[$id])->fetch();
if(!$row){ set_flash('error','Data tidak ditemukan'); redirect('/hospitalsystem/medications/index.php'); }

if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    q($pdo,"UPDATE medication SET MedicationName=?, MedicationDescription=?, Stock=?, MedicationPrice=?, Producer=? WHERE MedicationID=?",[
      trim($_POST['MedicationName'] ?? ''),
      trim($_POST['MedicationDescription'] ?? ''),
      (int)($_POST['Stock'] ?? 0),
      (float)($_POST['MedicationPrice'] ?? 0),
      trim($_POST['Producer'] ?? '') !== '' ? trim($_POST['Producer']) : null,
      $id
    ]);
    set_flash('','Perubahan disimpan.');
    redirect("/hospitalsystem/medications/edit.php?id=".urlencode($id));
  }catch(Exception $e){
    set_flash('error',"Gagal update: ".$e->getMessage());
  }
  $row = q($pdo,"SELECT * FROM medication WHERE MedicationID=?",[$id])->fetch();
}
$flash = get_flash();
?>
<main class="main">
  <div class="topbar">
    <div>
      <h2>Edit Obat</h2>
      <div class="sub">ID: <span class="mono"><?= h($id) ?></span></div>
    </div>
    <div class="actions">
      <a class="btn" href="/hospitalsystem/medications/index.php">← Kembali</a>
    </div>
  </div>

  <div class="container">
    <?php if($flash): ?><div class="flash <?= h($flash['type']) ?>" data-auto-dismiss="5200"><?= h($flash['msg']) ?></div><?php endif; ?>
    <div class="card">
      <form class="form" method="post">
        <div class="field">
          <label>Nama</label>
          <input required name="MedicationName" value="<?= h($row['MedicationName']) ?>" />
        </div>
        <div class="field">
          <label>Deskripsi</label>
          <textarea required name="MedicationDescription"><?= h($row['MedicationDescription']) ?></textarea>
        </div>
        <div class="grid cols-3">
          <div class="field">
            <label>Stok</label>
            <input required type="number" name="Stock" value="<?= h($row['Stock']) ?>" />
          </div>
          <div class="field">
            <label>Harga (Rp)</label>
            <input required type="number" step="0.01" name="MedicationPrice" value="<?= h($row['MedicationPrice']) ?>" />
          </div>
          <div class="field">
            <label>Produsen</label>
            <input name="Producer" value="<?= h($row['Producer'] ?? '') ?>" />
          </div>
        </div>
        <div class="actions">
          <button class="btn primary" type="submit">Simpan</button>
          <a class="btn" href="/hospitalsystem/medications/index.php">Kembali</a>
        </div>
      </form>
    </div>
  </div>
</main>
<?php require_once __DIR__ . "/../partials/footer.php"; ?>
