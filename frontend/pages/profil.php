<?php
// ============================================================
// FILE    : frontend/pages/profil.php
// FUNGSI  : Halaman Profil Perusahaan & Kredensial Resmi PT SMM
// PROYEK  : Sistem Informasi PT Shafa Marwa Multiguna
// ============================================================
require_once __DIR__ . '/../../config/app.php';

$current_page = 'profil';
$page_title   = 'Tentang Kami & Profil Perusahaan';

catat_pengunjung($pdo, 'profil');
$live_stats   = get_realtime_stats($pdo);
$company      = get_company_profile($pdo);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<style>
/* ============================================================
   TENTANG KAMI / PROFIL STYLING (BLACK & WHITE EDITORIAL MINIMALIST)
   ============================================================ */
:root {
    --bw-dark: #102c50;
    --bw-charcoal: #1e3a5f;
    --bw-muted: #71717a;
    --bw-light: #fafafa;
    --bw-border: #e4e4e7;
    --bw-card-bg: #ffffff;
}

/* Page Header */
.profil-header-wrap {
    position: relative;
    overflow: hidden;
    padding: 3.5rem 0;
    color: #ffffff;
    background: radial-gradient(circle at 82% 18%, rgba(96, 165, 250, 0.2), transparent 28%), linear-gradient(125deg, #071529 0%, #102c50 62%, #1e3a5f 100%);
}

.profil-header-wrap::before {
    content: "";
    position: absolute;
    inset: 0;
    pointer-events: none;
    opacity: 0.22;
    background-image: radial-gradient(rgba(255,255,255,.34) .7px, transparent .7px);
    background-size: 22px 22px;
    mask-image: linear-gradient(90deg, transparent, #000 65%);
}

.profil-hero-content,
.profil-hero-visual { position: relative; z-index: 1; }

.profil-hero-breadcrumb {
    display: flex;
    align-items: center;
    gap: 9px;
    margin-bottom: 1.4rem;
    color: rgba(255,255,255,.68);
    font-size: 12px;
}

.profil-hero-breadcrumb a { color: rgba(255,255,255,.68); text-decoration: none; }
.profil-hero-breadcrumb a:hover { color: #ffffff; }

.profil-hero-visual {
    min-height: 260px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.profil-logo-orbit {
    position: absolute;
    width: 250px;
    height: 250px;
    border: 1px solid rgba(255,255,255,.14);
    border-radius: 50%;
    box-shadow: 0 0 0 28px rgba(255,255,255,.025), 0 0 0 58px rgba(255,255,255,.02);
}

.profil-logo-card {
    position: relative;
    width: min(210px, 70%);
    padding: 18px;
    border-radius: 24px;
    background: #ffffff;
    box-shadow: 0 24px 55px rgba(0,0,0,.28);
    transform: rotate(2deg);
}

.profil-logo-card img { display: block; width: 100%; height: auto; }

@media (max-width: 767px) {
    .profil-header-wrap { padding: 2.5rem 0 2rem; }
    .profil-hero-visual { min-height: 210px; margin-top: 1.25rem; }
    .profil-logo-orbit { width: 190px; height: 190px; }
    .profil-logo-card { width: 165px; padding: 14px; }
}

.profil-pill-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255,255,255,.08);
    border: 1px solid rgba(255,255,255,.2);
    color: #ffffff;
    padding: 4px 12px;
    border-radius: 9999px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    margin-bottom: 0.75rem;
}

.profil-hero-title {
    font-size: clamp(1.85rem, 3.2vw, 2.5rem);
    font-weight: 800;
    color: #ffffff;
    letter-spacing: -0.025em;
    line-height: 1.2;
    margin-bottom: 0.5rem;
}

.profil-hero-lead {
    font-size: 0.95rem;
    color: rgba(255,255,255,.76);
    line-height: 1.65;
    max-width: 640px;
    margin: 0;
}

/* Navy accents across the company profile content */
.profil-header-wrap ~ section.bg-light {
    background-color: #f3f7fc !important;
}

.profil-header-wrap ~ section .text-dark {
    color: #102c50 !important;
}

.profil-header-wrap ~ section .bg-dark {
    background-color: #102c50 !important;
}

.profil-header-wrap ~ section .bg-light {
    background-color: #eef4fb !important;
}

.profil-header-wrap ~ section .doc-card,
.profil-header-wrap ~ section .executive-card {
    border-color: #dce6f1;
    box-shadow: 0 8px 24px rgba(16, 44, 80, .055);
}

.profil-header-wrap ~ section .doc-card:hover,
.profil-header-wrap ~ section .executive-card:hover {
    border-color: #9db6d1;
    box-shadow: 0 14px 32px rgba(16, 44, 80, .11);
}

.legalitas-section { padding: 2.5rem 0 !important; }
.legalitas-section > .container { max-width: 1140px; }
.legalitas-layout { --bs-gutter-x: 2rem; --bs-gutter-y: 1.5rem; }
.legal-doc-viewer { overflow: hidden; border: 1px solid #dce6f1; border-radius: 18px; background: #fff; box-shadow: 0 12px 30px rgba(16,44,80,.07); }
.legal-doc-viewer-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 14px 16px; border-bottom: 1px solid #e8edf3; }
.legal-doc-preview { height: 330px; padding: 16px; background: #f5f8fc; display: flex; align-items: center; justify-content: center; cursor: zoom-in; }
.legal-doc-preview img { width: 100%; height: 100%; object-fit: contain; }
.legal-doc-tabs { display: flex; gap: 8px; padding: 12px 14px; border-top: 1px solid #e8edf3; }
.legal-doc-tab { flex: 1; border: 1px solid #dce6f1; border-radius: 10px; padding: 8px 10px; background: #fff; color: #53657b; font-size: 12px; font-weight: 700; transition: .2s ease; }
.legal-doc-tab:hover, .legal-doc-tab.active { border-color: #1e3a5f; background: #1e3a5f; color: #fff; }
.legal-info-card { padding: 14px 16px; border: 1px solid #e1e9f2; border-radius: 14px; background: #fff; }
.legal-info-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 10px; }
.legal-info-title { margin: 0 0 3px; color: #102c50; font-size: 15px; font-weight: 750; }
.legal-info-subtitle { margin: 0; color: #778497; font-size: 11px; }
.legal-info-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px 16px; }
.legal-info-item { min-width: 0; }
.legal-info-label { display: block; margin-bottom: 2px; color: #8793a2; font-size: 10px; }
.legal-info-value { display: block; color: #263b56; font-size: 11px; font-weight: 650; overflow-wrap: anywhere; }
.legal-info-open { flex-shrink: 0; border: 0; padding: 5px 9px; border-radius: 999px; background: #eef4fb; color: #1e3a5f; font-size: 10px; font-weight: 700; }
.legal-info-open:hover { background: #1e3a5f; color: #fff; }
@media (max-width: 767px) {
    .legalitas-section { padding: 2rem 0 !important; }
    .legal-doc-preview { height: 260px; }
    .legalitas-layout { --bs-gutter-x: 1rem; }
}
@media (max-width: 420px) {
    .legal-info-grid { grid-template-columns: 1fr; }
}

/* Master Identity Card */
.master-identity-card {
    background: var(--bw-dark);
    color: #ffffff;
    border-radius: 24px;
    padding: 2.25rem;
    position: relative;
    box-shadow: 0 16px 40px rgba(0, 0, 0, 0.08);
}

.master-identity-card .badge-smm {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: #ffffff;
    color: var(--bw-dark);
    font-size: 13px;
    font-weight: 800;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

/* Document Cards */
.doc-card {
    background: #ffffff;
    border: 1px solid var(--bw-border);
    border-radius: 20px;
    overflow: hidden;
    transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
    height: 100%;
}

.doc-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.06);
    border-color: #a1a1aa;
}

.doc-img-preview {
    height: 240px;
    background: #f5f8fc;
    border-bottom: 1px solid var(--bw-border);
    padding: 14px;
    text-align: center;
    position: relative;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.doc-img-preview img {
    height: 100%;
    width: 100%;
    object-fit: contain;
    border-radius: 8px;
    transition: transform 0.3s ease;
}

.doc-img-preview:hover img {
    transform: scale(1.02);
}

.doc-zoom-badge {
    position: absolute;
    bottom: 12px;
    right: 12px;
    background: rgba(16, 44, 80, 0.9);
    color: #ffffff;
    font-size: 11px;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 9999px;
    letter-spacing: 0.3px;
    pointer-events: none;
    transition: background 0.2s ease;
}

.doc-img-preview:hover .doc-zoom-badge {
    background: var(--bw-dark);
}

@media (max-width: 576px) {
    .doc-img-preview { height: 205px; padding: 12px; }
}

/* Executive Cards */
.executive-card {
    background: #ffffff;
    border: 1px solid var(--bw-border);
    border-radius: 20px;
    padding: 24px;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.executive-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.06);
    border-color: #a1a1aa;
}

.executive-avatar {
    width: 88px;
    height: 88px;
    border-radius: 18px;
    object-fit: cover;
    border: 3px solid #f4f4f5;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
    cursor: pointer;
}

/* Clean Metadata Table */
.meta-table {
    width: 100%;
    font-size: 13px;
}

.meta-table tr td {
    padding: 8px 0;
    border-bottom: 1px dashed #f1f5f9;
}

.meta-table tr:last-child td {
    border-bottom: none;
    padding-bottom: 0;
}

.meta-table .meta-label {
    color: var(--bw-muted);
    width: 42%;
    font-weight: 500;
}

.meta-table .meta-value {
    color: var(--bw-dark);
    font-weight: 600;
    text-align: right;
}

.badge-soft {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.5px;
    padding: 4px 12px;
    border-radius: 9999px;
    background: #eff6ff;
    color: #1e3a5f;
    border: 1px solid #bfdbfe;
    display: inline-block;
}

/* Visi & Misi Modern Timeline (Kerabatani Reference Style) */
.vm-timeline {
    position: relative;
    padding-left: 2px;
}
.vm-timeline-cat {
    margin-bottom: 12px;
}
.vm-step {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    position: relative;
}
.vm-track {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex-shrink: 0;
    width: 32px;
}
.vm-node {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #eff6ff;
    border: 2px solid #1e3a5f;
    color: #1e3a5f;
    font-weight: 700;
    font-size: 11px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(30, 58, 95, 0.15);
    z-index: 2;
    transition: all 0.25s ease;
}
.vm-step:hover .vm-node {
    background: #1e3a5f;
    color: #ffffff;
    transform: scale(1.08);
}
.vm-line {
    width: 2px;
    flex-grow: 1;
    min-height: 28px;
    background: #e2e8f0;
    margin: 4px 0;
}
.vm-body {
    flex: 1;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 10px 14px 12px;
    margin-bottom: 12px;
    transition: all 0.2s ease;
}
.vm-step:hover .vm-body {
    border-color: #cbd5e1;
    background: #f8fafc;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
}
.vm-title {
    font-size: 13.5px;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 3px;
    line-height: 1.35;
}
.vm-desc {
    font-size: 12px;
    color: #64748b;
    margin-bottom: 0;
    line-height: 1.55;
}

/* Feature Icon Circle in B&W */
.icon-circle-bw {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: var(--bw-dark);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    flex-shrink: 0;
}

/* CTAs in B&W */
.btn-dark-bw {
    background: var(--bw-dark);
    color: #ffffff !important;
    border: 1px solid var(--bw-dark);
    border-radius: 9999px;
    padding: 0.45rem 1.25rem;
    font-weight: 600;
    font-size: 13px;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
}

.btn-dark-bw:hover {
    background: #27272a;
    border-color: #27272a;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.btn-outline-bw {
    background: #ffffff;
    color: var(--bw-dark) !important;
    border: 1px solid var(--bw-border);
    border-radius: 9999px;
    padding: 0.45rem 1.25rem;
    font-weight: 600;
    font-size: 13px;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
}

.btn-outline-bw:hover {
    border-color: var(--bw-dark);
    background: #fafafa;
}

/* Mobile layout: tighter gutters, readable type, and cards sized to the viewport. */
@media (max-width: 767px) {
    .profil-header-wrap { padding: 1.75rem 0 1.5rem; }
    .profil-header-wrap > .container,
    .profil-header-wrap ~ section > .container { padding-left: 16px; padding-right: 16px; }
    .profil-header-wrap .row,
    .profil-header-wrap ~ section .row { --bs-gutter-x: 1rem; --bs-gutter-y: 1.25rem; }
    .profil-header-wrap ~ section > .container > .row { width: 100%; margin-left: 0; margin-right: 0; }
    .profil-header-wrap ~ section .col,
    .profil-header-wrap ~ section [class*="col-"] { min-width: 0; }
    .profil-header-wrap ~ section img { max-width: 100%; }
    .profil-header-wrap ~ section p,
    .profil-header-wrap ~ section h2,
    .profil-header-wrap ~ section h3,
    .profil-header-wrap ~ section h4,
    .profil-header-wrap ~ section h5,
    .profil-header-wrap ~ section h6 { overflow-wrap: anywhere; }

    .visi-misi-section,
    .visi-misi-section ~ section { max-width: 100%; overflow-x: clip; }
    .visi-misi-section > .container,
    .visi-misi-section ~ section > .container {
        width: calc(100% - 32px);
        max-width: none;
        padding-left: 0 !important;
        padding-right: 0 !important;
    }
    .visi-misi-section .vm-timeline,
    .visi-misi-section .vm-step,
    .visi-misi-section .vm-body { min-width: 0; max-width: 100%; }

    .profil-hero-breadcrumb { gap: 7px; margin-bottom: 1rem; font-size: 11px; }
    .profil-pill-badge { max-width: 100%; gap: 6px; padding: 5px 10px; font-size: 9px; letter-spacing: .035em; }
    .profil-hero-title { max-width: 600px; font-size: clamp(1.75rem, 7vw, 2.15rem); line-height: 1.12; }
    .profil-hero-lead { font-size: .9rem; line-height: 1.6; }
    .profil-hero-visual { min-height: 155px; margin-top: .5rem; }
    .profil-logo-orbit { width: 145px; height: 145px; box-shadow: 0 0 0 18px rgba(255,255,255,.025), 0 0 0 36px rgba(255,255,255,.02); }
    .profil-logo-card { width: 132px; padding: 11px; border-radius: 17px; }

    .profil-header-wrap ~ section.py-5 { padding-top: 2.25rem !important; padding-bottom: 2.25rem !important; }
    .profil-header-wrap ~ section > .container.py-2,
    .profil-header-wrap ~ section > .container.py-3 { padding-top: .25rem !important; padding-bottom: .25rem !important; }
    .profil-header-wrap ~ section h2 { font-size: clamp(1.35rem, 5.8vw, 1.7rem) !important; line-height: 1.25; }
    .profil-header-wrap ~ section h3 { font-size: 1.2rem; line-height: 1.3; }
    .profil-header-wrap ~ section .text-center.mx-auto { margin-bottom: 1.5rem !important; }

    .master-identity-card { padding: 1.2rem; border-radius: 18px; }
    .master-identity-card .d-flex.align-items-center.gap-3.mb-4 { gap: .7rem !important; margin-bottom: 1rem !important; }
    .master-identity-card h4 { font-size: 1rem; line-height: 1.3; }
    .master-identity-card p { margin-bottom: 1rem !important; font-size: .86rem !important; line-height: 1.65 !important; }
    .master-identity-card .badge-smm { width: 38px; height: 38px; flex: 0 0 38px; }
    .master-identity-card .row.text-center { --bs-gutter-x: .5rem; }
    .master-identity-card .display-6 { font-size: 1.3rem !important; }
    .master-identity-card .mt-4.pt-3 { margin-top: 1rem !important; padding-top: .85rem !important; }
    .master-identity-card .badge { white-space: normal; line-height: 1.35; }

    .executive-card { min-height: 0; padding: 16px; border-radius: 16px; }
    .executive-avatar { width: 64px; height: 64px; flex: 0 0 64px; border-radius: 14px; }
    .executive-card h5 { font-size: 1rem; }
    .executive-card > div:last-child { gap: 8px; flex-wrap: wrap; }
    .doc-card { border-radius: 16px; }
    .doc-img-preview { height: 210px; padding: 10px; }
    .doc-img-preview img { max-width: 100%; }
    .doc-zoom-badge { right: 9px; bottom: 9px; font-size: 10px; }
    .doc-card .p-4 { padding: 1rem !important; }

    .vm-step { gap: 9px; }
    .vm-track, .vm-node { width: 28px; }
    .vm-node { height: 28px; font-size: 10px; }
    .vm-body { padding: 10px 11px; margin-bottom: 9px; }
    .vm-title { font-size: 12.5px; }
    .vm-desc { font-size: 11.5px; }

    .legalitas-section { padding-top: 1.75rem !important; padding-bottom: 1.75rem !important; }
    .legal-doc-viewer { border-radius: 15px; }
    .legal-doc-viewer-head { padding: 10px 11px; }
    .legal-doc-preview { height: 230px; padding: 10px; }
    .legal-doc-tabs { gap: 6px; padding: 9px; }
    .legal-doc-tab { min-width: 0; padding: 8px 6px; font-size: 10px; line-height: 1.25; }
    .legal-info-card { padding: 12px; border-radius: 12px; }
    .legal-info-head { gap: 8px; }
    .legal-info-title { font-size: 13px; line-height: 1.35; }
    .legal-info-subtitle { font-size: 10px; line-height: 1.4; }
    .legal-info-open { padding: 5px 8px; font-size: 9px; }
    .legal-info-grid { gap: 8px 10px; }
    .legal-info-value { font-size: 10px; }

    .profil-header-wrap ~ section .rounded-4 { border-radius: 16px !important; }
    .profil-header-wrap ~ section .p-4.p-md-5 { padding: 1rem !important; }
    .profil-header-wrap ~ section .d-flex.flex-wrap.gap-2 > .btn-dark-bw,
    .profil-header-wrap ~ section .d-flex.flex-wrap.gap-2 > .btn-outline-bw { justify-content: center; }
    .modal-dialog { margin: .5rem; }
    .modal-content { border-radius: 14px !important; }
    .modal-header, .modal-footer { padding: .75rem !important; }
    .modal-header .modal-title { font-size: 12px !important; }
    .modal-footer { gap: .6rem; flex-wrap: wrap; }
    .modal-footer > .text-start { flex: 1 1 100%; padding-right: 0 !important; }
    .modal-footer > .d-flex { width: 100%; }
    .modal-footer .btn { flex: 1; padding-left: .5rem !important; padding-right: .5rem !important; font-size: 11px; }
}

@media (max-width: 420px) {
    .profil-header-wrap > .container,
    .profil-header-wrap ~ section > .container { padding-left: 14px; padding-right: 14px; }
    .master-identity-card { padding: 1rem; }
    .master-identity-card .text-white-50.small { font-size: 9px !important; }
    .master-identity-card .badge { font-size: 9px !important; padding-left: .55rem !important; padding-right: .55rem !important; }
    .legal-info-grid { grid-template-columns: minmax(0, 1fr); }
    .legal-info-item[style*="grid-column"] { grid-column: auto !important; }
    .doc-img-preview { height: 190px; }
}
</style>

<!-- ============================================================
     1. COMPANY HERO
============================================================ -->
<div class="profil-header-wrap">
    <div class="container">
        <div class="row align-items-center g-4 g-lg-5">
            <div class="col-lg-7 profil-hero-content">
                <div class="profil-hero-breadcrumb">
                    <a href="<?= base_url('index.php') ?>">Beranda</a>
                    <i class="fas fa-chevron-right" style="font-size: 9px;"></i>
                    <span class="text-white fw-semibold">Tentang Kami</span>
                </div>
                <div class="profil-pill-badge">
                    <span>✦</span>
                    <span>TENTANG KAMI &bull; HEAD OFFICE WAY HALIM</span>
                </div>
                <h1 class="profil-hero-title">Mengenal PT Shafa Marwa Multiguna</h1>
                <p class="profil-hero-lead">Kami menghadirkan produk unggulan dan layanan distribusi yang terpercaya, dengan komitmen untuk terus memberi manfaat bagi masyarakat dan mitra di seluruh Indonesia.</p>
            </div>
            <div class="col-lg-5 profil-hero-visual" aria-label="Logo PT Shafa Marwa Multiguna">
                <div class="profil-logo-orbit"></div>
                <div class="profil-logo-card">
                    <img src="<?= asset_url('images/logo-smm.png') ?>" alt="Logo PT Shafa Marwa Multiguna">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================
     2. COMPANY OVERVIEW & CORE VALUES
============================================================ -->
<section class="py-5 bg-white">
    <div class="container py-2">
        <div class="row align-items-center g-5">
            
            <!-- Left: Master Identity Card in Pure Minimalist Black -->
            <div class="col-lg-6">
                <div class="master-identity-card">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="badge-smm">
                            SMM
                        </div>
                        <div>
                            <h4 class="fw-bold mb-0 text-white" style="letter-spacing: -0.02em;">PT Shafa Marwa Multiguna</h4>
                            <small class="text-white-50" style="font-size: 11px; letter-spacing: 0.5px;">EST. WAY HALIM &bull; KOTA BANDAR LAMPUNG</small>
                        </div>
                    </div>
                    
                    <p class="text-white-50 mb-4" style="line-height: 1.8; font-size: 0.95rem;">
                        Perusahaan pemasaran digital, siaran langsung (live streaming commerce) interaktif, dan distribusi produk multiguna terkurasi di Indonesia. Berfokus pada produk unggulan herbal alami, pertanian organik, perikanan, kopi nusantara, serta kebutuhan keluarga.
                    </p>

                    <!-- Real-Time Verified Stats Grid -->
                    <div class="border-top pt-4 mt-4" style="border-color: rgba(255,255,255,0.12) !important;">
                        <div class="row text-center g-3">
                            <div class="col-4">
                                <div class="display-6 fw-bold text-white mb-0" style="letter-spacing: -0.03em; font-size: 1.65rem;"><?= (int)$live_stats['total_produk'] ?>+</div>
                                <div class="text-white-50 small" style="font-size: 11px;">Produk Aktif</div>
                            </div>
                            <div class="col-4">
                                <div class="display-6 fw-bold text-white mb-0" style="letter-spacing: -0.03em; font-size: 1.65rem;"><?= (int)$live_stats['total_kategori'] ?></div>
                                <div class="text-white-50 small" style="font-size: 11px;">Lini Kategori</div>
                            </div>
                            <div class="col-4">
                                <div class="display-6 fw-bold text-white mb-0" style="letter-spacing: -0.03em; font-size: 1.65rem;"><?= e($live_stats['tingkat_kepuasan']) ?></div>
                                <div class="text-white-50 small" style="font-size: 11px;">Layanan Sukses</div>
                            </div>
                        </div>
                    </div>

                    <!-- Verified Legal Badges Strip -->
                    <div class="mt-4 pt-3 border-top d-flex flex-wrap gap-2 align-items-center" style="border-color: rgba(255,255,255,0.1) !important;">
                        <span class="badge bg-white text-dark rounded-pill px-3 py-1 fw-semibold" style="font-size: 11px;">
                            <i class="fas fa-check-circle text-success me-1"></i> NIB: 0220009660744
                        </span>
                        <span class="badge bg-white text-dark rounded-pill px-3 py-1 fw-semibold" style="font-size: 11px;">
                            <i class="fas fa-check-circle text-success me-1"></i> SK Kemenkumham RI Sah
                        </span>
                        <span class="badge bg-white text-dark rounded-pill px-3 py-1 fw-semibold" style="font-size: 11px;">
                            <i class="fas fa-check-circle text-success me-1"></i> PMDN Resmi
                        </span>
                    </div>
                </div>
            </div>

            <!-- Right: Narasi Dedikasi Perusahaan -->
            <div class="col-lg-6">
                <span class="badge-soft mb-3">KOMITMEN KORPORASI</span>
                <h2 class="fw-bold text-dark mb-3" style="letter-spacing: -0.03em; font-size: 2rem; line-height: 1.25;">
                    Membangun Rantai Distribusi Terpercaya &amp; Bernilai Nyata
                </h2>
                <p class="text-muted mb-3" style="line-height: 1.75; font-size: 0.95rem;">
                    <strong>PT Shafa Marwa Multiguna (PT SMM)</strong> didirikan di Way Halim, Kota Bandar Lampung, sebagai wujud penggabungan pemasaran interaktif dengan rantai pasok komoditas berkualitas tinggi.
                </p>
                <p class="text-muted mb-4" style="line-height: 1.75; font-size: 0.95rem;">
                    Kami mengedepankan transparansi, kejujuran edukasi produk, serta pembagian nilai manfaat yang adil bagi seluruh konsumen dan mitra usaha di seluruh pelosok Indonesia.
                </p>
                
                <div class="d-flex flex-column gap-3 mb-2">
                    <div class="d-flex align-items-start gap-3">
                        <div class="icon-circle-bw mt-1">
                            <i class="fas fa-check"></i>
                        </div>
                        <div>
                            <strong class="text-dark small d-block">Legalitas Sah Republik Indonesia</strong>
                            <span class="text-muted small">Memiliki NIB berbasis risiko dan SK pengesahan Kemenkumham RI resmi.</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-start gap-3">
                        <div class="icon-circle-bw mt-1">
                            <i class="fas fa-check"></i>
                        </div>
                        <div>
                            <strong class="text-dark small d-block">Pembinaan Mitra Usaha (AKSI)</strong>
                            <span class="text-muted small">Mendukung kemandirian wirausaha lewat Akademi Sukses Indonesia dan apresiasi reward nyata.</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-start gap-3">
                        <div class="icon-circle-bw mt-1">
                            <i class="fas fa-check"></i>
                        </div>
                        <div>
                            <strong class="text-dark small d-block">Infrastruktur Penjualan Modern</strong>
                            <span class="text-muted small">Studio penyiaran aktif, logistik Biteship terintegrasi, dan pembayaran otomatis Midtrans.</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ============================================================
     3. DEWAN PIMPINAN PERSEROAN
============================================================ -->
<section class="py-5 bg-light border-top border-bottom border-light-subtle">
    <div class="container py-3">
        <div class="text-center mx-auto mb-5" style="max-width: 640px;">
            <span class="badge-soft mb-2">STRUKTUR KEPEMIMPINAN</span>
            <h2 class="fw-bold text-dark" style="letter-spacing: -0.02em;">Dewan Direksi &amp; Pimpinan PT SMM</h2>
            <p class="text-muted small">Memandu arah strategis, integritas tata kelola, dan akselerasi kemitraan di kantor operasional Way Halim.</p>
        </div>

        <div class="row g-4 align-items-stretch">
            
            <!-- Direktur Utama -->
            <div class="col-md-6 col-lg-4">
                <div class="executive-card">
                    <div>
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <img src="<?= asset_url('images/tentang-kami/yusuf_efendi.jpg') ?>" 
                                 alt="Yusuf Efendi - Direktur Utama" 
                                 class="executive-avatar"
                                 onclick="openDocModal('<?= asset_url('images/tentang-kami/yusuf_efendi.jpg') ?>', 'Yusuf Efendi &mdash; Direktur Utama PT SMM', 'Dokumentasi resmi potret Direktur Utama PT Shafa Marwa Multiguna yang terpasang di Head Office Way Halim, Bandar Lampung.')">
                            <div>
                                <span class="badge bg-dark text-white rounded-pill px-2 py-1 mb-1" style="font-size: 10px;">DIREKTUR UTAMA</span>
                                <h5 class="fw-bold text-dark mb-0" style="letter-spacing: -0.02em;">Yusuf Efendi</h5>
                                <div class="text-muted small" style="font-size: 12px;">PT Shafa Marwa Multiguna</div>
                            </div>
                        </div>
                        <p class="text-muted small mb-0" style="line-height: 1.7;">
                            Memimpin strategi korporasi, pengembangan ekosistem live streaming commerce, kemitraan distribusi, dan inovasi lini komoditas multiguna.
                        </p>
                    </div>
                    <div class="pt-3 border-top border-light-subtle d-flex justify-content-between align-items-center mt-3">
                        <span class="text-muted small" style="font-size: 11px;">
                            <i class="fab fa-instagram me-1 text-dark"></i> @PakdirutSMM
                        </span>
                        <button class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1" style="font-size: 11px;"
                                onclick="openDocModal('<?= asset_url('images/tentang-kami/yusuf_efendi.jpg') ?>', 'Yusuf Efendi &mdash; Direktur Utama PT SMM', 'Dokumentasi resmi potret Direktur Utama PT Shafa Marwa Multiguna.')">
                            Lihat Foto <i class="fas fa-search-plus ms-1"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Komisaris Utama -->
            <div class="col-md-6 col-lg-4">
                <div class="executive-card">
                    <div>
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <img src="<?= asset_url('images/tentang-kami/puji_sartono.jpg') ?>" 
                                 alt="H. Puji Sartono - Komisaris Utama" 
                                 class="executive-avatar"
                                 onclick="openDocModal('<?= asset_url('images/tentang-kami/puji_sartono.jpg') ?>', 'H. Puji Sartono &mdash; Komisaris Utama PT SMM', 'Dokumentasi resmi potret Komisaris Utama PT Shafa Marwa Multiguna yang terpasang di Head Office Way Halim, Bandar Lampung.')">
                            <div>
                                <span class="badge bg-secondary text-white rounded-pill px-2 py-1 mb-1" style="font-size: 10px;">KOMISARIS UTAMA</span>
                                <h5 class="fw-bold text-dark mb-0" style="letter-spacing: -0.02em;">H. Puji Sartono</h5>
                                <div class="text-muted small" style="font-size: 12px;">PT Shafa Marwa Multiguna</div>
                            </div>
                        </div>
                        <p class="text-muted small mb-0" style="line-height: 1.7;">
                            Mengemban fungsi pengawasan tata kelola perseroan, kepatuhan regulasi hukum, serta mengawal implementasi nilai kemanfaatan sosial bagi masyarakat.
                        </p>
                    </div>
                    <div class="pt-3 border-top border-light-subtle d-flex justify-content-between align-items-center mt-3">
                        <span class="text-muted small" style="font-size: 11px;">
                            <i class="fab fa-instagram me-1 text-dark"></i> @Smmofficialindonesia
                        </span>
                        <button class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1" style="font-size: 11px;"
                                onclick="openDocModal('<?= asset_url('images/tentang-kami/puji_sartono.jpg') ?>', 'H. Puji Sartono &mdash; Komisaris Utama PT SMM', 'Dokumentasi resmi potret Komisaris Utama PT Shafa Marwa Multiguna.')">
                            Lihat Foto <i class="fas fa-search-plus ms-1"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Dokumentasi Wall Pimpinan Kantor -->
            <div class="col-lg-4">
                <div class="doc-card d-flex flex-column justify-content-between">
                    <div>
                        <div class="doc-img-preview"
                             onclick="openDocModal('<?= asset_url('images/tentang-kami/img_7341.jpg') ?>', 'Galeri Dinding Pimpinan PT SMM', 'Dokumentasi otentik plakat potret pimpinan Direktur Utama dan Komisaris Utama yang terpajang di Head Office PT SMM Way Halim, Bandar Lampung.')">
                            <img src="<?= asset_url('images/tentang-kami/img_7341.jpg') ?>" alt="Potret Pimpinan PT SMM di Kantor Pusat" style="max-height: 180px; object-fit: cover;">
                            <span class="doc-zoom-badge"><i class="fas fa-search-plus me-1"></i> Perbesar</span>
                        </div>
                        <div class="p-3">
                            <span class="badge-soft mb-2">HEAD OFFICE WAY HALIM</span>
                            <h6 class="fw-bold text-dark mb-1" style="font-size: 14px;">Galeri Dinding Pimpinan</h6>
                            <p class="text-muted small mb-0" style="line-height: 1.6; font-size: 12px;">
                                Potret pimpinan terpampang resmi di ruang utama kantor pusat sebagai simbol transparansi dan integritas kepengurusan perseroan.
                            </p>
                        </div>
                    </div>
                    <div class="p-3 pt-0">
                        <button class="btn btn-outline-dark btn-sm rounded-pill w-100"
                                onclick="openDocModal('<?= asset_url('images/tentang-kami/img_7341.jpg') ?>', 'Galeri Dinding Pimpinan PT SMM', 'Dokumentasi otentik plakat potret pimpinan Direktur Utama dan Komisaris Utama di Head Office PT SMM.')">
                            <i class="fas fa-expand me-1"></i> Buka Foto Asli
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ============================================================
     4. VISI & MISI RESMI (SESUAI PLAKAT OTENTIK KANTOR PUSAT)
============================================================ -->
<section class="py-5 bg-white visi-misi-section">
    <div class="container py-3">
        <div class="row align-items-center g-5">
            
            <!-- Left: Text Visi & Misi Sesuai Piagam Asli -->
            <div class="col-lg-7">
                <span class="badge-soft mb-2">FALSAFAH &amp; MOTTO PERUSAHAAN</span>
                <h2 class="fw-bold text-dark mb-3" style="letter-spacing: -0.03em; font-size: 2rem;">
                    Visi &amp; Misi PT Shafa Marwa Multiguna
                </h2>
                <p class="text-muted mb-4" style="line-height: 1.75; font-size: 0.95rem;">
                    Nilai-nilai fundamental yang dirumuskan secara otentik dan terpampang di kantor pusat kami, menjadi pedoman kerja bagi seluruh staf dan mitra:
                </p>

                <!-- Visi & Misi Timeline Track (Garis Timeline Sesuai Piagam Asli) -->
                <div class="vm-timeline">
                    <!-- Section Visi Header -->
                    <div class="vm-timeline-cat">
                        <span class="badge-soft"><i class="fas fa-eye me-1"></i> VISI PERUSAHAAN</span>
                    </div>

                    <!-- Step 1: Visi 1 -->
                    <div class="vm-step">
                        <div class="vm-track">
                            <div class="vm-node">01</div>
                            <div class="vm-line"></div>
                        </div>
                        <div class="vm-body">
                            <h6 class="vm-title">Bermanfaat untuk Semua Kalangan</h6>
                            <p class="vm-desc">Menjadi perusahaan yang senantiasa menebar manfaat nyata dan positif untuk semua kalangan masyarakat.</p>
                        </div>
                    </div>

                    <!-- Step 2: Visi 2 -->
                    <div class="vm-step">
                        <div class="vm-track">
                            <div class="vm-node">02</div>
                            <div class="vm-line"></div>
                        </div>
                        <div class="vm-body">
                            <h6 class="vm-title">Pelopor Penjualan Langsung Transparan</h6>
                            <p class="vm-desc">Menjadi pelopor perusahaan penjualan langsung dengan sistem yang transparan, adil, amanah, dan menguntungkan.</p>
                        </div>
                    </div>

                    <!-- Section Misi Header -->
                    <div class="vm-timeline-cat mt-2">
                        <span class="badge-soft"><i class="fas fa-bullseye me-1"></i> MISI PERUSAHAAN</span>
                    </div>

                    <!-- Step 3: Misi 1 -->
                    <div class="vm-step">
                        <div class="vm-track">
                            <div class="vm-node">01</div>
                            <div class="vm-line"></div>
                        </div>
                        <div class="vm-body">
                            <h6 class="vm-title">Kesehatan Masyarakat</h6>
                            <p class="vm-desc">Meningkatkan kesehatan masyarakat melalui pembudayaan gaya hidup bersih, sehat, dan harmonis.</p>
                        </div>
                    </div>

                    <!-- Step 4: Misi 2 -->
                    <div class="vm-step">
                        <div class="vm-track">
                            <div class="vm-node">02</div>
                            <div class="vm-line"></div>
                        </div>
                        <div class="vm-body">
                            <h6 class="vm-title">Inovasi Berkelanjutan</h6>
                            <p class="vm-desc">Selalu berinovasi secara konsisten untuk menghadirkan produk dan layanan komoditas bermutu tinggi.</p>
                        </div>
                    </div>

                    <!-- Step 5: Misi 3 -->
                    <div class="vm-step">
                        <div class="vm-track">
                            <div class="vm-node">03</div>
                        </div>
                        <div class="vm-body mb-0">
                            <h6 class="vm-title">Kualitas SDM &amp; Dukungan AKSI</h6>
                            <p class="vm-desc">Menyediakan support system Akademi Sukses Indonesia (AKSI) demi mewujudkan kemandirian dan kesejahteraan bersama.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Plakat Fisik Asli di Head Office -->
            <div class="col-lg-5">
                <div class="doc-card">
                    <div class="doc-img-preview"
                         onclick="openDocModal('<?= asset_url('images/tentang-kami/img_7340.jpg') ?>', 'Piagam Visi & Misi Resmi PT. SMM Shafa Marwa Multiguna', 'Dokumentasi plakat fisik resmi Visi dan Misi PT SMM Shafa Marwa Multiguna yang terpasang di Head Office Jalan Urip Sumoharjo, Way Halim, Bandar Lampung.')">
                        <img src="<?= asset_url('images/tentang-kami/img_7340.jpg') ?>" alt="Plakat Visi dan Misi PT SMM di Kantor Pusat" style="max-height: 320px; object-fit: contain;">
                        <span class="doc-zoom-badge"><i class="fas fa-search-plus me-1"></i> Perbesar</span>
                    </div>

                    <div class="p-4">
                        <span class="badge-soft mb-2"><i class="fas fa-certificate text-dark me-1"></i> PIAGAM RESMI KANTOR PUSAT</span>
                        <h6 class="fw-bold text-dark mb-1">Piagam Visi &amp; Misi Fisik di Head Office</h6>
                        <p class="text-muted small mb-3" style="line-height: 1.6; font-size: 12px;">
                            Foto otentik deklarasi Visi dan Misi yang terpasang di dinding Head Office PT SMM Way Halim, Bandar Lampung.
                        </p>

                        <button class="btn btn-dark w-100 rounded-pill btn-sm py-2 d-inline-flex align-items-center justify-content-center gap-2"
                                onclick="openDocModal('<?= asset_url('images/tentang-kami/img_7340.jpg') ?>', 'Piagam Visi & Misi Resmi PT. SMM Shafa Marwa Multiguna', 'Dokumentasi plakat fisik resmi Visi dan Misi PT SMM Shafa Marwa Multiguna di Head Office Way Halim, Bandar Lampung.')">
                            <i class="fas fa-expand"></i>
                            <span>Inspeksi Piagam Resolusi Tinggi</span>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ============================================================
     5. LEGALITAS & PERIZINAN RESMI (NIB BKPM & SK KEMENKUMHAM)
============================================================ -->
<section class="legalitas-section py-5 bg-light border-top border-bottom border-light-subtle">
    <div class="container py-3">
        <div class="row legalitas-layout align-items-center">
            <div class="col-lg-5">
                <div class="legal-doc-viewer">
                    <div class="legal-doc-viewer-head">
                        <span class="badge-soft mb-0"><i class="fas fa-landmark me-1"></i> DOKUMEN RESMI</span>
                        <span id="legalDocStatus" class="badge bg-success-subtle text-success border border-success-subtle rounded-pill" style="font-size:10px;">Terverifikasi OSS</span>
                    </div>
                    <div class="legal-doc-preview" onclick="showLegalDocument(activeLegalDocument, true)" role="button" aria-label="Perbesar dokumen legalitas">
                        <img id="legalDocImage" src="<?= asset_url('images/tentang-kami/img_7338.jpg') ?>" alt="Sertifikat Nomor Induk Berusaha PT SMM">
                    </div>
                    <div class="legal-doc-tabs" role="tablist" aria-label="Pilih dokumen legalitas">
                        <button type="button" class="legal-doc-tab active" data-legal-doc="nib" onclick="showLegalDocument('nib')">NIB BKPM</button>
                        <button type="button" class="legal-doc-tab" data-legal-doc="ahu" onclick="showLegalDocument('ahu')">SK Kemenkumham</button>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="mb-3">
                    <span class="badge-soft mb-2">KEPATUHAN REGULASI &amp; HUKUM</span>
                    <h2 class="fw-bold text-dark mb-2" style="letter-spacing: -0.025em;">Legalitas Perusahaan yang Terverifikasi</h2>
                    <p class="text-muted small mb-0" style="line-height:1.7;">PT Shafa Marwa Multiguna tercatat resmi pada OSS BKPM dan Kementerian Hukum dan HAM Republik Indonesia.</p>
                </div>

                <div class="d-flex flex-column gap-2">
                    <article class="legal-info-card">
                        <div class="legal-info-head">
                            <div>
                                <h3 class="legal-info-title">Nomor Induk Berusaha (NIB)</h3>
                                <p class="legal-info-subtitle">Sertifikat Perizinan Berusaha Berbasis Risiko</p>
                            </div>
                            <button type="button" class="legal-info-open" onclick="showLegalDocument('nib', true)"><i class="fas fa-arrow-up-right-from-square me-1"></i> Lihat</button>
                        </div>
                        <div class="legal-info-grid">
                            <div class="legal-info-item"><span class="legal-info-label">Nomor NIB</span><span class="legal-info-value font-monospace">0220009660744</span></div>
                            <div class="legal-info-item"><span class="legal-info-label">Diterbitkan</span><span class="legal-info-value">4 Juni 2020</span></div>
                            <div class="legal-info-item"><span class="legal-info-label">Pelaku Usaha</span><span class="legal-info-value">PT SMM Shafa Marwa Multiguna</span></div>
                            <div class="legal-info-item"><span class="legal-info-label">Status Modal</span><span class="legal-info-value">PMDN (Dalam Negeri)</span></div>
                            <div class="legal-info-item" style="grid-column:1/-1;"><span class="legal-info-label">Alamat</span><span class="legal-info-value">Jl. Urip Sumoharjo No. 159 B, Way Halim Permai, Bandar Lampung</span></div>
                        </div>
                    </article>

                    <article class="legal-info-card">
                        <div class="legal-info-head">
                            <div>
                                <h3 class="legal-info-title">Keputusan Kemenkumham RI</h3>
                                <p class="legal-info-subtitle">Pengesahan Anggaran Dasar Perseroan</p>
                            </div>
                            <button type="button" class="legal-info-open" onclick="showLegalDocument('ahu', true)"><i class="fas fa-arrow-up-right-from-square me-1"></i> Lihat</button>
                        </div>
                        <div class="legal-info-grid">
                            <div class="legal-info-item"><span class="legal-info-label">Nomor Keputusan</span><span class="legal-info-value font-monospace">AHU-0057503.AH.01.02.TAHUN 2021</span></div>
                            <div class="legal-info-item"><span class="legal-info-label">Tanggal</span><span class="legal-info-value">18 Oktober 2021</span></div>
                            <div class="legal-info-item"><span class="legal-info-label">NPWP</span><span class="legal-info-value font-monospace">94.910.426.9-323.000</span></div>
                            <div class="legal-info-item"><span class="legal-info-label">Notaris</span><span class="legal-info-value">Budi Kristiyanto, S.H. (Akta No. 09)</span></div>
                            <div class="legal-info-item" style="grid-column:1/-1;"><span class="legal-info-label">Daftar Perseroan</span><span class="legal-info-value font-monospace">AHU-0180293.AH.01.11.2021</span></div>
                        </div>
                    </article>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
const legalDocuments = {
    nib: {
        src: <?= json_encode(asset_url('images/tentang-kami/img_7338.jpg')) ?>,
        alt: 'Sertifikat Nomor Induk Berusaha PT SMM',
        status: 'Terverifikasi OSS',
        title: 'Nomor Induk Berusaha (NIB) PT SMM Shafa Marwa Multiguna',
        description: 'Dokumen resmi Perizinan Berusaha Berbasis Risiko NIB: 0220009660744 yang diterbitkan oleh Kementerian Investasi / Kepala BKPM Republik Indonesia.'
    },
    ahu: {
        src: <?= json_encode(asset_url('images/tentang-kami/img_7339.jpg')) ?>,
        alt: 'Surat Keputusan Kemenkumham RI PT SMM',
        status: 'SK AHU Sah',
        title: 'SK Pengesahan Kemenkumham RI PT SMM',
        description: 'Surat Keputusan Menteri Hukum dan Hak Asasi Manusia Republik Indonesia Nomor AHU-0057503.AH.01.02.TAHUN 2021 tentang Persetujuan Perubahan Anggaran Dasar PT SMM Shafa Marwa Multiguna.'
    }
};
let activeLegalDocument = 'nib';

function showLegalDocument(key, openModal = false) {
    const documentInfo = legalDocuments[key];
    if (!documentInfo) return;

    activeLegalDocument = key;
    const image = document.getElementById('legalDocImage');
    image.src = documentInfo.src;
    image.alt = documentInfo.alt;
    document.getElementById('legalDocStatus').textContent = documentInfo.status;
    document.querySelectorAll('.legal-doc-tab').forEach(tab => {
        const isActive = tab.dataset.legalDoc === key;
        tab.classList.toggle('active', isActive);
        tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
    });

    if (openModal) openDocModal(documentInfo.src, documentInfo.title, documentInfo.description);
}
</script>

<!-- ============================================================
     6. JEJAK REKAM & WALL OF FAME REWARD MITRA
============================================================ -->
<section class="py-5 bg-white">
    <div class="container py-3">
        <div class="row align-items-center g-5">
            
            <!-- Left: Wall of Fame Real Photo Card -->
            <div class="col-lg-5 order-2 order-lg-1">
                <div class="doc-card">
                    <div class="p-3 border-bottom border-light-subtle d-flex justify-content-between align-items-center bg-white">
                        <span class="badge-soft"><i class="fas fa-trophy text-dark me-1"></i> WALL OF FAME</span>
                        <small class="text-muted" style="font-size: 11px;">Head Office SMM</small>
                    </div>

                    <div class="doc-img-preview p-0"
                         onclick="openDocModal('<?= asset_url('images/tentang-kami/img_7342.jpg') ?>', 'Wall of Fame Reward Mitra Usaha PT SMM', 'Dokumentasi otentik deretan plakat para mitra dan reseller peraih Reward 1 Unit Sepeda Motor Cash di Head Office PT SMM Way Halim, Bandar Lampung.')">
                        <img src="<?= asset_url('images/tentang-kami/img_7342.jpg') ?>" alt="Wall of Fame Reward Mitra PT SMM" style="max-height: 380px; width: 100%; object-fit: cover; border-radius: 0;">
                        <span class="doc-zoom-badge"><i class="fas fa-search-plus me-1"></i> Perbesar Wall of Fame</span>
                    </div>

                    <div class="p-4 d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="fw-bold text-dark mb-0">Dinding Penghargaan Mitra Usaha</h6>
                            <small class="text-muted" style="font-size: 11px;">Penerima Reward 1 Unit Sepeda Motor Cash</small>
                        </div>
                        <button class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1" style="font-size: 11px;"
                                onclick="openDocModal('<?= asset_url('images/tentang-kami/img_7342.jpg') ?>', 'Wall of Fame Reward Mitra Usaha PT SMM', 'Dokumentasi otentik deretan plakat peraih Reward Motor Cash di Head Office PT SMM Way Halim.')">
                            <i class="fas fa-expand me-1"></i> Lihat Detail
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right: Penjelasan Program Kemitraan -->
            <div class="col-lg-7 order-1 order-lg-2">
                <span class="badge-soft mb-2">PEMBERDAYAAN MITRA USAHA</span>
                <h2 class="fw-bold text-dark mb-3" style="letter-spacing: -0.03em; font-size: 2rem; line-height: 1.25;">
                    Membuka Peluang Usaha Nyata &amp; Apresiasi Prestasi Berkelanjutan
                </h2>
                <p class="text-muted mb-4" style="line-height: 1.75; font-size: 0.95rem;">
                    Sebagai pelopor sistem pemasaran langsung berbasis interaksi nyata, PT SMM tidak hanya mendistribusikan produk berkualitas, melainkan aktif membina wirausahawan mandiri di berbagai daerah melalui <strong>Akademi Sukses Indonesia (AKSI)</strong>.
                </p>

                <div class="d-flex flex-column gap-3 mb-4">
                    <div class="p-3 bg-light rounded-3 border border-light-subtle d-flex align-items-start gap-3">
                        <div class="icon-circle-bw mt-1">
                            <i class="fas fa-motorcycle"></i>
                        </div>
                        <div>
                            <strong class="text-dark small d-block">Reward Nyata Tanpa Diundi</strong>
                            <span class="text-muted small">Sebagaimana terpampang di Wall of Fame kantor pusat kami, para mitra yang berprestasi menerima apresiasi langsung berupa 1 Unit Motor Cash dan bonus pembinaan.</span>
                        </div>
                    </div>

                    <div class="p-3 bg-light rounded-3 border border-light-subtle d-flex align-items-start gap-3">
                        <div class="icon-circle-bw mt-1">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div>
                            <strong class="text-dark small d-block">Support System Akademi Sukses Indonesia (AKSI)</strong>
                            <span class="text-muted small">Pelatihan teknik public speaking siaran langsung (live selling), manajemen inventori, dan pemahaman khasiat produk terstandar bagi seluruh mitra.</span>
                        </div>
                    </div>

                    <div class="p-3 bg-light rounded-3 border border-light-subtle d-flex align-items-start gap-3">
                        <div class="icon-circle-bw mt-1">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <div>
                            <strong class="text-dark small d-block">Sistem Bagi Hasil Adil &amp; Transparan</strong>
                            <span class="text-muted small">Skema insentif yang dapat dipantau jelas tanpa rekayasa, selaras dengan motto kami: <em>Semangat Menebar Manfaat</em>.</span>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <a href="<?= base_url('frontend/pages/kontak.php') ?>" class="btn-dark-bw">
                        <span>Konsultasi Peluang Kemitraan</span>
                        <i class="fas fa-arrow-right" style="font-size: 11px;"></i>
                    </a>
                    <a href="<?= base_url('frontend/pages/katalog.php') ?>" class="btn-outline-bw">
                        <span>Jelajahi Produk Kami</span>
                    </a>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ============================================================
     7. 5 LINI PRODUK UNGGULAN KORPORASI
============================================================ -->
<section class="py-5 bg-light border-top border-bottom border-light-subtle">
    <div class="container py-3">
        <div class="text-center mx-auto mb-5" style="max-width: 640px;">
            <span class="badge-soft mb-2">KATALOG &amp; KOMODITAS</span>
            <h2 class="fw-bold text-dark" style="letter-spacing: -0.02em;">5 Lini Produk Unggulan PT SMM</h2>
            <p class="text-muted small">Rangkaian komoditas berkualitas tinggi yang siap dipasarkan dengan rantai pasok terintegrasi dari Way Halim ke seluruh nusantara.</p>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="p-4 bg-white border border-light-subtle rounded-4 h-100 shadow-xs">
                    <div class="d-inline-flex align-items-center justify-content-center bg-light border border-light-subtle text-dark rounded-circle mb-3" style="width: 44px; height: 44px; font-size: 1.1rem;">
                        <i class="fas fa-seedling"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Pupuk Organik Pertanian</h5>
                    <p class="text-muted small mb-0" style="line-height: 1.7;">Solusi nutrisi tanah dan tanaman untuk meningkatkan produktivitas panen petani secara berkelanjutan tanpa merusak struktur hara tanah.</p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="p-4 bg-white border border-light-subtle rounded-4 h-100 shadow-xs">
                    <div class="d-inline-flex align-items-center justify-content-center bg-light border border-light-subtle text-dark rounded-circle mb-3" style="width: 44px; height: 44px; font-size: 1.1rem;">
                        <i class="fas fa-fish"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Produk Perikanan</h5>
                    <p class="text-muted small mb-0" style="line-height: 1.7;">Pakan apung protein tinggi dan probiotik kolam untuk mengoptimalkan kesehatan dan laju pertumbuhan budidaya perikanan.</p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="p-4 bg-white border border-light-subtle rounded-4 h-100 shadow-xs">
                    <div class="d-inline-flex align-items-center justify-content-center bg-light border border-light-subtle text-dark rounded-circle mb-3" style="width: 44px; height: 44px; font-size: 1.1rem;">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Produk Herbal Alami</h5>
                    <p class="text-muted small mb-0" style="line-height: 1.7;">Suplemen murni seperti madu hutan, habbatussauda, dan propolis yang diracik higienis untuk menjaga kebugaran keluarga.</p>
                </div>
            </div>

            <div class="col-md-6">
                <div class="p-4 bg-white border border-light-subtle rounded-4 h-100 shadow-xs">
                    <div class="d-inline-flex align-items-center justify-content-center bg-light border border-light-subtle text-dark rounded-circle mb-3" style="width: 44px; height: 44px; font-size: 1.1rem;">
                        <i class="fas fa-coffee"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Produk Kopi Nusantara</h5>
                    <p class="text-muted small mb-0" style="line-height: 1.7;">Kopi Robusta Lampung pilihan dengan cita rasa khas dan varian kopi herbal stamina untuk menemani produktivitas kerja harian.</p>
                </div>
            </div>

            <div class="col-md-6">
                <div class="p-4 bg-white border border-light-subtle rounded-4 h-100 shadow-xs">
                    <div class="d-inline-flex align-items-center justify-content-center bg-light border border-light-subtle text-dark rounded-circle mb-3" style="width: 44px; height: 44px; font-size: 1.1rem;">
                        <i class="fas fa-child"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Nutrisi &amp; Vitamin Anak</h5>
                    <p class="text-muted small mb-0" style="line-height: 1.7;">Suplemen temulawak, multivitamin, dan minyak ikan formula khusus untuk mendukung nafsu makan serta perkembangan anak.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     8. KANTOR OPERASIONAL & KEMITRAAN BANNER
============================================================ -->
<section class="py-5 bg-white">
    <div class="container py-3">
        <div class="p-4 p-md-5 bg-white border border-light-subtle rounded-4 shadow-xs">
            <div class="row g-5 align-items-center">
                
                <div class="col-lg-6">
                    <span class="badge-soft mb-2">KONTAK RESMI PERSEROAN</span>
                    <h3 class="fw-bold text-dark mb-3" style="letter-spacing: -0.02em;">Kantor Pusat &amp; Layanan Pelanggan</h3>
                    <p class="text-muted mb-4" style="line-height: 1.7;">
                        Silakan berkunjung langsung atau menghubungi kanal komunikasi resmi PT Shafa Marwa Multiguna untuk informasi produk dan kemitraan.
                    </p>
                    <div class="d-flex flex-column gap-3">
                        <div class="d-flex align-items-start gap-3">
                            <div class="d-flex align-items-center justify-content-center bg-light border border-light-subtle text-dark rounded-circle flex-shrink-0" style="width: 38px; height: 38px;">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <strong class="text-dark small d-block">Alamat Kantor &amp; Studio Live:</strong>
                                <span class="text-muted small">Jalan Urip Sumoharjo Nomor 159 B, Kel. Way Halim Permai, Kec. Way Halim, Kota Bandar Lampung, Lampung 35122</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-start gap-3">
                            <div class="d-flex align-items-center justify-content-center bg-light border border-light-subtle text-dark rounded-circle flex-shrink-0" style="width: 38px; height: 38px;">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div>
                                <strong class="text-dark small d-block">Email Resmi Perusahaan:</strong>
                                <span class="text-muted small"><?= e($company['email']) ?></span>
                            </div>
                        </div>
                        <div class="d-flex align-items-start gap-3">
                            <div class="d-flex align-items-center justify-content-center bg-light border border-light-subtle text-dark rounded-circle flex-shrink-0" style="width: 38px; height: 38px;">
                                <i class="fas fa-phone-alt"></i>
                            </div>
                            <div>
                                <strong class="text-dark small d-block">Telepon / WhatsApp Layanan:</strong>
                                <span class="text-muted small"><?= e($company['no_telepon']) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="p-4 p-md-5 rounded-4 text-center text-white" style="background-color: var(--bw-dark) !important; box-shadow: 0 12px 36px rgba(0,0,0,0.12);">
                        <span class="badge bg-white text-dark rounded-pill px-3 py-1 mb-3 fw-semibold" style="font-size: 11px;">
                            EKOSISTEM KEMITRAAN NASIONAL
                        </span>
                        <h4 class="fw-bold text-white mb-2" style="letter-spacing: -0.02em;">Ingin Bergabung Sebagai Mitra Resmi?</h4>
                        <p class="text-white-50 small mb-4" style="line-height: 1.7;">
                            Dapatkan akses produk bermutu, pelatihan siaran langsung interaktif melalui AKSI, serta peluang memperoleh reward prestasi nyata dari PT SMM.
                        </p>
                        <a href="<?= base_url('frontend/pages/kontak.php') ?>" class="btn btn-light rounded-pill px-4 py-2 fs-6 text-dark fw-semibold d-inline-flex align-items-center gap-2">
                            <span>Hubungi Kemitraan Sekarang</span>
                            <i class="fas fa-arrow-right" style="transform: rotate(-45deg); font-size: 13px;"></i>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     9. LIGHTBOX MODAL INSPEKSI DOKUMEN (CLEAN B&W)
============================================================ -->
<div class="modal fade" id="docViewerModal" tabindex="-1" aria-labelledby="docViewerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden; background: #ffffff;">
            <div class="modal-header border-bottom border-light-subtle px-4 py-3" style="background: #fafafa;">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-dark text-white rounded-pill px-2 py-1" style="font-size: 10px;">DOKUMEN RESMI</span>
                    <h6 class="modal-title fw-bold text-dark mb-0" id="docViewerModalLabel" style="font-size: 15px;">Inspeksi Dokumen</h6>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-3 p-md-4 text-center" style="background: #0f0f10;">
                <img id="modalPreviewImg" src="" alt="Dokumen Resmi" class="img-fluid rounded-3 shadow" style="max-height: 72vh; width: auto; object-fit: contain;">
            </div>
            <div class="modal-footer border-top border-light-subtle px-4 py-3 d-flex justify-content-between align-items-center" style="background: #fafafa;">
                <div class="text-start pe-3">
                    <div id="modalPreviewCaption" class="fw-semibold text-dark small mb-0" style="font-size: 13px;">Keterangan Dokumen</div>
                    <small id="modalPreviewSub" class="text-muted" style="font-size: 11px;">PT Shafa Marwa Multiguna &bull; Way Halim, Bandar Lampung</small>
                </div>
                <div class="d-flex gap-2">
                    <a id="modalDownloadBtn" href="" target="_blank" class="btn btn-outline-dark btn-sm rounded-pill px-3">
                        <i class="fas fa-external-link-alt me-1"></i> Buka Gambar Penuh
                    </a>
                    <button type="button" class="btn btn-dark btn-sm rounded-pill px-3" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function openDocModal(imageSrc, title, description) {
    const modalEl = document.getElementById('docViewerModal');
    if (!modalEl) return;
    
    document.getElementById('modalPreviewImg').src = imageSrc;
    document.getElementById('docViewerModalLabel').innerHTML = title;
    document.getElementById('modalPreviewCaption').innerHTML = title;
    document.getElementById('modalPreviewSub').innerHTML = description;
    document.getElementById('modalDownloadBtn').href = imageSrc;
    
    const bsModal = new bootstrap.Modal(modalEl);
    bsModal.show();
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
