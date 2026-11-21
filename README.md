# Sistem Informasi PT Shafa Marwa Multiguna (PT SMM)

Aplikasi berbasis **PHP Native + MySQL + PDO + Bootstrap 5** yang dirancang khusus untuk mengelola profil perusahaan, katalog aneka lini produk, pemesanan produk secara langsung, publikasi lowongan karir, serta panel administrasi terpadu untuk **PT Shafa Marwa Multiguna**.

---

## 1. Konsep Desain & Karakteristik
- **Tema Visual:** Modern Corporate Business Website — Premium, Elegant, Clean & Professional.
- **Palet Warna:**
  - *Primary:* Deep Navy Blue (`#0E2A47`)
  - *Secondary:* Soft Blue (`#2563EB`)
  - *Accent:* Sky Blue (`#60A5FA`)
  - *Background:* White (`#FFFFFF`) & Light Blue Gray (`#F8FAFC`)
  - *Text:* Dark Navy (`#0F172A`) & Slate (`#475569`)
- **Responsive:** Optimal di layar Desktop, Tablet, dan Smartphone.
- **Pintu Masuk Utama:** File `index.php` terletak di **ROOT PROJECT** (`/pt-smm/index.php`).

---

## 2. Struktur Direktori Proyek

```text
pt-smm/
│
├── index.php                          # Entry point utama publik (Beranda)
├── .htaccess                          # Aturan rewrite & header keamanan Apache
├── README.md                          # Dokumentasi lengkap sistem
│
├── config/
│   ├── database.php                   # Koneksi terpusat database MySQL via PDO
│   └── app.php                        # Inisialisasi session & konstanta global
│
├── frontend/
│   ├── pages/
│   │   ├── beranda.php                # Hero, highlight 16+ produk, layanan, lowongan
│   │   ├── profil.php                 # Visi misi, overview Way Halim, 5 pilar usaha
│   │   ├── katalog.php                # Filter kategori, pencarian, grid produk
│   │   ├── detail-produk.php          # Spesifikasi, foto, stok, tombol pemesanan
│   │   ├── lowongan.php               # Informasi rekrutmen karir & panduan lamaran
│   │   └── kontak.php                 # Informasi kantor, CS WA, formulir pesan
│   │
│   ├── auth/
│   │   ├── login.php                  # Form masuk akun Admin & Pengguna
│   │   ├── register.php               # Form registrasi pelanggan baru
│   │   └── logout.php                 # Terminasi session & pembersihan cookie
│   │
│   ├── admin/
│   │   ├── dashboard.php              # Statistik ringkasan, grafik, pesanan terbaru
│   │   ├── produk/                    # CRUD produk (index, tambah, edit, hapus)
│   │   ├── kategori/                  # CRUD kategori 5 lini produk
│   │   ├── pesanan/                   # Manajemen status transaksi & rincian nota
│   │   ├── lowongan/                  # CRUD lowongan pekerjaan
│   │   ├── pengguna/                  # Kelola hak akses, status aktif/nonaktif
│   │   └── pengaturan/                # Edit identitas & profil perusahaan
│   │
│   ├── pengguna/
│   │   ├── dashboard.php              # Dashboard ringkasan pelanggan
│   │   ├── pemesanan.php              # Form pemesanan produk & kalkulator subtotal
│   │   ├── riwayat-pesanan.php        # Daftar riwayat transaksi pesanan
│   │   ├── detail-pesanan.php         # Rincian nota transaksi & bukti cetak
│   │   └── profil.php                 # Perbarui kontak penerima & ubah password
│   │
│   └── includes/
│       ├── header.php                 # Global HTML head & asset CSS
│       ├── navbar.php                 # Global responsive corporate navbar & flash alert
│       ├── footer.php                 # Global corporate footer
│       ├── admin-header.php           # Header & Topbar Panel Admin
│       ├── admin-sidebar.php          # Sidebar navigasi Admin
│       ├── admin-footer.php           # Footer Admin
│       ├── pengguna-header.php        # Header Dashboard Pelanggan
│       ├── pengguna-sidebar.php       # Sidebar navigasi Pelanggan
│       └── pengguna-footer.php        # Footer Dashboard Pelanggan
│
├── backend/
│   ├── auth/                          # Logika autentikasi login, register, guard
│   ├── admin/                         # Controller pengolah aksi admin
│   ├── pengguna/                      # Controller pengolah aksi transaksi pelanggan
│   ├── proses/                        # Controller form publik (kontak dsb)
│   └── helpers/                       # Helper terpusat (url, upload, format rupiah, sanitasi)
│
├── assets/
│   ├── css/
│   │   ├── style.css                  # Styling utama corporate website
│   │   ├── admin.css                  # Styling executive dashboard admin
│   │   └── responsive.css             # Penyesuaian mobile & tablet
│   ├── js/
│   │   ├── main.js                    # Kalkulator dinamis & auto-dismiss flash
│   │   └── admin.js                   # Toggle sidebar & konfirmasi hapus data
│   ├── images/                        # Aset grafis & no-image fallback
│   └── uploads/                       # Direktori unggahan berkas (produk, lowongan, dsb)
│
└── database/
    ├── db_ptsmm.sql                   # Skema MySQL lengkap beserta seed data awal
    └── README_DATABASE.md             # Petunjuk impor & kamus data database
```

