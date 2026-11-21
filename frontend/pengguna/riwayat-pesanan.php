<?php
// ============================================================
// FILE    : frontend/pengguna/riwayat-pesanan.php
// FUNGSI  : Riwayat Transaksi Pesanan Pelanggan
// PROYEK  : Sistem Informasi PT Shafa Marwa Multiguna
// ============================================================
require_once __DIR__ . '/../../config/app.php';
cek_pengguna();

$page_title = 'Riwayat Pesanan Saya';
$user_page  = 'riwayat';
$id_user    = (int)$_SESSION['id_user'];

$filter_status = isset($_GET['status']) ? sanitize($_GET['status']) : '';

$pesanan_list    = [];
$total_pesanan   = 0;
$pesanan_proses  = 0;
$pesanan_selesai = 0;
try {
    $stmt_stat = $pdo->prepare("SELECT COUNT(*) AS total, SUM(status_pesanan IN ('Menunggu Konfirmasi', 'Diproses', 'Dikirim')) AS proses, SUM(status_pesanan = 'Selesai') AS selesai FROM pesanan WHERE id_user = ?");
    $stmt_stat->execute([$id_user]);
    $statistik       = $stmt_stat->fetch(PDO::FETCH_ASSOC) ?: [];
    $total_pesanan   = (int)($statistik['total'] ?? 0);
    $pesanan_proses  = (int)($statistik['proses'] ?? 0);
    $pesanan_selesai = (int)($statistik['selesai'] ?? 0);

    $sql = "SELECT p.*, COUNT(d.id_detail) AS total_item, SUM(d.jumlah) AS total_qty,
               GROUP_CONCAT(COALESCE(pr.gambar, '') ORDER BY d.id_detail SEPARATOR '||') AS gambar_produk,
               GROUP_CONCAT(COALESCE(d.nama_produk_saat_pesan, pr.nama_produk, 'Produk') ORDER BY d.id_detail SEPARATOR '||') AS nama_produk
        FROM pesanan p
        LEFT JOIN detail_pesanan d ON p.id_pesanan = d.id_pesanan
        LEFT JOIN produk pr ON d.id_produk = pr.id_produk
        WHERE p.id_user = ?";
    $params = [$id_user];

    if (!empty($filter_status)) {
        $sql .= " AND p.status_pesanan = ?";
        $params[] = $filter_status;
    }
    $sql .= " GROUP BY p.id_pesanan ORDER BY p.tanggal_pesanan DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $pesanan_list = $stmt->fetchAll();
} catch (PDOException $e) { $pesanan_list = []; }

require_once __DIR__ . '/../includes/pengguna-header.php';
?>

