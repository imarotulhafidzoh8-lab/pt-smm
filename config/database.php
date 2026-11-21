<?php
// ============================================================
// FILE    : config/database.php
// FUNGSI  : Koneksi Terpusat MySQL via PDO (db_ptsmm)
// PROYEK  : Sistem Informasi PT Shafa Marwa Multiguna (PT SMM)
// ============================================================

$db_host = app_env('DB_HOST', 'localhost');
$db_user = app_env('DB_USER', 'root');
$db_pass = app_env('DB_PASS', '');
$db_name = app_env('DB_NAME', 'db_ptsmm');
$charset = "utf8mb4";

$dsn = "mysql:host={$db_host};dbname={$db_name};charset={$charset}";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (PDOException $e) {
    error_log('[PT-SMM] Database connection failed: ' . $e->getMessage());
    http_response_code(500);
    die("Layanan sedang mengalami gangguan. Silakan coba kembali nanti.");
}

date_default_timezone_set("Asia/Jakarta");

// ============================================================
// Auto-Migration Schema Checker (Self-Healing Database)
// Menjamin semua kolom baru (Google OAuth, Midtrans, Kurir)
// selalu otomatis dibuat tanpa harus jalankan script manual.
// ============================================================
try {
    // 1. Cek tabel pesanan
    $cols_pesanan = [];
    $stmt_cp = $pdo->query("SHOW COLUMNS FROM pesanan");
    while ($r = $stmt_cp->fetch()) {
        $cols_pesanan[] = strtolower($r['Field']);
    }

    // Update status_pesanan ENUM
    try {
        $pdo->exec("ALTER TABLE pesanan MODIFY COLUMN status_pesanan ENUM('Menunggu Pembayaran', 'Menunggu Konfirmasi', 'Diproses', 'Dikirim', 'Selesai', 'Dibatalkan') NOT NULL DEFAULT 'Menunggu Pembayaran'");
    } catch (PDOException $e) {}

    if (!in_array('kurir', $cols_pesanan, true)) {
        $pdo->exec("ALTER TABLE pesanan ADD COLUMN kurir VARCHAR(50) NULL DEFAULT 'JNE' AFTER alamat_pengiriman");
    }
    if (!in_array('layanan_pengiriman', $cols_pesanan, true)) {
        $pdo->exec("ALTER TABLE pesanan ADD COLUMN layanan_pengiriman VARCHAR(50) NULL DEFAULT 'Reguler' AFTER kurir");
    }
    if (!in_array('ongkir', $cols_pesanan, true)) {
        $pdo->exec("ALTER TABLE pesanan ADD COLUMN ongkir DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER catatan");
    }
    if (!in_array('total_berat', $cols_pesanan, true)) {
        $pdo->exec("ALTER TABLE pesanan ADD COLUMN total_berat INT UNSIGNED NOT NULL DEFAULT 0 AFTER ongkir");
    }
    if (!in_array('status_pembayaran', $cols_pesanan, true)) {
        $pdo->exec("ALTER TABLE pesanan ADD COLUMN status_pembayaran ENUM('Belum Dibayar', 'Menunggu Pembayaran', 'Lunas', 'Kadaluarsa', 'Gagal') NOT NULL DEFAULT 'Belum Dibayar' AFTER status_pesanan");
    }
    if (!in_array('metode_pembayaran', $cols_pesanan, true)) {
        $pdo->exec("ALTER TABLE pesanan ADD COLUMN metode_pembayaran VARCHAR(50) NULL DEFAULT NULL AFTER status_pembayaran");
    }
    if (!in_array('snap_token', $cols_pesanan, true)) {
        $pdo->exec("ALTER TABLE pesanan ADD COLUMN snap_token VARCHAR(255) NULL DEFAULT NULL AFTER metode_pembayaran");
    }
    if (!in_array('waktu_pembayaran', $cols_pesanan, true)) {
        $pdo->exec("ALTER TABLE pesanan ADD COLUMN waktu_pembayaran DATETIME NULL DEFAULT NULL AFTER snap_token");
    }
    if (!in_array('midtrans_transaction_id', $cols_pesanan, true)) {
        $pdo->exec("ALTER TABLE pesanan ADD COLUMN midtrans_transaction_id VARCHAR(100) NULL DEFAULT NULL AFTER waktu_pembayaran");
    }
    if (!in_array('nomor_resi', $cols_pesanan, true)) {
        $pdo->exec("ALTER TABLE pesanan ADD COLUMN nomor_resi VARCHAR(60) NULL DEFAULT NULL AFTER midtrans_transaction_id");
    }

    // 2. Cek tabel produk (berat)
    $cols_produk = [];
    $stmt_pr = $pdo->query("SHOW COLUMNS FROM produk");
    while ($r = $stmt_pr->fetch()) {
        $cols_produk[] = strtolower($r['Field']);
    }
    if (!in_array('berat', $cols_produk, true)) {
        $pdo->exec("ALTER TABLE produk ADD COLUMN berat INT UNSIGNED NOT NULL DEFAULT 500 AFTER stok");
    }
    if (!in_array('tampil_di_beranda', $cols_produk, true)) {
        $pdo->exec("ALTER TABLE produk ADD COLUMN tampil_di_beranda TINYINT(1) NOT NULL DEFAULT 0 AFTER status_produk");
        // Pilih awal maksimal 8 produk secara bergiliran antarkategori agar Beranda tidak kosong.
        $produk_aktif_per_kategori = [];
        $stmt_produk_awal = $pdo->query("SELECT id_produk, id_kategori FROM produk WHERE status_produk = 'Aktif' ORDER BY id_kategori ASC, dibuat_pada DESC, id_produk DESC");
        foreach ($stmt_produk_awal->fetchAll() as $produk_awal) {
            $produk_aktif_per_kategori[$produk_awal['id_kategori']][] = (int)$produk_awal['id_produk'];
        }

        $id_produk_unggulan_awal = [];
        for ($urutan_awal = 0; $urutan_awal < 5 && count($id_produk_unggulan_awal) < 8; $urutan_awal++) {
            foreach ($produk_aktif_per_kategori as $id_produk_kategori) {
                if (isset($id_produk_kategori[$urutan_awal])) {
                    $id_produk_unggulan_awal[] = $id_produk_kategori[$urutan_awal];
                    if (count($id_produk_unggulan_awal) >= 8) {
                        break;
                    }
                }
            }
        }

        if (!empty($id_produk_unggulan_awal)) {
            $placeholder_produk = implode(',', array_fill(0, count($id_produk_unggulan_awal), '?'));
            $stmt_pilih_produk_awal = $pdo->prepare("UPDATE produk SET tampil_di_beranda = 1 WHERE id_produk IN ($placeholder_produk)");
            $stmt_pilih_produk_awal->execute($id_produk_unggulan_awal);
        }
    }

    // 3. Cek tabel users (google_id, avatar_url, password null)
    $cols_users = [];
    $stmt_us = $pdo->query("SHOW COLUMNS FROM users");
    while ($r = $stmt_us->fetch()) {
        $cols_users[] = strtolower($r['Field']);
    }
    if (!in_array('google_id', $cols_users, true)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN google_id VARCHAR(100) NULL DEFAULT NULL AFTER email");
    }
    if (!in_array('avatar_url', $cols_users, true)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN avatar_url VARCHAR(500) NULL DEFAULT NULL AFTER google_id");
    }
    $pdo->exec("ALTER TABLE users MODIFY COLUMN password VARCHAR(255) NULL DEFAULT NULL");

    // 4. Kolom Biteship di tabel pesanan
    if (!in_array('kode_pos_tujuan', $cols_pesanan, true)) {
        $pdo->exec("ALTER TABLE pesanan ADD COLUMN kode_pos_tujuan VARCHAR(10) NULL DEFAULT NULL AFTER alamat_pengiriman");
    }
    if (!in_array('kurir_code', $cols_pesanan, true)) {
        $pdo->exec("ALTER TABLE pesanan ADD COLUMN kurir_code VARCHAR(30) NULL DEFAULT NULL AFTER kurir");
    }
    if (!in_array('kurir_service', $cols_pesanan, true)) {
        $pdo->exec("ALTER TABLE pesanan ADD COLUMN kurir_service VARCHAR(50) NULL DEFAULT NULL AFTER kurir_code");
    }
    if (!in_array('biteship_order_id', $cols_pesanan, true)) {
        $pdo->exec("ALTER TABLE pesanan ADD COLUMN biteship_order_id VARCHAR(100) NULL DEFAULT NULL AFTER midtrans_transaction_id");
    }
    if (!in_array('tracking_history', $cols_pesanan, true)) {
        $pdo->exec("ALTER TABLE pesanan ADD COLUMN tracking_history JSON NULL DEFAULT NULL AFTER biteship_order_id");
    }

    // 5. Tabel Pengunjung (Visitor Counter Real-Time)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `pengunjung` (
            `id_pengunjung` INT AUTO_INCREMENT PRIMARY KEY,
            `ip_address` VARCHAR(45) NOT NULL,
            `user_agent` VARCHAR(255) NULL,
            `halaman` VARCHAR(100) NOT NULL DEFAULT 'beranda',
            `tanggal` DATE NOT NULL,
            `created_at` DATETIME NOT NULL,
            INDEX `idx_tanggal` (`tanggal`),
            INDEX `idx_ip_tanggal` (`ip_address`, `tanggal`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

} catch (Exception $e) {
    // Log error migration ke error_log PHP (tidak break halaman)
    error_log("[PT-SMM Auto-Migration Error] " . $e->getMessage());
}