---

## 3. Akun Bawaan (Pengujian Default)

| Role | Email | Password | Hak Akses |
|---|---|---|---|
| **Admin** | `admin@shafamarwa.com` | `admin123` | Akses penuh Panel Dashboard Admin, CRUD Produk, Kategori, Status Pesanan, Lowongan, Pengguna, dan Profil Perusahaan. |
| **Pengguna** | `user@shafamarwa.com` | `user123` | Akses Dashboard Pelanggan, Form Pemesanan Mandiri, Riwayat Pesanan, Nota Transaksi, dan Edit Profil. |

---

## 4. Cara Menjalankan di Localhost XAMPP

1. Pastikan **Apache** dan **MySQL** telah aktif di *XAMPP Control Panel*.
2. Salin folder project ke direktori `C:\xampp\htdocs\pt-smm`.
3. Buka browser dan akses **phpMyAdmin** (`http://localhost/phpmyadmin/`).
4. Buat database baru bernama `db_ptsmm` (atau impor langsung file `database/db_ptsmm.sql`).
5. Buka aplikasi pada browser:
   - **Beranda Publik:** `http://localhost/pt-smm/`
   - **Halaman Masuk (Login):** `http://localhost/pt-smm/frontend/auth/login.php`
   - **Katalog Produk:** `http://localhost/pt-smm/frontend/pages/katalog.php`
   - **Profil Perusahaan:** `http://localhost/pt-smm/frontend/pages/profil.php`
   - **Lowongan Karir:** `http://localhost/pt-smm/frontend/pages/lowongan.php`
   - **Kontak Perusahaan:** `http://localhost/pt-smm/frontend/pages/kontak.php`

---

## 5. Fitur Utama

- **Pintu Masuk Terpadu:** Root `index.php` terhubung langsung dengan sistem navigasi corporate modern.
- **Katalog 5 Kategori Produk:** Pupuk Organik Pertanian, Produk Perikanan, Produk Herbal, Produk Kopi, dan Vitamin Anak dengan kalkulasi stok otomatis.
- **Transaksi Aman (Atomic PDO Transaction):** Pemesanan produk menggunakan locking row (`FOR UPDATE`) dan pengurangan stok real-time yang mencegah *overselling* / *race condition*.
- **Role-Based Access Control:** Pemisahan ketat antara sesi Admin dan Pengguna. Pengguna biasa diblokir dari rute Admin dan otomatis dialihkan.
- **Enkripsi Sandi Terstandar:** Memanfaatkan `password_hash()` BCRYPT native PHP.
- **Sistem Notifikasi Flash Terpusat:** Umpan balik langsung untuk setiap aksi penambahan, perubahan, dan pembatalan status.

