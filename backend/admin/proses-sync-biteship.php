<?php
// ============================================================
// FILE    : backend/admin/proses-sync-biteship.php
// FUNGSI  : Admin sinkronkan status pesanan langsung dari API Biteship
//           (Bekerja di localhost tanpa butuh webhook)
// PROYEK  : Sistem Informasi PT Shafa Marwa Multiguna
// ============================================================
require_once __DIR__ . '/../../config/app.php';
cek_admin();

$id_pesanan = (int)($_GET['id'] ?? ($_POST['id_pesanan'] ?? 0));

if ($id_pesanan <= 0) {
    flash('error', 'ID Pesanan tidak valid.');
    redirect(base_url('frontend/admin/pesanan/index.php'));
}

$res = biteship_sync_status($pdo, $id_pesanan);

if ($res) {
    $info = "Status Pesanan: <strong>" . htmlspecialchars($res['status_pesanan']) . "</strong>";
    if (!empty($res['waybill_id'])) {
        $info .= " | Resi: <strong>" . htmlspecialchars($res['waybill_id']) . "</strong>";
    }
    flash('sukses', "✅ Status berhasil disinkronkan dari Biteship! " . $info);
} else {
    flash('info', "ℹ️ Tidak ada pembaruan status terbaru dari Biteship atau pesanan belum memiliki Order ID Biteship.");
}

redirect(base_url('frontend/admin/pesanan/detail.php?id=' . $id_pesanan));

