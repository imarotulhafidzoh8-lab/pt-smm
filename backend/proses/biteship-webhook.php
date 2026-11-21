<?php
// ============================================================
// FILE    : backend/proses/biteship-webhook.php
// FUNGSI  : Webhook Handler Update Status Pengiriman dari Biteship
// URL     : http://localhost/pt-smm/backend/proses/biteship-webhook.php
// Daftarkan URL ini di Dashboard Biteship > Settings > Webhook
// ============================================================
require_once __DIR__ . '/../../config/app.php';

header('Content-Type: application/json');

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (empty($data) || empty($data['order_id'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid payload']);
    exit;
}

// Verifikasi header signature dari Biteship (jika diatur di dashboard)
// $signature = $_SERVER['HTTP_X_API_KEY'] ?? '';
// if ($signature !== BITESHIP_API_KEY) { http_response_code(403); exit; }

$biteship_order_id  = $data['order_id'];
$courier_status     = $data['courier_status']     ?? null;  // allocated, picked, dropping, delivered, rejected, returned
$waybill_id         = $data['waybill_id']         ?? null;
$tracking_history   = $data['history']            ?? [];

// Cari pesanan berdasarkan biteship_order_id
try {
    $stmt = $pdo->prepare("SELECT id_pesanan, status_pesanan, nomor_resi FROM pesanan WHERE biteship_order_id = ? LIMIT 1");
    $stmt->execute([$biteship_order_id]);
    $pesanan = $stmt->fetch();
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'DB error']);
    exit;
}

if (!$pesanan) {
    // Coba fallback cari via kode_pesanan dari metadata
    $kode_pesanan = $data['metadata']['kode_pesanan'] ?? null;
    if ($kode_pesanan) {
        $stmt = $pdo->prepare("SELECT id_pesanan, status_pesanan, nomor_resi FROM pesanan WHERE kode_pesanan = ? LIMIT 1");
        $stmt->execute([$kode_pesanan]);
        $pesanan = $stmt->fetch();
    }
}

if (!$pesanan) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Order not found']);
    exit;
}

$id_pesanan = (int)$pesanan['id_pesanan'];

// Map status Biteship ke status_pesanan PT SMM
$status_pesanan_baru = $pesanan['status_pesanan'];
switch (strtolower((string)$courier_status)) {
    case 'allocated':
    case 'picked':
        $status_pesanan_baru = 'Diproses';
        break;
    case 'dropping':
        $status_pesanan_baru = 'Dikirim';
        break;
    case 'delivered':
        $status_pesanan_baru = 'Selesai';
        break;
    case 'rejected':
    case 'returned':
        $status_pesanan_baru = 'Dibatalkan';
        break;
}

try {
    $pdo->prepare("
        UPDATE pesanan
        SET status_pesanan      = ?,
            nomor_resi          = COALESCE(?, nomor_resi),
            biteship_order_id   = COALESCE(biteship_order_id, ?),
            tracking_history    = ?
        WHERE id_pesanan = ?
    ")->execute([
        $status_pesanan_baru,
        $waybill_id,
        $biteship_order_id,
        json_encode($tracking_history),
        $id_pesanan,
    ]);

    echo json_encode([
        'status'              => 'success',
        'id_pesanan'          => $id_pesanan,
        'status_pesanan_baru' => $status_pesanan_baru,
        'waybill_id'          => $waybill_id,
    ]);

} catch (PDOException $e) {
    error_log("[Biteship Webhook] DB Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

