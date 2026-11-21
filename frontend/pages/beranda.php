<?php
// ============================================================
// FILE    : frontend/pages/beranda.php
// FUNGSI  : Halaman Beranda Utama Website PT SMM
// STYLE   : Modern Scandinavian / Editorial Minimalist (Reference: Modulive)
// PROYEK  : Sistem Informasi PT Shafa Marwa Multiguna
// ============================================================
require_once __DIR__ . '/../../config/app.php';

$current_page = 'beranda';
$page_title   = 'Beranda | Distribusi Produk Unggulan & Herbal Berkualitas';

// Catat Kunjungan Pengunjung Real-Time & Ambil Statistik Bisnis Aktual
catat_pengunjung($pdo, 'beranda');
$live_stats = get_realtime_stats($pdo);

// Ambil Profil Perusahaan
$company = get_company_profile($pdo);

// Ambil Volume Pesanan 4 Minggu Terakhir (untuk chart hero)
$weekly_orders = [0, 0, 0, 0];
try {
    $stmt = $pdo->query("
        SELECT
            FLOOR(DATEDIFF(CURDATE(), DATE(dibuat_pada)) / 7) AS week_ago,
            COUNT(*) AS total
        FROM pesanan
        WHERE dibuat_pada >= DATE_SUB(CURDATE(), INTERVAL 28 DAY)
        GROUP BY week_ago
        ORDER BY week_ago ASC
    ");
    $rows = $stmt->fetchAll();
    foreach ($rows as $row) {
        $idx = 3 - (int)$row['week_ago'];
        if ($idx >= 0 && $idx <= 3) {
            $weekly_orders[$idx] = (int)$row['total'];
        }
    }
} catch (PDOException $e) {}
$max_weekly = max(max($weekly_orders), 1);

// Ambil 3 Pesanan Terbaru untuk panel Order Tracking di hero
$recent_orders_hero = [];
try {
    $stmt = $pdo->query("
        SELECT p.id_pesanan, p.status_pesanan, p.dibuat_pada,
               u.nama_lengkap
        FROM pesanan p
        JOIN pengguna u ON p.id_pengguna = u.id_pengguna
        ORDER BY p.dibuat_pada DESC
        LIMIT 3
    ");
    $recent_orders_hero = $stmt->fetchAll();
} catch (PDOException $e) {}

// Ambil Kategori Produk beserta jumlah produk aktif per kategori
$kategori_list = [];
try {
    $stmt = $pdo->query("
        SELECT k.id_kategori, k.nama_kategori, k.deskripsi, COUNT(p.id_produk) AS total_produk
        FROM kategori k
        LEFT JOIN produk p ON k.id_kategori = p.id_kategori AND p.status_produk = 'Aktif'
        GROUP BY k.id_kategori, k.nama_kategori, k.deskripsi
        ORDER BY k.id_kategori ASC
    ");
    $kategori_list = $stmt->fetchAll();
} catch (PDOException $e) {
    $kategori_list = [];
}

// Ambil maksimal 8 produk aktif yang dipilih admin sebagai Produk Unggulan
$produk_unggulan = [];
try {
    $stmt_produk_unggulan = $pdo->query("
        SELECT p.*, k.nama_kategori
        FROM produk p
        JOIN kategori k ON p.id_kategori = k.id_kategori
        WHERE p.status_produk = 'Aktif'
          AND p.tampil_di_beranda = 1
        ORDER BY p.dibuat_pada DESC, p.id_produk DESC
        LIMIT 8
    ");
    $produk_unggulan = $stmt_produk_unggulan->fetchAll();
} catch (PDOException $e) {
    $produk_unggulan = [];
}
$kategori_unggulan_ids = array_unique(array_map('intval', array_column($produk_unggulan, 'id_kategori')));

// Ambil 3 Lowongan Terbaru Dibuka
$lowongan_terbaru = [];
try {
    $stmt = $pdo->query("
        SELECT * FROM lowongan
        WHERE status_lowongan = 'Dibuka'
        ORDER BY dibuat_pada DESC
        LIMIT 3
    ");
    $lowongan_terbaru = $stmt->fetchAll();
} catch (PDOException $e) {
    $lowongan_terbaru = [];
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<!-- ============================================================
     CUSTOM MINIMALIST EDITORIAL STYLES (MATCHING REFERENCE)
============================================================ -->
<style>
:root {
    --brand-dark: #0f172a;
    --brand-charcoal: #1e293b;
    --brand-muted: #64748b;
    --brand-bg: #f8fafc;
    --brand-card-bg: #ffffff;
    --brand-border: #e2e8f0;
    --brand-accent: #1e3a5f;      /* Navy Blue */
    --brand-accent-bright: #3b82f6; /* Bright Blue for highlights */
    --brand-navy-dark: #0a1628;
    --brand-navy-mid: #1e3a5f;
    --radius-pill: 9999px;
    --radius-card: 20px;
    --radius-card-lg: 24px;
}

body {
    background-color: #ffffff;
    color: var(--brand-dark);
    font-family: 'Plus Jakarta Sans', sans-serif;
    -webkit-font-smoothing: antialiased;
}

/* ================== SHOPWAVE-INSPIRED HERO SECTION (NAVY DARK WITH HERO IMAGE & RICH VISUALS) ================== */
.hero-shopwave-wrap {
    padding: 5rem 0 3.5rem;
    position: relative;
    background: linear-gradient(135deg, #05101f 0%, #0a1f38 45%, #102d52 100%);
    min-height: 88vh;
    display: flex;
    align-items: center;
    overflow: hidden;
}

/* Real photo background texture overlay */
.hero-shopwave-wrap::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image: radial-gradient(circle at 85% 20%, rgba(59, 130, 246, 0.18) 0%, transparent 55%), radial-gradient(circle at 15% 85%, rgba(30, 58, 95, 0.35) 0%, transparent 60%);
    pointer-events: none;
    z-index: 1;
}

/* Subtle dot grid pattern */
.hero-shopwave-wrap::after {
    content: '';
    position: absolute;
    inset: 0;
    background-image: radial-gradient(rgba(255,255,255,0.065) 1px, transparent 1px);
    background-size: 26px 26px;
    z-index: 0;
}

/* Decorative ambient glow orbs */
.hero-blob-tl {
    position: absolute;
    top: -140px;
    left: -100px;
    width: 520px;
    height: 520px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(37, 99, 235, 0.22) 0%, transparent 65%);
    pointer-events: none;
    z-index: 1;
}
.hero-blob-br {
    position: absolute;
    bottom: -120px;
    right: -80px;
    width: 550px;
    height: 550px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(96, 165, 250, 0.16) 0%, transparent 65%);
    pointer-events: none;
    z-index: 1;
}

.hero-beam {
    position: absolute;
    top: 55%;
    left: 0;
    width: 100%;
    height: 1px;
    background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,0.08) 50%, transparent 100%);
    z-index: 1;
}

.hero-shopwave-wrap .container {
    position: relative;
    z-index: 2;
}

.hero-pill-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.18);
    backdrop-filter: blur(10px);
    padding: 6px 16px;
    border-radius: var(--radius-pill);
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.05em;
    color: rgba(255,255,255,0.92);
    margin-bottom: 1.25rem;
}

