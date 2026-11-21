<?php
// ============================================================
// FILE    : backend/helpers/biteship.php
// FUNGSI  : Helper Integrasi Biteship API (Ongkir, Order, Tracking)
// PROYEK  : Sistem Informasi PT Shafa Marwa Multiguna
// ORIGIN  : Way Halim, Bandar Lampung 35141
// DOCS    : https://docs.biteship.com
// ============================================================

// ----------------------
// 1. CARI TARIF ONGKIR
// ----------------------
/**
 * Ambil tarif ongkir dari Biteship berdasarkan kode pos asal-tujuan dan berat.
 * 
 * @param string $kode_pos_tujuan  Kode pos pembeli (5 digit)
 * @param int    $total_berat_gram Total berat dalam gram
 * @param int    $harga_produk     Nilai barang untuk proteksi/asuransi
 * @return array|false  Array tarif atau false jika gagal
 */
function biteship_get_rates(string $kode_pos_tujuan, int $total_berat_gram, int $harga_produk = 100000): array|false
{
    if (BITESHIP_API_KEY === '') {
        error_log('[Biteship] API key belum dikonfigurasi.');
        return false;
    }

    $payload = [
        'origin_postal_code'      => BITESHIP_ORIGIN_POSTAL,
        'destination_postal_code' => $kode_pos_tujuan,
        'couriers'                => BITESHIP_COURIERS,
        'items'                   => [
            [
                'name'     => 'Produk PT SMM',
                'value'    => max(1, $harga_produk),
                'weight'   => max(1, $total_berat_gram),
                'quantity' => 1,
            ]
        ],
    ];

    $ch = curl_init(BITESHIP_API_URL . '/v1/rates/couriers');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => [
            'Authorization: ' . BITESHIP_API_KEY,
            'Content-Type: application/json',
        ],
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);

    $response  = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_err  = curl_error($ch);
    curl_close($ch);

    if ($curl_err || $http_code !== 200) {
        error_log("[Biteship] rates error HTTP $http_code: $response | cURL: $curl_err");
        return false;
    }

    $data = json_decode($response, true);

    if (empty($data['success']) || empty($data['pricing'])) {
        error_log("[Biteship] rates kosong: $response");
        return false;
    }

    // Format output
    $tarif_list = [];
    foreach ($data['pricing'] as $p) {
        $tarif_list[] = [
            'courier_code'       => $p['courier_code'],
            'courier_name'       => $p['courier_name'],
            'courier_service'    => $p['courier_service_code'] ?? $p['type'],
            'courier_service_name' => $p['courier_service_name'] ?? ($p['courier_name'] . ' ' . ($p['type'] ?? '')),
            'duration'           => $p['shipment_duration_range'] ?? ($p['duration'] ?? '-'),
            'price'              => (int)($p['price'] ?? 0),
        ];
    }

    // Urutkan dari harga termurah
    usort($tarif_list, fn($a, $b) => $a['price'] - $b['price']);

    return $tarif_list;
}

// -------------------------
// 2. BUAT ORDER (BOOKING)
// -------------------------
/**
 * Buat order pengiriman di Biteship setelah pembayaran Lunas.
 * Mendapatkan nomor resi (waybill_id) otomatis dari kurir.
 *
 * @param PDO   $pdo
 * @param array $pesanan  Row dari tabel pesanan
 * @return array|false  ['biteship_order_id', 'waybill_id'] atau false jika gagal
 */
