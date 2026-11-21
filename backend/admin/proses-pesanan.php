<?php
// ============================================================
// FILE    : backend/admin/proses-pesanan.php
// FUNGSI  : Handler Pembaruan Status Pesanan (Admin)
// PROYEK  : Sistem Informasi PT Shafa Marwa Multiguna
// ============================================================
require_once __DIR__ . '/../../config/app.php';
cek_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    flash('error', 'Metode permintaan tidak valid.');
    redirect(base_url('frontend/admin/pesanan/index.php'));
}

$id_pesanan     = (int)($_POST['id_pesanan'] ?? 0);
$status_pesanan = sanitize($_POST['status_pesanan'] ?? '');
$nomor_resi     = sanitize($_POST['nomor_resi'] ?? '');

$status_valid = ['Menunggu Pembayaran', 'Menunggu Konfirmasi', 'Diproses', 'Dikirim', 'Selesai', 'Dibatalkan'];
if ($id_pesanan <= 0 || !in_array($status_pesanan, $status_valid, true)) {
    flash('error', 'Data status pesanan tidak valid.');
    redirect(base_url('frontend/admin/pesanan/index.php'));
}

try {
    $pdo->beginTransaction();

    // Ambil status lama
    $stmt = $pdo->prepare("SELECT status_pesanan, kode_pesanan FROM pesanan WHERE id_pesanan = ? FOR UPDATE");
    $stmt->execute([$id_pesanan]);
    $pesanan = $stmt->fetch();

    if (!$pesanan) {
        throw new Exception("Pesanan tidak ditemukan.");
    }

    $status_lama = $pesanan['status_pesanan'];

    // Jika pesanan dibatalkan (dan sebelumnya belum dibatalkan), kembalikan stok produk
    if ($status_pesanan === 'Dibatalkan' && $status_lama !== 'Dibatalkan') {
        $stmt_items = $pdo->prepare("SELECT id_produk, jumlah FROM detail_pesanan WHERE id_pesanan = ?");
        $stmt_items->execute([$id_pesanan]);
        $items = $stmt_items->fetchAll();

        foreach ($items as $item) {
            $stmt_restock = $pdo->prepare("UPDATE produk SET stok = stok + ? WHERE id_produk = ?");
            $stmt_restock->execute([(int)$item['jumlah'], (int)$item['id_produk']]);
        }
    }

    // Update status pesanan & nomor resi jika diisi
    if (!empty($nomor_resi)) {
        $stmt_update = $pdo->prepare("UPDATE pesanan SET status_pesanan = ?, nomor_resi = ? WHERE id_pesanan = ?");
        $stmt_update->execute([$status_pesanan, $nomor_resi, $id_pesanan]);
    } else {
        $stmt_update = $pdo->prepare("UPDATE pesanan SET status_pesanan = ? WHERE id_pesanan = ?");
        $stmt_update->execute([$status_pesanan, $id_pesanan]);
    }

    $pdo->commit();

    flash('sukses', 'Status pesanan ' . $pesanan['kode_pesanan'] . ' berhasil diubah menjadi "' . $status_pesanan . '".');
    redirect(base_url('frontend/admin/pesanan/detail.php?id=' . $id_pesanan));

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Status pesanan error: " . $e->getMessage());
    flash('error', 'Gagal mengubah status: ' . $e->getMessage());
    redirect(base_url('frontend/admin/pesanan/detail.php?id=' . $id_pesanan));
}

