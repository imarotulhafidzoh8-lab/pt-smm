<?php
// ============================================================
// FILE    : database/run-migration-kurir.php
// FUNGSI  : Script migrasi database untuk Kurir, Ongkir & Midtrans (All-In-One)
// CARA    : Akses sekali via browser: http://localhost/pt-smm/database/run-migration-kurir.php
// ============================================================
require_once __DIR__ . '/../config/app.php';

// Keamanan: hanya bisa diakses dari localhost
$allowed_ips = ['127.0.0.1', '::1', 'localhost'];
$client_ip   = $_SERVER['REMOTE_ADDR'] ?? '';
if (!in_array($client_ip, $allowed_ips) && $client_ip !== '') {
    http_response_code(403);
    die('Akses ditolak. Script ini hanya dapat dijalankan dari localhost.');
}

$results = [];
$errors  = [];

// 1. Kolom berat pada tabel produk
$produk_columns = [];
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM produk");
    while ($row = $stmt->fetch()) {
        $produk_columns[] = strtolower($row['Field']);
    }
} catch (PDOException $e) {
    die("Gagal membaca tabel produk: " . $e->getMessage());
}

if (!in_array('berat', $produk_columns, true)) {
    try {
        $pdo->exec("ALTER TABLE produk ADD COLUMN berat INT UNSIGNED NOT NULL DEFAULT 500 AFTER stok");
        $results[] = "✅ Tambah kolom berat pada tabel produk (default 500 gram)";
    } catch (PDOException $e) {
        $errors[] = "❌ Tambah kolom berat (produk): " . $e->getMessage();
    }
} else {
    $results[] = "ℹ️ Kolom berat pada produk sudah ada sebelumnya";
}

// 2. Baca kolom pada tabel pesanan
$pesanan_columns = [];
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM pesanan");
    while ($row = $stmt->fetch()) {
        $pesanan_columns[] = strtolower($row['Field']);
    }
} catch (PDOException $e) {
    die("Gagal membaca tabel pesanan: " . $e->getMessage());
}

