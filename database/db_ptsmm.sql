-- =========================================================
-- DATABASE: db_ptsmm
-- Sistem Informasi PT Shafa Marwa Multiguna
-- PHP Native + MySQL + PDO
-- =========================================================

CREATE DATABASE IF NOT EXISTS db_ptsmm
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE db_ptsmm;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS detail_pesanan;
DROP TABLE IF EXISTS pesanan;
DROP TABLE IF EXISTS produk;
DROP TABLE IF EXISTS kategori;
DROP TABLE IF EXISTS lowongan;
DROP TABLE IF EXISTS profil_perusahaan;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS kontak_pesan;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. USERS
CREATE TABLE users (
    id_user BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    google_id VARCHAR(100) DEFAULT NULL,
    avatar_url VARCHAR(500) DEFAULT NULL,
    password VARCHAR(255) NULL DEFAULT NULL,
    no_hp VARCHAR(20) DEFAULT NULL,
    alamat TEXT DEFAULT NULL,
    role ENUM('Admin', 'Pengguna') NOT NULL DEFAULT 'Pengguna',
    status_akun ENUM('Aktif', 'Nonaktif') NOT NULL DEFAULT 'Aktif',
    dibuat_pada TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    diperbarui_pada TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE INDEX idx_google_id (google_id)
) ENGINE=InnoDB;

