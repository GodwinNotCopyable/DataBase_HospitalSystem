<?php
// partials/sidebar.php
// Optional badges (counts)
$counts = $counts ?? [];
?>
<aside class="sidebar">
  <div class="brand">
    <div class="logo">H+</div>
    <div>
      <h1 style="font-size:large">Hospital System</h1>
    </div>
  </div>

  <nav class="nav">
    <a class="<?= nav_active('/hospitalsystem/index.php') ?>" href="/hospitalsystem/index.php">
      Dashboard
    </a>
    <a class="<?= nav_active('/hospitalsystem/patients') ?>" href="/hospitalsystem/patients/index.php">
      Pasien
    </a>
    <a class="<?= nav_active('/hospitalsystem/doctors') ?>" href="/hospitalsystem/doctors/index.php">
      Dokter
    </a>
    <a class="<?= nav_active('/hospitalsystem/rooms') ?>" href="/hospitalsystem/rooms/index.php">
      Ruangan
    </a>
    <a class="<?= nav_active('/hospitalsystem/medications') ?>" href="/hospitalsystem/medications/index.php">
      Obat
    </a>
    <a class="<?= nav_active('/hospitalsystem/appointments') ?>" href="/hospitalsystem/appointments/index.php">
      Appointment
    </a>
    <a class="<?= nav_active('/hospitalsystem/admissions') ?>" href="/hospitalsystem/admissions/index.php">
      Admission
    </a>
        <a class="<?= nav_active('/hospitalsystem/prescriptions') ?>" href="/hospitalsystem/prescriptions/index.php">
      Prescriptions
    </a>
    <a class="<?= nav_active('/hospitalsystem/reports') ?>" href="/hospitalsystem/reports/billing.php">
      Billing Report
    </a>
  </nav>
</aside>
