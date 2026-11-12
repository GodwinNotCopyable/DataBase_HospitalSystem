# 🏥 Hospital Management System (Database & Web Application)

Selamat datang di proyek **Hospital Management System**, sebuah sistem informasi manajemen rumah sakit berbasis web yang mengintegrasikan perancangan basis data relasional (*relational database*) tingkat lanjut dengan antarmuka web (*Web UI*) modern berbasis **PHP Native (PDO)** dan **MySQL / MariaDB**.

Proyek ini sepenuhnya kompatibel dijalankan menggunakan **Laragon** (sangat direkomendasikan) maupun **XAMPP**.

---

## 📚 Pembagian Dokumentasi Proyek

Untuk mempermudah pemahaman dan pengujian, dokumentasi proyek ini dipisahkan menjadi 3 bagian yang saling melengkapi:

```text
├── 1. README.md              <- (Anda sedang di sini) Penjelasan umum proyek & arsitektur
├── 2. README_DATABASE.md     <- Panduan setup & menjalankan database (Laragon / HeidiSQL / CLI)
└── 3. README_UI.md           <- Panduan setup, membuka antarmuka web & simulasi operasional
```

| Dokumen | Isi & Tujuan | Tautan Cepat |
|---|---|---|
| **README 1 (Utama)** | Pengenalan proyek, fitur sistem, struktur file, dan arsitektur data. | [README.md](README.md) |
| **README 2 (Database)** | Cara import database ke Laragon (HeidiSQL/Terminal), skema 12 tabel, dan fungsi Stored Procedure/Trigger. | 📖 **[Buka README_DATABASE.md](README_DATABASE.md)** |
| **README 3 (Web UI)** | Cara menempatkan folder di `laragon/www/`, membuka web di browser, dan panduan mencoba seluruh fitur web. | 🖥️ **[Buka README_UI.md](README_UI.md)** |

---

## 🎯 Gambaran Umum Sistem

Sistem ini dirancang untuk menangani alur operasional inti rumah sakit secara efisien, transparan, dan terotomatisasi dari mulai pendaftaran pasien hingga proses penagihan (*billing*).

### Fitur & Modul Utama:
1. **Executive Dashboard:** Ringkasan jumlah pasien, dokter, obat, ketersediaan kamar, dan statistik status kunjungan.
2. **Manajemen Pasien & Rekam Medis:** Penyimpanan data umum dan data medis fisik/alergi/riwayat penyakit secara terpadu melalui Stored Procedure.
3. **Manajemen Dokter & Spesialisasi:** Informasi dokter penanggung jawab pelayanan (DPJP) beserta spesialisasi masing-masing.
4. **Manajemen Ruangan / Kamar:** Pemantauan status ketersediaan kamar rawat inap (*Available*, *Occupied*, *Under Maintenance*).
5. **Katalog Obat & Farmasi:** Data obat, harga satuan, dan pemotongan stok otomatis via **Trigger** database saat resep dibuat.
6. **Rawat Jalan (Appointment):** Penjadwalan janji temu konsultasi pasien dengan dokter spesialis.
7. **Rawat Inap (Admission):** Pendaftaran rawat inap pasien dengan perubahan otomatis status kamar menjadi *Occupied* saat pasien masuk dan kembali *Available* saat pasien dipulangkan (*Discharge*).
8. **Resep Obat (Prescriptions):** Pemberian resep untuk pasien rawat jalan maupun rawat inap.
9. **Laporan & Billing:** Pembuatan dan pencatatan tagihan biaya perawatan serta status pembayaran (*Paid*, *Unpaid*, *Pending*).

---

## 🏗️ Teknologi yang Digunakan

* **Backend / Scripting:** PHP 8.0+ (Menggunakan PDO untuk akses database yang aman dan terstruktur).
* **Database Management System:** MySQL / MariaDB (Kompatibel dengan Laragon & XAMPP).
* **Database Advanced Features:** Relasi Foreign Key, Stored Procedures, Functions, dan Event Triggers.
* **Frontend / UI:** HTML5, CSS3 kustom dengan tema modern (*Clean Dark Theme*), dan Vanilla JavaScript.
* **Development Stack:** **Laragon** (Apache, MySQL, HeidiSQL).

---

## 📁 Struktur Direktori Repositori

```text
DataBase_HospitalSystem/
├── README.md                    # Dokumentasi 1: Pengenalan & ringkasan proyek
├── README_DATABASE.md           # Dokumentasi 2: Panduan menjalankan database
├── README_UI.md                 # Dokumentasi 3: Panduan menjalankan & membuka Web UI
├── Data Dummy Lenkap.sql        # Database dump lengkap (Skema + Prosedur + Data Dummy)
└── UI/                          # Folder antarmuka web (PHP)
    ├── index.php                # Dashboard utama
    ├── admissions/              # Modul Rawat Inap (Admission)
    ├── appointments/            # Modul Rawat Jalan (Appointment)
    ├── doctors/                 # Modul Data Dokter
    ├── medications/             # Modul Obat & Farmasi
    ├── patients/                # Modul Data Pasien & Rekam Medis
    ├── prescriptions/           # Modul Resep Obat
    ├── reports/                 # Modul Laporan & Billing Pembayaran
    ├── rooms/                   # Modul Manajemen Kamar
    ├── inc/
    │   ├── db.php               # Konfigurasi koneksi database PDO
    │   └── functions.php        # Helper functions & Session Flash
    ├── partials/                # Komponen layout (Header, Sidebar, Footer)
    └── assets/                  # File CSS dan JavaScript pendukung
```

---

## ⚡ Langkah Cepat Memulai (Quickstart)

1. **Jalankan Laragon:** Klik tombol **Start All** pada aplikasi Laragon Anda.
2. **Import Database:** Ikuti panduan cepat di **[README_DATABASE.md](README_DATABASE.md)** untuk meng-import file `Data Dummy Lenkap.sql` ke database bernama `finalprojectlab_hospitalsystem`.
3. **Pasang Web UI:** Buat folder `C:\laragon\www\hospitalsystem\`, lalu salin seluruh isi folder `UI/` ke dalamnya (ikuti panduan di **[README_UI.md](README_UI.md)**).
4. **Buka di Browser:** Akses alamat:
   ```text
   http://localhost/hospitalsystem/index.php
   ```
