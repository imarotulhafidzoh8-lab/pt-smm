<?php
// ============================================================
// FILE    : frontend/pengguna/detail-pesanan.php
// FUNGSI  : Rincian Nota & Status Pesanan Pelanggan (Pengguna)
// KONSEP  : Minimalis, Bersih, Rapi & Informatif (No AI Slop)
// PROYEK  : Sistem Informasi PT Shafa Marwa Multiguna
// ============================================================
require_once __DIR__ . '/../../config/app.php';
cek_pengguna();

$id_user    = (int)$_SESSION['id_user'];
$id_pesanan = (int)($_GET['id'] ?? 0);

if ($id_pesanan <= 0) {
    flash('error', 'Pesanan tidak ditemukan.');
    redirect(base_url('frontend/pengguna/riwayat-pesanan.php'));
}

// Ambil data pesanan
$pesanan = null;
try {
    $stmt = $pdo->prepare("SELECT * FROM pesanan WHERE id_pesanan = ? AND id_user = ? LIMIT 1");
    $stmt->execute([$id_pesanan, $id_user]);
    $pesanan = $stmt->fetch();
} catch (PDOException $e) {}

if (!$pesanan) {
    flash('error', 'Pesanan tidak ditemukan atau Anda tidak memiliki akses ke pesanan ini.');
    redirect(base_url('frontend/pengguna/riwayat-pesanan.php'));
}

// SINKRONISASI OTOMATIS KE BITESHIP:
// Setiap kali pelanggan membuka detail pesanan, sistem langsung mengambil update kurir terbaru
if (!empty($pesanan['biteship_order_id']) && !in_array($pesanan['status_pesanan'], ['Selesai', 'Dibatalkan'])) {
    biteship_sync_status($pdo, $id_pesanan);
    $stmt = $pdo->prepare("SELECT * FROM pesanan WHERE id_pesanan = ? AND id_user = ? LIMIT 1");
    $stmt->execute([$id_pesanan, $id_user]);
    $pesanan = $stmt->fetch();
}

