# 🗄️ Panduan Menjalankan Database (Database Guide)

Panduan ini menjelaskan langkah-langkah menyiapkan basis data **Hospital Management System** menggunakan **Laragon** (serta opsi XAMPP / MySQL CLI), penjelasan skema tabel, dan fitur database lanjutan yang ada di dalamnya.

---

## 📋 Informasi Database
* **Nama Database:** `finalprojectlab_hospitalsystem`
* **File Sumber:** `Data Dummy Lenkap.sql` (berada di root folder proyek)
* **Karakteristik:** Berisi 12 tabel, relasi Foreign Key, 3 Stored Procedure, 2 Function, 2 Trigger, dan data dummy siap pakai.

---

## 🚀 Cara Import Database di Laragon

Laragon sudah menyertakan MySQL/MariaDB dan aplikasi pengelola database bawaan (**HeidiSQL**) serta **Terminal** bawaan. Anda dapat memilih salah satu cara di bawah ini:

### Cara 1: Menggunakan HeidiSQL (Sangat Mudah / Visual)

1. Buka aplikasi **Laragon**, lalu klik tombol **Start All**.
2. Klik tombol **Database** pada Laragon. Aplikasi **HeidiSQL** akan terbuka otomatis.
3. Pada jendela koneksi HeidiSQL, klik tombol **Open** (default user `root`, password kosong, port `3306`).
4. **Buat Database Baru:**
   - Klik kanan pada nama sesi (misalnya `Unnamed` atau `localhost`) di panel sebelah kiri.
   - Pilih **Create new** > **Database**.
   - Beri nama: `finalprojectlab_hospitalsystem` lalu klik **OK**.
5. **Import File SQL:**
   - Klik database `finalprojectlab_hospitalsystem` yang baru dibuat di panel sebelah kiri.
   - Buka menu atas: **File** > **Load SQL file...** (atau tekan `Ctrl + O`).
   - Cari dan pilih file **`Data Dummy Lenkap.sql`**.
   - Jika muncul dialog konfirmasi ukuran file, pilih **Yes / Execute directly**.
   - Atau klik tombol icon **Play (Run / Execute SQL)** berwarna biru di toolbar (atau tekan tombol keyboard `F9`).
6. Selesai! Klik kanan pada database lalu pilih **Refresh (F5)**. Anda akan melihat 12 tabel, procedures, functions, dan triggers telah terisi.

---

### Cara 2: Menggunakan Terminal Laragon (Paling Cepat / 1 Langkah)

1. Buka **Laragon**, klik **Start All**.
2. Klik tombol **Terminal** pada jendela Laragon.
3. Masuk ke direktori proyek ini, lalu jalankan perintah berikut:
   ```bash
   mysql -u root -e "CREATE DATABASE IF NOT EXISTS finalprojectlab_hospitalsystem;"
   mysql -u root finalprojectlab_hospitalsystem < "Data Dummy Lenkap.sql"
   ```
4. Selesai! Basis data berhasil dibuat dan diisi dalam hitungan detik.

---

### Cara 3: Menggunakan phpMyAdmin (Alternatif)

Jika Anda telah memasang phpMyAdmin di Laragon (bisa diakses via `Menu > MySQL > phpMyAdmin` atau `http://localhost/phpmyadmin`):
1. Buka `http://localhost/phpmyadmin`.
2. Klik menu **New**, ketik nama database: `finalprojectlab_hospitalsystem`, lalu klik **Create**.
3. Pilih database `finalprojectlab_hospitalsystem` di menu samping.
4. Buka tab **Import**.
5. Klik **Choose File**, pilih file `Data Dummy Lenkap.sql`, lalu klik tombol **Import** di bagian bawah.

---

## 🔍 Struktur & Logika Basis Data

### 1. Daftar 12 Tabel Relasional
| No | Nama Tabel | Deskripsi |
|---|---|---|
| 1 | `patientgeneralinfo` | Identitas umum pasien (Nama, gender, tanggal lahir, kontak, alamat). |
| 2 | `patientmedicalinfo` | Informasi medis pasien (Golongan darah, berat/tinggi badan, riwayat alergi, kondisi kronis). |
| 3 | `doctordata` | Master dokter, kontak, dan spesialisasi medis. |
| 4 | `room` | Kamar rawat inap dan statusnya (`Available`, `Occupied`, `Under Maintenance`). |
| 5 | `medication` | Master data obat, produsen, harga satuan, dan jumlah stok. |
| 6 | `staffdata` | Data karyawan / staf administrasi rumah sakit. |
| 7 | `appointment` | Jadwal rawat jalan/konsultasi pasien dengan dokter. |
| 8 | `appointmentprescription` | Resep obat yang diberikan pada janji temu rawat jalan. |
| 9 | `appointmentbills` | Rincian tagihan biaya untuk janji temu rawat jalan. |
| 10 | `admission` | Pendaftaran rawat inap pasien ke kamar tertentu. |
| 11 | `admissionprescriptions` | Resep obat yang diberikan selama masa rawat inap. |
| 12 | `admissionbills` | Rincian tagihan total biaya selama rawat inap. |

---

### 2. Stored Procedures (Prosedur Tersimpan)
Aplikasi PHP memanggil prosedur-prosedur ini secara langsung untuk memastikan integritas data:

* **`InsertPatient`**
  * *Fungsi:* Menginput data ke dua tabel sekaligus (`patientgeneralinfo` dan `patientmedicalinfo`) dalam satu proses terpadu.
* **`InsertNewAppointment`**
  * *Fungsi:* Menambahkan appointment baru dengan status default `'Scheduled'`.
* **`InsertNewAdmission`**
  * *Fungsi:* Menambahkan data pasien rawat inap dan **secara otomatis mengubah status ruangan (`room`) terkait menjadi `'Occupied'`**.

---

### 3. Stored Functions
* **`CalculateTotalAdmissionCost(AdmissionID)`**
  * Menghitung total biaya obat-obatan yang dikonsumsi selama rawat inap.
* **`CalculateTotalAppointmentCost(AppointmentID)`**
  * Menghitung total biaya obat-obatan dari resep appointment.

---

### 4. Triggers (Otomatisasi Stok)
* **`UpdateStockAfterAdmission`**
  * Terpicu setelah penambahan baris resep rawat inap (`admissionprescriptions`). Mengurangi stok obat di tabel `medication` secara otomatis.
* **`UpdateStockAfterAppointment`**
  * Terpicu setelah penambahan baris resep rawat jalan (`appointmentprescription`). Mengurangi stok obat di tabel `medication` secara otomatis.

---

## ✅ Verifikasi Keberhasilan Setup

Untuk memastikan database sudah siap digunakan oleh Web UI:
1. Pastikan tabel `patientgeneralinfo`, `doctordata`, `room`, dan `medication` memiliki isi baris data dummy.
2. Pastikan 3 prosedur (`InsertPatient`, `InsertNewAppointment`, `InsertNewAdmission`) terlihat di daftar Stored Procedures.

Jika database sudah aktif, lanjutkan ke panduan web: **[README_UI.md](README_UI.md)**.
