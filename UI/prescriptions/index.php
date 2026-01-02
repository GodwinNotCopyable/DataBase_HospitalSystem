<?php
$title = "Prescriptions";
require_once __DIR__ . "/../partials/header.php";
$counts = ['patients'=>0,'doctors'=>0,'rooms'=>0,'medications'=>0,'appointments'=>0,'admissions'=>0,'dash'=>0];
require __DIR__ . "/../partials/sidebar.php";
?>
<main class="main">
  <div class="topbar">
    <div>
      <h2>Prescriptions</h2>
      <div class="sub">Tambah obat yang diberikan untuk Admission atau Appointment.</div>
    </div>
    <div class="actions">
      <a class="btn" href="/hospitalsystem/reports/billing.php">Lihat Billing →</a>
    </div>
  </div>

  <div class="container">
    <div class="grid cols-2">
      <div class="card">
        <h3 style="margin:0 0 8px 0;">Admission Prescriptions</h3>
        <div class="help">Resep untuk pasien rawat inap.</div>
        <div class="hr"></div>
        <a class="btn primary" href="/hospitalsystem/prescriptions/admission.php">Kelola Admission →</a>
      </div>

      <div class="card">
        <h3 style="margin:0 0 8px 0;">Appointment Prescriptions</h3>
        <div class="help">Resep untuk pasien konsultasi.</div>
        <div class="hr"></div>
        <a class="btn primary" href="/hospitalsystem/prescriptions/appointment.php">Kelola Appointment →</a>
      </div>
    </div>
  </div>
</main>
<?php require_once __DIR__ . "/../partials/footer.php"; ?>
