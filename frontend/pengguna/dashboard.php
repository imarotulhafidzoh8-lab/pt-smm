<?php
// ============================================================
// FILE    : frontend/pengguna/dashboard.php
// FUNGSI  : Dashboard Utama Pelanggan (Pengguna)
// PROYEK  : Sistem Informasi PT Shafa Marwa Multiguna
// ============================================================
require_once __DIR__ . '/../../config/app.php';
cek_pengguna();

$page_title = 'Dashboard Pelanggan';
$user_page  = 'dashboard';
$id_user    = (int)$_SESSION['id_user'];

$total_pesanan   = 0;
$pesanan_proses  = 0;
$pesanan_selesai = 0;

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM pesanan WHERE id_user = ?");
    $stmt->execute([$id_user]);
    $total_pesanan = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM pesanan WHERE id_user = ? AND status_pesanan IN ('Menunggu Konfirmasi', 'Diproses', 'Dikirim')");
    $stmt->execute([$id_user]);
    $pesanan_proses = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM pesanan WHERE id_user = ? AND status_pesanan = 'Selesai'");
    $stmt->execute([$id_user]);
    $pesanan_selesai = (int)$stmt->fetchColumn();
} catch (PDOException $e) {}

$pesanan_terbaru = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM pesanan WHERE id_user = ? ORDER BY tanggal_pesanan DESC LIMIT 5");
    $stmt->execute([$id_user]);
    $pesanan_terbaru = $stmt->fetchAll();
} catch (PDOException $e) { $pesanan_terbaru = []; }

$produk_terbaru = [];
try {
    $stmt = $pdo->query("SELECT id_produk, nama_produk, gambar, harga, stok FROM produk WHERE status_produk = 'Aktif' ORDER BY dibuat_pada DESC LIMIT 6");
    $produk_terbaru = $stmt->fetchAll();
} catch (PDOException $e) { $produk_terbaru = []; }

require_once __DIR__ . '/../includes/pengguna-header.php';
?>

<style>
/* ===== Dashboard Mobile-First Styles ===== */
.welcome-banner {
    background: linear-gradient(135deg, #071426 0%, #0f2744 55%, #1e3a5f 100%);
    border-radius: 16px;
    overflow: hidden;
    position: relative;
    margin-bottom: 20px;
    padding: 24px 20px;
}
.welcome-banner .deco {
    position: absolute;
    border-radius: 50%;
    pointer-events: none;
}
.welcome-name {
    font-size: 1.3rem;
    font-weight: 700;
    letter-spacing: -0.02em;
    color: #fff;
    margin: 0 0 4px;
}
.welcome-sub {
    font-size: 12px;
    color: rgba(255,255,255,0.6);
    margin: 0 0 14px;
    line-height: 1.5;
}
.welcome-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 16px;
}
.welcome-chip {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    border-radius: 999px;
    background: rgba(255,255,255,0.1);
    color: rgba(255,255,255,0.85);
    font-size: 11px;
    font-weight: 500;
}
.welcome-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 18px;
    border-radius: 999px;
    background: #fff;
    color: #0f2744;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    transition: opacity .2s;
}
.welcome-btn:hover { opacity: .88; color: #0f2744; }

/* Stat strip — inline 3 cols on any screen */
.stat-strip {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    margin-bottom: 20px;
}
.stat-box {
    background: #fff;
    border: 1px solid #e8edf3;
    border-radius: 14px;
    padding: 14px 12px;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 4px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.04);
}
.stat-icon {
    width: 36px; height: 36px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px;
    margin-bottom: 4px;
}
.stat-num {
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1;
    color: #0f172a;
}
.stat-label {
    font-size: 10px;
    color: #64748b;
    font-weight: 500;
    line-height: 1.2;
}

/* Section card */
.panel-section {
    background: #fff;
    border: 1px solid #e8edf3;
    border-radius: 16px;
    overflow: hidden;
    margin-bottom: 20px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.04);
}
.panel-section-head {
    padding: 14px 16px;
    border-bottom: 1px solid #eef2f6;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}
.panel-section-title {
    font-size: 14px;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
}
.panel-link {
    font-size: 12px;
    color: #1e3a5f;
    text-decoration: none;
    font-weight: 600;
    white-space: nowrap;
}
.panel-link:hover { text-decoration: underline; }

/* Product grid */
.product-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0;
}
@media (min-width: 576px) { .product-grid { grid-template-columns: repeat(3, 1fr); } }
@media (min-width: 992px) { .product-grid { grid-template-columns: repeat(6, 1fr); } }

