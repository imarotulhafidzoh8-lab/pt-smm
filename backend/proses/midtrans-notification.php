<?php
// ============================================================
// FILE    : backend/proses/midtrans-notification.php
// FUNGSI  : Webhook Handler Notifikasi Pembayaran dari Midtrans
// PROYEK  : Sistem Informasi PT Shafa Marwa Multiguna
// ============================================================
require_once __DIR__ . '/../../config/app.php';

header('Content-Type: application/json');

$raw_input = file_get_contents('php://input');
$notif = json_decode($raw_input, true);

if (empty($notif)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON input']);
    exit;
}

$order_id           = $notif['order_id'] ?? '';
$status_code        = $notif['status_code'] ?? '';
$gross_amount       = $notif['gross_amount'] ?? '';
$signature_key      = $notif['signature_key'] ?? '';
$transaction_status = $notif['transaction_status'] ?? '';
$payment_type       = $notif['payment_type'] ?? '';
$fraud_status       = $notif['fraud_status'] ?? '';
$transaction_id     = $notif['transaction_id'] ?? '';

// Verifikasi signature key keamanan dari Midtrans
$expected_signature = hash('sha512', $order_id . $status_code . $gross_amount . MIDTRANS_SERVER_KEY);

if (!hash_equals($expected_signature, $signature_key)) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Invalid signature key']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id_pesanan, status_pesanan, status_pembayaran FROM pesanan WHERE kode_pesanan = ? LIMIT 1");
    $stmt->execute([$order_id]);
    $pesanan = $stmt->fetch();

    if (!$pesanan) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Pesanan not found']);
        exit;
    }

    $id_pesanan         = (int)$pesanan['id_pesanan'];
    $status_pembayaran  = $pesanan['status_pembayaran'];
    $status_pesanan     = $pesanan['status_pesanan'];
    $waktu_bayar        = null;

    if ($transaction_status === 'capture') {
        if ($fraud_status === 'accept') {
            $status_pembayaran = 'Lunas';
            $status_pesanan    = 'Diproses';
            $waktu_bayar       = date('Y-m-d H:i:s');
        }
    } elseif ($transaction_status === 'settlement') {
        $status_pembayaran = 'Lunas';
        $status_pesanan    = 'Diproses';
        $waktu_bayar       = date('Y-m-d H:i:s');
    } elseif ($transaction_status === 'pending') {
        $status_pembayaran = 'Menunggu Pembayaran';
    } elseif (in_array($transaction_status, ['deny', 'cancel'])) {
        $status_pembayaran = 'Gagal';
    } elseif ($transaction_status === 'expire') {
        $status_pembayaran = 'Kadaluarsa';
        $status_pesanan    = 'Dibatalkan';
    }

    // Update pesanan
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
        $transaction_id,
        $waktu_bayar,
        $id_pesanan
    ]);

    // ===== CATATAN: Auto-booking Biteship SENGAJA DINONAKTIFKAN =====
    // Resi hanya diterbitkan setelah Admin secara manual mengklik
    // "Kirim via Biteship" di panel admin (backend/admin/proses-kirim-biteship.php).
    // Alur yang benar: Bayar → Diproses → (Admin kirim ke Biteship) → Dikirim + Resi.
    // Jangan aktifkan kembali blok ini.

    echo json_encode(['status' => 'success', 'message' => 'Status updated successfully']);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}