.hero-pill-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #60a5fa;
    display: inline-block;
    box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.35);
    animation: pulseDot 2s ease-in-out infinite;
}
@keyframes pulseDot {
    0%, 100% { box-shadow: 0 0 0 3px rgba(96,165,250,0.35); }
    50%       { box-shadow: 0 0 0 7px rgba(96,165,250,0.1); }
}

.hero-left-title {
    font-size: clamp(1.85rem, 3.4vw, 2.65rem);
    font-weight: 800;
    color: #ffffff;
    line-height: 1.2;
    letter-spacing: -0.025em;
    margin-bottom: 1.15rem;
    text-shadow: 0 2px 20px rgba(0,0,0,0.4);
}

.hero-brand-highlight {
    background: linear-gradient(135deg, #60a5fa 0%, #93c5fd 60%, #bfdbfe 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    display: inline-block;
}

.hero-left-lead {
    font-size: 0.95rem;
    line-height: 1.7;
    color: rgba(255,255,255,0.76);
    max-width: 520px;
    margin-bottom: 2rem;
}

.hero-cta-group {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

/* ── HERO RIGHT: Featured Image Stage & Interactive Floating Cards ── */
.hero-visual-stage {
    position: relative;
    padding: 24px 10px;
}

.hero-image-card {
    position: relative;
    height: 440px;
    border-radius: 26px;
    overflow: hidden;
    border: 1px solid rgba(255,255,255,0.2);
    box-shadow: 0 25px 65px rgba(0,0,0,0.45), 0 0 35px rgba(59,130,246,0.15);
    background: #071529;
}

.hero-main-featured-img {
    width: 100%;
    height: 100%;
    position: absolute;
    inset: 0;
    object-fit: cover;
    display: block;
    opacity: 0;
    transition: opacity 0.8s ease;
}
.hero-main-featured-img.is-active {
    opacity: 1;
}
.hero-main-featured-img.hero-slide-next {
    pointer-events: none;
}

.hero-image-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(5,16,31,0.2) 0%, rgba(5,16,31,0.45) 50%, rgba(5,16,31,0.92) 100%);
    pointer-events: none;
}

.hero-image-caption-box {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 24px 26px;
    z-index: 3;
}
.hero-image-caption-title {
    font-size: 1.15rem;
    font-weight: 800;
    color: #ffffff;
    margin-bottom: 4px;
    letter-spacing: -0.01em;
}
.hero-image-caption-sub {
    font-size: 12px;
    color: rgba(255,255,255,0.75);
    margin-bottom: 12px;
}
.hero-image-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.hero-image-badge-item {
    font-size: 11px;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 999px;
    background: rgba(255,255,255,0.14);
    backdrop-filter: blur(6px);
    color: #ffffff;
    border: 1px solid rgba(255,255,255,0.2);
}

@media (max-width: 991px) {
    .hero-visual-stage { margin-top: 2.5rem; padding: 20px 0; }
    .hero-image-card { height: 340px; }
}
@media (max-width: 576px) {
    .hero-image-card { height: 260px; }
}

/* Trusted Logo Cloud Strip */
.trusted-strip-box {
    margin-top: 3.5rem;
    padding-top: 2rem;
    border-top: 1px solid rgba(255,255,255,0.12);
    text-align: center;
}

.trusted-strip-title {
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    color: rgba(255,255,255,0.45);
    margin-bottom: 1.5rem;
}

.trusted-logo-items {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    gap: 2.5rem;
    padding-right: 2.5rem;
    flex-wrap: nowrap;
    flex: 0 0 auto;
}