<style>
/* ===== Riwayat Mobile-First ===== */
.riwayat-banner {
    background: linear-gradient(135deg, #071426 0%, #0f2744 55%, #1e3a5f 100%);
    border-radius: 16px;
    padding: 22px 18px;
    margin-bottom: 16px;
    overflow: hidden;
    position: relative;
}
.riwayat-banner .deco {
    position: absolute; border-radius: 50%; pointer-events: none;
}
.riwayat-banner-title {
    font-size: 1.15rem; font-weight: 700; color: #fff;
    letter-spacing: -.02em; margin: 0 0 4px;
}
.riwayat-banner-sub {
    font-size: 12px; color: rgba(255,255,255,.65); margin: 0 0 14px;
}
.riwayat-banner-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 7px 16px; border-radius: 999px;
    background: #fff; color: #0f2744;
    font-size: 12px; font-weight: 600; text-decoration: none;
    transition: opacity .2s;
}
.riwayat-banner-btn:hover { opacity: .88; color: #0f2744; }

/* Stat strip */
.stat-strip {
    display: grid; grid-template-columns: repeat(3, 1fr);
    gap: 10px; margin-bottom: 16px;
}
.stat-box {
    background: #fff; border: 1px solid #e8edf3; border-radius: 14px;
    padding: 12px 10px; display: flex; flex-direction: column;
    align-items: center; text-align: center; gap: 3px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.04);
}
.stat-icon {
    width: 34px; height: 34px; border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; margin-bottom: 3px;
}
.stat-num { font-size: 1.4rem; font-weight: 700; line-height: 1; color: #0f172a; }
.stat-label { font-size: 10px; color: #64748b; font-weight: 500; line-height: 1.2; }

/* Filter pills */
.filter-wrap {
    overflow-x: auto; -webkit-overflow-scrolling: touch;
    display: flex; gap: 6px; padding-bottom: 4px;
    scrollbar-width: none;
}
.filter-wrap::-webkit-scrollbar { display: none; }
.filter-pill {
    display: inline-flex; align-items: center; white-space: nowrap;
    padding: 6px 14px; border: 1px solid #e5e7eb; border-radius: 999px;
    color: #64748b; text-decoration: none; font-size: 12px; font-weight: 600;
    background: #fff; transition: all .2s ease; flex-shrink: 0;
}
.filter-pill:hover:not(.active) { color: #1e3a5f; border-color: #cbd5e1; background: #f1f5f9; }
.filter-pill.active { color: #fff; background: #1e3a5f; border-color: #1e3a5f; }

/* Panel section */
.panel-section {
    background: #fff; border: 1px solid #e8edf3; border-radius: 16px;
    overflow: hidden; margin-bottom: 20px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.04);
}
.panel-section-head {
    padding: 14px 16px; border-bottom: 1px solid #eef2f6;
    display: flex; align-items: center; justify-content: space-between; gap: 8px;
    flex-wrap: wrap;
}
.panel-section-title { font-size: 14px; font-weight: 700; color: #0f172a; margin: 0; }

/* Order card */
.order-cards { padding: 12px; display: flex; flex-direction: column; gap: 10px; }
.order-card {
    border: 1px solid #e8edf3; border-radius: 12px;
    background: #fff; overflow: hidden;
}
.order-card-top {
    padding: 11px 12px 9px;
    display: flex; align-items: flex-start;
    justify-content: space-between; gap: 8px;
    border-bottom: 1px solid #f0f4f8;
    flex-wrap: wrap;
}
.order-kode { font-size: 12px; font-weight: 700; color: #0f172a; }
.order-date { font-size: 10px; color: #94a3b8; margin-top: 2px; }
.order-badges { display: flex; gap: 4px; flex-wrap: wrap; }

.order-card-mid {
    padding: 10px 12px;
    display: flex; align-items: center;
    justify-content: space-between; gap: 8px;
    flex-wrap: wrap; border-bottom: 1px solid #f0f4f8;
}
.order-products {
    display: grid; grid-template-columns: repeat(auto-fit, minmax(145px, 1fr));
    gap: 7px; flex: 1 1 100%; min-width: 0;
}
.order-product {
    display: flex; align-items: center; gap: 7px; min-width: 0;
    padding: 5px 7px; border: 1px solid #edf1f5; border-radius: 9px;
    background: #fff;
}
.order-product img {
    width: 36px; height: 36px; flex: 0 0 36px; object-fit: cover;
    border-radius: 7px; border: 1px solid #e8edf3;
}
.order-product span {
    min-width: 0; color: #334155; font-size: 10px; font-weight: 600;
    line-height: 1.35; display: -webkit-box; -webkit-box-orient: vertical;
    -webkit-line-clamp: 2; overflow: hidden; overflow-wrap: anywhere;
}
.order-product-more {
    display: flex; align-items: center; justify-content: center;
    padding: 5px 8px; color: #64748b; font-size: 10px; font-weight: 600;
}
.order-info-row {
    display: flex; gap: 16px; flex-wrap: wrap;
}
.order-info-item small { display: block; font-size: 10px; color: #94a3b8; }
.order-info-item span { font-size: 12px; font-weight: 600; color: #0f172a; }
@media (max-width: 480px) {
    .order-products { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .order-card-mid { align-items: flex-start; }
    .order-info-row { width: 100%; justify-content: space-between; }
}

.order-card-bottom {
    padding: 9px 12px;
    display: flex; align-items: center;
    justify-content: space-between; gap: 8px;
    background: #fafbfc;
}
.order-total { font-size: 13px; font-weight: 700; color: #1e3a5f; }
.order-actions { display: flex; gap: 6px; }
.order-actions .btn { font-size: 11px; padding: 5px 12px; }
</style>

<!-- Banner -->
<div class="riwayat-banner">
    <div class="deco" style="width:200px;height:200px;border:1px solid rgba(255,255,255,.07);top:-90px;right:-40px;"></div>
    <span class="badge mb-3 d-inline-flex align-items-center gap-1" style="background:rgba(255,255,255,.12);color:#fff;border:1px solid rgba(255,255,255,.18);font-size:10px;border-radius:999px;padding:4px 10px;">
        <i class="fas fa-receipt" style="font-size:9px;"></i> RIWAYAT TRANSAKSI
    </span>
    <p class="riwayat-banner-title">Riwayat Pesanan</p>
    <p class="riwayat-banner-sub">Pantau status &amp; pengiriman semua pesanan Anda.</p>
    <a href="<?= base_url('frontend/pengguna/pemesanan.php') ?>" class="riwayat-banner-btn">
        <i class="fas fa-plus" style="font-size:11px;"></i> Pesan Produk Baru
    </a>
</div>

<!-- Stat Strip -->
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

<!-- Filter + List -->
<div class="panel-section">
    <div class="panel-section-head">
        <div>
            <h2 class="panel-section-title">Semua Transaksi</h2>
        </div>
        <span class="badge rounded-pill px-3 py-2" style="background:#eff6ff;color:#1e3a5f;font-size:11px;">
            <?= count($pesanan_list) ?> ditampilkan
        </span>
    </div>

    <!-- Filter scroll tabs -->
    <div style="padding: 12px 12px 0;">
        <div class="filter-wrap">
            <a href="<?= base_url('frontend/pengguna/riwayat-pesanan.php') ?>"
               class="filter-pill <?= empty($filter_status) ? 'active' : '' ?>">Semua</a>
            <a href="<?= base_url('frontend/pengguna/riwayat-pesanan.php?status=Menunggu+Konfirmasi') ?>"
               class="filter-pill <?= ($filter_status === 'Menunggu Konfirmasi') ? 'active' : '' ?>">Menunggu</a>
            <a href="<?= base_url('frontend/pengguna/riwayat-pesanan.php?status=Diproses') ?>"
               class="filter-pill <?= ($filter_status === 'Diproses') ? 'active' : '' ?>">Diproses</a>
            <a href="<?= base_url('frontend/pengguna/riwayat-pesanan.php?status=Dikirim') ?>"
               class="filter-pill <?= ($filter_status === 'Dikirim') ? 'active' : '' ?>">Dikirim</a>
            <a href="<?= base_url('frontend/pengguna/riwayat-pesanan.php?status=Selesai') ?>"
               class="filter-pill <?= ($filter_status === 'Selesai') ? 'active' : '' ?>">Selesai</a>
        </div>
    </div>

    <!-- Order list -->
    <?php if (empty($pesanan_list)): ?>
        <div class="text-center py-5 text-muted">
            <i class="fas fa-shopping-bag fa-2x mb-3 d-block" style="color:#cbd5e1;"></i>
            <div class="fw-semibold text-dark small">Belum ada pesanan</div>
            <small>Pesanan yang Anda buat akan muncul di sini.</small><br>
            <a href="<?= base_url('frontend/pages/katalog.php') ?>" class="btn btn-sm rounded-pill px-3 mt-3 text-white" style="background:#1e3a5f;">Belanja Sekarang</a>
        </div>
    <?php else: ?>
    <div class="order-cards" style="margin-top:10px;">
        <?php foreach ($pesanan_list as $pes):
            $gambar_produk = array_slice(explode('||', $pes['gambar_produk'] ?? ''), 0, 3);
            $nama_produk = array_slice(explode('||', $pes['nama_produk'] ?? ''), 0, 3);
            $sudah_lunas   = ($pes['status_pembayaran'] ?? 'Belum Dibayar') === 'Lunas';
            $dibatalkan    = $pes['status_pesanan'] === 'Dibatalkan';
        ?>
        <div class="order-card">
            <!-- Kode + Badge -->
            <div class="order-card-top">
                <div>
                    <div class="order-kode"><?= e($pes['kode_pesanan']) ?></div>
                    <div class="order-date"><i class="far fa-clock me-1"></i><?= date('d M Y, H:i', strtotime($pes['tanggal_pesanan'])) ?> WIB</div>
                </div>
                <div class="order-badges">
                    <?= status_badge_pesanan($pes['status_pesanan']) ?>
                    <?= status_badge_pembayaran($pes['status_pembayaran'] ?? 'Belum Dibayar') ?>
                </div>
            </div>

            <!-- Produk + Info -->
            <div class="order-card-mid">
                <?php if (!empty($nama_produk)): ?>
                <div class="order-products" aria-label="Produk yang dipesan">
                    <?php foreach ($nama_produk as $index_produk => $nama):
                        $gb = $gambar_produk[$index_produk] ?? '';
                    ?>
                    <div class="order-product">
                        <img src="<?= e(upload_url('produk', $gb)) ?>" alt="<?= e($nama) ?>">
                        <span title="<?= e($nama) ?>"><?= e($nama) ?></span>
                    </div>
                    <?php endforeach; ?>
                    <?php if ((int)$pes['total_item'] > 3): ?>
                        <span class="order-product-more">+<?= (int)$pes['total_item'] - 3 ?> produk</span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                <div class="order-info-row">
                    <div class="order-info-item">
                        <small>Penerima</small>
                        <span><?= e($pes['nama_penerima']) ?></span>
                    </div>
                    <div class="order-info-item">
                        <small>Jumlah</small>
                        <span><?= (int)$pes['total_qty'] ?> item</span>
                    </div>
                </div>
            </div>

            <!-- Total + Aksi -->
            <div class="order-card-bottom">
                <div class="order-total"><?= format_rupiah($pes['total_harga']) ?></div>
                <div class="order-actions">
                    <?php if (!$sudah_lunas && !$dibatalkan): ?>
                        <a href="<?= base_url('frontend/pengguna/detail-pesanan.php?id=' . (int)$pes['id_pesanan'] . '#ringkasan-pembayaran') ?>"
                           class="btn btn-sm rounded-pill text-white" style="background:#1e3a5f;border-color:#1e3a5f;">
                            <i class="fas fa-wallet me-1"></i> Bayar
                        </a>
                    <?php endif; ?>
                    <a href="<?= base_url('frontend/pengguna/detail-pesanan.php?id=' . $pes['id_pesanan']) ?>"
                       class="btn btn-sm btn-outline-dark rounded-pill">
                        <i class="fas fa-eye me-1"></i> Detail
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/pengguna-footer.php'; ?>