// Ambil item produk dalam pesanan
$items = [];
try {
    $stmt = $pdo->prepare("
        SELECT d.*, p.gambar, p.satuan
        FROM detail_pesanan d
        LEFT JOIN produk p ON d.id_produk = p.id_produk
        WHERE d.id_pesanan = ?
    ");
    $stmt->execute([$id_pesanan]);
    $items = $stmt->fetchAll();
} catch (PDOException $e) {
    $items = [];
}

$page_title = 'Nota Pesanan: ' . $pesanan['kode_pesanan'];
$user_page  = 'riwayat';
$company    = get_company_profile($pdo);

// Ambil atau buat Snap Token Midtrans jika belum lunas
$snap_token = null;
$status_bayar = $pesanan['status_pembayaran'] ?? 'Belum Dibayar';
if ($status_bayar !== 'Lunas' && $pesanan['status_pesanan'] !== 'Dibatalkan') {
    $snap_token = midtrans_get_or_create_snap_token($pdo, $id_pesanan);
}

// Ambil riwayat perjalanan kurir (Biteship)
$tracking_events = [];
if (!empty($pesanan['tracking_history'])) {
    $tracking_events = json_decode($pesanan['tracking_history'], true) ?? [];
}

// Hitung subtotal produk
$ongkir = (float)($pesanan['ongkir'] ?? 0);
$subtotal_produk = (float)$pesanan['total_harga'] - $ongkir;

// Logika Tahapan Alur Pesanan (Stepper):
$st = $pesanan['status_pesanan'] ?? 'Menunggu Pembayaran';

// Step 1: Dipesan (selalu passed)
$step1_passed = true;

// Step 2: Pembayaran
$step2_passed = ($status_bayar === 'Lunas') || in_array($st, ['Diproses', 'Dikirim', 'Selesai'], true);
$step2_active = !$step2_passed && ($st !== 'Dibatalkan');

// Step 3: Diproses (Gudang)
$step3_passed = in_array($st, ['Dikirim', 'Selesai'], true);
$step3_active = ($st === 'Diproses');

// Step 4: Pengiriman (Kurir)
$step4_passed = ($st === 'Selesai');
$step4_active = ($st === 'Dikirim');

// Step 5: Selesai
$step5_passed = ($st === 'Selesai');
$step5_active = false;

require_once __DIR__ . '/../includes/pengguna-header.php';
?>

<style>
/* ============================================================
   MINIMALIST UI CUSTOM STYLES (Clean & Professional)
   ============================================================ */
.detail-card {
    background: #ffffff;
    border: 1px solid #e8edf3;
    border-radius: 18px;
    box-shadow: 0 3px 12px rgba(15, 39, 68, 0.045);
    overflow: hidden;
}
.detail-card-header {
    padding: 16px 20px;
    border-bottom: 1px solid #eef2f6;
    background: #fff;
}
.detail-card-body {
    padding: 20px;
}
.detail-hero { background:linear-gradient(135deg,#071426 0%,#0f2744 58%,#1e3a5f 100%); box-shadow:0 12px 28px rgba(15,39,68,.12); }
.detail-hero-meta { color:rgba(255,255,255,.7); }
.destination-card { background:linear-gradient(135deg,#fff 0%,#f8fbff 100%); }
.destination-icon { width:48px;height:48px;background:#eaf2fc;color:#1e3a5f; }

/* Stepper Minimalis Compact */
.stepper-container {
    position: relative;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}
.stepper-track {
    position: absolute;
    top: 14px;
    left: 10%;
    right: 10%;
    height: 2px;
    background: #e2e8f0;
    z-index: 1;
}
.stepper-step {
    position: relative;
    z-index: 2;
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: 1;
    text-align: center;
}
.stepper-circle {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #ffffff;
    border: 2px solid #cbd5e1;
    color: #64748b;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 700;
    margin-bottom: 5px;
    transition: all 0.2s ease;
}
.stepper-step.passed .stepper-circle {
    background: #10b981;
    border-color: #10b981;
    color: #ffffff;
}
.stepper-step.active .stepper-circle {
    background: #1e3a5f;
    border-color: #1e3a5f;
    color: #ffffff;
    box-shadow: 0 0 0 3px rgba(30, 58, 95, 0.15);
}
.stepper-title {
    font-size: 11px;
    font-weight: 600;
    color: #64748b;
    margin-bottom: 1px;
}
.stepper-step.passed .stepper-title,
.stepper-step.active .stepper-title {
    color: #111111;
}
.stepper-caption {
    font-size: 10px;
    color: #94a3b8;
}
.stepper-step.passed .stepper-caption {
    color: #10b981;
    font-weight: 500;
}
.stepper-step.active .stepper-caption {
    color: #111111;
    font-weight: 600;
}

/* Timeline pelacakan */
.shipping-summary { display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px; }
.shipping-summary-item { min-width:0;padding:16px;border:1px solid #e8edf3;border-radius:14px;background:#f8fafc; }
.tracking-heading { display:flex;align-items:center;gap:8px;color:#334155;font-size:.9rem;font-weight:700;margin-bottom:14px; }
.tracking-count { margin-left:auto;padding:4px 10px;border-radius:999px;background:#eff6ff;color:#1e3a5f;font-size:11px;font-weight:700; }
.simple-timeline {
    position: relative;
    padding-left: 28px;
}
.simple-timeline::before {
    content: '';
    position: absolute;
    top: 14px;
    bottom: 18px;
    left: 9px;
    width: 2px;
    background: linear-gradient(#1e3a5f,#dbe4ef);
}
.timeline-point {
    position: relative;
    padding: 0 0 16px;
}
.timeline-point:last-child {
    padding-bottom: 0;
}
.timeline-point::before {
    content: '';
    position: absolute;
    left: -27px;
    top: 16px;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: #fff;
    border: 3px solid #cbd5e1;
    box-shadow: 0 0 0 4px #fff;
}
.timeline-point.latest::before {
    background: #1e3a5f;
    border-color: #1e3a5f;
}
.timeline-event { padding:13px 15px;border:1px solid #e8edf3;border-radius:14px;background:#fff; }
.timeline-point.latest .timeline-event { border-color:#c9d9ed;background:#f8fbff; }
.timeline-meta { display:flex;align-items:center;gap:7px;flex-wrap:wrap;margin-top:6px;color:#64748b;font-size:11px; }
@media (max-width: 575.98px) { .shipping-summary { grid-template-columns:1fr; } }

.print-invoice { display:none; }
@media print {
    @page { size:A4 portrait; margin:14mm; }
    html, body { background:#fff!important; color:#111!important; font-family:Arial,Helvetica,sans-serif!important; font-size:10pt!important; }
    .navbar, .page-header, footer, .footer, .footer-bottom { display:none!important; }
    .user-dashboard-wrapper { width:100%!important; max-width:none!important; margin:0!important; padding:0!important; }
    .user-dashboard-wrapper > .row, .user-dashboard-wrapper .col-12 { display:block!important; width:100%!important; margin:0!important; padding:0!important; }
    .user-dashboard-wrapper .col-12 > :not(#print-invoice) { display:none!important; }
    #print-invoice, .user-dashboard-wrapper .col-12 > #print-invoice { display:block!important; visibility:visible!important; position:relative!important; width:100%!important; height:auto!important; overflow:visible!important; color:#111!important; font-size:10pt!important; }
    #print-invoice * { visibility:visible!important; box-shadow:none!important; text-shadow:none!important; }
    .invoice-top { display:flex; justify-content:space-between; align-items:flex-start; gap:24px; padding-bottom:16px; border-bottom:2px solid #1e3a5f; }
    .invoice-company { display:flex; align-items:center; gap:12px; max-width:62%; }
    .invoice-company img { width:58px; height:58px; object-fit:contain; }
    .invoice-company-name { font-size:15pt; font-weight:700; margin:0 0 4px; }
    .invoice-company-info { color:#555; font-size:8.5pt; line-height:1.45; }
    .invoice-title { text-align:right; }
    .invoice-title h1 { color:#1e3a5f; font-size:23pt; letter-spacing:1px; margin:0 0 8px; }
    .invoice-title div { margin:3px 0; font-size:9pt; }
    .invoice-parties { display:grid; grid-template-columns:1fr 1fr; gap:28px; padding:18px 0; }
    .invoice-label { margin-bottom:6px; color:#64748b; font-size:8pt; font-weight:700; letter-spacing:.6px; text-transform:uppercase; }
    .invoice-party-name { font-weight:700; font-size:11pt; margin-bottom:4px; }
    .invoice-party-info { white-space:pre-line; color:#444; line-height:1.5; font-size:9pt; }
    .invoice-table { width:100%; border-collapse:collapse; margin:8px 0 18px; font-size:9pt; }
    .invoice-table th { padding:9px 8px; color:#fff; background:#1e3a5f!important; text-align:left; font-weight:700; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
    .invoice-table td { padding:9px 8px; border-bottom:1px solid #dfe4ea; vertical-align:top; }
    .invoice-table .text-right { text-align:right; }
    .invoice-table .text-center { text-align:center; }
    .invoice-totals { width:270px; margin-left:auto; }
    .invoice-total-row { display:flex; justify-content:space-between; gap:20px; padding:5px 0; color:#444; }
    .invoice-grand-total { border-top:1px solid #cbd5e1; margin-top:5px; padding-top:10px; color:#1e3a5f; font-size:13pt; font-weight:700; }
    .invoice-note { margin-top:30px; padding-top:12px; border-top:1px solid #e2e8f0; color:#64748b; font-size:8.5pt; }
    tr, .invoice-parties, .invoice-totals { break-inside:avoid; }
}
</style>

<section id="print-invoice" class="print-invoice">
    <div class="invoice-top">
        <div class="invoice-company">
            <img src="<?= asset_url('images/logo-smm-brand.png') ?>" alt="Logo PT SMM">
            <div>
                <div class="invoice-company-name"><?= e($company['nama_perusahaan'] ?? 'PT Shafa Marwa Multiguna') ?></div>
                <div class="invoice-company-info"><?= e($company['alamat'] ?? '') ?></div>
                <div class="invoice-company-info"><?= e($company['no_telepon'] ?? '') ?><?= !empty($company['email']) ? ' · ' . e($company['email']) : '' ?></div>
            </div>
        </div>
        <div class="invoice-title">
            <h1>INVOICE</h1>
            <div><strong>No. Invoice:</strong> <?= e($pesanan['kode_pesanan']) ?></div>
            <div><strong>Tanggal:</strong> <?= date('d M Y', strtotime($pesanan['tanggal_pesanan'])) ?></div>
            <div><strong>Status Pesanan:</strong> <?= e($pesanan['status_pesanan']) ?></div>
            <div><strong>Status Pembayaran:</strong> <?= e($status_bayar) ?></div>
        </div>
    </div>

    <div class="invoice-parties">
        <div>
            <div class="invoice-label">Ditagihkan kepada</div>
            <div class="invoice-party-name"><?= e($pesanan['nama_penerima']) ?></div>
            <div class="invoice-party-info"><?= e($pesanan['no_hp_penerima']) ?></div>
        </div>
        <div>
            <div class="invoice-label">Alamat pengiriman</div>
            <div class="invoice-party-info"><?= e($pesanan['alamat_pengiriman']) ?><?= !empty($pesanan['kode_pos_tujuan']) ? "\n" . 'Kode Pos: ' . e($pesanan['kode_pos_tujuan']) : '' ?></div>
        </div>
    </div>

    <table class="invoice-table">
        <thead><tr><th style="width:44px;">No.</th><th>Deskripsi</th><th class="text-center" style="width:90px;">Jumlah</th><th class="text-right" style="width:130px;">Harga Satuan</th><th class="text-right" style="width:140px;">Subtotal</th></tr></thead>
        <tbody>
            <?php foreach ($items as $idx => $it): ?>
                <tr>
                    <td><?= $idx + 1 ?></td>
                    <td><?= e($it['nama_produk_saat_pesan']) ?></td>
                    <td class="text-center"><?= (int)$it['jumlah'] ?> <?= e($it['satuan'] ?? 'pcs') ?></td>
                    <td class="text-right"><?= format_rupiah($it['harga_satuan']) ?></td>
                    <td class="text-right"><?= format_rupiah($it['subtotal']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="invoice-totals">
        <div class="invoice-total-row"><span>Subtotal Produk</span><span><?= format_rupiah($subtotal_produk) ?></span></div>
        <div class="invoice-total-row"><span>Ongkos Kirim</span><span><?= format_rupiah($ongkir) ?></span></div>
        <div class="invoice-total-row invoice-grand-total"><span>Total</span><span><?= format_rupiah($pesanan['total_harga']) ?></span></div>
    </div>

    <?php if (!empty($pesanan['catatan'])): ?>
        <div class="invoice-note"><strong>Catatan:</strong> <?= e($pesanan['catatan']) ?></div>
    <?php else: ?>
        <div class="invoice-note">Terima kasih telah berbelanja di <?= e($company['nama_perusahaan'] ?? 'PT Shafa Marwa Multiguna') ?>.</div>
    <?php endif; ?>
</section>

<!-- ============================================================
     HEADER BAR: Judul & Aksi
============================================================ -->
<div class="detail-hero text-white rounded-4 p-4 p-md-5 d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3 position-relative overflow-hidden">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
            <span class="badge rounded-pill px-3 py-2" style="background:rgba(255,255,255,.12);color:#fff;border:1px solid rgba(255,255,255,.2);"><i class="fas fa-receipt me-1"></i> DETAIL PESANAN</span>
        </div>
        <h3 class="fw-bold text-white mt-2 mb-2">Pesanan #<?= e($pesanan['kode_pesanan']) ?></h3>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <?= status_badge_pesanan($pesanan['status_pesanan']) ?>
            <span class="detail-hero-meta small"><i class="far fa-clock me-1"></i>Dibuat <?= date('d M Y, H:i', strtotime($pesanan['tanggal_pesanan'])) ?> WIB</span>
        </div>
    </div>
    <div class="d-flex gap-2">
        <button onclick="cetakInvoice();" class="btn btn-light btn-sm rounded-pill px-3">
            <i class="fas fa-print me-1"></i> Cetak Invoice
        </button>
        <a href="<?= base_url('frontend/pengguna/riwayat-pesanan.php') ?>" class="btn btn-outline-light btn-sm rounded-pill px-3">
            <i class="fas fa-arrow-left me-1"></i> Riwayat
        </a>
    </div>
</div>

<!-- ============================================================
     1. STEPPER ALUR STATUS PESANAN (Minimalis & Elegan)
============================================================ -->
<div class="detail-card mb-4 p-3 p-md-4">
    <?php if ($st === 'Dibatalkan'): ?>
        <div class="alert alert-danger py-2 mb-0 small text-center">
            <i class="fas fa-ban me-1"></i> Pesanan ini telah <strong>Dibatalkan</strong>.
        </div>
    <?php else: ?>
        <div class="stepper-container">
            <div class="stepper-track"></div>

            <!-- Step 1: Dipesan -->
            <div class="stepper-step passed">
                <div class="stepper-circle"><i class="fas fa-check"></i></div>
                <div class="stepper-title">1. Dipesan</div>
                <div class="stepper-caption">Tercatat</div>
            </div>

            <!-- Step 2: Pembayaran -->
            <div class="stepper-step <?= $step2_passed ? 'passed' : ($step2_active ? 'active' : '') ?>">
                <div class="stepper-circle">
                    <?php if ($step2_passed): ?><i class="fas fa-check"></i><?php else: ?>2<?php endif; ?>
                </div>
                <div class="stepper-title">2. Pembayaran</div>
                <div class="stepper-caption"><?= $step2_passed ? 'Lunas' : 'Menunggu Bayar' ?></div>
            </div>

            <!-- Step 3: Diproses -->
            <div class="stepper-step <?= $step3_passed ? 'passed' : ($step3_active ? 'active' : '') ?>">
                <div class="stepper-circle">
                    <?php if ($step3_passed): ?><i class="fas fa-check"></i><?php else: ?>3<?php endif; ?>
                </div>
                <div class="stepper-title">3. Diproses</div>
                <div class="stepper-caption"><?= $step3_passed ? 'Siap Kirim' : ($step3_active ? 'Sedang Dikemas' : 'Antrean Packing') ?></div>
            </div>

            <!-- Step 4: Pengiriman -->
            <div class="stepper-step <?= $step4_passed ? 'passed' : ($step4_active ? 'active' : '') ?>">
                <div class="stepper-circle">
                    <?php if ($step4_passed): ?><i class="fas fa-check"></i><?php else: ?>4<?php endif; ?>
                </div>
                <div class="stepper-title">4. Pengiriman</div>
                <div class="stepper-caption"><?= $step4_passed ? 'Terkirim' : ($step4_active ? 'Dalam Perjalanan' : 'Menunggu Kurir') ?></div>
            </div>

            <!-- Step 5: Selesai -->
            <div class="stepper-step <?= $step5_passed ? 'passed' : '' ?>">
                <div class="stepper-circle">
                    <?php if ($step5_passed): ?><i class="fas fa-check"></i><?php else: ?>5<?php endif; ?>
                </div>
                <div class="stepper-title">5. Selesai</div>
                <div class="stepper-caption"><?= $step5_passed ? 'Tiba di Tujuan' : 'Selesai' ?></div>
            </div>
        </div>
    <?php endif; ?>
</div>

<div class="detail-card destination-card mb-4">
    <div class="detail-card-header d-flex align-items-center gap-3">
        <div class="destination-icon rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"><i class="fas fa-map-marker-alt"></i></div>
        <div><span class="fw-bold text-navy d-block">Tujuan Pengiriman</span><small class="text-muted">Alamat penerima pesanan ini</small></div>
    </div>
    <div class="detail-card-body">
        <div class="row align-items-center g-3">
            <div class="col-md-4">
                <div class="small text-muted mb-1">Nama Penerima</div>
                <div class="fw-bold text-navy"><?= e($pesanan['nama_penerima']) ?></div>
                <div class="small text-muted mt-1"><i class="fas fa-phone me-1"></i><?= e($pesanan['no_hp_penerima']) ?></div>
            </div>
            <div class="col-md-5">
                <div class="small text-muted mb-1">Alamat Lengkap</div>
                <div class="text-secondary"><?= nl2br(e($pesanan['alamat_pengiriman'])) ?></div>
                <?php if (!empty($pesanan['kode_pos_tujuan'])): ?><span class="badge bg-light text-dark border rounded-pill mt-2">Kode Pos: <?= e($pesanan['kode_pos_tujuan']) ?></span><?php endif; ?>
            </div>
            <div class="col-md-3">
                <?php if (!empty($pesanan['catatan'])): ?>
                    <div class="small text-muted mb-1">Catatan Pengiriman</div><div class="small text-secondary fst-italic">“<?= e($pesanan['catatan']) ?>”</div>
                <?php else: ?>
                    <div class="small text-muted"><i class="fas fa-info-circle me-1"></i>Tidak ada catatan pengiriman.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================
     2. GRID UTAMA (Kiri: Produk & Pengiriman | Kanan: Pembayaran & Alamat)
============================================================ -->
<div class="row g-4 mb-4">

    <!-- ==========================================
         KOLOM KIRI: Produk & Lacak Pengiriman
    ========================================== -->
    <div class="col-lg-7 col-xl-8">

        <!-- Kartu 1: Rincian Barang yang Dipesan -->
        <div class="detail-card mb-4">
            <div class="detail-card-header d-flex justify-content-between align-items-center">
                <span class="fw-bold text-navy">Rincian Barang</span>
                <small class="text-muted"><?= count($items) ?> macam produk</small>
            </div>
            <div class="detail-card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0" style="font-size: 14px;">
                        <thead class="bg-light text-muted">
                            <tr>
                                <th class="ps-3 py-2 fw-semibold">Produk</th>
                                <th class="text-end py-2 fw-semibold">Harga</th>
                                <th class="text-center py-2 fw-semibold">Qty</th>
                                <th class="text-end pe-3 py-2 fw-semibold">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $it): ?>
                                <tr>
                                    <td class="ps-3 py-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="<?= upload_url('produk', $it['gambar']) ?>" alt="Foto" class="rounded border" style="width: 44px; height: 44px; object-fit: cover;">
                                            <div>
                                                <div class="fw-bold text-navy"><?= e($it['nama_produk_saat_pesan']) ?></div>
                                                <small class="text-muted">Item #<?= $it['id_produk'] ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end py-3"><?= format_rupiah($it['harga_satuan']) ?></td>
                                    <td class="text-center py-3">
                                        <span class="badge bg-light text-dark border"><?= $it['jumlah'] ?> <?= e($it['satuan'] ?? 'pcs') ?></span>
                                    </td>
                                    <td class="text-end pe-3 py-3 fw-bold text-navy"><?= format_rupiah($it['subtotal']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Kartu 2: Pengiriman & Riwayat Perjalanan Paket -->
        <div class="detail-card">
            <div class="detail-card-header d-flex justify-content-between align-items-center">
                <span class="fw-bold text-navy">
                    <i class="fas fa-truck text-primary me-2"></i>Pengiriman & Lacak Paket
                </span>
                <?php if (!empty($pesanan['biteship_order_id'])): ?>
                    <a href="<?= base_url('frontend/pengguna/detail-pesanan.php?id=' . $id_pesanan . '&sync=1') ?>" class="btn btn-outline-secondary btn-sm py-1 px-2" style="font-size: 11px;">
                        <i class="fas fa-sync-alt me-1"></i> Perbarui
                    </a>
                <?php endif; ?>
            </div>
            <div class="detail-card-body">
                <!-- Info Ekspedisi & Resi -->
                <div class="shipping-summary mb-4">
                    <div class="shipping-summary-item">
                        <div class="small text-muted mb-2"><i class="fas fa-shipping-fast me-1"></i> Kurir Ekspedisi</div>
                        <div class="fw-bold text-navy">
                            <?= e($pesanan['kurir'] ?? 'Ekspedisi') ?>
                            <?php if (!empty($pesanan['kurir_service'])): ?>
                                <span class="badge bg-light text-dark border ms-1 fw-normal"><?= e($pesanan['kurir_service']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="shipping-summary-item">
                        <div class="small text-muted mb-2"><i class="fas fa-barcode me-1"></i> Nomor Resi</div>
                        <?php if (!empty($pesanan['nomor_resi'])): ?>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="font-monospace fw-bold text-navy px-2 py-2 bg-white border rounded-3">
                                    <?= e($pesanan['nomor_resi']) ?>
                                </span>
                                <button type="button" class="btn btn-light btn-sm border py-1 px-2" title="Salin Resi" onclick="salinResi('<?= e($pesanan['nomor_resi']) ?>');">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        <?php else: ?>
                            <span class="text-muted small fst-italic">Akan diterbitkan setelah kurir menjemput paket.</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Timeline Perjalanan Paket -->
                <?php if (!empty($tracking_events)): ?>
                    <div class="tracking-heading"><i class="fas fa-route text-primary"></i><span>Perjalanan Paket</span><span class="tracking-count"><?= count($tracking_events) ?> pembaruan</span></div>
                    <div class="simple-timeline">
                        <?php 
                        $reversed_events = array_reverse($tracking_events);
                        foreach ($reversed_events as $idx => $ev): 
                        ?>
                            <div class="timeline-point <?= $idx === 0 ? 'latest' : '' ?>">
                                <div class="timeline-event">
                                    <div class="d-flex align-items-center gap-2 flex-wrap"><div class="fw-semibold text-navy small"><?= htmlspecialchars($ev['description'] ?? $ev['note'] ?? '-') ?></div><?php if ($idx === 0): ?><span class="badge rounded-pill" style="background:#eaf2fc;color:#1e3a5f;font-size:10px;">Terbaru</span><?php endif; ?></div>
                                    <div class="timeline-meta">
                                        <?php if (!empty($ev['updated_at'])): ?><span><i class="far fa-clock"></i><?= date('d M Y, H:i', strtotime($ev['updated_at'])) ?> WIB</span><?php endif; ?>
                                        <?php if (!empty($ev['location'])): ?><span><i class="fas fa-map-marker-alt"></i><?= htmlspecialchars($ev['location']) ?></span><?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php elseif (!empty($pesanan['biteship_order_id'])): ?>
                    <div class="text-center py-4 px-3 rounded-4" style="background:#f8fafc;border:1px dashed #cbd5e1;">
                        <div class="d-flex align-items-center justify-content-center rounded-circle mx-auto mb-2" style="width:42px;height:42px;background:#eff6ff;color:#1e3a5f;"><i class="fas fa-clock"></i></div>
                        <div class="fw-semibold text-dark small">Menunggu pembaruan kurir</div><div class="text-muted small mt-1">Kurir belum melakukan penjemputan paket dari gudang PT SMM.</div>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4 px-3 rounded-4" style="background:#f8fafc;border:1px dashed #cbd5e1;">
                        <div class="d-flex align-items-center justify-content-center rounded-circle mx-auto mb-2" style="width:42px;height:42px;background:#eff6ff;color:#1e3a5f;"><i class="fas fa-box"></i></div>
                        <div class="fw-semibold text-dark small">Pengiriman belum dimulai</div><div class="text-muted small mt-1">Paket dijadwalkan setelah pembayaran diverifikasi.</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- ==========================================
         KOLOM KANAN: Ringkasan Pembayaran & Alamat
    ========================================== -->
    <div class="col-lg-5 col-xl-4">

        <!-- Kartu 1: Ringkasan Pembayaran -->
        <div class="detail-card mb-4" id="ringkasan-pembayaran">
            <div class="detail-card-header">
                <span class="fw-bold text-navy">Ringkasan Pembayaran</span>
            </div>
            <div class="detail-card-body">
                <div class="d-flex justify-content-between mb-2 small text-muted">
                    <span>Subtotal Produk</span>
                    <span class="text-dark fw-medium"><?= format_rupiah($subtotal_produk) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-3 small text-muted">
                    <span>Ongkos Kirim (<?= e($pesanan['kurir'] ?? 'Kurir') ?>)</span>
                    <span class="text-dark fw-medium"><?= format_rupiah($ongkir) ?></span>
                </div>
                <hr class="my-2 border-light-subtle">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="fw-bold text-navy">Total Pembayaran</span>
                    <span class="fs-5 fw-bold text-navy"><?= format_rupiah($pesanan['total_harga']) ?></span>
                </div>

                <!-- Status Pembayaran / Tombol Bayar -->
                <?php if ($status_bayar === 'Lunas'): ?>
                    <div class="p-3 bg-success-subtle text-success-emphasis border border-success-subtle rounded-3 small">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="fas fa-check-circle text-success fs-5"></i>
                            <span class="fw-bold">Pembayaran Lunas</span>
                        </div>
                        <div class="small opacity-75">
                            Metode: <strong><?= strtoupper(e($pesanan['metode_pembayaran'] ?? 'Online')) ?></strong>
                            <?php if (!empty($pesanan['waktu_pembayaran'])): ?>
                                <br><?= date('d M Y, H:i', strtotime($pesanan['waktu_pembayaran'])) ?> WIB
                            <?php endif; ?>
                        </div>
                    </div>
                <?php elseif ($st === 'Dibatalkan'): ?>
                    <div class="alert alert-danger py-2 small mb-0 text-center">
                        Pesanan ini telah dibatalkan.
                    </div>
                <?php else: ?>
                    <?php if (!empty($snap_token)): ?>
                        <button id="btnBayarMidtrans" class="btn btn-primary w-100 py-2 fw-semibold shadow-sm mb-2">
                            <i class="fas fa-wallet me-2"></i> Bayar Sekarang
                        </button>
                    <?php else: ?>
                        <button class="btn btn-secondary w-100 py-2 small mb-2" disabled>Menyiapkan Pembayaran...</button>
                    <?php endif; ?>
                    <div class="text-center">
                        <a href="<?= base_url('backend/pengguna/proses-status-midtrans.php?id=' . $id_pesanan) ?>" class="text-decoration-none small text-muted">
                            <i class="fas fa-sync-alt me-1"></i> Cek Status Pembayaran
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<?php if (!empty($snap_token)): ?>
<!-- Midtrans Snap JS SDK -->
<script src="<?= MIDTRANS_SNAP_JS_URL ?>" data-client-key="<?= MIDTRANS_CLIENT_KEY ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const btnBayar = document.getElementById('btnBayarMidtrans');
    if (btnBayar) {
        btnBayar.addEventListener('click', function () {
            snap.pay('<?= e($snap_token) ?>', {
                onSuccess: function (result) {
                    window.location.href = '<?= base_url('backend/pengguna/proses-status-midtrans.php?id=' . $id_pesanan) ?>';
                },
                onPending: function (result) {
                    window.location.href = '<?= base_url('backend/pengguna/proses-status-midtrans.php?id=' . $id_pesanan) ?>';
                },
                onError: function (result) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Pembayaran Gagal',
                            text: result.status_message || 'Silakan coba lagi.',
                            confirmButtonText: 'Tutup'
                        }).then(() => {
                            window.location.href = '<?= base_url('backend/pengguna/proses-status-midtrans.php?id=' . $id_pesanan) ?>';
                        });
                    } else {
                        alert('Pembayaran gagal: ' + (result.status_message || 'Silakan coba lagi.'));
                        window.location.href = '<?= base_url('backend/pengguna/proses-status-midtrans.php?id=' . $id_pesanan) ?>';
                    }
                },
                onClose: function () {}
            });
        });
    }
});
</script>
<?php endif; ?>

<?php if (!empty($pesanan['biteship_order_id']) && !in_array($pesanan['status_pesanan'], ['Selesai', 'Dibatalkan'])): ?>
<!-- Live Polling Perjalanan Paket dari Biteship -->
<script>
(function() {
    let currentStatus = <?= json_encode($pesanan['status_pesanan']) ?>;
    setInterval(function() {
        fetch('<?= base_url('backend/api/cek-status-biteship.php?id=' . $id_pesanan) ?>')
            .then(res => res.json())
            .then(data => {
                if (data.success && data.status_pesanan && data.status_pesanan !== currentStatus) {
                    window.location.reload();
                }
            })
            .catch(() => {});
    }, 15000);
})();
</script>
<?php endif; ?>

<script>
function cetakInvoice() {
    const invoice = document.getElementById('print-invoice');
    if (!invoice) return;

    const printWindow = window.open('', '_blank', 'width=900,height=700');
    if (!printWindow) {
        alert('Izinkan pop-up untuk membuka invoice cetak.');
        return;
    }

    const invoiceStyles = `
        @page { size:A4 portrait; margin:14mm; }
        * { box-sizing:border-box; }
        html,body { margin:0; padding:0; color:#111827; background:#eef2f6; font:10pt Arial,Helvetica,sans-serif; }
        body { padding:28px; }
        #print-invoice { display:block!important; width:100%; max-width:900px; margin:0 auto; padding:42px 48px; background:#fff; color:#111827; }
        .invoice-top { display:flex; justify-content:space-between; align-items:flex-start; gap:24px; padding-bottom:16px; border-bottom:2px solid #1e3a5f; }
        .invoice-company { display:flex; align-items:center; gap:12px; max-width:62%; }
        .invoice-company img { width:58px; height:58px; object-fit:contain; }
        .invoice-company-name { font-size:15pt; font-weight:700; margin:0 0 4px; }
        .invoice-company-info { color:#555; font-size:8.5pt; line-height:1.45; }
        .invoice-title { text-align:right; }
        .invoice-title h1 { color:#1e3a5f; font-size:23pt; letter-spacing:1px; margin:0 0 8px; }
        .invoice-title div { margin:3px 0; font-size:9pt; }
        .invoice-parties { display:grid; grid-template-columns:1fr 1fr; gap:28px; padding:18px 0; }
        .invoice-label { margin-bottom:6px; color:#64748b; font-size:8pt; font-weight:700; letter-spacing:.6px; text-transform:uppercase; }
        .invoice-party-name { font-weight:700; font-size:11pt; margin-bottom:4px; }
        .invoice-party-info { white-space:pre-line; color:#444; line-height:1.5; font-size:9pt; }
        .invoice-table { width:100%; border-collapse:collapse; margin:8px 0 18px; font-size:9pt; }
        .invoice-table th { padding:9px 8px; color:#fff; background:#1e3a5f; text-align:left; font-weight:700; }
        .invoice-table td { padding:9px 8px; border-bottom:1px solid #dfe4ea; vertical-align:top; }
        .invoice-table .text-right { text-align:right; }
        .invoice-table .text-center { text-align:center; }
        .invoice-totals { width:270px; margin-left:auto; }
        .invoice-total-row { display:flex; justify-content:space-between; gap:20px; padding:5px 0; color:#444; }
        .invoice-grand-total { border-top:1px solid #cbd5e1; margin-top:5px; padding-top:10px; color:#1e3a5f; font-size:13pt; font-weight:700; }
        .invoice-note { margin-top:30px; padding-top:12px; border-top:1px solid #e2e8f0; color:#64748b; font-size:8.5pt; }
        @media print { html,body { background:#fff; } body { padding:0; } #print-invoice { max-width:none; margin:0; padding:0; } .invoice-table th { -webkit-print-color-adjust:exact; print-color-adjust:exact; } tr,.invoice-parties,.invoice-totals { break-inside:avoid; } }
    `;
    let printStarted = false;
    const triggerPrint = function () {
        if (printStarted || !printWindow.document.body) return;
        const logo = printWindow.document.querySelector('.invoice-company img');
        if (logo && !logo.complete) return;
        printStarted = true;
        printWindow.focus();
        printWindow.print();
    };
    printWindow.onload = triggerPrint;

    printWindow.document.open();
    printWindow.document.write(
        '<!doctype html><html lang="id"><head><meta charset="utf-8">' +
        '<meta name="viewport" content="width=device-width,initial-scale=1">' +
        '<title>' + <?= json_encode('Invoice ' . $pesanan['kode_pesanan']) ?> + '</title>' +
        '<style>' + invoiceStyles + '</style>' +
        '</head><body>' + invoice.outerHTML + '</body></html>'
    );
    printWindow.document.close();
    const printLogo = printWindow.document.querySelector('.invoice-company img');
    if (printLogo) {
        printLogo.onload = triggerPrint;
        printLogo.onerror = triggerPrint;
    }
    window.setTimeout(triggerPrint, 1200);
}

function salinResi(resi) {
    if (!resi) return;
    navigator.clipboard.writeText(resi).then(function() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Nomor resi berhasil disalin!',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true
            });
        } else {
            alert('Nomor resi berhasil disalin!');
        }
    }).catch(function() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'info',
                title: 'Nomor Resi',
                text: resi
            });
        } else {
            prompt('Salin nomor resi:', resi);
        }
    });
}
</script>

<?php require_once __DIR__ . '/../includes/pengguna-footer.php'; ?>