.trusted-logo-marquee {
    width: 100%;
    overflow: hidden;
    -webkit-mask-image: linear-gradient(90deg, transparent, #000 5%, #000 95%, transparent);
    mask-image: linear-gradient(90deg, transparent, #000 5%, #000 95%, transparent);
}

.trusted-logo-track {
    display: flex;
    width: max-content;
    animation: trustedLogoSlide 30s linear infinite;
}

@media (min-width: 768px) {
    .trusted-logo-marquee { display: block; }
    .trusted-logo-track { animation-duration: 34s; }
    .trusted-logo-items { flex-wrap: nowrap; }
    .trusted-logo-pill { flex: 0 0 auto; }
}

.trusted-logo-marquee:hover .trusted-logo-track {
    animation-play-state: paused;
}

@keyframes trustedLogoSlide {
    from { transform: translateX(0); }
    to { transform: translateX(-50%); }
}

.trusted-logo-pill {
    font-size: 14px;
    font-weight: 700;
    color: rgba(255,255,255,0.55);
    display: inline-flex;
    align-items: center;
    gap: 8px;
    letter-spacing: -0.01em;
    opacity: 0.85;
    transition: opacity 0.2s;
    text-decoration: none;
}
.trusted-logo-pill--image {
    min-width: 150px;
    min-height: 52px;
    justify-content: center;
    padding: 8px 14px;
    border: 1px solid rgba(255,255,255,0.72);
    border-radius: 12px;
    background: #fff;
    opacity: 0.92;
}
.trusted-logo-pill--image img {
    display: block;
    width: 122px;
    height: 34px;
    object-fit: contain;
}
.trusted-logo-pill:hover {
    opacity: 1;
    color: rgba(255,255,255,0.9);
}

/* ================== PROFESSIONAL RESI TRACKER SECTION ================== */
.resi-tracker-section {
    margin-top: 1.5rem;
    margin-bottom: 4rem;
}
.resi-tracker-card {
    background: #ffffff;
    border-radius: 18px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 24px rgba(15, 23, 42, 0.05);
    padding: 2rem 2.25rem;
}
.resi-section-tag {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #1e3a5f;
    margin-bottom: 4px;
}
.resi-section-title {
    font-size: 1.35rem;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -0.02em;
    margin-bottom: 6px;
}
.resi-section-desc {
    font-size: 13.5px;
    color: #64748b;
    line-height: 1.55;
    max-width: 620px;
    margin-bottom: 1.5rem;
}
.resi-form-row {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #f8fafc;
    border: 1.5px solid #cbd5e1;
    border-radius: 12px;
    padding: 6px 8px 6px 16px;
    transition: all 0.2s ease;
}
.resi-form-row:focus-within {
    border-color: #1e3a5f;
    background: #ffffff;
    box-shadow: 0 0 0 3px rgba(30, 58, 95, 0.08);
}
.resi-input-field {
    border: none;
    background: transparent;
    outline: none;
    width: 100%;
    font-size: 13.5px;
    color: #0f172a;
}
.resi-input-field::placeholder {
    color: #94a3b8;
    font-size: 13px;
}
.resi-submit-btn {
    background: #1e3a5f;
    color: #ffffff;
    border: none;
    border-radius: 8px;
    padding: 9px 20px;
    font-size: 13px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    white-space: nowrap;
    transition: background 0.2s ease;
}
.resi-submit-btn:hover {
    background: #122842;
    color: #ffffff;
}

/* Tracking Result Output */
.resi-result-box {
    display: none;
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid #f1f5f9;
}
.resi-meta-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 12px;
    background: #f8fafc;
    border-radius: 10px;
    padding: 12px 16px;
    margin-bottom: 1.25rem;
    border: 1px solid #e2e8f0;
}
.resi-meta-label {
    font-size: 11px;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    margin-bottom: 2px;
}
.resi-meta-val {
    font-size: 13px;
    font-weight: 700;
    color: #0f172a;
}
.resi-timeline-list {
    position: relative;
    padding-left: 20px;
    margin-left: 6px;
}
.resi-timeline-list::before {
    content: '';
    position: absolute;
    top: 5px;
    bottom: 5px;
    left: 4px;
    width: 2px;
    background: #e2e8f0;
}
.resi-timeline-item {
    position: relative;
    margin-bottom: 16px;
}
.resi-timeline-item:last-child {
    margin-bottom: 0;
}
.resi-timeline-dot {
    position: absolute;
    left: -20px;
    top: 4px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #1e3a5f;
}
.resi-timeline-item:not(:first-child) .resi-timeline-dot {
    background: #94a3b8;
}
.resi-timeline-desc {
    font-size: 13px;
    font-weight: 600;
    color: #1e293b;
    line-height: 1.4;
}
.resi-timeline-sub {
    font-size: 11.5px;
    color: #64748b;
    margin-top: 2px;
}

/* ================== STATS / METRICS STRIP ================== */
.stats-strip {
    background: #0f172a;
    border-top: none;
    border-bottom: none;
    padding: 2.5rem 0;
    margin-bottom: 3.5rem;
}
.stat-number {
    font-size: clamp(1.85rem, 3vw, 2.4rem);
    font-weight: 800;
    color: #60a5fa;
    letter-spacing: -0.025em;
    line-height: 1;
    margin-bottom: 0.35rem;
}
.stat-desc {
    color: rgba(255,255,255,0.55);
    font-size: 0.8125rem;
    max-width: 220px;
    margin: 0 auto;
    line-height: 1.4;
}

/* ================== PRODUCTS SECTION ================== */
.section-title-center {
    font-size: clamp(1.45rem, 2.5vw, 1.95rem);
    font-weight: 800;
    color: var(--brand-dark);
    letter-spacing: -0.02em;
    text-align: center;
    margin-bottom: 1.25rem;
}

/* Pill category filter */
.filter-pills {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 2rem;
}
.filter-pill-btn {
    border: 1px solid var(--brand-border);
    background: #ffffff;
    color: var(--brand-muted);
    border-radius: var(--radius-pill);
    padding: 6px 16px;
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}
.filter-pill-btn:hover {
    border-color: var(--brand-accent);
    color: var(--brand-accent);
}
.filter-pill-btn.active {
    background: var(--brand-accent);
    border-color: var(--brand-accent);
    color: #ffffff;
}

/* Product Card - Exact Match to Reference */
.modulive-product-card {
    background: var(--brand-card-bg);
    border: 1px solid #f0f0f2;
    border-radius: var(--radius-card);
    padding: 1.5rem;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    position: relative;
}
.modulive-product-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 14px 30px rgba(0,0,0,0.06);
    background: #ffffff;
    border-color: #e4e4e7;
}

.product-img-box {
    height: 180px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.25rem;
}
.product-img-box img {
    max-height: 170px;
    max-width: 100%;
    object-fit: contain;
    transition: transform 0.3s ease;
}
.modulive-product-card:hover .product-img-box img {
    transform: scale(1.06);
}

.product-info-row {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 12px;
}
.product-name-txt {
    font-size: 15px;
    font-weight: 700;
    color: var(--brand-dark);
    margin-bottom: 4px;
    line-height: 1.35;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.product-price-txt {
    font-size: 16px;
    font-weight: 800;
    color: var(--brand-dark);
    margin-bottom: 0;
}

/* Circular Arrow Action Button (From Reference) */
.circle-action-btn {
    width: 42px;
    height: 42px;
    min-width: 42px;
    border-radius: 50%;
    background: var(--brand-accent);
    color: #ffffff !important;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    text-decoration: none;
    transition: all 0.2s ease;
    border: none;
}
.circle-action-btn:hover {
    background: #1e3a8a;
    transform: scale(1.08);
}

/* ================== WHY CHOOSE US SPLIT SECTION ================== */
.why-section {
    padding: 5rem 0;
}
.video-feature-card {
    border-radius: var(--radius-card-lg);
    overflow: hidden;
    position: relative;
    height: 380px;
    background-size: cover;
    background-position: center;
    box-shadow: 0 8px 30px rgba(0,0,0,0.06);
    display: flex;
    align-items: center;
    justify-content: center;
}
.play-btn-circle {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: rgba(17, 17, 17, 0.85);
    backdrop-filter: blur(6px);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    cursor: pointer;
    transition: all 0.25s ease;
    border: 2px solid rgba(255, 255, 255, 0.3);
}
.play-btn-circle:hover {
    transform: scale(1.1);
    background: var(--brand-accent);
}
.video-caption {
    font-size: 13px;
    color: var(--brand-muted);
    margin-top: 12px;
}

/* Minimalist Accordion (Border-line style like reference) */
.minimal-accordion {
    border-top: 1px solid #e4e4e7;
}
.minimal-accordion-item {
    border-bottom: 1px solid #e4e4e7;
    padding: 1.25rem 0;
}
.minimal-accordion-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
    user-select: none;
}
.minimal-accordion-title {
    font-size: 17px;
    font-weight: 700;
    color: var(--brand-dark);
    margin: 0;
}
.minimal-accordion-icon {
    font-size: 18px;
    font-weight: 400;
    color: var(--brand-dark);
    transition: transform 0.2s ease;
}
.minimal-accordion-body {
    padding-top: 10px;
    font-size: 14px;
    color: var(--brand-muted);
    line-height: 1.6;
    display: none;
}
.minimal-accordion-item.open .minimal-accordion-body {
    display: block;
}
.minimal-accordion-item.open .minimal-accordion-icon {
    transform: rotate(45deg);
}

/* ================== WIDE BRAND PROMISE BANNER ================== */
.promise-banner {
    border-radius: var(--radius-card-lg);
    background: linear-gradient(90deg, rgba(8, 20, 38, 0.88) 0%, rgba(8, 20, 38, 0.72) 45%, rgba(8, 20, 38, 0.45) 100%), url('<?= asset_url("images/smm-store.webp") ?>') center center/cover no-repeat;
    padding: 5rem 3.5rem;
    position: relative;
    color: #ffffff;
    margin-bottom: 5rem;
    overflow: hidden;
}
.promise-banner-title {
    font-size: clamp(2rem, 4vw, 3rem);
    font-weight: 800;
    letter-spacing: -0.02em;
    max-width: 580px;
    line-height: 1.25;
    margin-bottom: 2rem;
}
.promise-banner-btn {
    background: #ffffff;
    color: var(--brand-dark) !important;
    border-radius: var(--radius-pill);
    padding: 12px 28px;
    font-weight: 700;
    font-size: 14px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    transition: all 0.2s ease;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}
