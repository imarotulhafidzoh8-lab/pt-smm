<?php
// ============================================================
// FILE    : backend/api/cek-ongkir.php
// FUNGSI  : AJAX Endpoint - Cek Tarif Ongkir via Biteship API
// METHOD  : GET  ?kode_pos=12345&berat=500&harga=100000
// RETURNS : JSON {success, rates:[{courier_name, price, ...}]}
// ============================================================
require_once __DIR__ . '/../../config/app.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// Hanya izinkan GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$kode_pos = trim($_GET['kode_pos'] ?? '');
$berat    = max(1, (int)($_GET['berat'] ?? 500));
$harga    = max(1, (int)($_GET['harga'] ?? 100000));

// Validasi kode pos (5 digit angka)
if (!preg_match('/^\d{5}$/', $kode_pos)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Kode pos tidak valid. Harus 5 digit angka.'
    ]);
    exit;
}

$rates = biteship_get_rates($kode_pos, $berat, $harga);

if ($rates === false) {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal mengambil tarif pengiriman. Periksa kode pos atau koneksi.'
    ]);
    exit;
}

echo json_encode([
    'success'       => true,
    'kode_pos'      => $kode_pos,
    'berat_gram'    => $berat,
    'jumlah_tarif'  => count($rates),
    'rates'         => $rates,
]);