// Update ENUM status_pesanan mencakup 'Menunggu Pembayaran'
try {
    $pdo->exec("
        ALTER TABLE pesanan 
        MODIFY COLUMN status_pesanan ENUM('Menunggu Pembayaran', 'Menunggu Konfirmasi', 'Diproses', 'Dikirim', 'Selesai', 'Dibatalkan') 
        NOT NULL DEFAULT 'Menunggu Pembayaran'
    ");
    $results[] = "✅ Update status_pesanan menjadi otomatis 'Menunggu Pembayaran'";
} catch (PDOException $e) {
    $errors[] = "❌ Update status_pesanan: " . $e->getMessage();
}

// Tambah kolom kurir
if (!in_array('kurir', $pesanan_columns, true)) {
    try {
        $pdo->exec("ALTER TABLE pesanan ADD COLUMN kurir VARCHAR(50) NULL DEFAULT 'JNE' AFTER alamat_pengiriman");
        $results[] = "✅ Tambah kolom kurir pada pesanan";
    } catch (PDOException $e) {
        $errors[] = "❌ Tambah kolom kurir: " . $e->getMessage();
    }
} else {
    $results[] = "ℹ️ Kolom kurir sudah ada sebelumnya";
}

// Tambah kolom layanan_pengiriman
if (!in_array('layanan_pengiriman', $pesanan_columns, true)) {
    try {
        $pdo->exec("ALTER TABLE pesanan ADD COLUMN layanan_pengiriman VARCHAR(50) NULL DEFAULT 'Reguler' AFTER kurir");
        $results[] = "✅ Tambah kolom layanan_pengiriman pada pesanan";
    } catch (PDOException $e) {
        $errors[] = "❌ Tambah kolom layanan_pengiriman: " . $e->getMessage();
    }
} else {
    $results[] = "ℹ️ Kolom layanan_pengiriman sudah ada sebelumnya";
}

// Tambah kolom ongkir
if (!in_array('ongkir', $pesanan_columns, true)) {
    try {
        $pdo->exec("ALTER TABLE pesanan ADD COLUMN ongkir DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER catatan");
        $results[] = "✅ Tambah kolom ongkir pada pesanan";
    } catch (PDOException $e) {
        $errors[] = "❌ Tambah kolom ongkir: " . $e->getMessage();
    }
} else {
    $results[] = "ℹ️ Kolom ongkir sudah ada sebelumnya";
}

// Tambah kolom total_berat
if (!in_array('total_berat', $pesanan_columns, true)) {
    try {
        $pdo->exec("ALTER TABLE pesanan ADD COLUMN total_berat INT UNSIGNED NOT NULL DEFAULT 0 AFTER ongkir");
        $results[] = "✅ Tambah kolom total_berat pada pesanan";
    } catch (PDOException $e) {
        $errors[] = "❌ Tambah kolom total_berat: " . $e->getMessage();
    }
} else {
    $results[] = "ℹ️ Kolom total_berat sudah ada sebelumnya";
}

// Tambah kolom status_pembayaran
if (!in_array('status_pembayaran', $pesanan_columns, true)) {
    try {
        $pdo->exec("
            ALTER TABLE pesanan 
            ADD COLUMN status_pembayaran ENUM('Belum Dibayar', 'Menunggu Pembayaran', 'Lunas', 'Kadaluarsa', 'Gagal') 
            NOT NULL DEFAULT 'Belum Dibayar' AFTER status_pesanan
        ");
        $results[] = "✅ Tambah kolom status_pembayaran";
    } catch (PDOException $e) {
        $errors[] = "❌ Tambah kolom status_pembayaran: " . $e->getMessage();
    }
} else {
    $results[] = "ℹ️ Kolom status_pembayaran sudah ada sebelumnya";
}

// Tambah kolom metode_pembayaran
if (!in_array('metode_pembayaran', $pesanan_columns, true)) {
    try {
        $pdo->exec("ALTER TABLE pesanan ADD COLUMN metode_pembayaran VARCHAR(50) NULL DEFAULT NULL AFTER status_pembayaran");
        $results[] = "✅ Tambah kolom metode_pembayaran";
    } catch (PDOException $e) {
        $errors[] = "❌ Tambah kolom metode_pembayaran: " . $e->getMessage();
    }
} else {
    $results[] = "ℹ️ Kolom metode_pembayaran sudah ada sebelumnya";
}

// Tambah kolom snap_token
if (!in_array('snap_token', $pesanan_columns, true)) {
    try {
        $pdo->exec("ALTER TABLE pesanan ADD COLUMN snap_token VARCHAR(255) NULL DEFAULT NULL AFTER metode_pembayaran");
        $results[] = "✅ Tambah kolom snap_token";
    } catch (PDOException $e) {
        $errors[] = "❌ Tambah kolom snap_token: " . $e->getMessage();
    }
} else {
    $results[] = "ℹ️ Kolom snap_token sudah ada sebelumnya";
}

// Tambah kolom waktu_pembayaran
if (!in_array('waktu_pembayaran', $pesanan_columns, true)) {
    try {
        $pdo->exec("ALTER TABLE pesanan ADD COLUMN waktu_pembayaran DATETIME NULL DEFAULT NULL AFTER snap_token");
        $results[] = "✅ Tambah kolom waktu_pembayaran";
    } catch (PDOException $e) {
        $errors[] = "❌ Tambah kolom waktu_pembayaran: " . $e->getMessage();
    }
} else {
    $results[] = "ℹ️ Kolom waktu_pembayaran sudah ada sebelumnya";
}

// Tambah kolom midtrans_transaction_id
if (!in_array('midtrans_transaction_id', $pesanan_columns, true)) {
    try {
        $pdo->exec("ALTER TABLE pesanan ADD COLUMN midtrans_transaction_id VARCHAR(100) NULL DEFAULT NULL AFTER waktu_pembayaran");
        $results[] = "✅ Tambah kolom midtrans_transaction_id";
    } catch (PDOException $e) {
        $errors[] = "❌ Tambah kolom midtrans_transaction_id: " . $e->getMessage();
    }
} else {
    $results[] = "ℹ️ Kolom midtrans_transaction_id sudah ada sebelumnya";
}

// Tambah kolom nomor_resi
if (!in_array('nomor_resi', $pesanan_columns, true)) {
    try {
        $pdo->exec("ALTER TABLE pesanan ADD COLUMN nomor_resi VARCHAR(60) NULL DEFAULT NULL AFTER midtrans_transaction_id");
        $results[] = "✅ Tambah kolom nomor_resi pada pesanan";
    } catch (PDOException $e) {
        $errors[] = "❌ Tambah kolom nomor_resi: " . $e->getMessage();
    }
} else {
    $results[] = "ℹ️ Kolom nomor_resi sudah ada sebelumnya";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Migration Lengkap Kurir & Midtrans - PT SMM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light py-5">
<div class="container" style="max-width: 760px;">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white py-3">
            <h5 class="mb-0 fw-bold">🚚 Migration Lengkap: Kurir, Ongkir, & Pembayaran Midtrans</h5>
        </div>
        <div class="card-body p-4">

            <h6 class="fw-bold mb-3">Hasil Eksekusi:</h6>
            <div class="list-group mb-3">
                <?php foreach ($results as $r): ?>
                    <div class="list-group-item py-2"><?= htmlspecialchars($r) ?></div>
                <?php endforeach; ?>
                <?php foreach ($errors as $e): ?>
                    <div class="list-group-item list-group-item-danger py-2"><?= htmlspecialchars($e) ?></div>
                <?php endforeach; ?>
            </div>

            <?php if (empty($errors)): ?>
                <div class="alert alert-success d-flex align-items-center mb-4">
                    <div>
                        <strong class="d-block mb-1">🎉 Sukses! Semua kolom Kurir, Ongkir, dan Midtrans sudah siap 100%.</strong>
                        Tabel <code>produk</code> dan <code>pesanan</code> sudah diperbarui lengkap.
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-danger mb-4">
                    <strong>Ada error saat eksekusi. Silakan periksa daftar error di atas.</strong>
                </div>
            <?php endif; ?>

            <div class="text-center mt-3">
                <a href="/pt-smm/frontend/pengguna/pemesanan.php" class="btn btn-primary px-4 py-2">
                    → Coba Form Pemesanan Sekarang
                </a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
