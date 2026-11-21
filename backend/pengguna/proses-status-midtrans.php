<?php
// ============================================================
// FILE    : backend/pengguna/proses-status-midtrans.php
// FUNGSI  : Cek & Sinkronisasi Status Transaksi dari Midtrans API
//           CATATAN: Auto-booking Biteship DINONAKTIFKAN.
//           Resi hanya diterbitkan oleh Admin via panel admin.
//           Alur: Bayar → Diproses → Admin kirim ke Biteship → Dikirim + Resi.
// PROYEK  : Sistem Informasi PT Shafa Marwa Multiguna
// ============================================================
require_once __DIR__ . '/../../config/app.php';
cek_pengguna();

$id_user    = (int)$_SESSION['id_user'];
$id_pesanan = (int)($_GET['id'] ?? 0);

if ($id_pesanan <= 0) {
    flash('error', 'Pesanan tidak valid.');
    redirect(base_url('frontend/pengguna/riwayat-pesanan.php'));
}

$stmt = $pdo->prepare("SELECT * FROM pesanan WHERE id_pesanan = ? AND id_user = ? LIMIT 1");
$stmt->execute([$id_pesanan, $id_user]);
$pesanan = $stmt->fetch();

if (!$pesanan) {
    flash('error', 'Pesanan tidak ditemukan.');
    redirect(base_url('frontend/pengguna/riwayat-pesanan.php'));
}

// ===== BAGIAN 1: Sinkronisasi Status Midtrans =====
$api_base   = MIDTRANS_IS_PRODUCTION
    ? 'https://api.midtrans.com/v2/'
    : 'https://api.sandbox.midtrans.com/v2/';
$status_url = $api_base . urlencode($pesanan['kode_pesanan']) . '/status';

$ch = curl_init($status_url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => [
        'Accept: application/json',
        'Authorization: Basic ' . base64_encode(MIDTRANS_SERVER_KEY . ':'),
    ],
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2,
]);
$response  = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$data = json_decode($response, true);

$status_pembayaran = $pesanan['status_pembayaran'];
$status_pesanan    = $pesanan['status_pesanan'];
$payment_type      = $pesanan['metode_pembayaran'];
$tx_id             = $pesanan['midtrans_transaction_id'];
$waktu_bayar       = null;
$bayar_baru_lunas  = false;

if ($http_code === 200 && !empty($data['transaction_status'])) {
    $ts     = $data['transaction_status'];
    $fraud  = $data['fraud_status'] ?? 'accept';
    $payment_type = $data['payment_type'] ?? $payment_type;
    $tx_id        = $data['transaction_id'] ?? $tx_id;

    if ($ts === 'settlement' || ($ts === 'capture' && $fraud === 'accept')) {
        if ($status_pembayaran !== 'Lunas') {
            $bayar_baru_lunas = true;
        }
        $status_pembayaran = 'Lunas';
        if (in_array($status_pesanan, ['Menunggu Pembayaran', 'Menunggu Konfirmasi'], true)) {
            $status_pesanan = 'Diproses';
        }
        $waktu_bayar = date('Y-m-d H:i:s');
        flash('sukses', 'Pembayaran berhasil dikonfirmasi! Pesanan sedang diproses.');
    } elseif ($ts === 'pending') {
        $status_pembayaran = 'Menunggu Pembayaran';
        flash('info', 'Pembayaran masih pending. Silakan selesaikan pembayaran.');
    } elseif ($ts === 'expire') {
        $status_pembayaran = 'Kadaluarsa';
        $status_pesanan    = 'Dibatalkan';
        flash('error', 'Waktu pembayaran telah kadaluarsa.');
    } elseif (in_array($ts, ['deny', 'cancel'])) {
        $status_pembayaran = 'Gagal';
        flash('error', 'Pembayaran gagal atau dibatalkan.');
    }

    $update = $pdo->prepare("
        UPDATE pesanan
        SET status_pembayaran = ?,
            status_pesanan = ?,
            metode_pembayaran = ?,
            midtrans_transaction_id = ?,
            waktu_pembayaran = COALESCE(?, waktu_pembayaran)
        WHERE id_pesanan = ?
    ");
    $update->execute([
        $status_pembayaran,
        $status_pesanan,
        $payment_type,
        $tx_id,
        $waktu_bayar,
        $id_pesanan,
    ]);
} else {
    flash('info', 'Status pembayaran belum berubah di Midtrans.');
}

// ===== BAGIAN 2: Auto-Booking Biteship DINONAKTIFKAN =====
// Resi hanya diterbitkan oleh Admin secara manual via panel admin
// (backend/admin/proses-kirim-biteship.php).
// Alur yang benar: Bayar → Diproses → Admin kirim ke Biteship → Dikirim + Resi.
// Jangan aktifkan kembali blok di bawah ini.

redirect(base_url('frontend/pengguna/detail-pesanan.php?id=' . $id_pesanan));
