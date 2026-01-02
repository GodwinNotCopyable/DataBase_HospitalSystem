<?php
$title = "Obat";
require_once __DIR__ . "/../partials/header.php";
$counts = ['patients'=>0,'doctors'=>0,'rooms'=>0,'medications'=>(int)q($pdo,"SELECT COUNT(*) c FROM medication")->fetch()['c'],'appointments'=>0,'admissions'=>0,'dash'=>0];
require __DIR__ . "/../partials/sidebar.php";

if(isset($_GET['delete'])){
  $id = $_GET['delete'];
  try{
    q($pdo,"DELETE FROM medication WHERE MedicationID=?",[$id]);
    set_flash('','Obat dihapus.');
  }catch(Exception $e){
    set_flash('error',"Gagal hapus (mungkin sedang direferensikan di prescription): ".$e->getMessage());
  }
  redirect("/hospitalsystem/medications/index.php");
}

$rows = q($pdo,"SELECT * FROM medication ORDER BY MedicationID DESC")->fetchAll();
$flash = get_flash();
?>
<main class="main">
  <div class="topbar">
    <div>
      <h2>Obat</h2>
      <div class="sub">Stok & harga obat.</div>
    </div>
    <div class="actions">
      <a class="btn primary" href="/hospitalsystem/medications/create.php">+ Tambah</a>
    </div>
  </div>

  <div class="container">
    <?php if($flash): ?><div class="flash <?= h($flash['type']) ?>" data-auto-dismiss="4200"><?= h($flash['msg']) ?></div><?php endif; ?>
    <div class="card">
      <div class="table-wrap">
        <table>
          <thead>
            <tr><th>ID</th><th>Nama</th><th>Stok</th><th>Harga</th><th>Produsen</th><th>Aksi</th></tr>
          </thead>
          <tbody>
          <?php foreach($rows as $r): ?>
            <tr>
              <td class="mono"><?= h($r['MedicationID']) ?></td>
              <td>
                <b><?= h($r['MedicationName']) ?></b><br/>
                <small><?= h($r['MedicationDescription']) ?></small>
              </td>
              <td><span class="tag"><?= h($r['Stock']) ?></span></td>
              <td class="mono">Rp <?= number_format((float)$r['MedicationPrice'],2,',','.') ?></td>
              <td><?= h($r['Producer'] ?? '-') ?></td>
              <td>
                <div class="actions">
                  <a class="btn" href="/hospitalsystem/medications/edit.php?id=<?= h($r['MedicationID']) ?>">Edit</a>
                  <a class="btn danger" data-confirm="Hapus obat <?= h($r['MedicationID']) ?>?" href="/hospitalsystem/medications/index.php?delete=<?= h($r['MedicationID']) ?>">Hapus</a>
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