.promise-banner-btn:hover {
    background: #f4f4f5;
    transform: translateY(-2px);
}

/* ================== NEWSLETTER STRIP ================== */
.newsletter-section {
    padding: 3rem 0 5rem;
    text-align: center;
}
.newsletter-title {
    font-size: clamp(1.8rem, 3.5vw, 2.5rem);
    font-weight: 800;
    color: var(--brand-dark);
    margin-bottom: 1.5rem;
    letter-spacing: -0.02em;
}
.newsletter-form-pill {
    max-width: 480px;
    margin: 0 auto;
    position: relative;
    display: flex;
    align-items: center;
    background: #ffffff;
    border: 1px solid var(--brand-border);
    border-radius: var(--radius-pill);
    padding: 4px 6px 4px 18px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.03);
}
.newsletter-form-pill input {
    border: none;
    outline: none;
    background: transparent;
    flex-grow: 1;
    font-size: 14px;
    color: var(--brand-dark);
}
.newsletter-form-pill button {
    background: var(--brand-accent);
    color: #ffffff;
    border: none;
    border-radius: var(--radius-pill);
    padding: 10px 24px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
}
.newsletter-form-pill button:hover {
    background: #1e3a8a;
}

/* Landing page responsive layout */
@media (max-width: 991px) {
    .hero-shopwave-wrap { min-height: auto; padding: 3.25rem 0 2.5rem; }
    .hero-visual-stage { margin-top: 0; }
    .trusted-strip-box { margin-top: 2.25rem; }
    .why-section { padding: 3.5rem 0; }
}