function biteship_create_order(PDO $pdo, array $pesanan): array|false
{
    if (BITESHIP_API_KEY === '') {
        error_log('[Biteship] API key belum dikonfigurasi.');
        return false;
    }

    // Ambil detail item pesanan
    $stmt = $pdo->prepare("
        SELECT d.nama_produk_saat_pesan, d.harga_satuan, d.jumlah, COALESCE(p.berat, 500) AS berat_satuan
        FROM detail_pesanan d
        LEFT JOIN produk p ON p.id_produk = d.id_produk
        WHERE d.id_pesanan = ?
    ");
    $stmt->execute([(int)$pesanan['id_pesanan']]);
    $items_db = $stmt->fetchAll();

    if (empty($items_db)) {
        error_log("[Biteship] Tidak ada item untuk pesanan ID " . $pesanan['id_pesanan']);
        return false;
    }

    $items = [];
    foreach ($items_db as $it) {
        $items[] = [
            'name'     => mb_substr($it['nama_produk_saat_pesan'], 0, 100),
            'value'    => (int)round((float)$it['harga_satuan']),
            'weight'   => max(1, (int)$it['berat_satuan']),
            'quantity' => (int)$it['jumlah'],
        ];
    }

    $no_hp_bersih = preg_replace('/[^0-9+]/', '', (string)($pesanan['no_hp_penerima'] ?? ''));
    if (empty($no_hp_bersih)) {
        $no_hp_bersih = '081234567890';
    }

    $kode_pos_tujuan = $pesanan['kode_pos_tujuan'] ?? '00000';

    $payload = [
        'shipper_contact_name'    => 'PT Shafa Marwa Multiguna',
        'shipper_contact_phone'   => BITESHIP_ORIGIN_PHONE,
        'shipper_contact_email'   => BITESHIP_ORIGIN_EMAIL,
        'shipper_organization'    => 'PT Shafa Marwa Multiguna',
        'origin_contact_name'     => 'Gudang PT SMM',
        'origin_contact_phone'    => BITESHIP_ORIGIN_PHONE,
        'origin_address'          => BITESHIP_ORIGIN_ADDRESS,
        'origin_note'             => 'Hubungi pengirim sebelum datang.',
        'origin_postal_code'      => (int)BITESHIP_ORIGIN_POSTAL,
        'destination_contact_name'  => mb_substr($pesanan['nama_penerima'], 0, 100),
        'destination_contact_phone' => $no_hp_bersih,
        'destination_address'       => mb_substr($pesanan['alamat_pengiriman'], 0, 255),
        'destination_postal_code'   => (int)$kode_pos_tujuan,
        'destination_note'          => !empty($pesanan['catatan']) ? mb_substr($pesanan['catatan'], 0, 100) : '',
        'courier_company'           => strtolower($pesanan['kurir_code'] ?? $pesanan['kurir']),
        'courier_type'              => strtolower($pesanan['kurir_service'] ?? $pesanan['layanan_pengiriman']),
        'delivery_type'             => 'now',
        'order_note'                => 'Pesanan: ' . $pesanan['kode_pesanan'],
        'metadata'                  => [
            'kode_pesanan' => $pesanan['kode_pesanan'],
        ],
        'items' => $items,
    ];

    $ch = curl_init(BITESHIP_API_URL . '/v1/orders');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => [
            'Authorization: ' . BITESHIP_API_KEY,
            'Content-Type: application/json',
        ],
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);

    $response  = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_err  = curl_error($ch);
    curl_close($ch);

    if ($curl_err) {
        error_log("[Biteship] create_order cURL error: $curl_err");
        return false;
    }

    $data = json_decode($response, true);

    if (empty($data['success'])) {
        error_log("[Biteship] create_order HTTP $http_code: $response");
        return false;
    }

    return [
        'biteship_order_id' => $data['id'] ?? null,
        'waybill_id'        => $data['courier']['waybill_id'] ?? null,
        'courier_name'      => $data['courier']['company'] ?? null,
        'tracking_id'       => $data['courier']['tracking_id'] ?? null,
    ];
}

// -------------------------
// 3. GET ORDER DARI BITESHIP
// -------------------------
/**
 * Ambil detail order dari Biteship API
 *
 * @param string $biteship_order_id
 * @return array|false
 */
function biteship_get_order(string $biteship_order_id): array|false
{
    $url = BITESHIP_API_URL . '/v1/orders/' . urlencode($biteship_order_id);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'Authorization: ' . BITESHIP_API_KEY,
        ],
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);

    $response  = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200) {
        return false;
    }

    $data = json_decode($response, true);
    return !empty($data['success']) ? $data : false;
}

// -------------------------
// 4. TRACKING PAKET
// -------------------------
/**
 * Ambil riwayat pelacakan paket dari Biteship.
 * Mendukung tracking via tracking_id, order_id, atau waybill_id + courier_code.
 *
 * @param string $identifier      Biteship tracking_id atau order_id atau waybill_id
 * @param string|null $courier_code Kode kurir (jne, jnt, sicepat, dll) jika menggunakan waybill
 * @return array|false Array tracking events atau false
 */
