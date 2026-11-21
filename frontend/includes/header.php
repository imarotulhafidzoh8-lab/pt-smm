<?php
// ============================================================
// FILE    : frontend/includes/header.php
// FUNGSI  : Reusable Global Header
// PROYEK  : Sistem Informasi PT Shafa Marwa Multiguna
// ============================================================
require_once __DIR__ . '/../../config/app.php';
$company = get_company_profile($pdo);
$page_title_text = isset($page_title) ? $page_title . ' - ' . APP_NAME : APP_NAME . ' | ' . APP_TAGLINE;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title_text) ?></title>
    
    <!-- Meta SEO -->
    <meta name="description" content="Sistem Informasi PT Shafa Marwa Multiguna (PT SMM) - Pemasaran digital, live streaming, dan katalog aneka produk berkualitas.">
    <meta name="author" content="PT Shafa Marwa Multiguna">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- SweetAlert2 (Popup Notifikasi Modern) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= asset_url('images/logo-smm-brand.png') ?>">

    <!-- Custom Stylesheet -->
    <link rel="stylesheet" href="<?= asset_url('css/style.css') ?>?v=<?= filemtime(__DIR__ . '/../../assets/css/style.css') ?>">
    <link rel="stylesheet" href="<?= asset_url('css/responsive.css') ?>">
</head>
<body>

<!-- Website Opening / Preloader Animation -->
<div id="site-preloader" class="site-preloader">
    <div class="preloader-content">
        <div class="mb-2">
            <img src="<?= asset_url('images/logo-smm-brand.png') ?>" alt="Logo PT SMM" style="height: 60px; width: auto; object-fit: contain; animation: preloaderPulse 1.2s ease-in-out infinite alternate;">
        </div>
        <div class="preloader-brand-title">PT SHAFA MARWA MULTIGUNA</div>
        <div class="preloader-track">
            <div class="preloader-bar"></div>
        </div>
    </div>
</div>


