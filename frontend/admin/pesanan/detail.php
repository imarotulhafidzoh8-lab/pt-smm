<?php
// ============================================================
// FILE    : frontend/admin/pesanan/detail.php
// FUNGSI  : Rincian Pesanan & Kelola Pengiriman (Admin)
// PROYEK  : Sistem Informasi PT Shafa Marwa Multiguna
// ============================================================
require_once __DIR__ . '/../../../config/app.php';
cek_admin();

$id_pesanan = (int) ($_GET['id'] ?? 0);
if ($id_pesanan <= 0) {
    flash('error', 'Pesanan tidak ditemukan.');
    redirect(base_url('frontend/admin/pesanan/index.php'));
}

// Ambil data pesanan
$pesanan = null;
try {
    $stmt = $pdo->prepare("
        SELECT p.*, u.nama_lengkap AS nama_user, u.email AS email_user, u.no_hp AS no_hp_user
        FROM pesanan p
        LEFT JOIN users u ON p.id_user = u.id_user
        WHERE p.id_pesanan = ?
        LIMIT 1
    ");
    $stmt->execute([$id_pesanan]);
    $pesanan = $stmt->fetch();
} catch (PDOException $e) {
}

if (!$pesanan) {
    flash('error', 'Pesanan tidak ditemukan.');
    redirect(base_url('frontend/admin/pesanan/index.php'));
}

// Ambil item detail_pesanan
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

// SINKRONISASI OTOMATIS KE BITESHIP:
// Setiap kali halaman detail dibuka, sistem otomatis mengecek update status kurir & riwayat perjalanan terbaru
if (!empty($pesanan['biteship_order_id']) && !in_array($pesanan['status_pesanan'], ['Selesai', 'Dibatalkan'])) {
    biteship_sync_status($pdo, $id_pesanan);
    // Refresh variable $pesanan lengkap dengan data user
    $stmt = $pdo->prepare("
        SELECT p.*, u.nama_lengkap AS nama_user, u.email AS email_user, u.no_hp AS no_hp_user
        FROM pesanan p
        LEFT JOIN users u ON p.id_user = u.id_user
        WHERE p.id_pesanan = ?
        LIMIT 1
    ");
    $stmt->execute([$id_pesanan]);
    $pesanan = $stmt->fetch();
}

// Ambil tracking history Biteship
$tracking_events = [];
if (!empty($pesanan['tracking_history'])) {
    $tracking_events = json_decode($pesanan['tracking_history'], true) ?? [];
}

// Variabel kondisi
$sudah_dikirim_biteship = !empty($pesanan['biteship_order_id']);
$sudah_lunas = ($pesanan['status_pembayaran'] === 'Lunas');
$status_pesanan = $pesanan['status_pesanan'];

$page_title = 'Rincian Pesanan: ' . $pesanan['kode_pesanan'];
$admin_page = 'pesanan';

require_once __DIR__ . '/../../includes/admin-header.php';
?>

<style>
.order-detail-head { display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; margin-bottom:14px; }
.order-detail-title { color:#0f2744; font-size:1.15rem; font-weight:750; margin:0; }
.order-detail-meta { display:flex; align-items:center; gap:7px; flex-wrap:wrap; margin-top:6px; color:#64748b; font-size:12px; }
.order-overview { display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; padding:12px 15px; margin-bottom:14px; border:1px solid #e5ebf2; border-radius:12px; background:#fff; }
.order-overview-status { display:flex; align-items:center; gap:7px; flex-wrap:wrap; }
.order-overview-total { color:#0f2744; font-size:1.05rem; font-weight:750; white-space:nowrap; }
.order-detail-card-title { color:#0f2744; font-size:14px; font-weight:700; margin:0; }
.order-manual-tools summary { cursor:pointer; list-style:none; }
.order-manual-tools summary::-webkit-details-marker { display:none; }
.order-manual-tools[open] summary { margin-bottom:10px; }
@media(max-width:575px) {
    .order-detail-head > div:last-child { width:100%; }
    .order-detail-head > div:last-child .btn { flex:1; }
    .order-overview { align-items:flex-start; }
}
</style>

<div class="order-detail-head">
    <div>
        <h1 class="order-detail-title">Pesanan <?= e($pesanan['kode_pesanan']) ?></h1>
        <div class="order-detail-meta"><span><i class="far fa-clock me-1"></i><?= date('d M Y, H:i', strtotime($pesanan['tanggal_pesanan'])) ?> WIB</span></div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $pesanan['no_hp_penerima']) ?>?text=Halo%20<?= urlencode($pesanan['nama_penerima']) ?>,%20pesanan%20Anda%20<?= urlencode($pesanan['kode_pesanan']) ?>%20sedang%20diproses%20oleh%20PT%20SMM."
            target="_blank" class="btn btn-outline-success btn-sm">
            <i class="fab fa-whatsapp me-1"></i> Hubungi Pembeli
        </a>
        <a href="<?= base_url('frontend/admin/pesanan/index.php') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Daftar Pesanan
        </a>
    </div>
</div>

<div class="order-overview">
    <div class="order-overview-status">
        <?= status_badge_pesanan($pesanan['status_pesanan']) ?>
        <?= status_badge_pembayaran($pesanan['status_pembayaran'] ?? 'Belum Dibayar') ?>
    </div>
    <div class="order-overview-total">Total <?= format_rupiah($pesanan['total_harga']) ?></div>
</div>

<div class="row g-3">
    <!-- ============================================================
         KOLOM KIRI: Detail Item & Alamat
    ============================================================ -->
    <div class="col-lg-8">
        <!-- Tabel Produk -->
        <div class="admin-card mb-3">
            <div class="admin-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="order-detail-card-title">Produk <span class="text-muted fw-normal">(<?= count($items) ?>)</span></h5>
            </div>

            <div class="table-responsive">
                <table class="table admin-table align-middle">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th class="text-end">Harga Satuan</th>
                            <th class="text-center">Jumlah</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="<?= upload_url('produk', $item['gambar']) ?>" alt="Produk"
                                            class="rounded border" style="width: 38px; height: 38px; object-fit: cover;">
                                        <div>
                                            <div class="fw-bold text-navy" style="font-size: 13px;"><?= e($item['nama_produk_saat_pesan']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-end"><?= format_rupiah($item['harga_satuan']) ?></td>
                                <td class="text-center">
                                    <span class="badge bg-light text-navy border"><?= $item['jumlah'] ?>
                                        <?= e($item['satuan'] ?? 'pcs') ?></span>
                                </td>
                                <td class="text-end fw-bold text-navy"><?= format_rupiah($item['subtotal']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <?php $ongkir = (float) ($pesanan['ongkir'] ?? 0); ?>
                        <?php if ($ongkir > 0): ?>
                            <tr>
                                <th colspan="3" class="text-end text-muted">Ongkos Kirim
                                    (<?= e($pesanan['kurir'] ?? 'Kurir') ?>):</th>
                                <th class="text-end text-muted"><?= format_rupiah($ongkir) ?></th>
                            </tr>
                        <?php endif; ?>
                        <tr>
                            <th colspan="3" class="text-end small">Total Pembayaran:</th>
                            <th class="text-end fw-bold text-navy" style="font-size: 0.95rem;"><?= format_rupiah($pesanan['total_harga']) ?>
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Tujuan Pengiriman -->
        <div class="admin-card mb-3">
            <div class="admin-card-header">
                <h5 class="admin-card-title">Tujuan Pengiriman</h5>
            </div>
            <div class="admin-card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <strong class="text-navy small d-block">Penerima</strong>
                        <p class="mb-2 fw-semibold"><?= e($pesanan['nama_penerima']) ?></p>

                        <strong class="text-navy small d-block">Telepon</strong>
                        <p class="mb-0 text-muted"><?= e($pesanan['no_hp_penerima']) ?></p>
                    </div>
                    <div class="col-md-6">
                        <strong class="text-navy small d-block">Alamat</strong>
                        <p class="mb-1 text-slate"><?= nl2br(e($pesanan['alamat_pengiriman'])) ?></p>
                        <?php if (!empty($pesanan['kode_pos_tujuan'])): ?>
                            <span class="badge bg-light text-navy border"><i
                                    class="fas fa-map-pin me-1 text-danger"></i>Kode Pos:
                                <?= e($pesanan['kode_pos_tujuan']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($pesanan['catatan'])): ?>
                            <strong class="text-navy small d-block mt-2">Catatan Pembeli:</strong>
                            <p class="mb-0 small fst-italic text-muted">"<?= e($pesanan['catatan']) ?>"</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================
             PANEL PENGIRIMAN BITESHIP (tracking timeline)
        ============================================================ -->
        <?php if ($sudah_dikirim_biteship): ?>
            <div class="admin-card mb-4">
                <div class="admin-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="admin-card-title mb-0">
                        <i class="fas fa-route text-primary me-2"></i>Lacak Perjalanan Paket
                    </h5>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <?php if (!empty($pesanan['nomor_resi'])): ?>
                            <span class="font-monospace fw-bold text-navy px-2 py-1 bg-light border rounded small">
                                <i class="fas fa-barcode me-1 text-muted"></i><?= e($pesanan['nomor_resi']) ?>
                            </span>
                        <?php endif; ?>
                        <a href="<?= base_url('backend/admin/proses-sync-biteship.php?id=' . $id_pesanan) ?>" class="btn btn-sm btn-light border py-1 px-2 text-muted" style="font-size: 11px;">
                            <i class="fas fa-sync-alt me-1"></i> Sinkronkan
                        </a>
                    </div>
                </div>
                <div class="admin-card-body">
                    <?php if (!empty($tracking_events)): ?>
                        <div class="simple-timeline mt-2" style="position:relative; padding-left:20px;">
                            <style>
                                .simple-timeline::before { content:''; position:absolute; top:6px; bottom:6px; left:5px; width:2px; background:#e2e8f0; }
                                .timeline-point { position:relative; padding-bottom:16px; }
                                .timeline-point:last-child { padding-bottom:0; }
                                .timeline-point::before { content:''; position:absolute; left:-20px; top:5px; width:12px; height:12px; border-radius:50%; background:#ffffff; border:2px solid #cbd5e1; }
                                .timeline-point.latest::before { background:#1e3a5f; border-color:#1e3a5f; }
                            </style>
                            <?php 
                            $reversed_events = array_reverse($tracking_events);
                            foreach ($reversed_events as $idx => $ev): 
                            ?>
                                <div class="timeline-point <?= $idx === 0 ? 'latest' : '' ?>">
                                    <div class="fw-semibold text-navy small">
                                        <?= htmlspecialchars($ev['description'] ?? $ev['note'] ?? '-') ?>
                                    </div>
                                    <div class="text-muted" style="font-size: 11px;">
                                        <?= !empty($ev['updated_at']) ? date('d M Y, H:i', strtotime($ev['updated_at'])) . ' WIB' : '' ?>
                                        <?= !empty($ev['location']) ? ' &bull; ' . htmlspecialchars($ev['location']) : '' ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted small">
                            <i class="fas fa-clock fa-lg mb-2 d-block opacity-50"></i>
                            <div>Menunggu pergerakan kurir. Status akan disinkronkan otomatis dari Biteship.</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- ============================================================
         KOLOM KANAN: Panel Pengiriman & Akun
    ============================================================ -->
    <div class="col-lg-4">

        <!-- ====== PANEL UTAMA: Kirim ke Biteship atau Status Manual ====== -->
        <div class="admin-card mb-3">
            <div class="admin-card-header">
                <h5 class="admin-card-title">
                    <i class="fas fa-shipping-fast me-2 text-primary"></i>Kelola Pengiriman
                </h5>
            </div>
            <div class="admin-card-body">

                <?php if (!$sudah_lunas && !$sudah_dikirim_biteship): ?>
                    <!-- Belum Lunas -->
                    <div class="alert alert-warning small py-2 mb-3"><i class="fas fa-clock me-1"></i>Menunggu pembayaran sebelum dikirim.</div>

                <?php elseif ($sudah_lunas && !$sudah_dikirim_biteship): ?>
                    <!-- Lunas tapi belum dikirim ke Biteship -->
                    <div class="alert alert-success small py-2 mb-3"><i class="fas fa-check-circle me-1"></i>Lunas · siap dikirim.</div>

                    <?php if (!empty($pesanan['kode_pos_tujuan'])): ?>
                        <!-- Info kurir yang dipilih pembeli -->
                        <div class="p-3 bg-light rounded-3 border mb-3">
                            <div class="small text-muted mb-1">Kurir</div>
                            <div class="fw-bold text-navy">
                                <?= e($pesanan['kurir'] ?? '-') ?>
                                <?php if (!empty($pesanan['kurir_service'])): ?>
                                    <span class="badge bg-info text-dark ms-1"><?= e($pesanan['kurir_service']) ?></span>
                                <?php endif; ?>
                            </div>
                            <small class="text-muted">Ongkir: <?= format_rupiah($pesanan['ongkir'] ?? 0) ?> · Kode Pos Tujuan:
                                <strong><?= e($pesanan['kode_pos_tujuan']) ?></strong></small>
                        </div>

                        <!-- Tombol Kirim ke Biteship -->
                        <form id="formKirimBiteship" action="<?= base_url('backend/admin/proses-kirim-biteship.php') ?>" method="POST">
                            <input type="hidden" name="id_pesanan" value="<?= $id_pesanan ?>">
                            <button type="button" class="btn w-100 py-2 fw-semibold text-white" style="background:#1e3a5f; border-color:#1e3a5f;" onclick="konfirmasiKirimBiteship()">
                                <i class="fas fa-paper-plane me-2"></i>Buat Pengiriman
                            </button>
                        </form>
                    <?php else: ?>
                        <!-- Kode pos tidak ada (pesanan lama sebelum Biteship) -->
                        <div class="alert alert-info small py-2">
                            <i class="fas fa-info-circle me-1"></i>
                            Tidak ada kode pos tujuan. Gunakan ubah status manual untuk melanjutkan.
                        </div>
                    <?php endif; ?>

                <?php elseif ($sudah_dikirim_biteship): ?>
                    <!-- Sudah dikirim ke Biteship -->
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small text-muted">Ekspedisi:</span>
                            <span class="badge bg-success-subtle text-success border border-success-subtle">Dikelola Biteship</span>
                        </div>
                        <div class="fw-bold text-navy mb-2"><?= e($pesanan['kurir'] ?? '-') ?></div>

                        <div class="small text-muted mb-1">Nomor Resi:</div>
                        <?php if (!empty($pesanan['nomor_resi'])): ?>
                            <div class="d-flex align-items-center gap-1 mb-2">
                                <span class="font-monospace fw-bold text-navy px-2 py-1 bg-white border rounded flex-grow-1 text-center">
                                    <?= e($pesanan['nomor_resi']) ?>
                                </span>
                                <button type="button" class="btn btn-light btn-sm border py-1 px-2" title="Salin Resi" onclick="salinResi('<?= e($pesanan['nomor_resi']) ?>');">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        <?php else: ?>
                            <div class="text-muted small mb-2 fst-italic">Menunggu kurir menjemput paket.</div>
                        <?php endif; ?>

                        <a href="<?= base_url('backend/admin/proses-sync-biteship.php?id=' . $id_pesanan) ?>"
                            class="btn btn-outline-primary btn-sm w-100">
                            <i class="fas fa-sync-alt me-1"></i> Perbarui Pelacakan
                        </a>
                    </div>

                    <!-- Alat Simulasi (Minimalist Collapsible) -->
                    <details class="mb-3 small">
                        <summary class="text-muted fw-semibold py-1" style="cursor: pointer;">
                            <i class="fas fa-flask me-1 text-warning"></i> Simulasi Status Kurir (Mode Demo)
                        </summary>
                        <div class="pt-2 d-grid gap-1">
                            <form action="<?= base_url('backend/admin/proses-simulasi-biteship.php') ?>" method="POST">
                                <input type="hidden" name="id_pesanan" value="<?= $id_pesanan ?>">
                                <input type="hidden" name="step" value="picked">
                                <button type="submit" class="btn btn-sm btn-outline-secondary w-100 text-start py-1" style="font-size:11px">
                                    <i class="fas fa-box me-1 text-info"></i> 1. Kurir Jemput (Diproses)
                                </button>
                            </form>
                            <form action="<?= base_url('backend/admin/proses-simulasi-biteship.php') ?>" method="POST">
                                <input type="hidden" name="id_pesanan" value="<?= $id_pesanan ?>">
                                <input type="hidden" name="step" value="dropping">
                                <button type="submit" class="btn btn-sm btn-outline-secondary w-100 text-start py-1" style="font-size:11px">
                                    <i class="fas fa-shipping-fast me-1 text-primary"></i> 2. Dalam Perjalanan (Dikirim)
                                </button>
                            </form>
                            <form action="<?= base_url('backend/admin/proses-simulasi-biteship.php') ?>" method="POST">
                                <input type="hidden" name="id_pesanan" value="<?= $id_pesanan ?>">
                                <input type="hidden" name="step" value="delivered">
                                <button type="submit" class="btn btn-sm btn-outline-success w-100 text-start py-1" style="font-size:11px">
                                    <i class="fas fa-check-circle me-1 text-success"></i> 3. Paket Diterima (Selesai)
                                </button>
                            </form>
                        </div>
                    </details>
                <?php endif; ?>

                <!-- ====== Form Batalkan / Override Manual ====== -->
                <details class="order-manual-tools border-top pt-3 mt-3">
                    <summary class="small text-muted fw-semibold"><i class="fas fa-sliders-h me-1"></i>Ubah status manual</summary>
                    <form action="<?= base_url('backend/admin/proses-pesanan.php') ?>" method="POST">
                        <input type="hidden" name="id_pesanan" value="<?= $pesanan['id_pesanan'] ?>">

                        <div class="mb-2">
                            <select name="status_pesanan" class="form-select form-select-sm">
                                <?php if (!$sudah_dikirim_biteship): ?>
                                    <option value="Menunggu Pembayaran" <?= ($status_pesanan === 'Menunggu Pembayaran') ? 'selected' : '' ?>>Menunggu Pembayaran</option>
                                    <option value="Diproses" <?= ($status_pesanan === 'Diproses') ? 'selected' : '' ?>>Diproses</option>
                                <?php endif; ?>
                                <?php if ($sudah_dikirim_biteship): ?>
                                    <!-- Jika sudah di Biteship, hanya opsi yang masuk akal -->
                                    <option value="Dikirim" <?= ($status_pesanan === 'Dikirim') ? 'selected' : '' ?>>Dikirim</option>
                                    <option value="Selesai" <?= ($status_pesanan === 'Selesai') ? 'selected' : '' ?>>Selesai</option>
                                <?php endif; ?>
                                <option value="Dibatalkan" <?= ($status_pesanan === 'Dibatalkan') ? 'selected' : '' ?>>
                                    Dibatalkan</option>
                            </select>
                        </div>

                        <?php if (!$sudah_dikirim_biteship): ?>
                            <div class="mb-2">
                                <input type="text" name="nomor_resi" class="form-control form-control-sm"
                                    value="<?= e($pesanan['nomor_resi'] ?? '') ?>" placeholder="Nomor resi (jika ada)">
                            </div>
                        <?php endif; ?>

                        <button type="submit" class="btn btn-secondary btn-sm w-100">
                            <i class="fas fa-save me-1"></i> Simpan Status
                        </button>
                    </form>
                </details>
            </div>
        </div>

        <!-- Info Pemesan -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h5 class="admin-card-title">Pemesan</h5>
            </div>
            <div class="admin-card-body">
                <?php 
                $nama_user_display  = $pesanan['nama_user'] ?? $pesanan['nama_penerima'] ?? 'Pengguna';
                $email_user_display = $pesanan['email_user'] ?? '-';
                $inisial            = strtoupper(substr((string)$nama_user_display, 0, 1));
                ?>
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="admin-avatar" style="width: 34px; height: 34px; font-size: 0.82rem;">
                        <?= $inisial ?>
                    </div>
                    <div>
                        <div class="fw-bold text-navy" style="font-size: 13px;"><?= e($nama_user_display) ?></div>
                        <small class="text-muted" style="font-size: 11px;"><?= e($email_user_display) ?></small>
                    </div>
                </div>

                <div class="small text-muted border-top pt-3">
                    <div class="mb-1"><i class="fas fa-calendar-alt me-2 text-primary"></i>Diperbarui <?= date('d M Y, H:i', strtotime($pesanan['diperbarui_pada'])) ?> WIB</div>
                    <?php if (!empty($pesanan['waktu_pembayaran'])): ?>
                        <div class="mt-1"><i class="fas fa-check-circle me-2 text-success"></i>Dibayar <?= date('d M Y, H:i', strtotime($pesanan['waktu_pembayaran'])) ?> WIB</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

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
function konfirmasiKirimBiteship() {
    if (typeof Swal === 'undefined') {
        if (confirm('Konfirmasi kirim pesanan ini ke Biteship?\n\nSetelah dikirim, Biteship akan otomatis memesan kurir penjemputan paket dan nomor resi akan digenerate otomatis.')) {
            document.getElementById('formKirimBiteship').submit();
        }
        return;
    }

    Swal.fire({
        title: 'Kirim ke Biteship?',
        text: 'Sistem akan memesan kurir penjemputan dan nomor resi akan digenerate otomatis.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#1e3a5f',
        cancelButtonColor: '#71717a',
        confirmButtonText: '<i class="fas fa-paper-plane me-1"></i> Ya, Kirim',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Menghubungkan ke Biteship...',
                text: 'Mendaftarkan pesanan ke ekspedisi...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            document.getElementById('formKirimBiteship').submit();
        }
    });
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

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>
