<?php
$title = "Ruangan";
require_once __DIR__ . "/../partials/header.php";
$counts = ['patients'=>0,'doctors'=>0,'rooms'=>(int)q($pdo,"SELECT COUNT(*) c FROM room")->fetch()['c'],'medications'=>0,'appointments'=>0,'admissions'=>0,'dash'=>0];
require __DIR__ . "/../partials/sidebar.php";

if($_SERVER['REQUEST_METHOD']==='POST'){
  // update status / type
  $RoomNumber = $_POST['RoomNumber'] ?? '';
  if($RoomNumber !== ''){
    try{
      q($pdo,"UPDATE room SET RoomType=?, RoomStatus=? WHERE RoomNumber=?",[
        trim($_POST['RoomType'] ?? ''),
        $_POST['RoomStatus'] ?? 'Available',
        $RoomNumber
      ]);
      set_flash('','Ruangan diperbarui.');
      redirect("/hospitalsystem/rooms/index.php");
    }catch(Exception $e){
      set_flash('error',"Gagal update ruangan: ".$e->getMessage());
    }
  }
}

$rows = q($pdo,"SELECT * FROM room ORDER BY RoomNumber ASC")->fetchAll();
$flash = get_flash();
?>
<main class="main">
  <div class="topbar">
    <div>
      <h2>Ruangan</h2>
      <div class="sub">Status: Available / Occupied / Under Maintenance.</div>
    </div>
    <div class="actions">
      <a class="btn" href="/hospitalsystem/admissions/create.php">+ Admission (pakai ruangan)</a>
    </div>
  </div>

  <div class="container">
    <?php if($flash): ?><div class="flash <?= h($flash['type']) ?>" data-auto-dismiss="4200"><?= h($flash['msg']) ?></div><?php endif; ?>
    <div class="card">
      <div class="table-wrap">
        <table>
          <thead>
            <tr><th>No</th><th>Type</th><th>Status</th><th>Update</th></tr>
          </thead>
          <tbody>
          <?php foreach($rows as $r): ?>
            <tr>
              <td class="mono"><?= h($r['RoomNumber']) ?></td>
              <td><?= h($r['RoomType']) ?></td>
              <td>
                <span class="status"><span class="dot <?= h(status_dot_class($r['RoomStatus'])) ?>"></span><?= h($r['RoomStatus']) ?></span>
              </td>
              <td>
                <form method="post" class="actions">
                  <input type="hidden" name="RoomNumber" value="<?= h($r['RoomNumber']) ?>"/>
                  <input style="max-width:160px;" name="RoomType" value="<?= h($r['RoomType']) ?>"/>
                  <select name="RoomStatus">
                    <?php foreach(['Available','Occupied','Under Maintenance'] as $s): ?>
                      <option value="<?= h($s) ?>" <?= ($r['RoomStatus']===$s?'selected':'') ?>><?= h($s) ?></option>
                    <?php endforeach; ?>
                  </select>
                  <button class="btn primary" type="submit">Simpan</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if(!$rows): ?><tr><td colspan="4"><small>Tidak ada data.</small></td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</main>
<?php require_once __DIR__ . "/../partials/footer.php"; ?>