@media (max-width: 767px) {
    .container { padding-left: 16px !important; padding-right: 16px !important; }

    .hero-shopwave-wrap { padding: 3rem 0 2rem; }
    .hero-pill-badge { font-size: 10px; padding: 5px 12px; }
    .hero-left-title { font-size: clamp(1.85rem, 8vw, 2.35rem); line-height: 1.15; }
    .hero-left-lead { font-size: 0.88rem; line-height: 1.6; margin-bottom: 1.5rem; }
    .hero-cta-group { gap: 8px; margin-bottom: 2rem; }
    .hero-cta-group .btn { width: 100%; justify-content: center; padding: 12px 20px; font-size: 13px; }

    /* Resi Tracker Responsive */
    .resi-tracker-section { margin-top: 1.25rem; margin-bottom: 2.5rem; }
    .resi-tracker-card { padding: 1.35rem 1.15rem; border-radius: 14px; }
    .resi-section-tag { font-size: 10px; }
    .resi-section-title { font-size: 1.15rem; margin-bottom: 4px; }
    .resi-section-desc { font-size: 12.5px; line-height: 1.5; margin-bottom: 0.85rem; }
    .resi-form-row { flex-direction: column; align-items: stretch; gap: 8px; padding: 8px; border-radius: 12px; }
    .resi-input-field { padding: 6px 4px; font-size: 13px; }
    .resi-submit-btn { width: 100%; justify-content: center; padding: 10px; font-size: 13px; }
    .resi-meta-grid { grid-template-columns: 1fr 1fr; gap: 8px; padding: 10px 12px; margin-bottom: 1rem; }
    .resi-meta-label { font-size: 10px; }
    .resi-meta-val { font-size: 12px; word-break: break-word; }
    .resi-timeline-desc { font-size: 12px; }
    .resi-timeline-sub { font-size: 11px; }

    .trusted-strip-box { margin-top: 1.75rem; padding-top: 1.25rem; }
    .trusted-strip-title { font-size: 10px; line-height: 1.5; margin-bottom: 1rem; letter-spacing: .5px; }
    .trusted-logo-items { gap: 1.5rem; padding-right: 1.5rem; }
    .trusted-logo-pill { flex: 0 0 auto; justify-content: center; font-size: 10px; gap: 5px; }

    .stats-strip { padding: 1.25rem 0; margin-bottom: 2rem; }
    .stats-strip .row { --bs-gutter-x: .4rem; --bs-gutter-y: .75rem; }
    .stats-strip .row > [class*="col-"] { width: 33.333333%; }
    .stat-number { font-size: 1.4rem; margin-bottom: .3rem; }
    .stat-desc { max-width: 115px; font-size: .64rem; line-height: 1.35; }

    .section-title-center { font-size: 1.4rem; line-height: 1.3; margin-bottom: 1rem; }
    .filter-pills { justify-content: flex-start; flex-wrap: nowrap; overflow-x: auto; margin: 0 -4px 1.25rem; padding: 0 4px 6px; scrollbar-width: none; }
    .filter-pills::-webkit-scrollbar { display: none; }
    .filter-pill-btn { flex: 0 0 auto; padding: 7px 12px; font-size: 11px; }
    #productGrid {
        --bs-gutter-x: .65rem;
        --bs-gutter-y: .75rem;
        flex-wrap: nowrap;
        overflow-x: auto;
        overflow-y: hidden;
        padding-bottom: .5rem;
        scroll-snap-type: x mandatory;
        scroll-padding-left: .5rem;
        padding-left: .5rem;
        padding-right: .5rem;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        overscroll-behavior-x: contain;
    }
    #productGrid::-webkit-scrollbar { display: none; }
    #productGrid .product-item-col { flex: 0 0 88%; width: 88%; max-width: 88%; scroll-snap-align: start; }
    .modulive-product-card { padding: .7rem; border-radius: 14px; }
    .product-img-box { height: 128px; margin-bottom: .7rem; }
    .product-img-box img { max-height: 118px; }
    .product-name-txt { font-size: 12px; line-height: 1.35; min-height: 32px; }
    .product-price-txt { font-size: 13px; }
    #productGrid .badge, #productGrid .text-success, #productGrid .text-danger { font-size: 9px !important; }
    #productGrid .modulive-product-card .mb-3 { margin-bottom: .65rem !important; }
    #productGrid .modulive-product-card .d-flex .btn { min-width: 0; padding: .4rem .3rem !important; font-size: 10px !important; gap: 4px !important; }

    .why-section { padding: 2.5rem 0; }
    .why-section .row { --bs-gutter-x: 1rem; --bs-gutter-y: 1.5rem; }
    .video-feature-card { height: 230px; border-radius: 16px; }
    .why-section h2 { font-size: 1.55rem !important; line-height: 1.25; }
    .why-section .mb-4 > .text-muted { font-size: 13px !important; line-height: 1.6; }
    .minimal-accordion-item { padding: .9rem 0; }
    .minimal-accordion-title { font-size: 14px; line-height: 1.4; padding-right: 8px; }
    .minimal-accordion-icon { flex: 0 0 auto; font-size: 17px; }
    .minimal-accordion-body { font-size: 12px; line-height: 1.55; }

    .promise-banner { padding: 2rem 1.25rem; margin-bottom: 2.5rem; border-radius: 16px; }
    .promise-banner-title { font-size: 1.55rem; line-height: 1.25; margin-bottom: 1.25rem; }
    .promise-banner-btn { min-height: 44px; padding: 10px 16px; font-size: 12px; }

    .newsletter-section { padding: 1rem 0 2.75rem; }
    .newsletter-title { font-size: 1.45rem; line-height: 1.3; margin-bottom: 1rem; }
    .newsletter-form-pill { padding: 4px 5px 4px 12px; }
    .newsletter-form-pill input { min-width: 0; font-size: 12px; }
    .newsletter-form-pill button { flex: 0 0 auto; padding: 9px 12px; font-size: 11px; }

    .product-showcase-section { margin-bottom: 1.25rem !important; padding-bottom: .25rem !important; }
    .mobile-product-footer { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-top: .35rem; padding: .7rem 0 .25rem; border-top: 1px solid #edf1f5; }
    .mobile-product-hint { color: #7a8797; font-size: 10px; line-height: 1.35; }
    .mobile-product-link { flex: 0 0 auto; display: inline-flex; align-items: center; gap: 6px; padding: 7px 11px; border-radius: 999px; background: #1e3a5f; color: #fff; text-decoration: none; font-size: 10px; font-weight: 700; }
    .mobile-product-link:hover { background: #102c50; color: #fff; }
    .why-section { padding: 1.25rem 0 2.5rem; }

    footer .container.py-3 { padding-top: 1rem !important; padding-bottom: 1rem !important; }
    footer .container.py-3 > .row { --bs-gutter-y: 1.25rem; margin-bottom: 1.5rem !important; }
    footer .container.py-3 > .d-flex { flex-direction: column; align-items: flex-start !important; gap: 12px !important; }
    footer .container.py-3 > .d-flex .d-flex { flex-wrap: wrap; gap: 10px !important; font-size: 11px !important; }
}

@media (max-width: 380px) {
    .resi-meta-grid { grid-template-columns: 1fr; }
    .hero-image-card { height: 250px; }
    .hero-image-caption-box { padding: 11px; }
    .hero-image-caption-title { font-size: 13px; }
    .hero-image-badge-item { font-size: 8px; padding: 3px 6px; }
    #productGrid .modulive-product-card .d-flex .btn span { font-size: 9px; }
    .stat-desc { font-size: .6rem; }
}

@media (prefers-reduced-motion: reduce) {
    .hero-pill-dot { animation: none; }
    .trusted-logo-track { animation: none; }
    .trusted-logo-items[aria-hidden="true"] { display: none; }
    .hero-main-featured-img,
    .modulive-product-card,
    .product-img-box img { transition: none !important; }
}
</style>

<!-- ============================================================
     1. HERO SECTION: RICH NAVY CORPORATE WITH IMAGE BANNER
============================================================ -->
<div class="hero-shopwave-wrap">
    <!-- Decorative background elements -->
    <div class="hero-blob-tl"></div>
    <div class="hero-blob-br"></div>
    <div class="hero-beam"></div>
    <div class="container">
        <div class="row align-items-center g-5">
            
            <!-- Left Column: Typography, Trust Signals & CTAs -->
            <div class="col-lg-6">
                <!-- Top Badge Pill -->
                <div class="hero-pill-badge hero-anim-item hero-delay-1">
                    <span class="hero-pill-dot"></span>
                    <span>DISTRIBUSI RESMI &bull; WAY HALIM, BANDAR LAMPUNG</span>
                </div>

                <!-- Big Left-Aligned Title with Glowing Gradient Accent -->
                <h1 class="hero-left-title hero-anim-item hero-delay-2">
                    Pusat Distribusi Terpercaya<br class="d-none d-md-inline">
                    &amp; Pemasaran Produk<br class="d-none d-md-inline">
                    Multiguna <span class="hero-brand-highlight">PT SMM</span>
                </h1>

                <!-- Sub-headline -->
                <p class="hero-left-lead hero-anim-item hero-delay-3">
                    Menghadirkan aneka komoditas unggulan pertanian, perikanan, herbal alami, kopi, dan nutrisi keluarga terpercaya. Dilayani langsung dari Head Office Way Halim dengan integrasi pengiriman cepat ke seluruh pelosok Nusantara.
                </p>

                <!-- Dual CTA Group -->
                <div class="hero-cta-group hero-anim-item hero-delay-4">
                    <a href="<?= base_url('frontend/pages/katalog.php') ?>" class="btn rounded-pill px-4 py-3 fw-semibold d-inline-flex align-items-center gap-2 text-white" style="background:#2563eb; border-color:#2563eb; box-shadow: 0 6px 20px rgba(37,99,235,0.45);">
                        <span>Jelajahi Katalog Produk</span>
                        <i class="fas fa-arrow-right" style="transform: rotate(-45deg); font-size: 13px;"></i>
                    </a>
                    <a href="<?= base_url('frontend/pages/profil.php') ?>" class="btn rounded-pill px-4 py-3 fw-semibold d-inline-flex align-items-center gap-2" style="border: 1.5px solid rgba(255,255,255,0.35); color: #fff; background: rgba(255,255,255,0.06); backdrop-filter: blur(8px);">
                        <i class="fas fa-building text-info" style="font-size: 13px;"></i>
                        <span>Profil &amp; Legalitas</span>
                    </a>
                </div>
            </div>

            <!-- Right Column: High-Impact Hero Visual Showcase with Real Photos -->
            <div class="col-lg-6 hero-anim-item hero-delay-3">
                <div class="hero-visual-stage">

                    <!-- ── MAIN FEATURED IMAGE CARD ── -->
                    <div class="hero-image-card">
                        <!-- Photo SMM Store -->
                        <img src="<?= asset_url('images/smm-store.webp') ?>"
                             alt="SMM Store &amp; Customer Lounge PT SMM"
                             class="hero-main-featured-img is-active"
                             style="object-position: center center;">

                        <!-- Cinematic Overlay -->
                        <div class="hero-image-overlay"></div>

                        <!-- Bottom Caption Overlay on the Image -->
                        <div class="hero-image-caption-box">
                            <div class="hero-image-caption-title" id="heroCaptionTitle">
                                SMM Store &amp; Customer Service Lounge
                            </div>
                            <div class="hero-image-caption-sub" id="heroCaptionSub">
                                Pelayanan Pembelian Langsung, Edukasi Produk &amp; Konsultasi Kemitraan
                            </div>
                            <div class="hero-image-badges">
                                <span class="hero-image-badge-item"><i class="fas fa-boxes-stacked me-1 text-info"></i> <?= $live_stats['total_produk'] ?>+ Komoditas Aktif</span>
                                <span class="hero-image-badge-item"><i class="fas fa-shield-halved me-1 text-success"></i> Berizin BPOM &amp; Halal</span>
                                <span class="hero-image-badge-item"><i class="fas fa-bolt me-1 text-warning"></i> Tracking Resi Live</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>

        <!-- Trusted by / Logistics Partners Strip (ShopWave Inspired) -->
        <div class="trusted-strip-box">
            <div class="trusted-strip-title">Didukung Ekspedisi Nasional &amp; Kanal Pembayaran Terpercaya</div>
            <div class="trusted-logo-marquee" aria-label="Mitra ekspedisi dan pembayaran">
                <div class="trusted-logo-track">
                    <div class="trusted-logo-items">
                        <span class="trusted-logo-pill trusted-logo-pill--image"><img src="<?= base_url('assets/images/ekspedisi/sicepat.png') ?>" alt="SiCepat Ekspres" loading="lazy"></span>
                        <span class="trusted-logo-pill trusted-logo-pill--image"><img src="<?= base_url('assets/images/ekspedisi/jnt.png') ?>" alt="J&amp;T Express" loading="lazy"></span>
                        <span class="trusted-logo-pill trusted-logo-pill--image"><img src="<?= base_url('assets/images/ekspedisi/anteraja.png') ?>" alt="Anteraja" loading="lazy"></span>
                        <span class="trusted-logo-pill trusted-logo-pill--image"><img src="<?= base_url('assets/images/ekspedisi/jne.png') ?>" alt="JNE Express" loading="lazy"></span>
                        <span class="trusted-logo-pill trusted-logo-pill--image"><img src="<?= base_url('assets/images/ekspedisi/midtrans.png?v=2') ?>" alt="Midtrans" loading="lazy"></span>
                    </div>
                    <div class="trusted-logo-items" aria-hidden="true">
                        <span class="trusted-logo-pill trusted-logo-pill--image"><img src="<?= base_url('assets/images/ekspedisi/sicepat.png') ?>" alt="" loading="lazy"></span>
                        <span class="trusted-logo-pill trusted-logo-pill--image"><img src="<?= base_url('assets/images/ekspedisi/jnt.png') ?>" alt="" loading="lazy"></span>
                        <span class="trusted-logo-pill trusted-logo-pill--image"><img src="<?= base_url('assets/images/ekspedisi/anteraja.png') ?>" alt="" loading="lazy"></span>
                        <span class="trusted-logo-pill trusted-logo-pill--image"><img src="<?= base_url('assets/images/ekspedisi/jne.png') ?>" alt="" loading="lazy"></span>
                        <span class="trusted-logo-pill trusted-logo-pill--image"><img src="<?= base_url('assets/images/ekspedisi/midtrans.png?v=2') ?>" alt="" loading="lazy"></span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- ============================================================
     2. STATS / METRICS STRIP (REAL-TIME DATABASE STATISTICS)
============================================================ -->
<section class="stats-strip">
    <div class="container">
        <div class="row text-center g-4">
            <div class="col-md-4">
                <div class="stat-number"><?= number_format($live_stats['total_pengunjung']) ?></div>
                <div class="stat-desc">Total Pengunjung</div>
            </div>
            <div class="col-md-4">
                <div class="stat-number"><?= $live_stats['total_produk'] ?>+</div>
                <div class="stat-desc">Pilihan Produk Aktif dalam <?= $live_stats['total_kategori'] ?> Lini Kategori</div>
            </div>
            <div class="col-md-4">
                <div class="stat-number"><?= $live_stats['tingkat_kepuasan'] ?></div>
                <div class="stat-desc">Tingkat Layanan Sukses (<?= $live_stats['total_pesanan'] ?> Pesanan Terlayani)</div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     2.5. LAYANAN LACAK PENGIRIMAN & STATUS PESANAN (CLEAN & MINIMALIST)
============================================================ -->
<section class="container resi-tracker-section">
    <div class="resi-tracker-card">
        <div class="row align-items-center g-4">
            <div class="col-lg-5">
                <div class="resi-section-tag">Layanan Pelanggan &amp; Distribusi</div>
                <h3 class="resi-section-title">Lacak Pengiriman Pesanan</h3>
                <p class="resi-section-desc mb-0">
                    Pantau status perjalanan komoditas Anda secara berkala dari gudang logistik Way Halim hingga sampai ke alamat penerima.
                </p>
            </div>
            <div class="col-lg-7">
                <form id="publicTrackerForm" onsubmit="handlePublicTrack(event)">
                    <div class="resi-form-row">
                        <i class="fas fa-magnifying-glass text-muted me-1"></i>
                        <input type="text" id="publicResiInput" class="resi-input-field" placeholder="Masukkan nomor resi atau ID pesanan..." autocomplete="off" required>
                        <button type="submit" class="resi-submit-btn" id="publicTrackBtn">
                            <span id="trackBtnText">Lacak</span>
                            <i class="fas fa-arrow-right" id="trackBtnIcon"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Dynamic Result Box -->
        <div id="trackerResultBox" class="resi-result-box">
            <!-- Injected by JavaScript -->
        </div>
    </div>
</section>

<!-- ============================================================
     3. PRODUCT SHOWCASE: "OUR BEST QUALITY PRODUCTS"
============================================================ -->
<section class="container mb-5 pb-4 product-showcase-section">
    
    <!-- Section Title -->
    <h2 class="section-title-center">Produk Pilihan Terbaik Kami</h2>

    <!-- Category Filter Pills (Interactive Client-side) -->
    <div class="filter-pills">
        <button class="filter-pill-btn active" data-filter="all">Semua</button>
        <?php foreach ($kategori_list as $kat): ?>
            <?php if (!in_array((int)$kat['id_kategori'], $kategori_unggulan_ids, true)) continue; ?>
            <button class="filter-pill-btn" data-filter="<?= $kat['id_kategori'] ?>">
                <?= e($kat['nama_kategori']) ?>
            </button>
        <?php endforeach; ?>
    </div>

    <!-- Product Grid (Match reference layout with circular arrow button) -->
    <div class="row g-4" id="productGrid">
        <?php if (empty($produk_unggulan)): ?>
            <div class="col-12 text-center py-5 text-muted">
                Belum ada produk unggulan yang dipilih.
            </div>
        <?php else: ?>
            <?php foreach ($produk_unggulan as $p): ?>
                <div class="col-xl-3 col-lg-4 col-md-6 product-item-col" data-cat="<?= $p['id_kategori'] ?>" data-all-visible="1">
                    <div class="modulive-product-card">
                        
                        <!-- Image Container -->
                        <div class="product-img-box">
                            <img src="<?= upload_url('produk', $p['gambar']) ?>" alt="<?= e($p['nama_produk']) ?>" loading="lazy" onerror="this.src='<?= asset_url('images/no-image.svg') ?>'">
                        </div>

                        <!-- Info & Action -->
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge bg-white text-muted border rounded-pill px-2 py-1" style="font-size:11px;">
                                    <?= e($p['nama_kategori']) ?>
                                </span>
                                <?php if ($p['stok'] > 0): ?>
                                    <span class="text-success small" style="font-size:11px;"><i class="fas fa-check-circle me-1"></i>Stok <?= $p['stok'] ?></span>
                                <?php else: ?>
                                    <span class="text-danger small" style="font-size:11px;">Habis</span>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <a href="<?= base_url('frontend/pages/detail-produk.php?id=' . $p['id_produk']) ?>" class="text-decoration-none">
                                    <div class="product-name-txt" title="<?= e($p['nama_produk']) ?>">
                                        <?= e($p['nama_produk']) ?>
                                    </div>
                                </a>
                                <div class="product-price-txt">
                                    <?= format_rupiah($p['harga']) ?>
                                </div>
                            </div>

                            <!-- Action Buttons: Detail & Beli -->
                            <div class="d-flex align-items-center gap-2 pt-2 border-top border-light-subtle">
                                <a href="<?= base_url('frontend/pages/detail-produk.php?id=' . $p['id_produk']) ?>" class="btn btn-outline-secondary rounded-pill btn-sm flex-fill d-inline-flex align-items-center justify-content-center gap-1 py-2 fw-semibold" style="font-size: 12px;">
                                    <i class="fas fa-info-circle"></i>
                                    <span>Detail</span>
                                </a>
                                <?php if ($p['stok'] > 0): ?>
                                    <a href="<?= base_url('frontend/pengguna/pemesanan.php?id=' . $p['id_produk']) ?>" class="btn rounded-pill btn-sm flex-fill d-inline-flex align-items-center justify-content-center gap-1 py-2 text-white fw-semibold" style="font-size: 12px; background: #1e3a5f; border-color: #1e3a5f; box-shadow: 0 4px 10px rgba(30, 58, 95, 0.2);">
                                        <i class="fas fa-shopping-cart"></i>
                                        <span>Beli</span>
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-secondary rounded-pill btn-sm flex-fill d-inline-flex align-items-center justify-content-center gap-1 py-2 fw-semibold" style="font-size: 12px; background: #e2e8f0; color: #94a3b8; border: none;" disabled>
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

    <div class="mobile-product-footer d-md-none">
        <span class="mobile-product-hint"><i class="fas fa-arrows-left-right me-1"></i>Geser untuk melihat produk lainnya</span>
        <a href="<?= base_url('frontend/pages/katalog.php') ?>" class="mobile-product-link">Lihat Katalog <i class="fas fa-arrow-right"></i></a>
    </div>

</section>

<!-- ============================================================
     4. WHY CHOOSE US (SPLIT SECTION WITH MINIMAL ACCORDION)
============================================================ -->
<section class="container why-section">
    <div class="row g-5 align-items-center">
        
        <!-- Left: Studio / Video Showcase Card -->
        <div class="col-lg-6">
            <div class="video-feature-card" style="background-image: url('<?= asset_url('images/tentang-kami/img_7340.jpg') ?>'); background-size: cover; background-position: center;">
                <a href="<?= base_url('frontend/pages/profil.php') ?>" class="play-btn-circle" title="Pelajari Dokumen & Profil Perusahaan">
                    <i class="fas fa-play" style="margin-left: 3px;"></i>
                </a>
            </div>
            <div class="video-caption text-center">
                Piagam Visi &amp; Misi Resmi &bull; Pelajari legalitas &amp; profil lengkap PT Shafa Marwa Multiguna
            </div>
        </div>

        <!-- Right: Accordion List -->
        <div class="col-lg-6">
            <div class="mb-4">
                <h2 class="fw-bold text-navy fs-1 mb-2" style="letter-spacing:-0.02em;">Mengapa Memilih Kami</h2>
                <p class="text-muted" style="font-size:15px;">
                    Komitmen PT SMM dalam menghadirkan rantai pasok terpercaya, keaslian produk, serta kenyamanan transaksi bagi seluruh pelanggan.
                </p>
            </div>

            <div class="minimal-accordion">
                
                <!-- Accordion 1 -->
                <div class="minimal-accordion-item open">
                    <div class="minimal-accordion-header" onclick="toggleAccordion(this)">
                        <h4 class="minimal-accordion-title">Kualitas Produk Teruji &amp; 100% Alami</h4>
                        <span class="minimal-accordion-icon">+</span>
                    </div>
                    <div class="minimal-accordion-body">
                        Seluruh komoditas herbal, madu hutan, dan produk kesehatan diproses secara higienis, berizin resmi, serta bebas bahan pengawet berbahaya untuk melindungi kesehatan keluarga Anda.
                    </div>
                </div>

                <!-- Accordion 2 -->
                <div class="minimal-accordion-item">
                    <div class="minimal-accordion-header" onclick="toggleAccordion(this)">
                        <h4 class="minimal-accordion-title">Pemasaran Digital &amp; Live Shopping Interaktif</h4>
                        <span class="minimal-accordion-icon">+</span>
                    </div>
                    <div class="minimal-accordion-body">
                        Melalui siaran langsung di TikTok Live dengan host terlatih, konsumen mendapatkan edukasi detail mengenai manfaat produk secara transparan sebelum memesan.
                    </div>
                </div>

                <!-- Accordion 3 -->
                <div class="minimal-accordion-item">
                    <div class="minimal-accordion-header" onclick="toggleAccordion(this)">
                        <h4 class="minimal-accordion-title">Pengiriman Cepat &amp; Resi Otomatis (Biteship)</h4>
                        <span class="minimal-accordion-icon">+</span>
                    </div>
                    <div class="minimal-accordion-body">
                        Didukung integrasi kurir Biteship multi-ekspedisi (JNE, J&T, SiCepat, dll), ongkos kirim dihitung akurat berdasarkan kode pos dan riwayat perjalanan dapat dilacak live.
                    </div>
                </div>

                <!-- Accordion 4 -->
                <div class="minimal-accordion-item">
                    <div class="minimal-accordion-header" onclick="toggleAccordion(this)">
                        <h4 class="minimal-accordion-title">Pembayaran Instan Terverifikasi (Midtrans)</h4>
                        <span class="minimal-accordion-icon">+</span>
                    </div>
                    <div class="minimal-accordion-body">
                        Mendukung pembayaran digital otomatis melalui QRIS (GoPay, OVO, Dana), Virtual Account bank nasional, serta transfer bank yang terkonfirmasi secara real-time.
                    </div>
                </div>

            </div>
        </div>

    </div>
</section>

<!-- ============================================================
     5. WIDE QUALITY PROMISE BANNER (DARK MOOD CARD)
============================================================ -->
<div class="container">
    <div class="promise-banner">
        <h2 class="promise-banner-title">
            Menghubungkan Produk Berkualitas Lokal Menuju Pasar Nasional.
        </h2>
        <a href="<?= base_url('frontend/pages/katalog.php') ?>" class="promise-banner-btn">
            <span>Lihat Semua Produk</span>
            <i class="fas fa-arrow-right" style="transform: rotate(-45deg);"></i>
        </a>
    </div>
</div>

<!-- ============================================================
     6. NEWSLETTER & OFFER STRIP
============================================================ -->
<section class="container newsletter-section">
    <h3 class="newsletter-title">Dapatkan Informasi &amp; Penawaran Spesial Terbaru</h3>
    <form class="newsletter-form-pill" onsubmit="event.preventDefault(); if (typeof Swal !== 'undefined') { Swal.fire({ icon: 'success', title: 'Terima Kasih!', text: 'Anda telah berhasil terdaftar untuk menerima penawaran spesial.', confirmButtonText: 'Selesai' }); } else { alert('Terima kasih! Anda telah terdaftar untuk menerima penawaran spesial.'); } this.reset();">
        <i class="fas fa-envelope text-muted me-2 ms-1"></i>
        <input type="email" placeholder="Masukkan alamat email Anda..." required>
        <button type="submit">Langganan</button>
    </form>
</section>

<!-- Client-side Category Filtering Script -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const filterBtns = document.querySelectorAll('.filter-pill-btn');
    const productItems = document.querySelectorAll('.product-item-col');

    filterBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            // Update active state
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const filterVal = this.getAttribute('data-filter');

            productItems.forEach(item => {
                const showItem = filterVal === 'all'
                    ? item.getAttribute('data-all-visible') === '1'
                    : item.getAttribute('data-cat') === filterVal;
                if (showItem) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
});

function toggleAccordion(el) {
    const item = el.closest('.minimal-accordion-item');
    const wasOpen = item.classList.contains('open');

    // Close all
    document.querySelectorAll('.minimal-accordion-item').forEach(i => i.classList.remove('open'));

    if (!wasOpen) {
        item.classList.add('open');
    }
}

// Quick Resi Tracker Handlers
function quickFillTrack(val) {
    const input = document.getElementById('publicResiInput');
    if (!input) return;
    input.value = val;
    handlePublicTrack(new Event('submit'));
}

async function handlePublicTrack(e) {
    if (e && e.preventDefault) e.preventDefault();
    const input = document.getElementById('publicResiInput');
    const val = input ? input.value.trim() : '';
    if (!val) return;

    const btn = document.getElementById('publicTrackBtn');
    const btnText = document.getElementById('trackBtnText');
    const btnIcon = document.getElementById('trackBtnIcon');
    const resultBox = document.getElementById('trackerResultBox');

    // Loading state
    if (btn) btn.disabled = true;
    if (btnText) btnText.textContent = 'Melacak...';
    if (btnIcon) btnIcon.className = 'fas fa-spinner fa-spin';

    try {
        const resp = await fetch('<?= base_url("backend/api/lacak-resi-publik.php?q=") ?>' + encodeURIComponent(val));
        const res = await resp.json();

        if (!res.success) {
            resultBox.innerHTML = `
                <div class="alert alert-warning d-flex align-items-center gap-2 mb-0 rounded-3 border-0 py-3" style="background:#fef3c7;color:#92400e;">
                    <i class="fas fa-triangle-exclamation fs-5 flex-shrink-0"></i>
                    <div>
                        <div class="fw-bold">Resi Tidak Ditemukan</div>
                        <div class="small">${res.message || 'Silakan cek kembali nomor resi Anda.'}</div>
                    </div>
                </div>
            `;
            resultBox.style.display = 'block';
            return;
        }

        // Render clean, professional corporate data
        let badgeClass = 'bg-secondary-subtle text-secondary-emphasis';
        let statusTitle = res.status_pesanan || 'Diproses';
        if (statusTitle === 'Selesai') badgeClass = 'bg-success-subtle text-success border border-success-subtle';
        else if (statusTitle === 'Dikirim') badgeClass = 'bg-primary-subtle text-primary border border-primary-subtle';
        else if (statusTitle === 'Diproses') badgeClass = 'bg-warning-subtle text-warning-emphasis border border-warning-subtle';

        let eventsHtml = '';
        if (Array.isArray(res.history) && res.history.length > 0) {
            res.history.forEach((ev, idx) => {
                const desc = ev.note || ev.description || '-';
                const loc = ev.location ? `<span class="me-3"><i class="fas fa-location-dot me-1 text-muted"></i>${ev.location}</span>` : '';
                const time = ev.updated_at ? `<span><i class="far fa-clock me-1 text-muted"></i>${ev.updated_at}</span>` : '';
                eventsHtml += `
                    <div class="resi-timeline-item">
                        <div class="resi-timeline-dot"></div>
                        <div class="resi-timeline-desc">${desc}</div>
                        <div class="resi-timeline-sub">${loc} ${time}</div>
                    </div>
                `;
            });
        } else {
            eventsHtml = `
                <div class="resi-timeline-item">
                    <div class="resi-timeline-dot"></div>
                    <div class="resi-timeline-desc">Paket telah diverifikasi dan siap dijemput oleh pihak ekspedisi.</div>
                    <div class="resi-timeline-sub">Gudang Logistik Head Office Way Halim, Bandar Lampung</div>
                </div>
            `;
        }

        resultBox.innerHTML = `
            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                <span class="badge ${badgeClass} rounded-pill px-3 py-2 fw-semibold" style="font-size:12px;">
                    Status: ${statusTitle}
                </span>
                <button type="button" class="btn btn-sm text-muted p-0 text-decoration-none" onclick="document.getElementById('trackerResultBox').style.display='none'">
                    <i class="fas fa-times me-1"></i>Tutup
                </button>
            </div>

            <div class="resi-meta-grid">
                <div>
                    <div class="resi-meta-label">Nomor Resi</div>
                    <div class="resi-meta-val text-navy">${res.nomor_resi}</div>
                </div>
                <div>
                    <div class="resi-meta-label">Ekspedisi</div>
                    <div class="resi-meta-val">${res.kurir}</div>
                </div>
                <div>
                    <div class="resi-meta-label">Tujuan</div>
                    <div class="resi-meta-val">${res.tujuan}</div>
                </div>
                <div>
                    <div class="resi-meta-label">Penerima</div>
                    <div class="resi-meta-val">${res.penerima_sensor}</div>
                </div>
            </div>

            <div class="mb-2 fw-bold text-dark small" style="font-size:12.5px;">
                Riwayat Pengiriman Paket
            </div>
            <div class="resi-timeline-list">
                ${eventsHtml}
            </div>
        `;

        resultBox.style.display = 'block';

    } catch (err) {
        resultBox.innerHTML = `
            <div class="alert alert-danger py-2 small mb-0 rounded-3">
                <i class="fas fa-exclamation-circle me-1"></i> Gagal menghubungi server pelacakan. Silakan coba kembali.
            </div>
        `;
        resultBox.style.display = 'block';
    } finally {
        if (btn) btn.disabled = false;
        if (btnText) btnText.textContent = 'Lacak Paket';
        if (btnIcon) btnIcon.className = 'fas fa-arrow-right';
    }
}
</script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
