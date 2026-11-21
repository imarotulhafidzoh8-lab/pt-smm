<?php
// ============================================================
// FILE    : frontend/pages/katalog.php
// FUNGSI  : Halaman Katalog Produk Terpadu (Modern Minimalist Edition)
// PROYEK  : Sistem Informasi PT Shafa Marwa Multiguna
// ============================================================
require_once __DIR__ . '/../../config/app.php';

$current_page = 'katalog';
$page_title   = 'Katalog Produk Unggulan';

catat_pengunjung($pdo, 'katalog');

// Ambil Kategori untuk Filter
$kategori_list = [];
try {
    $stmt = $pdo->query("SELECT * FROM kategori ORDER BY id_kategori ASC");
    $kategori_list = $stmt->fetchAll();
} catch (PDOException $e) {
    $kategori_list = [];
}

// Hitung Jumlah Produk per Kategori
$kategori_counts = [];
try {
    $c_stmt = $pdo->query("SELECT id_kategori, COUNT(*) as total FROM produk WHERE status_produk = 'Aktif' GROUP BY id_kategori");
    while ($row = $c_stmt->fetch()) {
        $kategori_counts[$row['id_kategori']] = (int)$row['total'];
    }
} catch (PDOException $e) {
    $kategori_counts = [];
}
$total_semua_produk = array_sum($kategori_counts);

// Tangkap Parameter Filter & Urutan
$filter_kategori = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;
$keyword         = isset($_GET['q']) ? sanitize($_GET['q']) : '';
$sort            = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'terbaru';

// Tentukan Urutan Query
$order_clause = "p.dibuat_pada DESC";
if ($sort === 'harga_asc') {
    $order_clause = "p.harga ASC";
} elseif ($sort === 'harga_desc') {
    $order_clause = "p.harga DESC";
} elseif ($sort === 'nama_asc') {
    $order_clause = "p.nama_produk ASC";
}

// Query Produk
$produk_list = [];
try {
    $sql = "
        SELECT p.*, k.nama_kategori
        FROM produk p
        JOIN kategori k ON p.id_kategori = k.id_kategori
        WHERE p.status_produk = 'Aktif'
    ";
    $params = [];

    if ($filter_kategori > 0) {
        $sql .= " AND p.id_kategori = ?";
        $params[] = $filter_kategori;
    }

    if ($keyword !== '') {
        $sql .= " AND (p.nama_produk LIKE ? OR p.deskripsi LIKE ?)";
        $params[] = '%' . $keyword . '%';
        $params[] = '%' . $keyword . '%';
    }

    $sql .= " ORDER BY " . $order_clause;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $produk_list = $stmt->fetchAll();
} catch (PDOException $e) {
    $produk_list = [];
}

// Pilihan foto untuk banner hero, terpisah dari hasil filter katalog.
$hero_produk_list = [];
try {
    $hero_stmt = $pdo->query("\n        SELECT id_produk, nama_produk, harga, gambar\n        FROM produk\n        WHERE status_produk = 'Aktif' AND gambar IS NOT NULL AND gambar <> ''\n        ORDER BY dibuat_pada DESC\n        LIMIT 5\n    ");
    $hero_produk_list = $hero_stmt->fetchAll();
} catch (PDOException $e) {
    $hero_produk_list = [];
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<style>
/* ============================================================
   KATALOG PRODUK - CLEAN MINIMALIST
============================================================ */
.catalog-hero-wrap {
    background: #ffffff;
    border-bottom: 1px solid #f0f0f4;
    padding: 1.75rem 0 1.5rem;
}

.catalog-hero-wrap.page-hero-banner {
    border-bottom: 0;
    background: radial-gradient(circle at 82% 18%, rgba(96, 165, 250, .2), transparent 28%), linear-gradient(125deg, #071529 0%, #102c50 62%, #1e3a5f 100%);
}

.catalog-pill-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    color: #1e3a5f;
    padding: 4px 12px;
    border-radius: 9999px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    margin-bottom: 0.75rem;
}

.catalog-hero-title {
    font-size: clamp(1.6rem, 2.6vw, 2.05rem);
    font-weight: 800;
    color: #111111;
    letter-spacing: -0.02em;
    line-height: 1.25;
    margin-bottom: 0.5rem;
}

.catalog-hero-lead {
    font-size: 0.925rem;
    color: #71717a;
    line-height: 1.6;
    max-width: 540px;
    margin: 0;
}

/* Filter Card */
.catalog-filter-card {
    background: #ffffff;
    border: 1px solid #e8edf3;
    border-radius: 18px;
    padding: 1.1rem 1.25rem;
    box-shadow: 0 8px 24px rgba(15, 39, 68, 0.035);
    margin-bottom: 1.5rem;
}

.catalog-filter-card .form-control,
.catalog-filter-card .form-select {
    font-size: 13px;
    border-color: #e4e4e7;
    border-radius: 9999px;
    padding: 0.5rem 1rem;
    background-color: #fafafa;
}

.catalog-filter-card .form-control:focus,
.catalog-filter-card .form-select:focus {
    background-color: #ffffff;
    border-color: #1e3a5f;
    box-shadow: 0 0 0 3px rgba(30, 58, 95, 0.15);
}

/* Category Quick Filter Pills */
.category-pill-group {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    padding-bottom: 6px;
    scrollbar-width: none;
}
.category-pill-group::-webkit-scrollbar {
    display: none;
}

.cat-pill-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
    border: 1px solid #e4e4e7;
    background: #ffffff;
    color: #71717a;
    border-radius: 9999px;
    padding: 6px 14px;
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s ease;
}

