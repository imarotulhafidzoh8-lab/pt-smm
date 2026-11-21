<?php
// ============================================================
// FILE    : frontend/pages/detail-produk.php
// FUNGSI  : Halaman Detail Produk & Informasi Pemesanan
// PROYEK  : Sistem Informasi PT Shafa Marwa Multiguna
// ============================================================
require_once __DIR__ . '/../../config/app.php';

$id_produk = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$slug      = isset($_GET['slug']) ? sanitize($_GET['slug']) : '';

if ($id_produk <= 0 && empty($slug)) {
    flash('error', 'Produk tidak ditemukan.');
    redirect(base_url('frontend/pages/katalog.php'));
}

// Ambil Detail Produk
$produk = null;
try {
    if ($id_produk > 0) {
        $stmt = $pdo->prepare("
            SELECT p.*, k.nama_kategori
            FROM produk p
            JOIN kategori k ON p.id_kategori = k.id_kategori
            WHERE p.id_produk = ? AND p.status_produk = 'Aktif'
            LIMIT 1
        ");
        $stmt->execute([$id_produk]);
    } else {
        $stmt = $pdo->prepare("
            SELECT p.*, k.nama_kategori
            FROM produk p
            JOIN kategori k ON p.id_kategori = k.id_kategori
            WHERE p.slug = ? AND p.status_produk = 'Aktif'
            LIMIT 1
        ");
        $stmt->execute([$slug]);
    }
    $produk = $stmt->fetch();
} catch (PDOException $e) {
    $produk = null;
}

if (!$produk) {
    flash('error', 'Produk tidak ditemukan atau saat ini berstatus tidak aktif.');
    redirect(base_url('frontend/pages/katalog.php'));
}

// Ambil 4 Produk Terkait (kategori sama)
$produk_terkait = [];
try {
    $stmt = $pdo->prepare("
        SELECT p.*, k.nama_kategori
        FROM produk p
        JOIN kategori k ON p.id_kategori = k.id_kategori
        WHERE p.id_kategori = ? AND p.id_produk != ? AND p.status_produk = 'Aktif'
        ORDER BY p.dibuat_pada DESC
        LIMIT 4
    ");
    $stmt->execute([$produk['id_kategori'], $produk['id_produk']]);
    $produk_terkait = $stmt->fetchAll();
} catch (PDOException $e) {
    $produk_terkait = [];
}

$current_page = 'katalog';
$page_title   = $produk['nama_produk'];
$company      = get_company_profile($pdo);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<style>
.product-detail-section { background: #f5f8fc !important; }
.product-detail-shell { padding: 1.5rem; border: 1px solid #e5ebf3 !important; border-radius: 24px !important; box-shadow: 0 14px 40px rgba(16,44,80,.07) !important; }
.product-image-frame { min-height: 320px; border-color: #e2eaf3 !important; border-radius: 20px !important; background: radial-gradient(circle at 50% 42%, #fff 0%, #f5f8fc 72%) !important; }
.product-image-frame img { max-width: 100%; max-height: 100%; object-fit: contain; transition: transform .35s ease; }
.product-image-frame:hover img { transform: scale(1.025); }
.product-detail-title { color: #102c50; font-size: clamp(1.65rem, 2.7vw, 2.25rem); letter-spacing: -.035em !important; line-height: 1.2; overflow-wrap: anywhere; }
.product-price-box { padding: 1rem 1.1rem; border: 1px solid #dbe7f5; border-radius: 16px; background: linear-gradient(135deg, #f1f6fd, #fff); }
.product-price-value { color: #102c50; font-size: clamp(1.8rem, 3vw, 2.35rem); letter-spacing: -.04em; line-height: 1.1; }
.product-description { max-width: 68ch; color: #5d6b7c; font-size: .93rem; line-height: 1.8; overflow-wrap: anywhere; }
.product-trust-card { padding: .85rem .65rem !important; border-color: #e6edf5 !important; border-radius: 14px !important; background: #f8fafc !important; }
.product-trust-card i { color: #1e3a5f !important; }
.product-actions { gap: .7rem !important; }
.product-action-buy { min-height: 48px; box-shadow: 0 8px 20px rgba(30,58,95,.2) !important; }
.product-action-chat { min-height: 48px; justify-content: center; }
.related-product-card { padding: .8rem !important; border-color: #e5ebf3 !important; border-radius: 17px !important; box-shadow: 0 5px 18px rgba(16,44,80,.045); }
.related-product-image { height: 170px; border-radius: 12px !important; background: #f5f8fc !important; }
.related-product-image img { max-width: 100%; max-height: 150px; object-fit: contain; }

@media (max-width: 767.98px) {
    .product-detail-section { padding-top: 1.2rem !important; padding-bottom: 2.25rem !important; }
    .product-detail-section > .container { padding-left: 16px; padding-right: 16px; }
    .product-detail-section > .container.py-2 { padding-top: 0 !important; padding-bottom: 0 !important; }
    .product-detail-shell { padding: .9rem !important; margin-bottom: 2rem !important; border-radius: 19px !important; }
    .product-detail-shell > .row { --bs-gutter-x: .85rem; --bs-gutter-y: 1rem; }
    .product-image-frame { min-height: 0; padding: 12px !important; border-radius: 16px !important; }
    .product-detail-title { margin-bottom: .75rem !important; font-size: clamp(1.4rem, 6vw, 1.8rem) !important; }
    .product-price-box { padding: .85rem; margin-bottom: 1rem !important; border-radius: 14px; }
    .product-price-value { font-size: clamp(1.65rem, 7vw, 2rem) !important; }
    .product-price-box .text-muted.fs-6 { font-size: .85rem !important; }
    .product-description { font-size: .86rem !important; line-height: 1.7 !important; }
    .product-trust-grid { --bs-gutter-x: .45rem; --bs-gutter-y: .45rem; }
    .product-trust-grid > [class*="col-"] { width: 33.3333%; }
    .product-trust-card { padding: .65rem .4rem !important; }
    .product-trust-card i { font-size: 1rem !important; }
    .product-trust-card .fw-bold { font-size: 9px !important; line-height: 1.3; }
    .product-trust-card .text-muted { font-size: 8px !important; line-height: 1.35; }
    .product-actions { flex-direction: column; gap: .6rem !important; padding-top: 1rem !important; }
    .product-actions .btn { width: 100%; min-height: 46px; }
    .related-section { margin-top: 1.75rem !important; padding-top: 0 !important; }
    .related-section-heading { align-items: flex-end !important; gap: .75rem; margin-bottom: 1rem !important; }
    .related-section-heading h4 { font-size: 1.05rem; }
    .related-section-heading .btn { flex-shrink: 0; padding: .35rem .65rem; font-size: 10px; }
    .related-product-grid { --bs-gutter-x: .6rem; --bs-gutter-y: .7rem; }
    .related-product-grid > [class*="col-"] { width: 50%; }
    .related-product-card { padding: .55rem !important; border-radius: 14px !important; }
    .related-product-image { height: 118px !important; padding: .5rem !important; }
    .related-product-image img { max-height: 100px !important; }
    .related-product-meta { align-items: flex-start !important; flex-direction: column; gap: 4px; }
    .related-product-meta .badge { max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .related-product-meta > span:last-child { font-size: 9px !important; }
    .related-product-card .related-product-name { min-height: 2.7em; font-size: 11px !important; line-height: 1.35 !important; }
    .related-product-card .related-product-price { font-size: 12px !important; }
    .related-product-card .related-product-actions { gap: 4px !important; }
    .related-product-card .related-product-actions .btn { min-width: 0; padding: .35rem .2rem !important; font-size: 9px !important; white-space: nowrap; }
}

@media (max-width: 380px) {
    .product-detail-section > .container { padding-left: 13px; padding-right: 13px; }
    .product-detail-shell { padding: .75rem !important; }
    .product-trust-card .fw-bold { font-size: 8px !important; }
    .product-trust-card .text-muted { font-size: 7px !important; }
    .related-product-card .related-product-actions .btn { font-size: 8px !important; }
}
</style>

<!-- Page Header Breadcrumb -->
<div class="page-header">
    <div class="container">
        <h1>Detail Produk</h1>
        <div class="breadcrumb-custom">
            <a href="<?= base_url('index.php') ?>">Beranda</a>
            <i class="fas fa-chevron-right small"></i>
            <a href="<?= base_url('frontend/pages/katalog.php') ?>">Katalog</a>
            <i class="fas fa-chevron-right small"></i>
            <a href="<?= base_url('frontend/pages/katalog.php?kategori=' . $produk['id_kategori']) ?>"><?= e($produk['nama_kategori']) ?></a>
            <i class="fas fa-chevron-right small"></i>
            <span><?= e($produk['nama_produk']) ?></span>
        </div>
    </div>
</div>

<section class="py-5 bg-white product-detail-section">
    <div class="container py-2">
        <div class="p-4 p-lg-5 mb-5 bg-white border border-light-subtle rounded-4 shadow-xs product-detail-shell">
            <div class="row g-5">
                <!-- Product Image Column -->
                <div class="col-lg-5">
                    <div class="rounded-4 overflow-hidden border border-light-subtle bg-light d-flex align-items-center justify-content-center position-relative p-4 product-image-frame" style="aspect-ratio: 1/1;">
                        <img src="<?= upload_url('produk', $produk['gambar']) ?>" alt="<?= e($produk['nama_produk']) ?>" class="w-100 h-100 object-fit-contain" onerror="this.src='<?= asset_url('images/no-image.svg') ?>'">
                        <span class="badge bg-white text-dark border rounded-pill position-absolute top-0 start-0 m-3 px-3 py-2 fw-semibold shadow-xs" style="font-size: 12px;">
                            <?= e($produk['nama_kategori']) ?>
                        </span>
                    </div>
                </div>

                <!-- Product Info Column -->
                <div class="col-lg-7 d-flex flex-column">
                    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                        <span class="badge text-white rounded-pill px-3 py-1 fw-normal" style="font-size: 11px; letter-spacing: 0.5px; background: #1e3a5f;">
                            PRODUK RESMI PT SMM
                        </span>
                        <?php if ($produk['stok'] > 0): ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1" style="font-size: 12px;">
                                <i class="fas fa-check-circle me-1"></i> Stok Tersedia (<?= $produk['stok'] ?> <?= e($produk['satuan']) ?>)
                            </span>
                        <?php else: ?>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1" style="font-size: 12px;">
                                <i class="fas fa-times-circle me-1"></i> Stok Habis
                            </span>
                        <?php endif; ?>
                    </div>

                    <h1 class="h2 fw-bold text-dark mb-3 product-detail-title" style="letter-spacing: -0.02em;"><?= e($produk['nama_produk']) ?></h1>

                    <div class="mb-4 product-price-box">
                        <div class="text-muted small mb-1">Harga Resmi PT SMM</div>
                        <div class="d-flex align-items-baseline gap-2">
                            <span class="display-6 fw-bold text-dark product-price-value"><?= format_rupiah($produk['harga']) ?></span>
                            <span class="text-muted fs-6">/ <?= e($produk['satuan']) ?></span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="fw-bold text-dark small text-uppercase mb-2" style="letter-spacing: 0.5px;">Deskripsi Produk</div>
                        <div class="text-muted product-description" style="line-height: 1.8; font-size: 0.95rem;">
                            <?= nl2br(e($produk['deskripsi'])) ?>
                        </div>
                    </div>

                    <!-- 3 Minimalist Service Badges -->
                    <div class="row g-2 mb-4 pt-2 product-trust-grid">
                        <div class="col-sm-4">
                            <div class="p-3 bg-light rounded-3 border border-light-subtle text-center h-100 product-trust-card">
                                <i class="fas fa-certificate text-dark mb-1 d-block fs-5"></i>
                                <div class="fw-bold text-dark" style="font-size: 12px;">100% Produk Asli</div>
                                <div class="text-muted" style="font-size: 11px;">Kualitas Terjamin</div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="p-3 bg-light rounded-3 border border-light-subtle text-center h-100 product-trust-card">
                                <i class="fas fa-truck text-dark mb-1 d-block fs-5"></i>
                                <div class="fw-bold text-dark" style="font-size: 12px;">Kirim Cepat</div>
                                <div class="text-muted" style="font-size: 11px;">Integrasi Biteship</div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="p-3 bg-light rounded-3 border border-light-subtle text-center h-100 product-trust-card">
                                <i class="fas fa-shield-alt text-dark mb-1 d-block fs-5"></i>
                                <div class="fw-bold text-dark" style="font-size: 12px;">Garansi Aman</div>
                                <div class="text-muted" style="font-size: 11px;">Gudang Way Halim</div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-auto pt-4 border-top">
                        <div class="d-flex flex-wrap gap-3 product-actions">
                            <?php if ($produk['stok'] > 0): ?>
                                <a href="<?= base_url('frontend/pengguna/pemesanan.php?id=' . $produk['id_produk']) ?>" class="btn rounded-pill px-4 py-2 fs-6 flex-fill d-inline-flex align-items-center justify-content-center gap-2 text-white product-action-buy" style="background: #1e3a5f; border-color: #1e3a5f; box-shadow: 0 4px 14px rgba(30, 58, 95, 0.28);">
                                    <span>Pesan Produk Ini</span>
                                    <i class="fas fa-arrow-right" style="transform: rotate(-45deg); font-size: 13px;"></i>
                                </a>
                            <?php else: ?>
                                <button class="btn btn-secondary rounded-pill px-4 py-2 fs-6 flex-fill" disabled>
                                    <i class="fas fa-ban me-2"></i> Stok Habis Sementara
                                </button>
                            <?php endif; ?>

                            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $company['no_telepon']) ?>?text=Halo%20PT%20SMM,%20saya%20tertarik%20dengan%20produk%20<?= urlencode($produk['nama_produk']) ?>" target="_blank" class="btn btn-outline-dark rounded-pill px-4 py-2 fs-6 d-inline-flex align-items-center gap-2 product-action-chat">
                                <i class="fab fa-whatsapp text-success fs-5"></i>
                                <span>Tanya Admin</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Products (Modulive Grid) -->
        <?php if (!empty($produk_terkait)): ?>
            <div class="mt-5 pt-3 related-section">
                <div class="d-flex justify-content-between align-items-center mb-4 related-section-heading">
                    <div>
                        <span class="text-muted small text-uppercase fw-bold" style="letter-spacing: 0.5px;">Koleksi Terkait</span>
                        <h4 class="fw-bold text-dark mb-0">Produk Serupa Pilihan</h4>
                    </div>
                    <a href="<?= base_url('frontend/pages/katalog.php?kategori=' . $produk['id_kategori']) ?>" class="btn btn-sm btn-outline-dark rounded-pill px-3">
                        Lihat Semua <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>

                <div class="row g-4 related-product-grid">
                    <?php foreach ($produk_terkait as $pt): ?>
                        <div class="col-xl-3 col-lg-4 col-md-6 col-6">
                            <div class="p-3 bg-white border border-light-subtle rounded-4 h-100 d-flex flex-column justify-content-between shadow-xs transition-hover related-product-card" style="transition: all 0.25s ease;">
                                <!-- Img Box -->
                                <div class="d-flex align-items-center justify-content-center p-3 mb-2 bg-light rounded-3 related-product-image" style="height: 180px;">
                                    <img src="<?= upload_url('produk', $pt['gambar']) ?>" alt="<?= e($pt['nama_produk']) ?>" loading="lazy" style="max-height: 160px; max-width: 100%; object-fit: contain;" onerror="this.src='<?= asset_url('images/no-image.svg') ?>'">
                                </div>

                                <!-- Meta -->
                                <div>
                                    <div class="d-flex align-items-center justify-content-between mb-2 related-product-meta">
                                        <span class="badge bg-light text-muted border rounded-pill px-2 py-1" style="font-size: 11px;">
                                            <?= e($pt['nama_kategori']) ?>
                                        </span>
                                        <?php if ($pt['stok'] > 0): ?>
                                            <span class="text-success small" style="font-size: 11px;"><i class="fas fa-check-circle me-1"></i>Stok <?= $pt['stok'] ?></span>
                                        <?php else: ?>
                                            <span class="text-danger small" style="font-size: 11px;">Habis</span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="mt-2 mb-2">
                                        <a href="<?= base_url('frontend/pages/detail-produk.php?id=' . $pt['id_produk']) ?>" class="text-decoration-none">
                                            <div class="fw-bold text-dark small mb-1 related-product-name" style="line-height: 1.35; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;" title="<?= e($pt['nama_produk']) ?>">
                                                <?= e($pt['nama_produk']) ?>
                                            </div>
                                        </a>
                                        <div class="fw-bold text-dark fs-6 mb-0 related-product-price"><?= format_rupiah($pt['harga']) ?></div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 pt-2 border-top border-light-subtle mt-auto related-product-actions">
                                        <a href="<?= base_url('frontend/pages/detail-produk.php?id=' . $pt['id_produk']) ?>" class="btn btn-outline-secondary rounded-pill btn-sm flex-fill d-inline-flex align-items-center justify-content-center gap-1 py-2 fw-semibold" style="font-size: 12px;">
                                            <i class="fas fa-info-circle"></i>
                                            <span>Detail</span>
                                        </a>
                                        <?php if ($pt['stok'] > 0): ?>
                                            <a href="<?= base_url('frontend/pengguna/pemesanan.php?id=' . $pt['id_produk']) ?>" class="btn rounded-pill btn-sm flex-fill d-inline-flex align-items-center justify-content-center gap-1 py-2 text-white fw-semibold" style="font-size: 12px; background: #1e3a5f; border-color: #1e3a5f; box-shadow: 0 4px 10px rgba(30,58,95,0.2);">
                                                <i class="fas fa-shopping-cart"></i>
                                                <span>Beli</span>
                                            </a>
                                        <?php else: ?>
                                            <button class="btn rounded-pill btn-sm flex-fill d-inline-flex align-items-center justify-content-center gap-1 py-2 fw-semibold" style="font-size: 12px; background: #e2e8f0; color: #94a3b8; border: none;" disabled>
                                                <i class="fas fa-ban"></i>
                                                <span>Habis</span>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