-- 2. KATEGORI
CREATE TABLE kategori (
    id_kategori INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(100) NOT NULL UNIQUE,
    deskripsi TEXT DEFAULT NULL,
    dibuat_pada TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 3. PRODUK
CREATE TABLE produk (
    id_produk BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_kategori INT UNSIGNED NOT NULL,
    nama_produk VARCHAR(150) NOT NULL,
    slug VARCHAR(180) NOT NULL UNIQUE,
    deskripsi TEXT DEFAULT NULL,
    harga DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    stok INT UNSIGNED NOT NULL DEFAULT 0,
    berat INT UNSIGNED NOT NULL DEFAULT 500,
    satuan VARCHAR(30) DEFAULT 'pcs',
    gambar VARCHAR(255) DEFAULT NULL,
    status_produk ENUM('Aktif', 'Tidak Aktif') NOT NULL DEFAULT 'Aktif',
    tampil_di_beranda TINYINT(1) NOT NULL DEFAULT 0,
    dibuat_pada TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    diperbarui_pada TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_produk_kategori
        FOREIGN KEY (id_kategori) REFERENCES kategori(id_kategori)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_produk_kategori (id_kategori),
    INDEX idx_produk_status (status_produk)
) ENGINE=InnoDB;

-- 4. PESANAN
CREATE TABLE pesanan (
    id_pesanan BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kode_pesanan VARCHAR(30) NOT NULL UNIQUE,
    id_user BIGINT UNSIGNED NOT NULL,
    nama_penerima VARCHAR(100) NOT NULL,
    no_hp_penerima VARCHAR(20) NOT NULL,
    alamat_pengiriman TEXT NOT NULL,
    kurir VARCHAR(50) DEFAULT 'JNE',
    layanan_pengiriman VARCHAR(50) DEFAULT 'Reguler',
    catatan TEXT DEFAULT NULL,
    ongkir DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    total_berat INT UNSIGNED NOT NULL DEFAULT 0,
    total_harga DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    status_pesanan ENUM(
        'Menunggu Pembayaran',
        'Menunggu Konfirmasi',
        'Diproses',
        'Dikirim',
        'Selesai',
        'Dibatalkan'
    ) NOT NULL DEFAULT 'Menunggu Pembayaran',
    status_pembayaran ENUM(
        'Belum Dibayar',
        'Menunggu Pembayaran',
        'Lunas',
        'Kadaluarsa',
        'Gagal'
    ) NOT NULL DEFAULT 'Belum Dibayar',
    metode_pembayaran VARCHAR(50) DEFAULT NULL,
    snap_token VARCHAR(255) DEFAULT NULL,
    waktu_pembayaran DATETIME DEFAULT NULL,
    midtrans_transaction_id VARCHAR(100) DEFAULT NULL,
    nomor_resi VARCHAR(60) DEFAULT NULL,
    tanggal_pesanan TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    diperbarui_pada TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_pesanan_user
        FOREIGN KEY (id_user) REFERENCES users(id_user)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_pesanan_user (id_user),
    INDEX idx_pesanan_status (status_pesanan),
    INDEX idx_pesanan_tanggal (tanggal_pesanan)
) ENGINE=InnoDB;

-- 5. DETAIL PESANAN
CREATE TABLE detail_pesanan (
    id_detail BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_pesanan BIGINT UNSIGNED NOT NULL,
    id_produk BIGINT UNSIGNED NOT NULL,
    nama_produk_saat_pesan VARCHAR(150) NOT NULL,
    harga_satuan DECIMAL(15,2) NOT NULL,
    jumlah INT UNSIGNED NOT NULL,
    subtotal DECIMAL(15,2) NOT NULL,
    CONSTRAINT fk_detail_pesanan
        FOREIGN KEY (id_pesanan) REFERENCES pesanan(id_pesanan)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_detail_produk
        FOREIGN KEY (id_produk) REFERENCES produk(id_produk)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_detail_pesanan (id_pesanan),
    INDEX idx_detail_produk (id_produk)
) ENGINE=InnoDB;

-- 6. LOWONGAN
CREATE TABLE lowongan (
    id_lowongan BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    judul_lowongan VARCHAR(150) NOT NULL,
    posisi VARCHAR(100) NOT NULL,
    deskripsi TEXT NOT NULL,
    kualifikasi TEXT DEFAULT NULL,
    lokasi VARCHAR(150) DEFAULT NULL,
    batas_lamaran DATE DEFAULT NULL,
    status_lowongan ENUM('Dibuka', 'Ditutup') NOT NULL DEFAULT 'Dibuka',
    dibuat_pada TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    diperbarui_pada TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_lowongan_status (status_lowongan)
) ENGINE=InnoDB;

-- 7. PROFIL PERUSAHAAN
CREATE TABLE profil_perusahaan (
    id_profil TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_perusahaan VARCHAR(150) NOT NULL,
    deskripsi TEXT DEFAULT NULL,
    alamat TEXT DEFAULT NULL,
    email VARCHAR(150) DEFAULT NULL,
    no_telepon VARCHAR(30) DEFAULT NULL,
    website VARCHAR(150) DEFAULT NULL,
    logo VARCHAR(255) DEFAULT NULL,
    diperbarui_pada TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 8. KONTAK PESAN (Menampung aspirasi/pesan pengunjung)
CREATE TABLE kontak_pesan (
    id_pesan BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    subjek VARCHAR(200) NOT NULL,
    pesan TEXT NOT NULL,
    status_pesan ENUM('Baru', 'Dibaca', 'Dibalas') NOT NULL DEFAULT 'Baru',
    dibuat_pada TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =========================================================
-- DATA AWAL (SEED DATA)
-- =========================================================

-- Data Profil Perusahaan
INSERT INTO profil_perusahaan (id_profil, nama_perusahaan, deskripsi, alamat, email, no_telepon, website) VALUES
(1, 'PT Shafa Marwa Multiguna', 'PT Shafa Marwa Multiguna (PT SMM) merupakan perusahaan yang berlokasi di Way Halim, Bandar Lampung, yang bergerak di bidang pemasaran digital dan promosi produk terpadu. Perusahaan aktif mengelola penyiaran langsung (live streaming) melalui platform digital modern serta memasarkan produk-produk unggulan bernilai tinggi.', 'Jl. Kimaja No. 45, Way Halim, Bandar Lampung, Lampung 35141', 'kontak@shafamarwa.com', '+62 812-7890-1234', 'www.shafamarwa.com');

-- Data Akun Pengguna & Admin (Password: admin123 / user123)
INSERT INTO users (nama_lengkap, email, password, no_hp, alamat, role, status_akun) VALUES
('Administrator SMM', 'admin@shafamarwa.com', '$2y$10$ywv2R03PBlnt7LQ2nbR9Eeckh95GMaKWfBapscJIT2rNt7AYm.uKm', '081234567890', 'Kantor Pusat PT SMM, Way Halim, Bandar Lampung', 'Admin', 'Aktif'),
('Budi Santoso', 'user@shafamarwa.com', '$2y$10$DgmDihoHABD9GBVqe2guUe19fI1sE5xciF5C9La8DZbo78AysrISe', '085712345678', 'Jl. Teuku Umar No. 12, Kedaton, Bandar Lampung', 'Pengguna', 'Aktif');

-- Data Kategori
INSERT INTO kategori (id_kategori, nama_kategori, deskripsi) VALUES
(1, 'Pupuk Organik Pertanian', 'Produk pupuk organik berkualitas tinggi untuk meningkatkan kesuburan tanah dan hasil panen pertanian.'),
(2, 'Produk Perikanan', 'Suplemen, pakan, dan nutrisi organik pendukung budidaya ikan dan udang produktif.'),
(3, 'Produk Herbal', 'Suplemen dan ramuan herbal alami berkhasiat untuk menjaga kesehatan dan kebugaran tubuh.'),
(4, 'Produk Kopi', 'Biji kopi pilihan dan bubuk kopi khas nusantara dengan aroma kuat dan cita rasa premium.'),
(5, 'Vitamin Anak', 'Suplemen multivitamin dan nutrisi terbaik untuk mendukung tumbuh kembang anak secara optimal.');

-- Data Produk
INSERT INTO produk (id_kategori, nama_produk, slug, deskripsi, harga, stok, satuan, gambar, status_produk) VALUES
(1, 'Pupuk Organik Cair Bio-Harvest 1 Liter', 'pupuk-organik-cair-bio-harvest-1-liter', 'Pupuk organik cair konsentrat tinggi diperkaya unsur hara makro & mikro esensial untuk memacu pertumbuhan vegetatif dan generatif tanaman pangan serta hortikultura.', 65000.00, 150, 'botol', 'pupuk-cair-harvest.jpg', 'Aktif'),
(1, 'Pupuk Hayati Granul Super Subur 5 Kg', 'pupuk-hayati-granul-super-subur-5-kg', 'Formula granul slow-release dengan mikroba penambat nitrogen dan pelarut fosfat alami untuk restorasi kegemburan tanah pertanian.', 85000.00, 95, 'sak', 'pupuk-granul-subur.jpg', 'Aktif'),
(2, 'Pakan Ikan Apung Protein Tinggi Pro-Grow 10 Kg', 'pakan-ikan-apung-protein-tinggi-pro-grow-10-kg', 'Pakan apung berkualitas tinggi dengan kadar protein minimum 32%, memacu pertumbuhan lele, nila, dan gurame tanpa mencemari kolam.', 145000.00, 60, 'sak', 'pakan-ikan-progrow.jpg', 'Aktif'),
(2, 'Suplemen Probiotik Kolam Aqua-Shield 500 ml', 'suplemen-probiotik-kolam-aqua-shield-500-ml', 'Cairan probiotik pengurai amoniak dan penstabil kualitas air tambak serta kolam terpal air tawar.', 75000.00, 80, 'botol', 'probiotik-aqua-shield.jpg', 'Aktif'),
(3, 'Madu Hutan Murni Riau 500 Gram', 'madu-hutan-murni-riau-500-gram', 'Madu hutan mentah (raw honey) murni dari pedalaman hutan tropis tanpa campuran bahan pemanis, berkhasiat menjaga imunitas.', 95000.00, 120, 'botol', 'madu-hutan-murni.jpg', 'Aktif'),
(3, 'Kapsul Minyak Habbatussauda Extra Propolis 100 Butir', 'kapsul-minyak-habbatussauda-extra-propolis-100-butir', 'Kombinasi habbatussauda murni cold-pressed dengan ekstrak propolis trigona untuk stamina dan perlindungan antioksidan menyeluruh.', 110000.00, 85, 'botol', 'habbatussauda-propolis.jpg', 'Aktif'),
(4, 'Kopi Robusta Lampung Gold Roasted Beans 250g', 'kopi-robusta-lampung-gold-roasted-beans-250g', 'Biji kopi robusta pilihan dari dataran tinggi Lampung Barat dengan roasting medium-dark, body tebal dan sentuhan rasa cokelat karamel.', 48000.00, 200, 'pouch', 'kopi-robusta-lampung.jpg', 'Aktif'),
(4, 'Kopi Stamina Ginseng Purwoceng Premium 10 Sachet', 'kopi-stamina-ginseng-purwoceng-premium-10-sachet', 'Kopi herbal bubuk siap seduh dengan ekstrak ginseng panax dan akar purwoceng untuk memulihkan energi dan vitalitas pria aktif.', 60000.00, 110, 'box', 'kopi-ginseng-purwoceng.jpg', 'Aktif'),
(5, 'Sirup Curcuma Smart Plus Multivitamin 120 ml', 'sirup-curcuma-smart-plus-multivitamin-120-ml', 'Sirup rasa jeruk manis kaya ekstrak temulawak organik, minyak ikan kod (DHA/EPA), dan lysine untuk meningkatkan nafsu makan dan daya tahan anak.', 42000.00, 140, 'botol', 'sirup-curcuma-smart.jpg', 'Aktif'),
(5, 'Gummy Vitamin C & Zinc Kids 60 Tablet Kunyah', 'gummy-vitamin-c-zinc-kids-60-tablet-kunyah', 'Permen kunyah rasa stroberi lezat mengandung vitamin C dosis aman dan zinc untuk daya tahan fisik anak usia sekolah.', 55000.00, 75, 'botol', 'gummy-vitc-zinc.jpg', 'Aktif');

-- Pilihan awal Produk Unggulan yang tersebar antarkategori (dapat diubah dari admin)
UPDATE produk SET tampil_di_beranda = 1
WHERE slug IN (
    'pupuk-organik-cair-bio-harvest-1-liter',
    'pakan-ikan-apung-protein-tinggi-pro-grow-10-kg',
    'madu-hutan-murni-riau-500-gram',
    'kopi-robusta-lampung-gold-roasted-beans-250g',
    'sirup-curcuma-smart-plus-multivitamin-120-ml',
    'pupuk-hayati-granul-super-subur-5-kg',
    'suplemen-probiotik-kolam-aqua-shield-500-ml',
    'kapsul-minyak-habbatussauda-extra-propolis-100-butir'
);

-- Data Lowongan Pekerjaan
INSERT INTO lowongan (judul_lowongan, posisi, deskripsi, kualifikasi, lokasi, batas_lamaran, status_lowongan) VALUES
('Host Live Streaming TikTok Shop', 'Host Live Streaming', 'Bertanggung jawab memandu sesi siaran langsung (live streaming) di platform TikTok Shop untuk mempromosikan produk-produk PT SMM, berinteraksi dengan pemirsa, dan mencapai target konversi penjualan.', '- Pria / Wanita, usia maksimal 28 tahun.
- Komunikatif, berpenampilan menarik, percaya diri di depan kamera.
- Memiliki pemahaman tren media sosial dan live shopping.
- Bersedia bekerja dalam sistem shift di studio Way Halim.', 'Bandar Lampung', DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'Dibuka'),
('Digital Marketing Specialist', 'Digital Marketing', 'Merancang dan mengeksekusi kampanye iklan berbayar (Meta Ads, TikTok Ads), mengoptimalkan funnel penjualan, menganalisis performa konten, serta meningkatkan ROI pemasaran.', '- Pendidikan minimal D3/S1 semua jurusan.
- Berpengalaman minimal 1 tahun di bidang digital advertising.
- Mahir riset audiens, copywriting persuasif, dan dashboard analitik.
- Mampu bekerja dalam tim yang dinamis.', 'Bandar Lampung', DATE_ADD(CURDATE(), INTERVAL 25 DAY), 'Dibuka'),
('Staff Gudang & Logistik', 'Staff Gudang', 'Melakukan pengecekan fisik barang masuk dan keluar, mencatat inventaris stok gudang, mengemas pesanan pelanggan secara aman, serta berkoordinasi dengan kurir ekspedisi.', '- Pria, usia maksimal 30 tahun.
- Pendidikan minimal SMA/SMK sederajat.
- Teliti, disiplin, berstamina fisik prima.
- Diutamakan berdomisili di sekitar Way Halim / Sukarame.', 'Bandar Lampung', DATE_ADD(CURDATE(), INTERVAL 20 DAY), 'Dibuka');

-- Contoh Data Pesanan Awal untuk Statistik Dashboard
INSERT INTO pesanan (id_pesanan, kode_pesanan, id_user, nama_penerima, no_hp_penerima, alamat_pengiriman, catatan, total_harga, status_pesanan, tanggal_pesanan) VALUES
(1, 'PS-20261101-A1B2C3', 2, 'Budi Santoso', '085712345678', 'Jl. Teuku Umar No. 12, Kedaton, Bandar Lampung', 'Tolong packing kayu bubble wrap tebal', 190000.00, 'Menunggu Konfirmasi', '2026-11-01 10:15:00'),
(2, 'PS-20261102-D4E5F6', 2, 'Budi Santoso', '085712345678', 'Jl. Teuku Umar No. 12, Kedaton, Bandar Lampung', 'Kirim siang hari', 145000.00, 'Diproses', '2026-11-02 14:30:00');

INSERT INTO detail_pesanan (id_pesanan, id_produk, nama_produk_saat_pesan, harga_satuan, jumlah, subtotal) VALUES
(1, 5, 'Madu Hutan Murni Riau 500 Gram', 95000.00, 2, 190000.00),
(2, 3, 'Pakan Ikan Apung Protein Tinggi Pro-Grow 10 Kg', 145000.00, 1, 145000.00);