.product-item {
    padding: 10px;
    border-right: 1px solid #eef2f6;
    border-bottom: 1px solid #eef2f6;
}
.product-item:nth-child(2n) { border-right: 0; }
@media (min-width: 576px) {
    .product-item:nth-child(2n) { border-right: 1px solid #eef2f6; }
    .product-item:nth-child(3n) { border-right: 0; }
}
@media (min-width: 992px) {
    .product-item:nth-child(3n) { border-right: 1px solid #eef2f6; }
    .product-item:nth-child(6n) { border-right: 0; }
}
.product-item img {
    width: 100%; aspect-ratio: 1; object-fit: cover;
    border-radius: 10px; border: 1px solid #eef2f6;
    display: block; margin-bottom: 8px;
}
.product-item-name {
    font-size: 11px; font-weight: 600; color: #0f172a;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    margin-bottom: 3px; line-height: 1.3;
}
.product-item-price {
    font-size: 11px; font-weight: 700; color: #1e3a5f; margin-bottom: 8px;
}
.product-item-btns {
    display: flex; gap: 4px;
}
.product-item-btns .btn {
    flex: 1; font-size: 10px; padding: 4px 2px;
    border-radius: 8px; font-weight: 600;
}

/* Order list (mobile cards, not table) */
.order-list { padding: 12px; display: flex; flex-direction: column; gap: 8px; }
.order-row {
    display: flex;
    flex-direction: column;
    gap: 8px;
    padding: 12px;
    border: 1px solid #e8edf3;
    border-radius: 12px;
    background: #fff;
}
.order-row-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 8px;
    flex-wrap: wrap;
}
.order-kode { font-size: 12px; font-weight: 700; color: #0f172a; }
.order-date { font-size: 10px; color: #94a3b8; margin-top: 1px; }
.order-row-bottom {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    flex-wrap: wrap;
}
.order-total { font-size: 13px; font-weight: 700; color: #0f172a; }
.order-recipient { font-size: 11px; color: #64748b; }
.order-action .btn { font-size: 11px; padding: 4px 12px; }

/* Keep dashboard panels and their contents within narrow viewports. */
*, *::before, *::after { box-sizing: border-box; }
.pnav-content { min-width: 0; }
.panel-section, .welcome-banner, .stat-box { min-width: 0; }
.order-kode { overflow-wrap: anywhere; }
.order-recipient { overflow-wrap: anywhere; }
.product-item-name { overflow-wrap: anywhere; }

/* Show table on desktop, card list on mobile */
.orders-table-wrap { display: none; }
.orders-card-list { display: flex; flex-direction: column; }
@media (min-width: 768px) {
    .orders-table-wrap { display: block; }
    .orders-card-list { display: none; }
}

@media (max-width: 575.98px) {
    .pnav-content { padding: 12px 12px 24px; }
    .pnav-inner { padding-right: 12px; padding-left: 12px; }
    .welcome-banner { padding: 20px 16px; border-radius: 14px; }
    .welcome-name { font-size: 1.2rem; }
    .welcome-sub { max-width: 30ch; }
    .stat-strip { gap: 6px; }
    .stat-box { padding: 11px 6px; border-radius: 12px; }
    .stat-icon { width: 32px; height: 32px; }
    .stat-num { font-size: 1.3rem; }
    .stat-label { font-size: 9px; }
    .panel-section-head { padding: 12px; }
    .panel-section-title { font-size: 13px; }
    .panel-link { font-size: 11px; }
    .product-item { padding: 8px; }
    .product-item-btns { gap: 3px; }
    .product-item-btns .btn { font-size: 9px; }
    .order-list { padding: 10px; }
    .order-row { padding: 10px; }
    .order-row-bottom { align-items: flex-end; }
    .order-total { overflow-wrap: anywhere; }
}

@media (max-width: 359.98px) {
    .pnav-brand img { height: 28px; }
    .pnav-profile-name, .pnav-chevron { display: none; }
    .pnav-profile-btn { padding: 4px; }
    .stat-strip { gap: 4px; }
    .stat-box { padding-right: 3px; padding-left: 3px; }
    .stat-label { font-size: 8px; }
    .product-item-btns .btn { padding-right: 1px; padding-left: 1px; }
}
</style>

<!-- Welcome Banner -->
<div class="welcome-banner">
    <div class="deco" style="width:200px;height:200px;border:1.5px solid rgba(255,255,255,0.06);top:-70px;right:-50px;"></div>
    <div class="deco" style="width:100px;height:100px;border:1.5px solid rgba(255,255,255,0.08);top:-10px;right:70px;"></div>

    <span class="badge mb-3 d-inline-flex align-items-center gap-1" style="background:rgba(255,255,255,0.12);color:#fff;border:1px solid rgba(255,255,255,0.18);font-size:10px;border-radius:999px;padding:4px 10px;letter-spacing:.5px;">
        <i class="fas fa-user-check" style="font-size:9px;"></i> AKUN PELANGGAN
    </span>

    <p class="welcome-name">Halo, <?= e(explode(' ', $nama_user)[0]) ?>! 👋</p>
    <p class="welcome-sub">Pantau pesanan &amp; temukan produk PT SMM kapan saja.</p>

    <div class="welcome-chips">
        <span class="welcome-chip"><i class="fas fa-box-open" style="font-size:9px;"></i> <?= $total_pesanan ?> Pesanan</span>
        <span class="welcome-chip"><i class="fas fa-truck" style="font-size:9px;"></i> <?= $pesanan_proses ?> Berjalan</span>
        <span class="welcome-chip"><i class="fas fa-check-circle" style="font-size:9px;"></i> <?= $pesanan_selesai ?> Selesai</span>
    </div>

    <a href="<?= base_url('frontend/pages/katalog.php') ?>" class="welcome-btn">
        <i class="fas fa-box-open" style="font-size:12px;"></i> Belanja Sekarang
    </a>
</div>

<!-- Stat Strip (always 3 cols) -->
<div class="stat-strip">
    <div class="stat-box">
        <div class="stat-icon" style="background:linear-gradient(135deg,#eff6ff,#dbeafe);">
            <i class="fas fa-receipt text-primary"></i>
        </div>
        <div class="stat-num"><?= $total_pesanan ?></div>
        <div class="stat-label">Total<br>Pesanan</div>
    </div>
    <div class="stat-box">
        <div class="stat-icon" style="background:linear-gradient(135deg,#fff7ed,#fed7aa);">
            <i class="fas fa-truck-moving" style="color:#ea580c;"></i>
        </div>
        <div class="stat-num"><?= $pesanan_proses ?></div>
        <div class="stat-label">Sedang<br>Berjalan</div>
    </div>
    <div class="stat-box">
        <div class="stat-icon" style="background:linear-gradient(135deg,#f0fdf4,#bbf7d0);">
            <i class="fas fa-check-circle" style="color:#16a34a;"></i>
        </div>
        <div class="stat-num"><?= $pesanan_selesai ?></div>
        <div class="stat-label">Pesanan<br>Selesai</div>
    </div>
</div>

<!-- Produk Terbaru -->
<?php if (!empty($produk_terbaru)): ?>
<div class="panel-section">
    <div class="panel-section-head">
        <h2 class="panel-section-title">Produk Terbaru</h2>
        <a href="<?= base_url('frontend/pages/katalog.php') ?>" class="panel-link">Lihat Semua →</a>
    </div>
    <div class="product-grid">
        <?php foreach ($produk_terbaru as $produk): ?>
        <div class="product-item">
            <a href="<?= base_url('frontend/pages/detail-produk.php?id=' . (int)$produk['id_produk']) ?>">
                <img src="<?= upload_url('produk', $produk['gambar']) ?>"
                     alt="<?= e($produk['nama_produk']) ?>"
                     loading="lazy"
                     onerror="this.src='<?= asset_url('images/no-image.svg') ?>'">
            </a>
            <div class="product-item-name"><?= e($produk['nama_produk']) ?></div>
            <div class="product-item-price"><?= format_rupiah($produk['harga']) ?></div>
            <div class="product-item-btns">
                <a href="<?= base_url('frontend/pages/detail-produk.php?id=' . (int)$produk['id_produk']) ?>"
                   class="btn btn-outline-secondary">Detail</a>
                <?php if ((int)$produk['stok'] > 0): ?>
                    <a href="<?= base_url('frontend/pengguna/pemesanan.php?id=' . (int)$produk['id_produk']) ?>"
                       class="btn text-white" style="background:#1e3a5f;border-color:#1e3a5f;">Beli</a>
                <?php else: ?>
                    <button class="btn" style="background:#e2e8f0;color:#94a3b8;border:0;" disabled>Habis</button>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Pesanan Terakhir -->
<div class="panel-section">
    <div class="panel-section-head">
        <h2 class="panel-section-title">Pesanan Terakhir</h2>
        <a href="<?= base_url('frontend/pengguna/riwayat-pesanan.php') ?>" class="panel-link">Lihat Semua →</a>
    </div>

    <?php if (empty($pesanan_terbaru)): ?>
        <div class="text-center py-5 text-muted">
            <i class="fas fa-box-open fa-2x mb-3 d-block opacity-50"></i>
            <div class="small">Belum ada pesanan.</div>
            <a href="<?= base_url('frontend/pages/katalog.php') ?>" class="btn btn-sm rounded-pill px-3 mt-3 text-white" style="background:#1e3a5f;">
                Mulai Belanja
            </a>
        </div>
    <?php else: ?>

        <!-- Desktop: table -->
        <div class="orders-table-wrap">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="text-muted small">
                            <th class="ps-4">Kode Pesanan</th>
                            <th>Penerima</th>
                            <th>Waktu</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pesanan_terbaru as $pes): ?>
                        <tr>
                            <td class="ps-4"><strong class="text-dark small"><?= e($pes['kode_pesanan']) ?></strong></td>
                            <td>
                                <div class="fw-semibold text-dark small"><?= e($pes['nama_penerima']) ?></div>
                                <small class="text-muted" style="font-size:11px;"><?= e($pes['no_hp_penerima']) ?></small>
                            </td>
                            <td><div class="small text-muted"><?= date('d M Y, H:i', strtotime($pes['tanggal_pesanan'])) ?> WIB</div></td>
                            <td><strong class="text-dark small"><?= format_rupiah($pes['total_harga']) ?></strong></td>
                            <td><?= status_badge_pesanan($pes['status_pesanan']) ?></td>
                            <td class="text-end pe-4">
                                <a href="<?= base_url('frontend/pengguna/detail-pesanan.php?id=' . $pes['id_pesanan']) ?>" class="btn btn-sm btn-outline-dark rounded-pill px-3">
                                    <i class="fas fa-eye me-1"></i> Lihat
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Mobile: card list -->
        <div class="orders-card-list order-list">
            <?php foreach ($pesanan_terbaru as $pes): ?>
            <div class="order-row">
                <div class="order-row-top">
                    <div>
                        <div class="order-kode"><?= e($pes['kode_pesanan']) ?></div>
                        <div class="order-date"><i class="far fa-clock me-1"></i><?= date('d M Y, H:i', strtotime($pes['tanggal_pesanan'])) ?> WIB</div>
                    </div>
                    <div><?= status_badge_pesanan($pes['status_pesanan']) ?></div>
                </div>
                <div class="order-row-bottom">
                    <div>
                        <div class="order-recipient"><?= e($pes['nama_penerima']) ?></div>
                        <div class="order-total"><?= format_rupiah($pes['total_harga']) ?></div>
                    </div>
                    <div class="order-action">
                        <a href="<?= base_url('frontend/pengguna/detail-pesanan.php?id=' . $pes['id_pesanan']) ?>" class="btn btn-sm btn-outline-dark rounded-pill">
                            <i class="fas fa-eye me-1"></i> Lihat
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/pengguna-footer.php'; ?>