function biteship_get_tracking(string $identifier, ?string $courier_code = null): array|false
{
    if (empty($identifier)) {
        return false;
    }

    // Opsi A: Endpoint standar /v1/trackings/{id}
    $url = BITESHIP_API_URL . '/v1/trackings/' . urlencode($identifier);
    if (!empty($courier_code)) {
        // Opsi B: Tracking via waybill + kurir: /v1/trackings/{waybill_id}/couriers/{courier_code}
        $url = BITESHIP_API_URL . '/v1/trackings/' . urlencode($identifier) . '/couriers/' . urlencode(strtolower($courier_code));
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'Authorization: ' . BITESHIP_API_KEY,
            'Content-Type: application/json',
        ],
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);

    $response  = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code === 200 && $response) {
        $data = json_decode($response, true);
        if (!empty($data['success']) && !empty($data['history'])) {
            return $data['history'];
        }
    }

    return false;
}

// -------------------------
// 5. SYNC STATUS DARI BITESHIP KE DATABASE LOCAL
// (Otomatis berjalan di Localhost tanpa perlu Webhook)
// -------------------------
/**
 * Sinkronkan status pesanan dan tracking history dari Biteship ke database lokal.
 *
 * @param PDO $pdo
 * @param int $id_pesanan
 * @return array|false
 */