.cat-pill-btn:hover {
    border-color: #1e3a5f;
    color: #1e3a5f;
}

.cat-pill-btn.active {
    background: #1e3a5f;
    border-color: #1e3a5f;
    color: #ffffff;
}

.cat-pill-badge {
    display: inline-block;
    padding: 1px 6px;
    border-radius: 999px;
    font-size: 10px;
    font-weight: 700;
    background: #f4f4f5;
    color: #52525b;
}

.cat-pill-btn.active .cat-pill-badge {
    background: rgba(255, 255, 255, 0.25);
    color: #ffffff;
}

/* Modern Product Card */
.cat-product-card {
    background: #ffffff;
    border: 1px solid #e8edf3;
    border-radius: 18px;
    padding: 0.875rem;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
    box-shadow: 0 3px 12px rgba(15, 39, 68, 0.035);
}

.cat-product-card:hover {
    transform: translateY(-2px);
    border-color: #cbd8e8;
    box-shadow: 0 10px 22px rgba(15, 39, 68, 0.08);
}

.cat-img-box {
    background: #f5f8fc;
    border-radius: 14px;
    height: 190px;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
    margin-bottom: 0.85rem;
    padding: 12px;
}

.cat-img-box img {
    max-height: 165px;
    max-width: 100%;
    object-fit: contain;
    transition: transform 0.35s ease;
}

.cat-product-card:hover .cat-img-box img {
    transform: scale(1.06);
}

.cat-badge-top {
    position: absolute;
    top: 8px;
    left: 8px;
    background: #ffffff;
    border: 1px solid #bfdbfe;
    color: #1e3a5f;
    font-size: 10px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 999px;
    backdrop-filter: blur(4px);
}

.cat-stock-top {
    position: absolute;
    top: 8px;
    right: 8px;
    font-size: 10px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 999px;
}

.cat-product-name {
    font-size: 13.5px;
    font-weight: 700;
    color: #111111;
    line-height: 1.35;
    margin-bottom: 4px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    min-height: 36px;
    transition: color 0.2s ease;
}

.cat-product-card:hover .cat-product-name {
    color: #1e3a5f;
}

.cat-product-price {
    font-size: 15px;
    font-weight: 800;
    color: #111111;
    margin: 0;
}

.cat-product-unit {
    font-size: 11px;
    color: #a1a1aa;
    font-weight: 500;
}

.cat-action-circle {
    width: 38px;
    height: 38px;
    min-width: 38px;
    border-radius: 50%;
    background: #1e3a5f;
    color: #ffffff !important;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    text-decoration: none;
    transition: all 0.2s ease;
    border: none;
    box-shadow: 0 4px 12px rgba(30, 58, 95, 0.25);
}

.cat-action-circle:hover {
    background: #1e3a8a;
    transform: scale(1.08);
    box-shadow: 0 6px 16px rgba(30, 58, 95, 0.35);
}

