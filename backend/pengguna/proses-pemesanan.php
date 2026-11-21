<?php
// ============================================================
// FILE    : backend/pengguna/proses-pemesanan.php
// FUNGSI  : Proses Pemesanan Produk (Atomic PDO Transaction)
// PROYEK  : Sistem Informasi PT Shafa Marwa Multiguna
// ============================================================
require_once __DIR__ . '/../../config/app.php';
cek_login(base_url('frontend/auth/login.php'));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    flash('error', 'Metode permintaan tidak valid.');
    redirect(base_url('frontend/pages/katalog.php'));
}

// Setiap form hanya boleh diproses sekali; sesi PHP mengunci request paralel.
$form_token = $_POST['order_form_token'] ?? '';
if (!is_string($form_token) || !preg_match('/^[a-f0-9]{64}$/', $form_token)
    || empty($_SESSION['order_submit_tokens'][$form_token])) {
    flash('warning', 'Permintaan ini sudah diproses atau kedaluwarsa. Periksa riwayat pesanan sebelum mencoba lagi.');
    redirect(base_url('frontend/pengguna/riwayat-pesanan.php'));
}
unset($_SESSION['order_submit_tokens'][$form_token]);

$id_user           = (int)$_SESSION['id_user'];
$items_post        = $_POST['items'] ?? [];
$items_post        = is_array($items_post) ? $items_post : [];
$items             = [];
foreach ($items_post as $product_id => $quantity) {
    $product_id = (int)$product_id;
    $quantity = (int)$quantity;
    if ($product_id > 0 && $quantity > 0) $items[$product_id] = $quantity;
}
$nama_penerima     = sanitize($_POST['nama_penerima'] ?? '');
$no_hp_penerima    = sanitize($_POST['no_hp_penerima'] ?? '');
$alamat_pengiriman = sanitize($_POST['alamat_pengiriman'] ?? '');
$catatan           = sanitize($_POST['catatan'] ?? '');

// Data Biteship dari form
$kurir_code    = sanitize($_POST['kurir_code']    ?? '');
$kurir_name    = sanitize($_POST['kurir_name']    ?? '');
$kurir_service = sanitize($_POST['kurir_service'] ?? '');
$ongkir        = max(0, (int)($_POST['ongkir'] ?? 0));
$kode_pos_tujuan = sanitize($_POST['kode_pos_tujuan'] ?? '');

// Validasi
if (empty($items)) {
    flash('error', 'Tambahkan minimal satu produk ke pesanan.');
    redirect(base_url('frontend/pengguna/pemesanan.php'));
}

if (empty($nama_penerima) || mb_strlen($nama_penerima) < 3) {
    flash('error', 'Nama penerima minimal 3 karakter.');
    redirect(base_url('frontend/pengguna/pemesanan.php'));
}

if (empty($no_hp_penerima) || !preg_match('/^[0-9+\-\s]{8,20}$/', $no_hp_penerima)) {
    flash('error', 'Nomor telepon/WhatsApp harus 8-20 digit.');
    redirect(base_url('frontend/pengguna/pemesanan.php'));
}

if (empty($alamat_pengiriman) || mb_strlen($alamat_pengiriman) < 8) {
    flash('error', 'Alamat pengiriman minimal 8 karakter.');
    redirect(base_url('frontend/pengguna/pemesanan.php'));
}

if (empty($kurir_code) || empty($kurir_name)) {
    flash('error', 'Silakan pilih kurir pengiriman terlebih dahulu.');
    redirect(base_url('frontend/pengguna/pemesanan.php'));
}

if (empty($kode_pos_tujuan) || !preg_match('/^\d{5}$/', $kode_pos_tujuan)) {
    flash('error', 'Kode pos tujuan tidak valid. Pastikan 5 digit angka.');
    redirect(base_url('frontend/pengguna/pemesanan.php'));
}

try {
    $pdo->beginTransaction();

    // Lock semua produk dan hitung harga/berat dari data server.
    ksort($items);
    $stmt_produk = $pdo->prepare("SELECT * FROM produk WHERE id_produk = ? FOR UPDATE");
    $order_items = [];
    $subtotal = 0;
    $total_berat = 0;
    foreach ($items as $product_id => $quantity) {
        $stmt_produk->execute([$product_id]);
        $produk = $stmt_produk->fetch();
        if (!$produk || strcasecmp($produk['status_produk'], 'Aktif') !== 0) {
            throw new Exception("Salah satu produk tidak tersedia lagi. Silakan periksa keranjang.");
        }
        if ((int)$produk['stok'] < $quantity) {
            throw new Exception("Stok {$produk['nama_produk']} tidak mencukupi. Tersedia {$produk['stok']} {$produk['satuan']}.");
        }
        $harga_satuan = (float)$produk['harga'];
        $item_subtotal = $harga_satuan * $quantity;
        $berat_satuan = max(1, (int)($produk['berat'] ?? 500));
        $subtotal += $item_subtotal;
        $total_berat += $berat_satuan * $quantity;
        $order_items[] = ['id_produk' => (int)$produk['id_produk'], 'nama_produk' => $produk['nama_produk'], 'harga_satuan' => $harga_satuan, 'jumlah' => $quantity, 'subtotal' => $item_subtotal];
    }
    $total_harga = $subtotal + $ongkir;

    // Generate kode pesanan
    $kode_pesanan = generate_kode_pesanan();

    // INSERT pesanan
    $stmt = $pdo->prepare("
        INSERT INTO pesanan (
            kode_pesanan, id_user, nama_penerima, no_hp_penerima, alamat_pengiriman,
            kode_pos_tujuan, kurir, kurir_code, kurir_service, layanan_pengiriman,
            catatan, ongkir, total_berat, total_harga,
            status_pesanan, status_pembayaran
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Menunggu Pembayaran', 'Menunggu Pembayaran')
    ");
    $stmt->execute([
        $kode_pesanan,
        $id_user,
        $nama_penerima,
        $no_hp_penerima,
        $alamat_pengiriman,
        $kode_pos_tujuan,
        $kurir_name,
        $kurir_code,
        $kurir_service,
        $kurir_service,
        !empty($catatan) ? $catatan : null,
        $ongkir,
        $total_berat,
        $total_harga,
    ]);

    $id_pesanan = (int)$pdo->lastInsertId();

    // INSERT semua detail dan kurangi stok dalam transaksi yang sama.
    $stmt_detail = $pdo->prepare("INSERT INTO detail_pesanan (id_pesanan, id_produk, nama_produk_saat_pesan, harga_satuan, jumlah, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt_stok = $pdo->prepare("UPDATE produk SET stok = stok - ? WHERE id_produk = ?");
    foreach ($order_items as $item) {
        $stmt_detail->execute([$id_pesanan, $item['id_produk'], $item['nama_produk'], $item['harga_satuan'], $item['jumlah'], $item['subtotal']]);
        $stmt_stok->execute([$item['jumlah'], $item['id_produk']]);
    }

    $pdo->commit();

    flash('sukses', 'Pesanan berhasil dibuat! Kode: ' . $kode_pesanan . '. Silakan selesaikan pembayaran.');
    redirect(base_url('frontend/pengguna/detail-pesanan.php?id=' . $id_pesanan));

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Pemesanan error: " . $e->getMessage());
    flash('error', $e->getMessage());
    redirect(base_url('frontend/pengguna/pemesanan.php'));
}
