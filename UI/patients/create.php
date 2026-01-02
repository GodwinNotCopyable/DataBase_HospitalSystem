<?php
$title = "Tambah Pasien";
require_once __DIR__ . "/../partials/header.php";

$counts = ['patients'=>0,'doctors'=>0,'rooms'=>0,'medications'=>0,'appointments'=>0,'admissions'=>0,'dash'=>0];
require __DIR__ . "/../partials/sidebar.php";

if($_SERVER['REQUEST_METHOD']==='POST'){
  $PatientID = trim($_POST['PatientID'] ?? '');
  if($PatientID==='') $PatientID = next_id($pdo,'patientgeneralinfo','PatientID','PA');

  $data = [
    'PatientID'=>$PatientID,
    'PatientName'=>trim($_POST['PatientName'] ?? ''),
    'PatientGender'=>($_POST['PatientGender'] ?? 'Others'),
    'PatientDOB'=>($_POST['PatientDOB'] ?? ''),
    'PatientPhone'=>trim($_POST['PatientPhone'] ?? ''),
    'PatientEmail'=>trim($_POST['PatientEmail'] ?? ''),
    'PatientEmergencyContact'=>trim($_POST['PatientEmergencyContact'] ?? ''),
    'PatientAddress'=>trim($_POST['PatientAddress'] ?? ''),
    'WeightInKg'=>(float)($_POST['WeightInKg'] ?? 0),
    'HeightInCm'=>(int)($_POST['HeightInCm'] ?? 0),
    'BloodType'=>($_POST['BloodType'] ?? null),
    'Allergies'=>trim($_POST['Allergies'] ?? ''),
  ];

  try{
    // gunakan stored procedure InsertPatient (insert 2 tabel sekaligus)
    q($pdo, "CALL InsertPatient(?,?,?,?,?,?,?,?,?,?,?,?)", [
      $data['PatientID'],
      $data['PatientName'],
      $data['PatientGender'],
      $data['PatientDOB'],
      $data['PatientPhone'],
      $data['PatientEmail'] !== '' ? $data['PatientEmail'] : null,
      $data['PatientEmergencyContact'],
      $data['PatientAddress'],
      $data['WeightInKg'],
      $data['HeightInCm'],
      $data['BloodType'],
      $data['Allergies'] !== '' ? $data['Allergies'] : null,
    ]);
    set_flash('','Pasien berhasil ditambahkan: '.$data['PatientID']);
    redirect("/hospitalsystem/patients/index.php");
  }catch(Exception $e){
    set_flash('error',"Gagal menambah pasien: ".$e->getMessage());
  }
}

$next = next_id($pdo,'patientgeneralinfo','PatientID','PA');
$flash = get_flash();
?>
<main class="main">
  <div class="topbar">
    <div>
      <h2>Tambah Pasien</h2>
    </div>
    <div class="actions">
      <a class="btn" href="/hospitalsystem/patients/index.php">← Kembali</a>
    </div>
  </div>

  <div class="container">
    <?php if($flash): ?>
      <div class="flash <?= h($flash['type']) ?>" data-auto-dismiss="5200"><?= h($flash['msg']) ?></div>
    <?php endif; ?>

    <div class="card">
      <form class="form" method="post">
        <div class="grid cols-2">
          <div class="field">
            <label>PatientID</label>
            <input name="PatientID" value="<?= h($_POST['PatientID'] ?? $next) ?>" placeholder="PA001" />
            <div class="help">Kosongkan untuk auto-generate.</div>
          </div>
          <div class="field">
            <label>Nama</label>
            <input required name="PatientName" value="<?= h($_POST['PatientName'] ?? '') ?>" />
            <div class="help" style="color: transparent;">_</div>
          </div>
        </div>

        <div class="grid cols-3">
          <div class="field">
            <label>Gender</label>
            <select name="PatientGender">
              <?php foreach(['Male','Female','Others'] as $g): ?>
                <option value="<?= h($g) ?>" <?= (($_POST['PatientGender'] ?? '')===$g?'selected':'') ?>><?= h($g) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field">
            <label>Tanggal Lahir</label>
            <input required type="date" name="PatientDOB" value="<?= h($_POST['PatientDOB'] ?? '') ?>" />
          </div>
          <div class="field">
            <label>No. HP</label>
            <input required name="PatientPhone" value="<?= h($_POST['PatientPhone'] ?? '') ?>" />
          </div>
        </div>

        <div class="grid cols-2">
          <div class="field">
            <label>Email (opsional)</label>
            <input type="email" name="PatientEmail" value="<?= h($_POST['PatientEmail'] ?? '') ?>" />
          </div>
          <div class="field">
            <label>Emergency Contact</label>
            <input required name="PatientEmergencyContact" value="<?= h($_POST['PatientEmergencyContact'] ?? '') ?>" />
          </div>
        </div>

        <div class="field">
          <label>Alamat</label>
          <textarea required name="PatientAddress"><?= h($_POST['PatientAddress'] ?? '') ?></textarea>
        </div>

        <div class="hr"></div>
        <h3 style="margin:0;">Info Medis (ringkas)</h3>

        <div class="grid cols-3">
          <div class="field">
            <label>Berat (kg)</label>
            <input required type="number" step="0.1" name="WeightInKg" value="<?= h($_POST['WeightInKg'] ?? '0') ?>" />
          </div>
          <div class="field">
            <label>Tinggi (cm)</label>
            <input required type="number" name="HeightInCm" value="<?= h($_POST['HeightInCm'] ?? '0') ?>" />
          </div>
          <div class="field">
            <label>Gol. Darah</label>
            <select name="BloodType">
              <?php foreach([null,'A+','A-','B+','B-','AB+','AB-','O+','O-'] as $b): ?>
                <option value="<?= h($b) ?>" <?= (($_POST['BloodType'] ?? '')===$b?'selected':'') ?>><?= h($b ?? '-') ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="field">
          <label>Alergi (opsional)</label>
          <textarea name="Allergies"><?= h($_POST['Allergies'] ?? '') ?></textarea>
        </div>

        <div class="actions">
          <button class="btn primary" type="submit">Simpan</button>
          <a class="btn" href="/hospitalsystem/patients/index.php">Batal</a>
        </div>
      </form>
    </div>
  </div>
</main>
<?php require_once __DIR__ . "/../partials/footer.php"; ?>