function biteship_sync_status(PDO $pdo, int $id_pesanan): array|false
{
    $stmt = $pdo->prepare("SELECT * FROM pesanan WHERE id_pesanan = ? LIMIT 1");
    $stmt->execute([$id_pesanan]);
    $pesanan = $stmt->fetch();

    if (!$pesanan || empty($pesanan['biteship_order_id'])) {
        return false;
    }

    // 1. Ambil data order live dari Biteship API
    $b_order = biteship_get_order($pesanan['biteship_order_id']);
    if (!$b_order) {
        return false;
    }

    $courier_status = strtolower($b_order['courier']['status'] ?? ($b_order['status'] ?? ''));
    $waybill_id     = $b_order['courier']['waybill_id'] ?? null;
    $tracking_id    = $b_order['courier']['tracking_id'] ?? null;

    // 2. Ambil riwayat tracking (coba dari berbagai sumber yang disediakan Biteship)
    $tracking_history = [];
    if (!empty($b_order['history']) && is_array($b_order['history'])) {
        $tracking_history = $b_order['history'];
    }

    if (empty($tracking_history) && !empty($tracking_id)) {
        $tr = biteship_get_tracking($tracking_id);
        if ($tr) $tracking_history = $tr;
    }

    if (empty($tracking_history)) {
        $tr = biteship_get_tracking($pesanan['biteship_order_id']);
        if ($tr) $tracking_history = $tr;
    }

    if (empty($tracking_history) && !empty($waybill_id) && !empty($pesanan['kurir_code'])) {
        $tr = biteship_get_tracking($waybill_id, $pesanan['kurir_code']);
        if ($tr) $tracking_history = $tr;
    }

    // Jika Biteship API belum kirim history baru, pertahankan history lama
    if (empty($tracking_history) && !empty($pesanan['tracking_history'])) {
        $tracking_history = json_decode($pesanan['tracking_history'], true) ?? [];
    }

    // 3. Mapping status kurir Biteship ke status_pesanan PT SMM
    $status_baru = $pesanan['status_pesanan'];
    switch ($courier_status) {
        case 'allocated':
        case 'picking_up':
        case 'picked':
            $status_baru = 'Diproses';
            break;
        case 'dropping_off':
        case 'dropping':
        case 'in_transit':
            $status_baru = 'Dikirim';
            break;
        case 'delivered':
            $status_baru = 'Selesai';
            break;
        case 'rejected':
        case 'returned':
        case 'cancelled':
            $status_baru = 'Dibatalkan';
            break;
    }

    $final_resi = !empty($waybill_id) ? $waybill_id : $pesanan['nomor_resi'];

    // 4. Update ke database
    $update = $pdo->prepare("
        UPDATE pesanan
        SET status_pesanan   = ?,
            nomor_resi       = ?,
            tracking_history = ?
        WHERE id_pesanan = ?
    ");
    $update->execute([
        $status_baru,
        $final_resi,
        json_encode($tracking_history),
        $id_pesanan,
    ]);

    return [
        'status_pesanan' => $status_baru,
        'courier_status' => $courier_status,
        'waybill_id'     => $final_resi,
        'history'        => $tracking_history,
    ];
}

// -------------------------
// 6. SIMULASI TRACKING STATUS (UNTUK DEMO / SIDANG DI LOCALHOST)
// -------------------------
function biteship_simulate_status(PDO $pdo, int $id_pesanan, string $target_status): bool
{
    $stmt = $pdo->prepare("SELECT * FROM pesanan WHERE id_pesanan = ? LIMIT 1");
    $stmt->execute([$id_pesanan]);
    $pesanan = $stmt->fetch();
    if (!$pesanan) return false;

    $history = !empty($pesanan['tracking_history'])
        ? json_decode($pesanan['tracking_history'], true)
        : [];
    if (!is_array($history)) $history = [];

    $now = date('Y-m-d H:i:s');
    $kurir_name = $pesanan['kurir'] ?? 'Kurir Ekspedisi';
    $status_pesanan_baru = $pesanan['status_pesanan'];

    // Generate dummy resi jika belum ada
    $resi = $pesanan['nomor_resi'];
    if (empty($resi)) {
        $prefix = strtoupper(substr($pesanan['kurir_code'] ?? 'EXP', 0, 3));
        $resi = $prefix . date('ymd') . rand(10000, 99999) . 'ID';
    }

    switch ($target_status) {
        case 'picked':
            $status_pesanan_baru = 'Diproses';
            $history[] = [
                'status'      => 'picked',
                'note'        => 'Paket telah dijemput oleh driver ' . $kurir_name . ' dari gudang PT SMM.',
                'location'    => 'Gudang Way Halim, Bandar Lampung',
                'updated_at'  => $now,
            ];
            break;

        case 'dropping':
            $status_pesanan_baru = 'Dikirim';
            $history[] = [
                'status'      => 'dropping',
                'note'        => 'Paket sedang dalam perjalanan menuju alamat penerima (' . $pesanan['nama_penerima'] . ').',
                'location'    => 'Hub Logistik Transit',
                'updated_at'  => $now,
            ];
            break;

        case 'delivered':
            $status_pesanan_baru = 'Selesai';
            $history[] = [
                'status'      => 'delivered',
                'note'        => 'Paket telah berhasil diterima oleh penerima (' . $pesanan['nama_penerima'] . '). Terima kasih!',
                'location'    => $pesanan['alamat_pengiriman'],
                'updated_at'  => $now,
            ];
            break;

        default:
            return false;
    }

    $upd = $pdo->prepare("
        UPDATE pesanan
        SET status_pesanan   = ?,
            nomor_resi       = ?,
            tracking_history = ?
        WHERE id_pesanan = ?
    ");
    return $upd->execute([
        $status_pesanan_baru,
        $resi,
        json_encode($history),
        $id_pesanan,
    ]);
}

// -------------------------
// 7. STATUS BADGE TRACKING
// -------------------------
function status_badge_pengiriman(string $status): string
{
    $map = [
        'allocated'  => ['bg-info',     'fas fa-box',         'Dijemput'],
        'picked'     => ['bg-primary',  'fas fa-shipping-fast','Di Tangan Kurir'],
        'dropping'   => ['bg-primary',  'fas fa-truck',        'Dikirim'],
        'delivered'  => ['bg-success',  'fas fa-check-circle', 'Terkirim'],
        'rejected'   => ['bg-danger',   'fas fa-times-circle', 'Ditolak'],
        'returned'   => ['bg-warning',  'fas fa-undo',         'Dikembalikan'],
        'cancelled'  => ['bg-secondary','fas fa-ban',          'Dibatalkan'],
    ];

    $s = strtolower($status);
    [$bg, $icon, $label] = $map[$s] ?? ['bg-secondary', 'fas fa-question', htmlspecialchars($status)];
    return "<span class=\"badge {$bg}\"><i class=\"{$icon} me-1\"></i>{$label}</span>";
}

