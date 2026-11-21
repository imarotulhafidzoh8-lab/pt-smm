<?php
// ============================================================
// FILE    : backend/helpers/midtrans.php
// FUNGSI  : Helper & Layanan Integrasi Midtrans Snap API (cURL Native)
// PROYEK  : Sistem Informasi PT Shafa Marwa Multiguna
// ============================================================

/**
 * Generate Snap Token untuk Pesanan tertentu
 *
 * @param PDO $pdo
 * @param int $id_pesanan
 * @return string|false
 */
function midtrans_get_or_create_snap_token($pdo, $id_pesanan)
{
    if (MIDTRANS_SERVER_KEY === '' || MIDTRANS_CLIENT_KEY === '') {
        error_log('[Midtrans] Kredensial belum dikonfigurasi. Isi MIDTRANS_SERVER_KEY dan MIDTRANS_CLIENT_KEY di environment.');
        return false;
    }

    // 1. Ambil data pesanan
    $stmt = $pdo->prepare("SELECT * FROM pesanan WHERE id_pesanan = ? LIMIT 1");
    $stmt->execute([(int)$id_pesanan]);
    $pesanan = $stmt->fetch();

    if (!$pesanan) {
        return false;
    }

    // Jika sudah lunas atau dibatalkan, jangan buat token lagi
    if ($pesanan['status_pembayaran'] === 'Lunas' || $pesanan['status_pesanan'] === 'Dibatalkan') {
        return false;
    }

    // Jika sudah memiliki snap_token yang valid, gunakan yang sudah ada
    if (!empty($pesanan['snap_token'])) {
        return $pesanan['snap_token'];
    }

    // 2. Ambil data item pesanan
    $stmt = $pdo->prepare("
        SELECT id_detail, nama_produk_saat_pesan, harga_satuan, jumlah, subtotal 
        FROM detail_pesanan 
        WHERE id_pesanan = ?
    ");
    $stmt->execute([(int)$id_pesanan]);
    $items = $stmt->fetchAll();

    $item_details = [];
    $total_item_calc = 0;
    foreach ($items as $it) {
        $harga = (int)round((float)$it['harga_satuan']);
        $qty   = (int)$it['jumlah'];
        $item_details[] = [
            'id'       => (string)$it['id_detail'],
            'price'    => $harga,
            'quantity' => $qty,
            'name'     => mb_substr($it['nama_produk_saat_pesan'], 0, 45),
        ];
        $total_item_calc += ($harga * $qty);
    }

    // Pastikan gross_amount sesuai
    $gross_amount = (int)round((float)$pesanan['total_harga']);

    // Jika ada ongkos kirim atau selisih, tambahkan item ongkir
    if ($gross_amount !== $total_item_calc) {
        $selisih = $gross_amount - $total_item_calc;
        $kurir_label = !empty($pesanan['kurir']) ? $pesanan['kurir'] : 'Ekspedisi';
        $item_details[] = [
            'id'       => 'ONGKIR',
            'price'    => $selisih,
            'quantity' => 1,
            'name'     => mb_substr('Ongkos Kirim (' . $kurir_label . ')', 0, 45),
        ];
    }

    // Sanitasi no hp untuk Midtrans
    $phone = preg_replace('/[^0-9+]/', '', (string)$pesanan['no_hp_penerima']);
    if (empty($phone)) {
        $phone = '081234567890';
    }

    // Buat Callback Finish URL absolut
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $finish_url = $protocol . $host . base_url('frontend/pengguna/detail-pesanan.php?id=' . $id_pesanan);

    // 3. Susun payload Midtrans Snap
    $payload = [
        'transaction_details' => [
            'order_id'     => $pesanan['kode_pesanan'],
            'gross_amount' => $gross_amount,
        ],
        'item_details' => $item_details,
        'customer_details' => [
            'first_name'       => mb_substr($pesanan['nama_penerima'], 0, 50),
            'phone'            => $phone,
            'shipping_address' => [
                'first_name' => mb_substr($pesanan['nama_penerima'], 0, 50),
                'phone'      => $phone,
                'address'    => mb_substr($pesanan['alamat_pengiriman'], 0, 200),
            ]
        ],
        'callbacks' => [
            'finish' => $finish_url,
        ]
    ];

    // 4. Kirim request ke Midtrans Snap API via cURL
    $ch = curl_init(MIDTRANS_SNAP_API_URL);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Basic ' . base64_encode(MIDTRANS_SERVER_KEY . ':'),
        ],
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);

    $response   = curl_exec($ch);
    $curl_error = curl_error($ch);
    $http_code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($curl_error) {
        $GLOBALS['midtrans_last_error'] = "cURL Error: " . $curl_error;
        error_log("Midtrans cURL Error: " . $curl_error);
        return false;
    }

    $result = json_decode($response, true);

    if ($http_code === 201 && !empty($result['token'])) {
        $token = $result['token'];

        // Simpan snap_token ke database
        $update = $pdo->prepare("
            UPDATE pesanan 
            SET snap_token = ?, 
                status_pembayaran = IF(status_pembayaran = 'Belum Dibayar', 'Menunggu Pembayaran', status_pembayaran)
            WHERE id_pesanan = ?
        ");
        $update->execute([$token, (int)$id_pesanan]);

        return $token;
    } else {
        $err_msg = !empty($result['error_messages']) 
            ? implode(', ', $result['error_messages']) 
            : ("HTTP Code " . $http_code . ": " . $response);
        $GLOBALS['midtrans_last_error'] = $err_msg;
        error_log("Midtrans Error Response (HTTP $http_code): " . $response);
        return false;
    }
}

/**
 * Tampilkan badge status pembayaran dengan warna Bootstrap
 */
function status_badge_pembayaran($status)
{
    switch ($status) {
        case 'Lunas':
            return '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Lunas</span>';
        case 'Menunggu Pembayaran':
            return '<span class="badge bg-warning text-dark"><i class="fas fa-hourglass-half me-1"></i>Menunggu Pembayaran</span>';
        case 'Belum Dibayar':
            return '<span class="badge bg-secondary"><i class="fas fa-money-bill me-1"></i>Belum Dibayar</span>';
        case 'Kadaluarsa':
            return '<span class="badge bg-danger"><i class="fas fa-clock me-1"></i>Kadaluarsa</span>';
        case 'Gagal':
            return '<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Gagal</span>';
        default:
            return '<span class="badge bg-secondary">' . htmlspecialchars((string)$status) . '</span>';
    }
}

