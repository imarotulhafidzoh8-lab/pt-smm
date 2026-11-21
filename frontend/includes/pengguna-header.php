<?php
// ============================================================
// FILE    : frontend/includes/pengguna-header.php
// FUNGSI  : Layout Header Dashboard Pengguna
// PROYEK  : Sistem Informasi PT Shafa Marwa Multiguna
// ============================================================
require_once __DIR__ . '/../../config/app.php';
cek_pengguna();

$page_title_text = isset($page_title) ? $page_title . ' - Dashboard Pengguna' : 'Dashboard Pengguna - ' . APP_NAME;
$nama_user  = $_SESSION['nama_lengkap'] ?? 'Pengguna';
$email_user = $_SESSION['email'] ?? '';
$company    = get_company_profile($pdo);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title_text) ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link rel="stylesheet" href="<?= asset_url('css/style.css') ?>?v=<?= filemtime(__DIR__ . '/../../assets/css/style.css') ?>">
    <link rel="stylesheet" href="<?= asset_url('css/responsive.css') ?>">

    <style>
    /* ===== NAVBAR PENGGUNA ===== */
    .pnav {
        position: sticky; top: 0; z-index: 1030;
        background: #fff;
        border-bottom: 1px solid #eef2f6;
        box-shadow: 0 1px 4px rgba(0,0,0,0.05);
    }
    .pnav-inner {
        display: flex;
        align-items: center;
        height: 54px;
        gap: 8px;
        padding: 0 16px;
        max-width: 1200px;
        margin: 0 auto;
    }
    .pnav-brand {
        display: flex; align-items: center; gap: 8px;
        text-decoration: none; flex-shrink: 0; margin-right: auto;
    }
    .pnav-brand img { height: 32px; width: auto; }
    .pnav-brand-name {
        font-size: 13px; font-weight: 700; color: #0f172a;
        letter-spacing: -.01em;
        display: none;
    }
    @media (min-width: 480px) { .pnav-brand-name { display: inline; } }

    /* Desktop nav links */
    .pnav-links {
        display: none;
        align-items: center;
        gap: 2px;
    }
    @media (min-width: 768px) { .pnav-links { display: flex; } }

    .pnav-link {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 6px 14px; border-radius: 999px;
        text-decoration: none; font-size: 13px; font-weight: 600;
        color: #64748b; transition: all .15s;
    }
    .pnav-link:hover { background: #f1f5f9; color: #1e3a5f; }
    .pnav-link.active { background: #1e3a5f; color: #fff; }
    .pnav-link i { font-size: 10px; }

    /* Profile button (always visible) */
    .pnav-profile {
        position: relative; flex-shrink: 0;
    }
    .pnav-profile-btn {
        display: flex; align-items: center; gap: 7px;
        padding: 5px 12px 5px 5px;
        border: 1px solid #e2e8f0; border-radius: 999px;
        background: #fff; cursor: pointer;
        font-size: 13px; font-weight: 600; color: #0f172a;
        transition: border-color .15s, box-shadow .15s;
    }
    .pnav-profile-btn:hover { border-color: #cbd5e1; box-shadow: 0 2px 6px rgba(0,0,0,.06); }
    .pnav-avatar {
        width: 28px; height: 28px; border-radius: 50%;
        background: #1e3a5f; color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: 11px; font-weight: 700; flex-shrink: 0;
    }
    .pnav-profile-name { max-width: 90px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .pnav-chevron { font-size: 9px; color: #94a3b8; }

    .pnav-dropdown {
        display: none; position: absolute; right: 0; top: calc(100% + 8px);
        min-width: 200px; background: #fff;
        border: 1px solid #e8edf3; border-radius: 14px;
        box-shadow: 0 8px 24px rgba(15,39,68,.12);
        z-index: 1050; overflow: hidden;
    }
    .pnav-dropdown.open { display: block; }
    .pnav-dropdown-header { padding: 12px 14px; border-bottom: 1px solid #eef2f6; }
    .pnav-dropdown-header .name { font-size: 13px; font-weight: 700; color: #0f172a; }
    .pnav-dropdown-header .email { font-size: 11px; color: #94a3b8; word-break: break-all; }
    .pnav-dropdown a {
        display: flex; align-items: center; gap: 8px;
        padding: 9px 14px; font-size: 13px; color: #334155;
        text-decoration: none; transition: background .12s;
    }
    .pnav-dropdown a:hover { background: #f8fafc; }
    .pnav-dropdown a.danger { color: #dc2626; }
    .pnav-dropdown a i { width: 16px; text-align: center; font-size: 12px; }

    /* Mobile menu toggle */
    .pnav-toggle {
        display: flex; align-items: center; justify-content: center;
        width: 36px; height: 36px; border-radius: 10px;
        border: 1px solid #e2e8f0; background: #fff;
        cursor: pointer; color: #334155; font-size: 15px;
        transition: background .15s;
    }
    .pnav-toggle:hover { background: #f1f5f9; }
    @media (min-width: 768px) { .pnav-toggle { display: none; } }

    /* Mobile drawer */
    .pnav-drawer {
        display: none; position: absolute; top: 100%; left: 0; right: 0;
        flex-direction: column; gap: 2px;
        padding: 10px 16px 14px;
        border-top: 1px solid #eef2f6;
        background: #fff;
        box-shadow: 0 12px 24px rgba(15, 23, 42, .12);
        max-height: calc(100vh - 54px);
        overflow-y: auto;
    }
    .pnav-drawer.open { display: flex; }
    @media (min-width: 768px) { .pnav-drawer { display: none !important; } }

    .pnav-drawer-link {
        display: flex; align-items: center; gap: 10px;
        padding: 10px 12px; border-radius: 10px;
        text-decoration: none; font-size: 13px; font-weight: 600;
        color: #334155; transition: background .12s;
    }
    .pnav-drawer-link:hover { background: #f1f5f9; color: #1e3a5f; }
    .pnav-drawer-link.active { background: #eff6ff; color: #1e3a5f; }
    .pnav-drawer-link i { width: 18px; text-align: center; font-size: 13px; color: #94a3b8; }
    .pnav-drawer-link.active i { color: #1e3a5f; }
    .pnav-drawer-link.danger { color: #dc2626; }
    .pnav-drawer-link.danger i { color: #dc2626; }
    .pnav-drawer-sep { height: 1px; background: #eef2f6; margin: 4px 0; }

    /* Page header */
    .pnav-page-header {
        background: #fff; border-bottom: 1px solid #eef2f6;
        padding: 10px 16px;
    }
    .pnav-page-header-inner {
        max-width: 1200px; margin: 0 auto;
    }
    .pnav-page-header h1 {
        font-size: 15px; font-weight: 700; color: #0f172a;
        margin: 0 0 2px; letter-spacing: -.01em;
    }
    .pnav-breadcrumb {
        display: flex; align-items: center; gap: 5px;
        font-size: 11px; color: #94a3b8;
    }
    .pnav-breadcrumb a { color: #64748b; text-decoration: none; }
    .pnav-breadcrumb a:hover { color: #1e3a5f; }

    /* Content wrapper */
    .pnav-content {
        max-width: 1200px; margin: 0 auto;
        padding: 16px 16px 32px;
    }
    @media (min-width: 768px) { .pnav-content { padding: 24px 24px 40px; } }
    </style>
</head>
<body style="background:#f4f7fb; font-family:'Plus Jakarta Sans',sans-serif;">

<!-- Navbar -->
<nav class="pnav">
    <div class="pnav-inner">

        <!-- Brand -->
        <a class="pnav-brand" href="<?= base_url('index.php') ?>">
            <img src="<?= asset_url('images/logo-smm-brand.png') ?>" alt="Logo PT SMM">
            <span class="pnav-brand-name">PT Shafa Marwa</span>
        </a>

        <!-- Desktop links -->
        <div class="pnav-links">
            <a href="<?= base_url('frontend/pengguna/dashboard.php') ?>" class="pnav-link <?= ($user_page === 'dashboard') ? 'active' : '' ?>">
                <i class="fas fa-th-large"></i> Dashboard
            </a>
            <a href="<?= base_url('frontend/pages/katalog.php') ?>" class="pnav-link">
                <i class="fas fa-box-open"></i> Katalog
            </a>
            <a href="<?= base_url('frontend/pengguna/riwayat-pesanan.php') ?>" class="pnav-link <?= ($user_page === 'riwayat') ? 'active' : '' ?>">
                <i class="fas fa-receipt"></i> Riwayat
            </a>
        </div>

        <!-- Profile dropdown -->
        <div class="pnav-profile" id="pnavProfile">
            <button class="pnav-profile-btn" id="pnavProfileBtn" type="button">
                <div class="pnav-avatar"><?= strtoupper(substr($nama_user, 0, 1)) ?></div>
                <span class="pnav-profile-name"><?= e(explode(' ', $nama_user)[0]) ?></span>
                <i class="fas fa-chevron-down pnav-chevron"></i>
            </button>
            <div class="pnav-dropdown" id="pnavDropdown">
                <div class="pnav-dropdown-header">
                    <div class="name"><?= e($nama_user) ?></div>
                    <div class="email"><?= e($email_user) ?></div>
                </div>
                <a href="<?= base_url('frontend/pengguna/profil.php') ?>">
                    <i class="fas fa-user-edit"></i> Edit Profil
                </a>
                <a href="<?= base_url('frontend/auth/logout.php') ?>" class="danger">
                    <i class="fas fa-sign-out-alt"></i> Keluar
                </a>
            </div>
        </div>

        <!-- Mobile toggle -->
        <button class="pnav-toggle" id="pnavToggle" type="button">
            <i class="fas fa-bars" id="pnavToggleIcon"></i>
        </button>

    </div>

    <!-- Mobile drawer -->
    <div class="pnav-drawer" id="pnavDrawer">
        <a href="<?= base_url('frontend/pengguna/dashboard.php') ?>" class="pnav-drawer-link <?= ($user_page === 'dashboard') ? 'active' : '' ?>">
            <i class="fas fa-th-large"></i> Dashboard
        </a>
        <a href="<?= base_url('frontend/pages/katalog.php') ?>" class="pnav-drawer-link">
            <i class="fas fa-box-open"></i> Katalog Produk
        </a>
        <a href="<?= base_url('frontend/pengguna/riwayat-pesanan.php') ?>" class="pnav-drawer-link <?= ($user_page === 'riwayat') ? 'active' : '' ?>">
            <i class="fas fa-receipt"></i> Riwayat Pesanan
        </a>
        <a href="<?= base_url('frontend/pengguna/pemesanan.php') ?>" class="pnav-drawer-link <?= ($user_page === 'pemesanan') ? 'active' : '' ?>">
            <i class="fas fa-cart-plus"></i> Form Pemesanan
        </a>
    </div>
</nav>

<!-- Page header -->
<div class="pnav-page-header">
    <div class="pnav-page-header-inner">
        <h1><?= e($page_title ?? 'Dashboard Pelanggan') ?></h1>
        <div class="pnav-breadcrumb">
            <a href="<?= base_url('index.php') ?>">Beranda</a>
            <i class="fas fa-chevron-right" style="font-size:8px;"></i>
            <span><?= e($page_title ?? 'Dashboard') ?></span>
        </div>
    </div>
</div>

<!-- Content wrapper open -->
<div class="pnav-content">

<script>
(function() {
    // Profile dropdown toggle
    var profileBtn = document.getElementById('pnavProfileBtn');
    var dropdown   = document.getElementById('pnavDropdown');
    if (profileBtn && dropdown) {
        profileBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdown.classList.toggle('open');
            // close drawer if open
            var drawer = document.getElementById('pnavDrawer');
            if (drawer) drawer.classList.remove('open');
        });
        document.addEventListener('click', function() { dropdown.classList.remove('open'); });
    }

    // Mobile drawer toggle
    var toggle = document.getElementById('pnavToggle');
    var icon   = document.getElementById('pnavToggleIcon');
    var drawer = document.getElementById('pnavDrawer');
    if (toggle && drawer) {
        toggle.addEventListener('click', function(e) {
            e.stopPropagation();
            var open = drawer.classList.toggle('open');
            icon.className = open ? 'fas fa-times' : 'fas fa-bars';
            // close profile dropdown
            if (dropdown) dropdown.classList.remove('open');
        });
    }
})();
</script>
