# Dokumentasi Database PT Shafa Marwa Multiguna (db_ptsmm)

## Ringkasan
Database: `db_ptsmm`  
RDBMS: MySQL (MariaDB) via PDO  
Karakter set: `utf8mb4_unicode_ci`

---

## Akun Bawaan (Default Credentials)
Aplikasi telah dilengkapi dengan akun awal untuk pengujian:

| Role | Nama Lengkap | Email | Password |
|---|---|---|---|
| **Admin** | Administrator SMM | `admin@shafamarwa.com` | `admin123` |
| **Pengguna** | Budi Santoso | `user@shafamarwa.com` | `user123` |

> *Catatan Keamanan:* Semua password dienkripsi menggunakan fungsi native PHP `password_hash()` dengan algoritma BCRYPT.

---

## Struktur Tabel

1. **`users`**
   - `id_user` (BIGINT UNSIGNED, PK, AUTO_INCREMENT)
   - `nama_lengkap` (VARCHAR 100)
   - `email` (VARCHAR 150, UNIQUE)
   - `password` (VARCHAR 255)
   - `no_hp` (VARCHAR 20)
   - `alamat` (TEXT)
   - `role` (ENUM: 'Admin', 'Pengguna')
   - `status_akun` (ENUM: 'Aktif', 'Nonaktif')
   - `dibuat_pada` (TIMESTAMP)
   - `diperbarui_pada` (TIMESTAMP)

2. **`kategori`**
   - `id_kategori` (INT UNSIGNED, PK, AUTO_INCREMENT)
   - `nama_kategori` (VARCHAR 100, UNIQUE)
   - `deskripsi` (TEXT)
   - `dibuat_pada` (TIMESTAMP)

3. **`produk`**
   - `id_produk` (BIGINT UNSIGNED, PK, AUTO_INCREMENT)
   - `id_kategori` (INT UNSIGNED, FK -> kategori.id_kategori)
   - `nama_produk` (VARCHAR 150)
   - `slug` (VARCHAR 180, UNIQUE)
   - `deskripsi` (TEXT)
   - `harga` (DECIMAL 15,2)
   - `stok` (INT UNSIGNED)
   - `satuan` (VARCHAR 30)
   - `gambar` (VARCHAR 255)
   - `status_produk` (ENUM: 'Aktif', 'Tidak Aktif')
   - `tampil_di_beranda` (TINYINT, penanda pilihan Produk Unggulan Beranda; maksimal 8)
   - `dibuat_pada` (TIMESTAMP)
   - `diperbarui_pada` (TIMESTAMP)

4. **`pesanan`**
   - `id_pesanan` (BIGINT UNSIGNED, PK, AUTO_INCREMENT)
   - `kode_pesanan` (VARCHAR 30, UNIQUE)
   - `id_user` (BIGINT UNSIGNED, FK -> users.id_user)
   - `nama_penerima` (VARCHAR 100)
   - `no_hp_penerima` (VARCHAR 20)
   - `alamat_pengiriman` (TEXT)
   - `catatan` (TEXT)
   - `total_harga` (DECIMAL 15,2)
   - `status_pesanan` (ENUM: 'Menunggu Konfirmasi', 'Diproses', 'Dikirim', 'Selesai', 'Dibatalkan')
   - `tanggal_pesanan` (TIMESTAMP)
   - `diperbarui_pada` (TIMESTAMP)

5. **`detail_pesanan`**
   - `id_detail` (BIGINT UNSIGNED, PK, AUTO_INCREMENT)
   - `id_pesanan` (BIGINT UNSIGNED, FK -> pesanan.id_pesanan)
   - `id_produk` (BIGINT UNSIGNED, FK -> produk.id_produk)
   - `nama_produk_saat_pesan` (VARCHAR 150)
   - `harga_satuan` (DECIMAL 15,2)
   - `jumlah` (INT UNSIGNED)
   - `subtotal` (DECIMAL 15,2)

6. **`lowongan`**
   - `id_lowongan` (BIGINT UNSIGNED, PK, AUTO_INCREMENT)
   - `judul_lowongan` (VARCHAR 150)
   - `posisi` (VARCHAR 100)
   - `deskripsi` (TEXT)
   - `kualifikasi` (TEXT)
   - `lokasi` (VARCHAR 150)
   - `batas_lamaran` (DATE)
   - `status_lowongan` (ENUM: 'Dibuka', 'Ditutup')
   - `dibuat_pada` (TIMESTAMP)
   - `diperbarui_pada` (TIMESTAMP)

7. **`profil_perusahaan`**
   - `id_profil` (TINYINT UNSIGNED, PK, AUTO_INCREMENT)
   - `nama_perusahaan` (VARCHAR 150)
   - `deskripsi` (TEXT)
   - `alamat` (TEXT)
   - `email` (VARCHAR 150)
   - `no_telepon` (VARCHAR 30)
   - `website` (VARCHAR 150)
   - `logo` (VARCHAR 255)
   - `diperbarui_pada` (TIMESTAMP)

8. **`kontak_pesan`**
   - `id_pesan` (BIGINT UNSIGNED, PK, AUTO_INCREMENT)
   - `nama` (VARCHAR 100)
   - `email` (VARCHAR 150)
   - `subjek` (VARCHAR 200)
   - `pesan` (TEXT)
   - `status_pesan` (ENUM: 'Baru', 'Dibaca', 'Dibalas')
   - `dibuat_pada` (TIMESTAMP)

---

## Cara Import Ulang Manual
Melalui phpMyAdmin:
1. Buka `http://localhost/phpmyadmin/`.
2. Klik tab **Import**.
3. Pilih file `database/db_ptsmm.sql`.
4. Klik **Kirim / Go**.

