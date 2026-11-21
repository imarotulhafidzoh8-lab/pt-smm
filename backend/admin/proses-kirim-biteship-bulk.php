<?php
// Pengiriman massal pesanan lunas ke Biteship.
require_once __DIR__ . '/../../config/app.php';
cek_admin();

$redirect_url = base_url('frontend/admin/pesanan/index.php?pembayaran=Lunas');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    flash('error', 'Metode permintaan tidak valid.');
    redirect($redirect_url);
}

$csrf_token = $_POST['csrf_token'] ?? '';
if (!is_string($csrf_token) || empty($_SESSION['bulk_biteship_csrf'])
    || !hash_equals($_SESSION['bulk_biteship_csrf'], $csrf_token)) {
    flash('error', 'Sesi formulir sudah kedaluwarsa. Muat ulang daftar pesanan lalu coba lagi.');
    redirect($redirect_url);
}

$raw_ids = $_POST['id_pesanan'] ?? [];
if (!is_array($raw_ids)) $raw_ids = [];
$ids = array_values(array_unique(array_filter(array_map('intval', $raw_ids), static fn($id) => $id > 0)));

if (empty($ids)) {
    flash('warning', 'Pilih minimal satu pesanan yang siap dikirim.');
    redirect($redirect_url);
}
if (count($ids) > 20) {
    flash('warning', 'Maksimal 20 pesanan dapat diproses sekaligus.');
    redirect($redirect_url);
}

$berhasil = 0;
$gagal = 0;
$dilewati = 0;
$stmt_pesanan = $pdo->prepare('SELECT * FROM pesanan WHERE id_pesanan = ? LIMIT 1');
$stmt_update = $pdo->prepare("UPDATE pesanan SET biteship_order_id = ?, nomor_resi = ?, status_pesanan = 'Dikirim' WHERE id_pesanan = ? AND (biteship_order_id IS NULL OR biteship_order_id = '')");

foreach ($ids as $id_pesanan) {
    try {
        $stmt_pesanan->execute([$id_pesanan]);
        $pesanan = $stmt_pesanan->fetch();

        if (!$pesanan
            || ($pesanan['status_pembayaran'] ?? '') !== 'Lunas'
            || !empty($pesanan['biteship_order_id'])
            || empty($pesanan['kode_pos_tujuan'])
            || in_array($pesanan['status_pesanan'], ['Dikirim', 'Selesai', 'Dibatalkan'], true)) {
            $dilewati++;
            continue;
        }

        $result = biteship_create_order($pdo, $pesanan);
        if (!$result || empty($result['biteship_order_id'])) {
            $gagal++;
            error_log('[Biteship] Pengiriman massal gagal untuk ID pesanan ' . $id_pesanan . '.');
            continue;
        }

        $stmt_update->execute([
            $result['biteship_order_id'],
            $result['waybill_id'] ?? null,
            $id_pesanan,
        ]);

        if ($stmt_update->rowCount() !== 1) {
            $gagal++;
            error_log('[Biteship] Booking dibuat tetapi gagal disimpan untuk ID pesanan ' . $id_pesanan . '. Periksa dashboard Biteship.');
            continue;
        }

        $berhasil++;
        biteship_sync_status($pdo, $id_pesanan);
    } catch (Throwable $e) {
        $gagal++;
        error_log('[Biteship] Pengiriman massal ID pesanan ' . $id_pesanan . ' gagal: ' . $e->getMessage());
    }
}

if ($gagal > 0 || $dilewati > 0) {
    flash('warning', "Pengiriman massal selesai: {$berhasil} berhasil, {$gagal} gagal, {$dilewati} dilewati. Buka rincian pesanan yang gagal untuk melihat penyebabnya.");
} else {
    flash('sukses', "{$berhasil} pesanan berhasil dikirim ke Biteship.");
}

redirect($redirect_url);
