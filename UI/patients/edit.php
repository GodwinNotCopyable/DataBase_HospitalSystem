<?php
$title = "Edit Pasien";
require_once __DIR__ . "/../partials/header.php";
$counts = ['patients'=>0,'doctors'=>0,'rooms'=>0,'medications'=>0,'appointments'=>0,'admissions'=>0,'dash'=>0];
require __DIR__ . "/../partials/sidebar.php";

$id = $_GET['id'] ?? '';
if($id===''){ set_flash('error','PatientID tidak valid'); redirect('/hospitalsystem/patients/index.php'); }

$row = q($pdo,"SELECT g.*, m.WeightInKg, m.HeightInCm, m.BloodType, m.Allergies, 
  m.ChronicConditions, m.PastSurgeries, m.CurrentMedications, m.SmokingStatus, m.AlcoholUse, m.DisabilityStatus, m.ExtraNote
  FROM patientgeneralinfo g JOIN patientmedicalinfo m ON g.PatientID=m.PatientID
  WHERE g.PatientID=?",[$id])->fetch();
if(!$row){ set_flash('error','Data tidak ditemukan'); redirect('/hospitalsystem/patients/index.php'); }

if($_SERVER['REQUEST_METHOD']==='POST'){
  $g = [
    'PatientName'=>trim($_POST['PatientName'] ?? ''),
    'PatientGender'=>($_POST['PatientGender'] ?? 'Others'),
    'PatientDOB'=>($_POST['PatientDOB'] ?? ''),
    'PatientPhone'=>trim($_POST['PatientPhone'] ?? ''),
    'PatientEmail'=>trim($_POST['PatientEmail'] ?? ''),
    'PatientEmergencyContact'=>trim($_POST['PatientEmergencyContact'] ?? ''),
    'PatientAddress'=>trim($_POST['PatientAddress'] ?? ''),
  ];
  $m = [
    'WeightInKg'=>(float)($_POST['WeightInKg'] ?? 0),
    'HeightInCm'=>(int)($_POST['HeightInCm'] ?? 0),
    'BloodType'=>($_POST['BloodType'] ?? null),
    'Allergies'=>trim($_POST['Allergies'] ?? ''),
    'ChronicConditions'=>trim($_POST['ChronicConditions'] ?? ''),
    'PastSurgeries'=>trim($_POST['PastSurgeries'] ?? ''),
    'CurrentMedications'=>trim($_POST['CurrentMedications'] ?? ''),
    'SmokingStatus'=>($_POST['SmokingStatus'] ?? null),
    'AlcoholUse'=>($_POST['AlcoholUse'] ?? null),
    'DisabilityStatus'=>trim($_POST['DisabilityStatus'] ?? ''),
    'ExtraNote'=>trim($_POST['ExtraNote'] ?? ''),
  ];

  try{
    q($pdo,"UPDATE patientgeneralinfo SET PatientName=?, PatientGender=?, PatientDOB=?, PatientPhone=?, PatientEmail=?, PatientEmergencyContact=?, PatientAddress=? WHERE PatientID=?",[
      $g['PatientName'],$g['PatientGender'],$g['PatientDOB'],$g['PatientPhone'],
      $g['PatientEmail']!==''?$g['PatientEmail']:null,
      $g['PatientEmergencyContact'],$g['PatientAddress'],$id
    ]);
    q($pdo,"UPDATE patientmedicalinfo SET WeightInKg=?, HeightInCm=?, BloodType=?, Allergies=?, ChronicConditions=?, PastSurgeries=?, CurrentMedications=?, SmokingStatus=?, AlcoholUse=?, DisabilityStatus=?, ExtraNote=? WHERE PatientID=?",[
      $m['WeightInKg'],$m['HeightInCm'],$m['BloodType'],
      $m['Allergies']!==''?$m['Allergies']:null,
      $m['ChronicConditions']!==''?$m['ChronicConditions']:null,
      $m['PastSurgeries']!==''?$m['PastSurgeries']:null,
      $m['CurrentMedications']!==''?$m['CurrentMedications']:null,
      $m['SmokingStatus']!==''?$m['SmokingStatus']:null,
      $m['AlcoholUse']!==''?$m['AlcoholUse']:null,
      $m['DisabilityStatus']!==''?$m['DisabilityStatus']:null,
      $m['ExtraNote']!==''?$m['ExtraNote']:null,
      $id
    ]);
    set_flash('','Perubahan disimpan.');
    redirect("/hospitalsystem/patients/edit.php?id=".urlencode($id));
  }catch(Exception $e){
    set_flash('error',"Gagal update: ".$e->getMessage());
  }
  $row = q($pdo,"SELECT g.*, m.* FROM patientgeneralinfo g JOIN patientmedicalinfo m ON g.PatientID=m.PatientID WHERE g.PatientID=?",[$id])->fetch();
}

$flash = get_flash();
?>
<main class="main">
  <div class="topbar">
    <div>
      <h2>Edit Pasien</h2>
      <div class="sub">ID: <span class="mono"><?= h($id) ?></span></div>
    </div>
    <div class="actions">
      <a class="btn" href="/hospitalsystem/patients/index.php">← Kembali</a>
      <a class="btn" href="/hospitalsystem/patients/medications.php?id=<?= h($id) ?>">Riwayat Obat</a>
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
            <label>Nama</label>
            <input required name="PatientName" value="<?= h($row['PatientName']) ?>" />
          </div>
          <div class="field">
            <label>Gender</label>
            <select name="PatientGender">
              <?php foreach(['Male','Female','Others'] as $g): ?>
                <option value="<?= h($g) ?>" <?= ($row['PatientGender']===$g?'selected':'') ?>><?= h($g) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="grid cols-3">
          <div class="field">
            <label>Tanggal Lahir</label>
            <input required type="date" name="PatientDOB" value="<?= h($row['PatientDOB']) ?>" />
          </div>
          <div class="field">
            <label>No. HP</label>
            <input required name="PatientPhone" value="<?= h($row['PatientPhone']) ?>" />
          </div>
          <div class="field">
            <label>Email</label>
            <input type="email" name="PatientEmail" value="<?= h($row['PatientEmail'] ?? '') ?>" />
          </div>
        </div>

        <div class="grid cols-2">
          <div class="field">
            <label>Emergency Contact</label>
            <input required name="PatientEmergencyContact" value="<?= h($row['PatientEmergencyContact']) ?>" />
          </div>
          <div class="field">
            <label>Gol. Darah</label>
            <select name="BloodType">
              <?php foreach([null,'A+','A-','B+','B-','AB+','AB-','O+','O-'] as $b): ?>
                <option value="<?= h($b) ?>" <?= (($row['BloodType'] ?? '')===$b?'selected':'') ?>><?= h($b ?? '-') ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="field">
          <label>Alamat</label>
          <textarea required name="PatientAddress"><?= h($row['PatientAddress']) ?></textarea>
        </div>

        <div class="hr"></div>
        <h3 style="margin:0;">Info Medis</h3>

        <div class="grid cols-3">
          <div class="field">
            <label>Berat (kg)</label>
            <input required type="number" step="0.1" name="WeightInKg" value="<?= h($row['WeightInKg']) ?>" />
          </div>
          <div class="field">
            <label>Tinggi (cm)</label>
            <input required type="number" name="HeightInCm" value="<?= h($row['HeightInCm']) ?>" />
          </div>
          <div class="field">
            <label>Alergi</label>
            <input name="Allergies" value="<?= h($row['Allergies'] ?? '') ?>" />
          </div>
        </div>

        <div class="grid cols-2">
          <div class="field">
            <label>Chronic Conditions</label>
            <textarea name="ChronicConditions"><?= h($row['ChronicConditions'] ?? '') ?></textarea>
          </div>
          <div class="field">
            <label>Current Medications</label>
            <textarea name="CurrentMedications"><?= h($row['CurrentMedications'] ?? '') ?></textarea>
          </div>
        </div>

        <div class="grid cols-2">
          <div class="field">
            <label>Past Surgeries</label>
            <textarea name="PastSurgeries"><?= h($row['PastSurgeries'] ?? '') ?></textarea>
          </div>
          <div class="field">
            <label>Extra Note</label>
            <textarea name="ExtraNote"><?= h($row['ExtraNote'] ?? '') ?></textarea>
          </div>
        </div>

        <div class="grid cols-3">
          <div class="field">
            <label>Smoking Status</label>
            <select name="SmokingStatus">
              <?php foreach([null,'Never','Former','Current'] as $s): ?>
                <option value="<?= h($s) ?>" <?= (($row['SmokingStatus'] ?? '')===$s?'selected':'') ?>><?= h($s ?? '-') ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field">
            <label>Alcohol Use</label>
            <select name="AlcoholUse">
              <?php foreach([null,'None','Occasional','Regular'] as $s): ?>
                <option value="<?= h($s) ?>" <?= (($row['AlcoholUse'] ?? '')===$s?'selected':'') ?>><?= h($s ?? '-') ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field">
            <label>Disability Status</label>
            <input name="DisabilityStatus" value="<?= h($row['DisabilityStatus'] ?? '') ?>" />
          </div>
        </div>

        <div class="actions">
          <button class="btn primary" type="submit">Simpan</button>
          <a class="btn" href="/hospitalsystem/patients/index.php">Kembali</a>
        </div>
      </form>
    </div>
  </div>
</main>
<?php require_once __DIR__ . "/../partials/footer.php"; ?>
