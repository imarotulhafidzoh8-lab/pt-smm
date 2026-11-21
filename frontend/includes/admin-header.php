<?php
// ============================================================
// FILE    : frontend/includes/admin-header.php
// FUNGSI  : Layout Header Khusus Panel Administrator
// PROYEK  : Sistem Informasi PT Shafa Marwa Multiguna
// ============================================================
require_once __DIR__ . '/../../config/app.php';
cek_admin();

$admin_name = $_SESSION['nama_lengkap'] ?? 'Administrator';
$admin_email = $_SESSION['email'] ?? 'admin@shafamarwa.com';
$admin_title = isset($page_title) ? $page_title . ' - Admin Panel PT SMM' : 'Admin Panel - ' . APP_NAME;
$flash = get_flash();
$admin_hero_context = [
    'produk' => ['label' => 'MANAJEMEN KATALOG', 'title' => 'Kelola katalog produk', 'copy' => 'Atur informasi, harga, stok, dan produk unggulan agar katalog PT SMM selalu siap ditampilkan.', 'icon' => 'fa-box-open'],
    'kategori' => ['label' => 'KATEGORI PRODUK', 'title' => 'Susun katalog lebih teratur', 'copy' => 'Kelola kategori untuk membantu pelanggan menemukan produk dari setiap lini usaha.', 'icon' => 'fa-tags'],
    'pesanan' => ['label' => 'PUSAT TRANSAKSI', 'title' => 'Pantau pesanan pelanggan', 'copy' => 'Kelola pembayaran, konfirmasi, pemrosesan, dan pengiriman pesanan dari satu tempat.', 'icon' => 'fa-receipt'],
    'lowongan' => ['label' => 'REKRUTMEN PT SMM', 'title' => 'Bangun tim bersama kami', 'copy' => 'Kelola informasi lowongan dan pantau peluang karir yang tersedia di PT Shafa Marwa Multiguna.', 'icon' => 'fa-briefcase'],
    'pengguna' => ['label' => 'AKSES PENGGUNA', 'title' => 'Kelola akun dan akses', 'copy' => 'Tinjau akun pengguna yang terdaftar dan jaga informasi akses sistem tetap tertata.', 'icon' => 'fa-users'],
    'pengaturan' => ['label' => 'IDENTITAS PERUSAHAAN', 'title' => 'Perbarui profil perusahaan', 'copy' => 'Jaga informasi profil, alamat, dan kontak resmi perusahaan tetap akurat di website.', 'icon' => 'fa-building'],
];
$admin_hero = $admin_hero_context[$admin_page ?? ''] ?? ['label' => 'ADMINISTRATOR PT SMM', 'title' => 'Kelola sistem perusahaan', 'copy' => 'Akses informasi dan kelola kebutuhan operasional website PT Shafa Marwa Multiguna.', 'icon' => 'fa-layer-group'];
$admin_scorecards = [];
$admin_current_file = basename($_SERVER['SCRIPT_NAME'] ?? '');
$admin_scorecards_enabled = ($admin_page ?? '') !== 'dashboard'
    && ($admin_current_file === 'index.php' || ($admin_page ?? '') === 'pengaturan');
