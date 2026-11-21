<?php
// ============================================================
// FILE    : backend/admin/proses-simulasi-biteship.php
// FUNGSI  : Simulasi Perjalanan Paket Kurir (Demo Sidang / Sandbox Localhost)
// PROYEK  : Sistem Informasi PT Shafa Marwa Multiguna
// ============================================================
require_once __DIR__ . '/../../config/app.php';
cek_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    flash('error', 'Metode tidak valid.');
    redirect(base_url('frontend/admin/pesanan/index.php'));
}

$id_pesanan = (int)($_POST['id_pesanan'] ?? 0);
$step       = $_POST['step'] ?? '';

if ($id_pesanan <= 0 || !in_array($step, ['picked', 'dropping', 'delivered'])) {
    flash('error', 'Parameter simulasi tidak valid.');
    redirect(base_url('frontend/admin/pesanan/index.php'));
}

$ok = biteship_simulate_status($pdo, $id_pesanan, $step);

if ($ok) {
    $labels = [
        'picked'    => '📦 [Simulasi] Driver kurir telah menjemput paket dari gudang (Status: Diproses).',
        'dropping'  => '🚚 [Simulasi] Paket sedang dalam perjalanan kurir menuju alamat pembeli (Status: Dikirim).',
        'delivered' => '✅ [Simulasi] Paket telah diterima pembeli dan pesanan selesai (Status: Selesai).',
    ];
    flash('sukses', $labels[$step] ?? 'Simulasi status berhasil.');
} else {
    flash('error', 'Gagal melakukan simulasi status pesanan.');
}

redirect(base_url('frontend/admin/pesanan/detail.php?id=' . $id_pesanan));

