<?php
$title = "Dashboard";
require_once __DIR__ . "/partials/header.php";

// counts
$counts = [
  'patients' => (int)q($pdo,"SELECT COUNT(*) c FROM patientgeneralinfo")->fetch()['c'],
  'doctors' => (int)q($pdo,"SELECT COUNT(*) c FROM doctordata")->fetch()['c'],
  'rooms' => (int)q($pdo,"SELECT COUNT(*) c FROM room")->fetch()['c'],
  'medications' => (int)q($pdo,"SELECT COUNT(*) c FROM medication")->fetch()['c'],
  'appointments' => (int)q($pdo,"SELECT COUNT(*) c FROM appointment")->fetch()['c'],
  'admissions' => (int)q($pdo,"SELECT COUNT(*) c FROM admission")->fetch()['c'],
  'dash' => 1
];
require __DIR__ . "/partials/sidebar.php";

// quick statuses
$rooms = q($pdo, "SELECT 
  SUM(RoomStatus='Available') AS available,
  SUM(RoomStatus='Occupied') AS occupied,
  SUM(RoomStatus='Under Maintenance') AS maint
  FROM room")->fetch();

$ap = q($pdo,"SELECT 
  SUM(AppointmentStatus='Scheduled') AS scheduled,
  SUM(AppointmentStatus='Completed') AS completed,
  SUM(AppointmentStatus='Cancelled') AS cancelled
  FROM appointment")->fetch();

$ad = q($pdo,"SELECT 
  SUM(DischargeDate IS NULL) AS inhouse,
  SUM(DischargeDate IS NOT NULL) AS discharged
  FROM admission")->fetch();

$flash = get_flash();
?>
<main class="main">
  <div class="topbar">
    <div>
      <h2>Dashboard</h2>
    </div>
    <div class="actions">
      <a class="btn primary" href="/hospitalsystem/patients/create.php">+ Tambah Pasien</a>
      <a class="btn" href="/hospitalsystem/admissions/create.php">+ Admission</a>
      <a class="btn" href="/hospitalsystem/appointments/create.php">+ Appointment</a>
    </div>
  </div>

  <?php if($flash): ?>
    <div class="flash <?= h($flash['type']) ?>" data-auto-dismiss="4200"><?= h($flash['msg']) ?></div>
  <?php endif; ?>

  <div class="container">
    <div class="grid cols-3">
      <div class="card kpi">
        <div>
          <div class="label">Total Pasien</div>
          <div class="num"><?= (int)$counts['patients'] ?></div>
        </div>
      </div>
      <div class="card kpi">
        <div>
          <div class="label">Total Dokter</div>
          <div class="num"><?= (int)$counts['doctors'] ?></div>
        </div>
      </div>
      <div class="card kpi">
        <div>
          <div class="label">Total Obat</div>
          <div class="num"><?= (int)$counts['medications'] ?></div>
        </div>
      </div>
    </div>

    <div class="grid cols-2" style="margin-top:14px;">
      <div class="card">
        <h3 style="margin:0 0 10px 0;">Status Ruangan</h3>
        <div class="grid" style="gap:10px;">
          <div class="status"><span class="dot ok"></span> Available: <b><?= (int)$rooms['available'] ?></b></div>
          <div class="status"><span class="dot warn"></span> Occupied: <b><?= (int)$rooms['occupied'] ?></b></div>
          <div class="status"><span class="dot bad"></span> Under Maintenance: <b><?= (int)$rooms['maint'] ?></b></div>
        </div>
        <div class="hr"></div>
        <a class="btn" href="/hospitalsystem/rooms/index.php">Kelola Ruangan →</a>
      </div>

      <div class="card">
        <h3 style="margin:0 0 10px 0;">Appointment & Admission</h3>
        <div class="grid" style="gap:10px;">
          <div class="status"><span class="dot warn"></span> Appointment Scheduled: <b><?= (int)$ap['scheduled'] ?></b></div>
          <div class="status"><span class="dot ok"></span> Appointment Completed: <b><?= (int)$ap['completed'] ?></b></div>
          <div class="status"><span class="dot bad"></span> Appointment Cancelled: <b><?= (int)$ap['cancelled'] ?></b></div>
          <div class="hr"></div>
          <div class="status"><span class="dot warn"></span> Pasien In-house: <b><?= (int)$ad['inhouse'] ?></b></div>
          <div class="status"><span class="dot ok"></span> Discharged: <b><?= (int)$ad['discharged'] ?></b></div>
        </div>
        <div class="hr"></div>
        <div class="actions">
          <a class="btn" href="/hospitalsystem/appointments/index.php">Daftar Appointment</a>
          <a class="btn" href="/hospitalsystem/admissions/index.php">Daftar Admission</a>
        </div>
      </div>
    </div>
  </div>
</main>
<?php require_once __DIR__ . "/partials/footer.php"; ?>
