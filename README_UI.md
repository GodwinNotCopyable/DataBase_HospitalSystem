# 🖥️ Panduan Menjalankan & Menggunakan Web UI (Web Interface Guide)

Panduan ini menjelaskan cara memasang dan menjalankan antarmuka web **Hospital Management System** menggunakan **Laragon**, cara membuka halamannya di browser, serta panduan lengkap mencoba setiap fitur di aplikasinya.

---

## ⚙️ Prasyarat
1. **Laragon** sudah terpasang dan service **Apache** & **MySQL** dalam status aktif (**Start All**).
2. Database **`finalprojectlab_hospitalsystem`** sudah di-import sesuai panduan di **[README_DATABASE.md](README_DATABASE.md)**.

---

## 🚀 Langkah 1: Memasang Folder Web di Laragon

Aplikasi ini menggunakan tautan navigasi absolut berawalan `/hospitalsystem/...`. Agar semua tautan berfungsi tanpa error 404, file web harus diletakkan dalam folder bernama **`hospitalsystem`** di dalam direktori `www` Laragon.

### Cara Penempatan Folder (Paling Mudah):
1. Buka folder instalasi Laragon Anda (biasanya `C:\laragon\www\`).
2. Buat folder baru bernama **`hospitalsystem`**:
   ```text
   C:\laragon\www\hospitalsystem\
   ```
3. **Salin seluruh file dan subfolder yang ada di dalam folder `UI/` proyek ini** ke dalam folder `C:\laragon\www\hospitalsystem\`.
4. Pastikan struktur di dalam `C:\laragon\www\hospitalsystem\` tampak seperti ini:
   ```text
   C:\laragon\www\hospitalsystem\
   ├── index.php
   ├── test.txt
   ├── admissions/
   ├── appointments/
   ├── assets/
   ├── doctors/
   ├── inc/
   ├── medications/
   ├── partials/
   ├── patients/
   ├── prescriptions/
   ├── reports/
   └── rooms/
   ```

---

## 🔧 Langkah 2: Cek Konfigurasi Koneksi Database

Buka file `C:\laragon\www\hospitalsystem\inc\db.php` menggunakan teks editor (VS Code, Notepad, dll.) untuk memastikan konfigurasinya sesuai dengan standar Laragon:

```php
$DB_HOST = "127.0.0.1";
$DB_NAME = "finalprojectlab_hospitalsystem";
$DB_USER = "root";
$DB_PASS = ""; // Default Laragon adalah string kosong (tanpa password)
```
*(Pengaturan default ini sudah langsung cocok dengan Laragon tanpa perlu diubah)*.

---

## 🌐 Langkah 3: Membuka Aplikasi di Browser

Buka peramban web Anda (Google Chrome, Microsoft Edge, Mozilla Firefox) dan ketik alamat URL berikut:

```text
http://localhost/hospitalsystem/index.php
```

> [!TIP]
> **Catatan Pengguna Laragon:**
> Buka melalui **`http://localhost/hospitalsystem/index.php`** (bukan `hospitalsystem.test`). Hal ini dikarenakan navigasi menu di dalam kode aplikasi mengarah ke rute `/hospitalsystem/...`.

Jika berhasil, halaman **Dashboard Hospital System** dengan tema antarmuka modern bernuansa gelap (*clean dark theme*) akan langsung terbuka!

---

## 🧭 Panduan Penggunaan & Uji Coba Fitur Web

Berikut adalah skenario lengkap untuk mencoba operasional rumah sakit dari hulu ke hilir:

### 1. Dashboard (`/index.php`)
* Menampilkan kartu metrik (KPI): Total Pasien, Total Dokter, Total Obat.
* Menampilkan panel ringkasan ketersediaan kamar (*Available, Occupied, Under Maintenance*).
* Menampilkan status janji temu (*Scheduled, Completed, Cancelled*) dan status rawat inap (*In-house vs Discharged*).
* Tersedia tombol pintasan cepat di kanan atas: `+ Tambah Pasien`, `+ Admission`, `+ Appointment`.

### 2. Menu Pasien (`/patients/index.php`)
* **Melihat Data Pasien:** Menampilkan daftar pasien yang terdaftar.
* **Menambah Pasien Baru:**
  1. Klik tombol **+ Tambah Pasien**.
  2. Isi formulir informasi umum (Nama, Jenis Kelamin, Tanggal Lahir, No. HP, Email, Alamat).
  3. Isi data medis awal (Berat Badan, Tinggi Badan, Golongan Darah, Alergi).
  4. Klik **Simpan**. Sistem akan memanggil Stored Procedure `InsertPatient` untuk menyimpan ke dua tabel sekaligus.
* **Edit & Riwayat:** Dari tabel pasien, Anda dapat mengklik **Edit** untuk memperbarui rekam medis lengkap, atau tombol **Obat** untuk melihat riwayat obat yang pernah diresepkan ke pasien tersebut.

### 3. Menu Dokter (`/doctors/index.php`)
* Menampilkan daftar dokter beserta nomor kontak, email, dan bidang spesialisasi (misal: *Cardiologist, Pediatrician*).
* Anda dapat menambah atau mengedit dokter yang bertugas.

### 4. Menu Ruangan (`/rooms/index.php`)
* Menampilkan kamar rawat inap (Nomor Ruangan, Tipe Kamar seperti *VIP/Standard*, dan Status).
* Anda dapat mengubah status kamar secara manual jika kamar sedang dalam perbaikan (*Under Maintenance*).

### 5. Menu Obat & Stok (`/medications/index.php`)
* Menampilkan katalog obat, produsen (*Producer*), harga satuan, dan sisa stok.
* **Coba Fitur Otomatisasi Stok:** Perhatikan jumlah stok obat (misal: Paracetamol / Amoxicillin). Saat Anda memberi resep di menu Prescription, stok di halaman ini akan otomatis berkurang karena dipicu oleh **Trigger** database.

### 6. Menu Appointment / Rawat Jalan (`/appointments/index.php`)
* Digunakan untuk pasien yang ingin berkonsultasi dengan dokter tanpa menginap.
* **Membuat Appointment Baru:**
  1. Klik **+ Tambah Appointment**.
  2. Pilih Pasien dan Dokter dari menu dropdown.
  3. Tentukan tanggal janji temu dan catatan keluhan.
  4. Klik **Simpan** (otomatis berstatus *Scheduled* via Stored Procedure `InsertNewAppointment`).
* **Mengubah Status:** Anda dapat mengubah status janji temu menjadi *Completed* atau *Cancelled*.

### 7. Menu Admission / Rawat Inap (`/admissions/index.php`)
* Digunakan untuk pasien yang membutuhkan rawat inap di kamar rumah sakit.
* **Mendaftarkan Rawat Inap Baru:**
  1. Klik **+ Tambah Admission**.
  2. Pilih Pasien, Dokter penanggung jawab, dan Kamar yang bertatus *Available*.
  3. Isi diagnosis penyakit.
  4. Klik **Simpan** (Stored Procedure `InsertNewAdmission` akan mendaftarkan pasien dan **secara otomatis mengubah status kamar yang dipilih menjadi 'Occupied'**).
* **Pasien Pulang (Discharge):** Klik tombol **Discharge Pasien** jika masa rawat inap selesai. Sistem akan mengisi tanggal keluar dan mengembalikan status kamar menjadi *Available*.

### 8. Menu Prescriptions / Resep Obat (`/prescriptions/index.php`)
* Terbagi dua bagian: Resep untuk Pasien Rawat Inap (*Admission Prescriptions*) dan Resep untuk Pasien Rawat Jalan (*Appointment Prescriptions*).
* Klik **+ Resep Admission** atau **+ Resep Appointment**, lalu pilih obat yang diberikan beserta dosisnya.
* Setelah disimpan, stok obat di tabel master obat akan berkurang secara otomatis.

### 9. Menu Billing Report / Tagihan (`/reports/billing.php`)
* Menampilkan laporan keuangan dan tagihan pasien:
  * Tagihan Rawat Inap (*Admission Bills*)
  * Tagihan Rawat Jalan (*Appointment Bills*)
* Menghitung total tagihan berdasarkan akumulasi obat dan perawatan.
* Staf dapat mengubah status pembayaran (*Unpaid*, *Pending*, *Paid*) dan mencatat metode pembayaran (*Cash*, *Card*, *Transfer Bank*).

---

## ❓ Troubleshooting (Kendala Umum)

| Kendala | Penyebab | Solusi |
|---|---|---|
| **Koneksi DB gagal** | Layanan MySQL belum menyala atau nama database salah | Pastikan tombol *Start All* di Laragon sudah menyala, dan pastikan database di Laragon bernama `finalprojectlab_hospitalsystem`. |
| **Error 404 Not Found** saat klik menu | Folder tidak bernama `hospitalsystem` atau link rute salah | Pastikan isi folder `UI/` diletakkan langsung di `C:\laragon\www\hospitalsystem\`, lalu akses via `http://localhost/hospitalsystem/index.php`. |
| **Data Dropdown Pasien / Dokter Kosong** | Database belum diisi data dummy | Import ulang file `Data Dummy Lenkap.sql` melalui HeidiSQL atau Terminal Laragon sesuai panduan di **README_DATABASE.md**. |
| **Gagal Input Pasien / Admission (Procedure Error)** | Database di-import tanpa Stored Procedure | Pastikan meng-import file `Data Dummy Lenkap.sql` secara utuh karena file tersebut berisi stored procedure yang dibutuhkan oleh aplikasi web. |
