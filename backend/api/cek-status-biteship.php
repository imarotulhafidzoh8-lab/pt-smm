<?php
// ============================================================
// FILE    : backend/api/cek-status-biteship.php
// FUNGSI  : Endpoint AJAX untuk cek update status & tracking kurir live
// URL     : /pt-smm/backend/api/cek-status-biteship.php?id={id_pesanan}
// ============================================================
require_once __DIR__ . '/../../config/app.php';

header('Content-Type: application/json; charset=utf-8');

$id_pesanan = (int)($_GET['id'] ?? 0);
if ($id_pesanan <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID pesanan tidak valid']);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM pesanan WHERE id_pesanan = ? LIMIT 1");
$stmt->execute([$id_pesanan]);
$pesanan = $stmt->fetch();

if (!$pesanan) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Pesanan tidak ditemukan']);
    exit;
}

// Cek hak akses jika login sebagai user biasa
if (!empty($_SESSION['role']) && $_SESSION['role'] === 'pengguna') {
    if ((int)$pesanan['id_user'] !== (int)$_SESSION['id_user']) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Akses ditolak']);
        exit;
    }
}

// Lakukan sync jika ada biteship_order_id
if (!empty($pesanan['biteship_order_id'])) {
    biteship_sync_status($pdo, $id_pesanan);

    // Ambil data terbaru
    $stmt = $pdo->prepare("SELECT status_pesanan, nomor_resi, tracking_history FROM pesanan WHERE id_pesanan = ? LIMIT 1");
    $stmt->execute([$id_pesanan]);
    $updated = $stmt->fetch();

    $history = !empty($updated['tracking_history'])
        ? json_decode($updated['tracking_history'], true)
        : [];

    echo json_encode([
        'success'        => true,
        'status_pesanan' => $updated['status_pesanan'],
        'nomor_resi'     => $updated['nomor_resi'],
        'history'        => $history,
    ]);
    exit;
}

echo json_encode([
    'success'        => true,
    'status_pesanan' => $pesanan['status_pesanan'],
    'nomor_resi'     => $pesanan['nomor_resi'],
    'history'        => [],
]);

