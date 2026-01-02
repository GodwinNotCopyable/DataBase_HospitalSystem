<?php
$title = "Tambah Obat";
require_once __DIR__ . "/../partials/header.php";
$counts = ['patients'=>0,'doctors'=>0,'rooms'=>0,'medications'=>0,'appointments'=>0,'admissions'=>0,'dash'=>0];
require __DIR__ . "/../partials/sidebar.php";

if($_SERVER['REQUEST_METHOD']==='POST'){
  $MedicationID = trim($_POST['MedicationID'] ?? '');
  if($MedicationID==='') $MedicationID = next_id($pdo,'medication','MedicationID','ME');

  try{
    q($pdo,"INSERT INTO medication (MedicationID, MedicationName, MedicationDescription, Stock, MedicationPrice, Producer) VALUES (?,?,?,?,?,?)",[
      $MedicationID,
      trim($_POST['MedicationName'] ?? ''),
      trim($_POST['MedicationDescription'] ?? ''),
      (int)($_POST['Stock'] ?? 0),
      (float)($_POST['MedicationPrice'] ?? 0),
      trim($_POST['Producer'] ?? '') !== '' ? trim($_POST['Producer']) : null
    ]);
    set_flash('','Obat ditambahkan: '.$MedicationID);
    redirect("/hospitalsystem/medications/index.php");
  }catch(Exception $e){
    set_flash('error',"Gagal menambah obat: ".$e->getMessage());
  }
}

$next = next_id($pdo,'medication','MedicationID','ME');
$flash = get_flash();
?>
<main class="main">
  <div class="topbar">
    <div>
      <h2>Tambah Obat</h2>
    </div>
    <div class="actions">
      <a class="btn" href="/hospitalsystem/medications/index.php">← Kembali</a>
    </div>
  </div>

  <div class="container">
    <?php if($flash): ?><div class="flash <?= h($flash['type']) ?>" data-auto-dismiss="5200"><?= h($flash['msg']) ?></div><?php endif; ?>
    <div class="card">
      <form class="form" method="post">
        <div class="grid cols-2">
          <div class="field">
            <label>MedicationID</label>
            <input name="MedicationID" value="<?= h($_POST['MedicationID'] ?? $next) ?>" placeholder="ME001" />
            <div class="help">Kosongkan untuk auto-generate.</div>
          </div>
          <div class="field">
            <label>Nama Obat</label>
            <input required name="MedicationName" value="<?= h($_POST['MedicationName'] ?? '') ?>" />
            <div class="help" style="color: transparent;">_</div>
          </div>
        </div>
        <div class="field">
          <label>Deskripsi</label>
          <textarea required name="MedicationDescription"><?= h($_POST['MedicationDescription'] ?? '') ?></textarea>
        </div>
        <div class="grid cols-3">
          <div class="field">
            <label>Stok</label>
            <input required type="number" name="Stock" value="<?= h($_POST['Stock'] ?? '0') ?>" />
          </div>
          <div class="field">
            <label>Harga (Rp)</label>
            <input required type="number" step="0.01" name="MedicationPrice" value="<?= h($_POST['MedicationPrice'] ?? '0') ?>" />
          </div>
          <div class="field">
            <label>Produsen (opsional)</label>
            <input name="Producer" value="<?= h($_POST['Producer'] ?? '') ?>" />
          </div>
        </div>
        <div class="actions">
          <button class="btn primary" type="submit">Simpan</button>
          <a class="btn" href="/hospitalsystem/medications/index.php">Batal</a>
        </div>
      </form>
    </div>
  </div>
</main>
<?php require_once __DIR__ . "/../partials/footer.php"; ?>
