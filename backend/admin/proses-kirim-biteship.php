<?php
// ============================================================
// FILE    : backend/admin/proses-kirim-biteship.php
// FUNGSI  : Admin kirimkan pesanan ke Biteship (booking kurir)
//           → sistem mendapat nomor resi otomatis dari Biteship
//           → Biteship webhook akan auto-update status perjalanan paket
// PROYEK  : Sistem Informasi PT Shafa Marwa Multiguna
// ============================================================
require_once __DIR__ . '/../../config/app.php';
cek_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    flash('error', 'Metode tidak valid.');
    redirect(base_url('frontend/admin/pesanan/index.php'));
}

$id_pesanan = (int)($_POST['id_pesanan'] ?? 0);

if ($id_pesanan <= 0) {
    flash('error', 'Pesanan tidak valid.');
    redirect(base_url('frontend/admin/pesanan/index.php'));
}

// Ambil data pesanan
$stmt = $pdo->prepare("SELECT * FROM pesanan WHERE id_pesanan = ? LIMIT 1");
$stmt->execute([$id_pesanan]);
$pesanan = $stmt->fetch();

if (!$pesanan) {
    flash('error', 'Pesanan tidak ditemukan.');
    redirect(base_url('frontend/admin/pesanan/index.php'));
}

// Validasi: hanya kirim jika lunas dan belum dikirim ke Biteship
if ($pesanan['status_pembayaran'] !== 'Lunas') {
    flash('error', 'Pesanan belum lunas. Hanya pesanan yang sudah dibayar yang bisa dikirimkan ke Biteship.');
    redirect(base_url('frontend/admin/pesanan/detail.php?id=' . $id_pesanan));
}

if (!empty($pesanan['biteship_order_id'])) {
    flash('info', 'Pesanan ini sudah dikirimkan ke Biteship sebelumnya. Nomor Resi: ' . ($pesanan['nomor_resi'] ?? 'menunggu kurir'));
    redirect(base_url('frontend/admin/pesanan/detail.php?id=' . $id_pesanan));
}

// Validasi kode pos tujuan
if (empty($pesanan['kode_pos_tujuan'])) {
    flash('error', 'Kode pos tujuan tidak tersedia. Mungkin pesanan ini dibuat sebelum integrasi Biteship. Isi nomor resi secara manual.');
    redirect(base_url('frontend/admin/pesanan/detail.php?id=' . $id_pesanan));
}

// Panggil Biteship API untuk booking kurir
$result = biteship_create_order($pdo, $pesanan);

if ($result && !empty($result['biteship_order_id'])) {
    // Simpan biteship_order_id dan nomor resi (waybill), ubah status ke Dikirim
    $upd = $pdo->prepare("
        UPDATE pesanan
        SET biteship_order_id = ?,
            nomor_resi        = ?,
            status_pesanan    = 'Dikirim'
        WHERE id_pesanan = ?
    ");
    $upd->execute([
        $result['biteship_order_id'],
        $result['waybill_id'] ?? null,
        $id_pesanan,
    ]);

    // Lakukan initial sync langsung untuk mendapatkan tracking history pertama
    biteship_sync_status($pdo, $id_pesanan);

    // Ambil resi terbaru setelah sync
    $cek_resi = $pdo->prepare("SELECT nomor_resi FROM pesanan WHERE id_pesanan = ?");
    $cek_resi->execute([$id_pesanan]);
    $current_resi = $cek_resi->fetchColumn();

    $resi_info = !empty($current_resi)
        ? 'Nomor resi: ' . $current_resi
        : 'Resi akan muncul otomatis saat kurir menjemput paket.';

    flash('sukses', 'Pesanan berhasil dikirimkan ke Biteship. ' . $resi_info . ' Status pelacakan diperbarui otomatis.');
} else {
    // Gagal booking Biteship
    flash('error', '❌ Gagal mengirimkan pesanan ke Biteship. Periksa: (1) kode pos tujuan valid, (2) kurir tersedia di rute tersebut, (3) API key Biteship aktif. Silakan coba lagi atau isi nomor resi manual.');
}

redirect(base_url('frontend/admin/pesanan/detail.php?id=' . $id_pesanan));

