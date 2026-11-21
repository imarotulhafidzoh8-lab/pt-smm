<?php
// ============================================================
// FILE    : database/run-migration-midtrans.php
// FUNGSI  : Script migrasi database untuk kolom Pembayaran Midtrans
// CARA    : Akses sekali via browser: http://localhost/pt-smm/database/run-migration-midtrans.php
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

// 1. Ambil daftar kolom yang sudah ada di tabel pesanan
$existing_columns = [];
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM pesanan");
    while ($row = $stmt->fetch()) {
        $existing_columns[] = strtolower($row['Field']);
    }
} catch (PDOException $e) {
    die("Gagal membaca tabel pesanan: " . $e->getMessage());
}

// Update ENUM status_pesanan agar mencakup 'Menunggu Pembayaran'
try {
    $pdo->exec("
        ALTER TABLE pesanan 
        MODIFY COLUMN status_pesanan ENUM('Menunggu Pembayaran', 'Menunggu Konfirmasi', 'Diproses', 'Dikirim', 'Selesai', 'Dibatalkan') 
        NOT NULL DEFAULT 'Menunggu Pembayaran'
    ");
    $results[] = "✅ Update status_pesanan agar otomatis 'Menunggu Pembayaran' saat pesan";
} catch (PDOException $e) {
    $errors[] = "❌ Update status_pesanan: " . $e->getMessage();
}

// 2. Tambah kolom status_pembayaran jika belum ada
if (!in_array('status_pembayaran', $existing_columns, true)) {
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

// 3. Tambah kolom metode_pembayaran
if (!in_array('metode_pembayaran', $existing_columns, true)) {
    try {
        $pdo->exec("ALTER TABLE pesanan ADD COLUMN metode_pembayaran VARCHAR(50) NULL DEFAULT NULL AFTER status_pembayaran");
        $results[] = "✅ Tambah kolom metode_pembayaran";
    } catch (PDOException $e) {
        $errors[] = "❌ Tambah kolom metode_pembayaran: " . $e->getMessage();
    }
} else {
    $results[] = "ℹ️ Kolom metode_pembayaran sudah ada sebelumnya";
}

// 4. Tambah kolom snap_token
if (!in_array('snap_token', $existing_columns, true)) {
    try {
        $pdo->exec("ALTER TABLE pesanan ADD COLUMN snap_token VARCHAR(255) NULL DEFAULT NULL AFTER metode_pembayaran");
        $results[] = "✅ Tambah kolom snap_token";
    } catch (PDOException $e) {
        $errors[] = "❌ Tambah kolom snap_token: " . $e->getMessage();
    }
} else {
    $results[] = "ℹ️ Kolom snap_token sudah ada sebelumnya";
}

// 5. Tambah kolom waktu_pembayaran
if (!in_array('waktu_pembayaran', $existing_columns, true)) {
    try {
        $pdo->exec("ALTER TABLE pesanan ADD COLUMN waktu_pembayaran DATETIME NULL DEFAULT NULL AFTER snap_token");
        $results[] = "✅ Tambah kolom waktu_pembayaran";
    } catch (PDOException $e) {
        $errors[] = "❌ Tambah kolom waktu_pembayaran: " . $e->getMessage();
    }
} else {
    $results[] = "ℹ️ Kolom waktu_pembayaran sudah ada sebelumnya";
}

// 6. Tambah kolom midtrans_transaction_id
if (!in_array('midtrans_transaction_id', $existing_columns, true)) {
    try {
        $pdo->exec("ALTER TABLE pesanan ADD COLUMN midtrans_transaction_id VARCHAR(100) NULL DEFAULT NULL AFTER waktu_pembayaran");
        $results[] = "✅ Tambah kolom midtrans_transaction_id";
    } catch (PDOException $e) {
        $errors[] = "❌ Tambah kolom midtrans_transaction_id: " . $e->getMessage();
    }
} else {
    $results[] = "ℹ️ Kolom midtrans_transaction_id sudah ada sebelumnya";
}

// Ambil struktur terbaru
$cols = $pdo->query("SHOW COLUMNS FROM pesanan")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Migration Midtrans Payment - PT SMM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light py-5">
<div class="container" style="max-width: 760px;">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white py-3">
            <h5 class="mb-0 fw-bold">💳 Migration: Payment Gateway Midtrans — PT SMM</h5>
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
                        <strong class="d-block mb-1">🎉 Sukses! Database tabel pesanan sudah siap.</strong>
                        Kolom <code>status_pembayaran</code>, <code>metode_pembayaran</code>, <code>snap_token</code>, <code>waktu_pembayaran</code>, dan <code>midtrans_transaction_id</code> sudah aktif.
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-danger mb-4">
                    <strong>Ada error saat eksekusi. Silakan periksa daftar error di atas.</strong>
                </div>
            <?php endif; ?>

            <h6 class="fw-bold mb-2">Struktur Kolom Tabel <code>pesanan</code>:</h6>
            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Field</th>
                            <th>Type</th>
                            <th>Null</th>
                            <th>Default</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cols as $col): ?>
                        <tr class="<?= in_array($col['Field'], ['status_pembayaran', 'metode_pembayaran', 'snap_token', 'waktu_pembayaran', 'midtrans_transaction_id']) ? 'table-success fw-bold' : '' ?>">
                            <td><code><?= htmlspecialchars($col['Field']) ?></code></td>
                            <td><?= htmlspecialchars($col['Type']) ?></td>
                            <td><?= htmlspecialchars($col['Null']) ?></td>
                            <td><?= htmlspecialchars($col['Default'] ?? 'NULL') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="text-center mt-4">
                <a href="/pt-smm/frontend/pengguna/riwayat-pesanan.php" class="btn btn-primary px-4 py-2">
                    → Ke Halaman Riwayat Pesanan
                </a>
            </div>
        </div>
    </div>
</div>
</body>
</html>