if ($admin_scorecards_enabled) {
    try {
        switch ($admin_page ?? '') {
            case 'produk':
                $data = $pdo->query("SELECT COUNT(*) AS total, SUM(status_produk = 'Aktif') AS aktif, SUM(status_produk = 'Aktif' AND tampil_di_beranda = 1) AS unggulan, SUM(status_produk = 'Aktif' AND stok <= 5) AS stok_tipis FROM produk")->fetch(PDO::FETCH_ASSOC) ?: [];
                $admin_scorecards = [
                    ['label' => 'Produk aktif', 'value' => (int)($data['aktif'] ?? 0), 'note' => 'dari ' . (int)($data['total'] ?? 0) . ' produk', 'icon' => 'fa-box-open', 'tone' => 'blue'],
                    ['label' => 'Produk unggulan', 'value' => (int)($data['unggulan'] ?? 0), 'note' => 'ditampilkan di beranda', 'icon' => 'fa-star', 'tone' => 'amber'],
                    ['label' => 'Stok menipis', 'value' => (int)($data['stok_tipis'] ?? 0), 'note' => 'stok 5 atau kurang', 'icon' => 'fa-boxes-stacked', 'tone' => 'rose'],
                ];
                break;
            case 'kategori':
                $data = $pdo->query("SELECT (SELECT COUNT(*) FROM kategori) AS kategori, (SELECT COUNT(*) FROM produk) AS produk, (SELECT COUNT(*) FROM kategori k WHERE NOT EXISTS (SELECT 1 FROM produk p WHERE p.id_kategori = k.id_kategori)) AS kosong")->fetch(PDO::FETCH_ASSOC) ?: [];
                $admin_scorecards = [
                    ['label' => 'Kategori tersedia', 'value' => (int)($data['kategori'] ?? 0), 'note' => 'kelompok katalog', 'icon' => 'fa-tags', 'tone' => 'blue'],
                    ['label' => 'Produk terdaftar', 'value' => (int)($data['produk'] ?? 0), 'note' => 'di semua kategori', 'icon' => 'fa-box-open', 'tone' => 'green'],
                    ['label' => 'Kategori kosong', 'value' => (int)($data['kosong'] ?? 0), 'note' => 'belum memiliki produk', 'icon' => 'fa-folder-open', 'tone' => 'amber'],
                ];
                break;
            case 'pesanan':
                $data = $pdo->query("SELECT COUNT(*) AS total, SUM(status_pesanan IN ('Menunggu Pembayaran', 'Menunggu Konfirmasi')) AS menunggu, SUM(status_pesanan = 'Diproses') AS diproses, SUM(status_pesanan = 'Dikirim') AS dikirim FROM pesanan")->fetch(PDO::FETCH_ASSOC) ?: [];
                $admin_scorecards = [
                    ['label' => 'Total pesanan', 'value' => (int)($data['total'] ?? 0), 'note' => 'seluruh transaksi', 'icon' => 'fa-receipt', 'tone' => 'blue'],
                    ['label' => 'Perlu ditinjau', 'value' => (int)($data['menunggu'] ?? 0), 'note' => 'menunggu pembayaran/konfirmasi', 'icon' => 'fa-clock', 'tone' => 'amber'],
                    ['label' => 'Dalam pengiriman', 'value' => (int)($data['diproses'] ?? 0) + (int)($data['dikirim'] ?? 0), 'note' => 'diproses atau dikirim', 'icon' => 'fa-truck-fast', 'tone' => 'green'],
                ];
                break;
            case 'lowongan':
                $data = $pdo->query("SELECT COUNT(*) AS total, SUM(status_lowongan = 'Dibuka') AS dibuka, SUM(status_lowongan = 'Dibuka' AND batas_lamaran BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)) AS segera_tutup FROM lowongan")->fetch(PDO::FETCH_ASSOC) ?: [];
                $admin_scorecards = [
                    ['label' => 'Lowongan aktif', 'value' => (int)($data['dibuka'] ?? 0), 'note' => 'sedang menerima lamaran', 'icon' => 'fa-briefcase', 'tone' => 'green'],
                    ['label' => 'Segera ditutup', 'value' => (int)($data['segera_tutup'] ?? 0), 'note' => 'batas dalam 7 hari', 'icon' => 'fa-hourglass-half', 'tone' => 'amber'],
                    ['label' => 'Total lowongan', 'value' => (int)($data['total'] ?? 0), 'note' => 'aktif dan ditutup', 'icon' => 'fa-list-check', 'tone' => 'blue'],
                ];
                break;
            case 'pengguna':
                $data = $pdo->query("SELECT COUNT(*) AS total, SUM(status_akun = 'Aktif') AS aktif, SUM(dibuat_pada >= DATE_SUB(NOW(), INTERVAL 30 DAY)) AS baru FROM users WHERE role = 'Pengguna'")->fetch(PDO::FETCH_ASSOC) ?: [];
                $admin_scorecards = [
                    ['label' => 'Pengguna terdaftar', 'value' => (int)($data['total'] ?? 0), 'note' => 'akun pelanggan', 'icon' => 'fa-users', 'tone' => 'blue'],
                    ['label' => 'Akun aktif', 'value' => (int)($data['aktif'] ?? 0), 'note' => 'dapat mengakses sistem', 'icon' => 'fa-user-check', 'tone' => 'green'],
                    ['label' => 'Pengguna baru', 'value' => (int)($data['baru'] ?? 0), 'note' => '30 hari terakhir', 'icon' => 'fa-user-plus', 'tone' => 'amber'],
                ];
                break;
            case 'pengaturan':
                $data = $pdo->query("SELECT COUNT(*) AS total, SUM(NULLIF(TRIM(nama_perusahaan), '') IS NOT NULL AND NULLIF(TRIM(alamat), '') IS NOT NULL) AS identitas, SUM(NULLIF(TRIM(email), '') IS NOT NULL AND NULLIF(TRIM(no_telepon), '') IS NOT NULL) AS kontak, SUM(NULLIF(TRIM(logo), '') IS NOT NULL) AS logo FROM profil_perusahaan")->fetch(PDO::FETCH_ASSOC) ?: [];
                $admin_scorecards = [
                    ['label' => 'Profil perusahaan', 'value' => !empty($data['identitas']) ? 'Siap' : 'Perlu data', 'note' => 'nama dan alamat usaha', 'icon' => 'fa-building', 'tone' => 'blue'],
                    ['label' => 'Kontak resmi', 'value' => !empty($data['kontak']) ? 'Lengkap' : 'Perlu data', 'note' => 'email dan telepon', 'icon' => 'fa-address-card', 'tone' => 'green'],
                    ['label' => 'Logo perusahaan', 'value' => !empty($data['logo']) ? 'Terpasang' : 'Belum ada', 'note' => 'identitas visual website', 'icon' => 'fa-image', 'tone' => 'amber'],
                ];
                break;
        }
    } catch (PDOException $e) {
        $admin_scorecards = [];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($admin_title) ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- SweetAlert2 (Popup Notifikasi Modern) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Admin & Global CSS -->
    <link rel="stylesheet" href="<?= asset_url('css/style.css') ?>?v=<?= filemtime(__DIR__ . '/../../assets/css/style.css') ?>">
    <link rel="stylesheet" href="<?= asset_url('css/admin.css') ?>?v=<?= filemtime(__DIR__ . '/../../assets/css/admin.css') ?>">
    <link rel="stylesheet" href="<?= asset_url('css/responsive.css') ?>">
</head>
<body class="admin-body">

<div class="admin-wrapper d-flex">
    <?php require_once __DIR__ . '/admin-sidebar.php'; ?>

    <div class="admin-main flex-grow-1">
        <!-- Admin Topbar -->
        <header class="admin-topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm btn-outline-secondary d-lg-none" id="adminSidebarToggle" type="button" aria-label="Toggle Sidebar">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="admin-topbar-title"><?= e($page_title ?? 'Dashboard') ?></h1>
            </div>

            <div class="d-flex align-items-center gap-3">
                <a href="<?= base_url('index.php') ?>" target="_blank" class="btn btn-sm btn-outline-primary d-none d-sm-inline-flex" title="Lihat Website Frontend">
                    <i class="fas fa-external-link-alt"></i> Website Publik
                </a>
                
                <div class="dropdown">
                    <button class="btn btn-sm btn-light border dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="admin-avatar" style="width: 28px; height: 28px; font-size: 0.8rem;">
                            <?= strtoupper(substr($admin_name, 0, 1)) ?>
                        </div>
                        <span class="d-none d-md-inline fw-semibold"><?= e($admin_name) ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 py-2 mt-2">
                        <li>
                            <div class="px-3 py-1">
                                <p class="mb-0 fw-bold small"><?= e($admin_name) ?></p>
                                <p class="text-muted small mb-0"><?= e($admin_email) ?></p>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item small" href="<?= base_url('frontend/admin/pengaturan/profil-perusahaan.php') ?>">
                                <i class="fas fa-cog me-2 text-primary"></i>Pengaturan Website
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item small text-danger" href="<?= base_url('frontend/auth/logout.php') ?>">
                                <i class="fas fa-sign-out-alt me-2"></i>Keluar
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Admin Content Body -->
        <main class="admin-content-body">
            <?php if ($flash): ?>
                <?php
                $alert_class = 'alert-info';
                $alert_icon  = 'fa-info-circle';
                if ($flash['tipe'] === 'sukses' || $flash['tipe'] === 'success') {
                    $alert_class = 'alert-success';
                    $alert_icon  = 'fa-check-circle';
                } elseif ($flash['tipe'] === 'error' || $flash['tipe'] === 'danger') {
                    $alert_class = 'alert-danger';
                    $alert_icon  = 'fa-exclamation-triangle';
                } elseif ($flash['tipe'] === 'warning') {
                    $alert_class = 'alert-warning';
                    $alert_icon  = 'fa-exclamation-circle';
                }
                ?>
                <div class="alert <?= $alert_class ?> alert-dismissible fade show d-flex align-items-center shadow-sm mb-4" role="alert">
                    <i class="fas <?= $alert_icon ?> fs-5 me-3"></i>
                    <div><?= e($flash['pesan']) ?></div>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    if (typeof Swal !== 'undefined') {
                        const iconType = '<?= ($flash['tipe'] === 'sukses' || $flash['tipe'] === 'success') ? 'success' : (($flash['tipe'] === 'error' || $flash['tipe'] === 'danger') ? 'error' : ($flash['tipe'] === 'warning' ? 'warning' : 'info')) ?>';
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: iconType,
                            title: <?= json_encode($flash['pesan']) ?>,
                            showConfirmButton: false,
                            timer: 3500,
                            timerProgressBar: true
                        });
                    }
                });
                </script>
            <?php endif; ?>

            <?php if (($admin_page ?? '') !== 'dashboard'): ?>
                <section class="admin-page-hero mb-4" aria-label="Pengantar halaman <?= e($admin_hero['label']) ?>">
                    <div class="admin-page-hero-copy">
                        <span class="admin-welcome-kicker"><i class="fas fa-shield-alt"></i> <?= e($admin_hero['label']) ?></span>
                        <h2><?= e($admin_hero['title']) ?></h2>
                        <p><?= e($admin_hero['copy']) ?></p>
                    </div>
                    <div class="admin-page-hero-mark" aria-hidden="true">
                        <i class="fas <?= e($admin_hero['icon']) ?>"></i>
                        <span>PT SMM <b>•</b> ADMIN</span>
                    </div>
                </section>
            <?php endif; ?>

            <?php if ($admin_scorecards_enabled && !empty($admin_scorecards)): ?>
                <section class="admin-context-scorecards row g-3 mb-4" aria-label="Ringkasan <?= e($admin_hero['label']) ?>">
                    <?php foreach ($admin_scorecards as $scorecard): ?>
                        <div class="col-xl-4 col-sm-6">
                            <article class="admin-context-scorecard">
                                <span class="admin-context-score-icon <?= e($scorecard['tone']) ?>"><i class="fas <?= e($scorecard['icon']) ?>"></i></span>
                                <div class="admin-context-score-copy">
                                    <span class="admin-context-score-label"><?= e($scorecard['label']) ?></span>
                                    <strong class="admin-context-score-value"><?= e((string)$scorecard['value']) ?></strong>
                                    <span class="admin-context-score-note"><?= e($scorecard['note']) ?></span>
                                </div>
                            </article>
                        </div>
                    <?php endforeach; ?>
                </section>
            <?php endif; ?>