@media (max-width: 576px) {
    .cat-img-box { height: 150px; }
    .cat-img-box img { max-height: 128px; }
    .cat-product-name { font-size: 12.5px; min-height: 34px; }
    .cat-product-price { font-size: 13.5px; }
    .cat-action-circle { width: 34px; height: 34px; min-width: 34px; font-size: 12px; }
}

@media (max-width: 767.98px) {
    .catalog-hero-wrap { padding: 1.25rem 0 1.1rem; }
    .catalog-hero-wrap > .container,
    .catalog-hero-wrap + section > .container { padding-left: 16px; padding-right: 16px; }
    .catalog-hero-wrap .row { --bs-gutter-x: .75rem; }
    .catalog-pill-badge { max-width: 100%; gap: 6px; padding: 5px 10px; font-size: 9px; }
    .catalog-hero-title { font-size: clamp(1.5rem, 7vw, 1.9rem); line-height: 1.16; }
    .catalog-hero-lead { font-size: .85rem; line-height: 1.55; }

    .catalog-hero-wrap + section { padding-top: 1rem !important; }
    .catalog-filter-card { padding: .8rem; margin-bottom: 1.1rem; border-radius: 15px; }
    .catalog-filter-card form.row { --bs-gutter-x: .6rem; --bs-gutter-y: .6rem; }
    .catalog-filter-card .input-group { min-width: 0; }
    .catalog-filter-card .form-control,
    .catalog-filter-card .form-select { min-width: 0; min-height: 42px; padding: .55rem .75rem; font-size: 16px; }
    .catalog-filter-card form .col-lg-5,
    .catalog-filter-card form .col-md-5 { width: 100%; }
    .catalog-filter-card form > .col-6 { width: 50%; }
    .catalog-filter-card form > .col-md-2:last-child { width: 100%; }
    .catalog-filter-card form > .col-md-2 .btn { min-height: 40px; }
    .catalog-filter-card > .pt-3 { align-items: flex-start !important; gap: 8px !important; padding-top: .75rem !important; margin-top: .75rem !important; }
    .catalog-filter-card > .pt-3 > span { flex: 0 0 100%; }
    .category-pill-group { width: 100%; min-width: 0; flex: 1 1 100% !important; gap: 6px; padding: 0 0 5px; overscroll-behavior-x: contain; }
    .cat-pill-btn { padding: 6px 10px; font-size: 11px; }

    .catalog-hero-wrap + section .d-flex.justify-content-between { margin-bottom: .85rem !important; }
    .catalog-hero-wrap + section h5 { font-size: 13px !important; line-height: 1.45; overflow-wrap: anywhere; }
    .catalog-product-grid { --bs-gutter-x: .65rem; --bs-gutter-y: .65rem; }
    .product-item-col { min-width: 0; }
    .cat-product-card { padding: .65rem; border-radius: 14px; }
    .cat-img-box { height: 132px; margin-bottom: .65rem; padding: 8px; border-radius: 11px; }
    .cat-img-box img { max-height: 112px; }
    .cat-badge-top, .cat-stock-top { top: 6px; font-size: 8px; padding: 3px 6px; }
    .cat-badge-top { left: 6px; max-width: 58%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .cat-stock-top { right: 6px; }
    .cat-product-name { min-height: 2.65em; font-size: 11.5px; line-height: 1.32; }
    .cat-product-price { font-size: 12px; overflow-wrap: anywhere; }
    .cat-product-unit { font-size: 10px; }
    .cat-product-card .d-flex.align-items-center.gap-2 { gap: 5px !important; }
    .cat-product-card .d-flex.align-items-center.gap-2 .btn { min-width: 0; padding: .35rem .2rem !important; font-size: 10px !important; white-space: nowrap; }
    .cat-product-card .d-flex.align-items-center.gap-2 .btn i { font-size: 9px; }
}

@media (max-width: 380px) {
    .catalog-hero-wrap > .container,
    .catalog-hero-wrap + section > .container { padding-left: 13px; padding-right: 13px; }
    .catalog-filter-card { padding: .65rem; }
    .catalog-product-grid { --bs-gutter-x: .5rem; --bs-gutter-y: .55rem; }
    .cat-product-card { padding: .55rem; }
    .cat-img-box { height: 118px; }
    .cat-img-box img { max-height: 100px; }
    .cat-product-card .d-flex.align-items-center.gap-2 .btn { font-size: 9px !important; }
}
</style>

<!-- ============================================================
     1. HEADER KATALOG
============================================================ -->
<div class="catalog-hero-wrap page-hero-banner">
    <div class="container">
        <div class="row align-items-center page-hero-layout">
            <div class="col-lg-7 page-hero-content">
                <div class="page-hero-breadcrumb">
                    <a href="<?= base_url('index.php') ?>">Beranda</a>
                    <i class="fas fa-chevron-right" style="font-size: 9px;"></i>
                    <span>Katalog Produk</span>
                </div>
                <div class="page-hero-kicker">
                    <span>✦</span>
                    <span>Distribusi Resmi &bull; Gudang Way Halim</span>
                </div>
                <h1 class="page-hero-title">
                    Katalog Produk Unggulan
                </h1>
                <p class="page-hero-lead">
                    Pilihan komoditas herbal alami, pertanian organik, perikanan, dan produk keluarga terkurasi.
                </p>
            </div>
            <div class="col-lg-5 page-hero-visual">
                <div class="page-hero-visual-card page-hero-product-art">
                    <?php if (!empty($hero_produk_list)): ?>
                        <div class="page-hero-product-carousel" id="catalogHeroCarousel" aria-label="Pilihan produk PT SMM">
                            <?php foreach ($hero_produk_list as $slide_index => $hero_produk): ?>
                                <img
                                    class="page-hero-product-image<?= $slide_index === 0 ? ' is-active' : '' ?>"
                                    src="<?= upload_url('produk', $hero_produk['gambar']) ?>"
                                    alt="<?= e($hero_produk['nama_produk']) ?>"
                                    data-product-name="<?= e($hero_produk['nama_produk']) ?>"
                                    data-product-price="<?= e(format_rupiah($hero_produk['harga'])) ?>"
                                    aria-hidden="<?= $slide_index === 0 ? 'false' : 'true' ?>"
                                    onerror="this.src='<?= asset_url('images/no-image.svg') ?>'">
                            <?php endforeach; ?>
                        </div>
                        <div class="page-hero-product-info">
                            <strong id="catalogHeroProductName"><?= e($hero_produk_list[0]['nama_produk']) ?></strong>
                            <span><span id="catalogHeroProductPrice"><?= format_rupiah($hero_produk_list[0]['harga']) ?></span> &bull; Produk pilihan</span>
                        </div>
                    <?php else: ?>
                        <span class="page-hero-visual-icon"><i class="fas fa-box-open"></i></span>
                        <div class="page-hero-product-info"><strong>Produk Pilihan PT SMM</strong><span>Kualitas untuk keluarga dan mitra</span></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (count($hero_produk_list) > 1): ?>
<script>
(() => {
    const carousel = document.getElementById('catalogHeroCarousel');
    const productName = document.getElementById('catalogHeroProductName');
    const productPrice = document.getElementById('catalogHeroProductPrice');
    if (!carousel || !productName || !productPrice) return;

    const slides = Array.from(carousel.querySelectorAll('.page-hero-product-image'));
    let activeIndex = 0;

    window.setInterval(() => {
        slides[activeIndex].classList.remove('is-active');
        slides[activeIndex].setAttribute('aria-hidden', 'true');
        activeIndex = (activeIndex + 1) % slides.length;
        slides[activeIndex].classList.add('is-active');
        slides[activeIndex].setAttribute('aria-hidden', 'false');
        productName.textContent = slides[activeIndex].dataset.productName || 'Produk pilihan PT SMM';
        productPrice.textContent = slides[activeIndex].dataset.productPrice || '';
    }, 3000);
})();
</script>
<?php endif; ?>

<!-- ============================================================
     2. FILTER & PRODUCT LISTING SECTION
============================================================ -->
<section class="py-4 bg-white">
    <div class="container">
        
        <!-- Filter & Search Toolbar -->
        <div class="catalog-filter-card">
            <form action="<?= base_url('frontend/pages/katalog.php') ?>" method="GET" class="row g-3 align-items-center">
                
                <!-- Search Input -->
                <div class="col-lg-5 col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border border-light-subtle rounded-pill-start text-muted ps-3">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" name="q" class="form-control bg-white border border-light-subtle border-start-0 rounded-pill-end pe-3" placeholder="Cari nama produk, herbal, kopi, pupuk..." value="<?= e($keyword) ?>">
                    </div>
                </div>

                <!-- Category Select Dropdown -->
                <div class="col-lg-3 col-md-3 col-6">
                    <select name="kategori" class="form-select bg-white border border-light-subtle rounded-pill" onchange="this.form.submit()">
                        <option value="0">Semua Kategori (<?= $total_semua_produk ?>)</option>
                        <?php foreach ($kategori_list as $kat): ?>
                            <?php $cnt = $kategori_counts[$kat['id_kategori']] ?? 0; ?>
                            <option value="<?= $kat['id_kategori'] ?>" <?= ($filter_kategori === (int)$kat['id_kategori']) ? 'selected' : '' ?>>
                                <?= e($kat['nama_kategori']) ?> (<?= $cnt ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Sort Dropdown -->
                <div class="col-lg-2 col-md-2 col-6">
                    <select name="sort" class="form-select bg-white border border-light-subtle rounded-pill" onchange="this.form.submit()">
                        <option value="terbaru" <?= ($sort === 'terbaru') ? 'selected' : '' ?>>Terbaru</option>
                        <option value="harga_asc" <?= ($sort === 'harga_asc') ? 'selected' : '' ?>>Harga: Rendah ke Tinggi</option>
                        <option value="harga_desc" <?= ($sort === 'harga_desc') ? 'selected' : '' ?>>Harga: Tinggi ke Rendah</option>
                        <option value="nama_asc" <?= ($sort === 'nama_asc') ? 'selected' : '' ?>>Nama: A &rarr; Z</option>
                    </select>
                </div>

                <!-- Action Button -->
                <div class="col-lg-2 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn rounded-pill flex-fill text-white fw-semibold" style="background:#1e3a5f; border-color:#1e3a5f; font-size:13px; padding: 0.5rem 1rem; box-shadow: 0 4px 12px rgba(30, 58, 95, 0.25);">
                        <i class="fas fa-filter me-1"></i> Terapkan
                    </button>
                    <?php if ($filter_kategori > 0 || $keyword !== '' || $sort !== 'terbaru'): ?>
                        <a href="<?= base_url('frontend/pages/katalog.php') ?>" class="btn btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px;" title="Reset Filter">
                            <i class="fas fa-undo small"></i>
                        </a>
                    <?php endif; ?>
                </div>

            </form>

            <!-- Horizontal Category Quick Navigation Pills -->
            <div class="pt-3 mt-3 border-top d-flex align-items-center gap-2 flex-wrap">
                <span class="small fw-bold text-muted me-1" style="font-size: 11px; letter-spacing: 0.05em; text-transform: uppercase;">Lini Kategori:</span>
                <div class="category-pill-group flex-grow-1">
                    <a href="<?= base_url('frontend/pages/katalog.php' . ($sort !== 'terbaru' ? '?sort=' . $sort : '')) ?>" class="cat-pill-btn <?= ($filter_kategori === 0) ? 'active' : '' ?>">
                        <span>Semua</span>
                        <span class="cat-pill-badge"><?= $total_semua_produk ?></span>
                    </a>
                    <?php foreach ($kategori_list as $kat): ?>
                        <?php 
                        $cnt = $kategori_counts[$kat['id_kategori']] ?? 0;
                        $link = base_url('frontend/pages/katalog.php?kategori=' . $kat['id_kategori'] . ($sort !== 'terbaru' ? '&sort=' . $sort : ''));
                        ?>
                        <a href="<?= $link ?>" class="cat-pill-btn <?= ($filter_kategori === (int)$kat['id_kategori']) ? 'active' : '' ?>">
                            <span><?= e($kat['nama_kategori']) ?></span>
                            <span class="cat-pill-badge"><?= $cnt ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>

        <!-- Result Summary -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h5 class="fw-bold text-dark mb-0 fs-6">
                    <?= count($produk_list) ?> Produk Ditemukan
                    <?php if ($filter_kategori > 0): ?>
                        <?php 
                        $cat_match = array_filter($kategori_list, fn($c) => (int)$c['id_kategori'] === $filter_kategori);
                        $first_match = reset($cat_match);
                        if ($first_match) {
                            echo '<span class="text-muted fw-normal"> &bull; ' . e($first_match['nama_kategori']) . '</span>';
                        }
                        ?>
                    <?php endif; ?>
                    <?php if ($keyword !== ''): ?>
                        <span class="text-muted fw-normal"> &bull; "<?= e($keyword) ?>"</span>
                    <?php endif; ?>
                </h5>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="row g-4 catalog-product-grid">
            <?php if (empty($produk_list)): ?>
                <div class="col-12">
                    <div class="p-5 text-center bg-white border border-light-subtle rounded-4 shadow-xs">
                        <div class="d-inline-flex align-items-center justify-content-center bg-light text-muted rounded-circle mb-3" style="width: 68px; height: 68px; font-size: 26px;">
                            <i class="fas fa-box-open"></i>
                        </div>
                        <h4 class="fw-bold text-dark mb-2">Produk Tidak Ditemukan</h4>
                        <p class="text-muted mb-4 small" style="max-width: 440px; margin: 0 auto;">
                            Tidak ada produk yang cocok dengan kata kunci atau filter kategori yang Anda pilih. Coba gunakan kata kunci lain atau tampilkan seluruh lini produk.
                        </p>
                        <a href="<?= base_url('frontend/pages/katalog.php') ?>" class="btn rounded-pill px-4 py-2 text-white fw-semibold small" style="background:#1e3a5f; border-color:#1e3a5f; box-shadow: 0 4px 12px rgba(30, 58, 95, 0.25);">
                            <i class="fas fa-undo me-1"></i> Tampilkan Semua Produk
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($produk_list as $idx => $p): ?>
                    <div class="col-xl-3 col-lg-4 col-md-6 col-6 product-item-col">
                        <div class="cat-product-card">
                            
                            <!-- Product Image & Badges -->
                            <div class="cat-img-box">
                                <span class="cat-badge-top">
                                    <?= e($p['nama_kategori']) ?>
                                </span>

                                <?php if ($p['stok'] > 0): ?>
                                    <span class="cat-stock-top bg-success-subtle text-success border border-success-subtle">
                                        <i class="fas fa-check-circle me-1"></i>Stok <?= $p['stok'] ?>
                                    </span>
                                <?php else: ?>
                                    <span class="cat-stock-top bg-danger-subtle text-danger border border-danger-subtle">
                                        Habis
                                    </span>
                                <?php endif; ?>

                                <a href="<?= base_url('frontend/pages/detail-produk.php?id=' . $p['id_produk']) ?>" class="d-block text-center w-100">
                                    <img src="<?= upload_url('produk', $p['gambar']) ?>" alt="<?= e($p['nama_produk']) ?>" loading="lazy" onerror="this.src='<?= asset_url('images/no-image.svg') ?>'">
                                </a>
                            </div>

                            <!-- Product Info & Actions -->
                            <div>
                                <a href="<?= base_url('frontend/pages/detail-produk.php?id=' . $p['id_produk']) ?>" class="text-decoration-none">
                                    <div class="cat-product-name" title="<?= e($p['nama_produk']) ?>">
                                        <?= e($p['nama_produk']) ?>
                                    </div>
                                </a>

                                <div class="mb-2 mt-1">
                                    <div class="cat-product-price"><?= format_rupiah($p['harga']) ?></div>
                                    <?php if (!empty($p['satuan'])): ?>
                                        <span class="cat-product-unit">/ <?= e($p['satuan']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="d-flex align-items-center gap-2 pt-2 border-top border-light-subtle mt-auto">
                                    <a href="<?= base_url('frontend/pages/detail-produk.php?id=' . $p['id_produk']) ?>" class="btn btn-outline-secondary rounded-pill btn-sm flex-fill d-inline-flex align-items-center justify-content-center gap-1 py-2 fw-semibold" style="font-size: 12px;">
                                        <i class="fas fa-info-circle"></i>
                                        <span>Detail</span>
                                    </a>
                                    <?php if ($p['stok'] > 0): ?>
                                        <a href="<?= base_url('frontend/pengguna/pemesanan.php?id=' . $p['id_produk']) ?>" class="btn rounded-pill btn-sm flex-fill d-inline-flex align-items-center justify-content-center gap-1 py-2 text-white fw-semibold" style="font-size: 12px; background: #1e3a5f; border-color: #1e3a5f; box-shadow: 0 4px 10px rgba(30,58,95,0.2);">
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
            <?php endif; ?>
        </div>

    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
