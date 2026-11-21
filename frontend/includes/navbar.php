<?php
// ============================================================
// FILE    : frontend/includes/navbar.php
// FUNGSI  : Reusable Global Navbar & Flash Alert
// PROYEK  : Sistem Informasi PT Shafa Marwa Multiguna
// ============================================================
$current_page = $current_page ?? '';
$is_login = isset($_SESSION['id_user']) && !empty($_SESSION['id_user']);
$role_user = $_SESSION['role'] ?? '';
$nama_user = $_SESSION['nama_lengkap'] ?? '';
$flash = get_flash();
?>

<!-- Main Sticky Navbar (Modern Minimalist Style) -->
<nav class="navbar navbar-expand-lg navbar-public sticky-top bg-white border-bottom border-light-subtle py-2" style="z-index: 1030;">
    <div class="container">
        <!-- Brand Logo & Name -->
        <a class="navbar-brand d-flex align-items-center gap-2 text-decoration-none" href="<?= base_url('index.php') ?>">
            <img src="<?= asset_url('images/logo-smm-brand.png') ?>" alt="Logo PT Shafa Marwa Multiguna" style="height: 38px; width: auto; object-fit: contain;">
            <span class="fw-bold text-dark fs-6 tracking-tight" style="letter-spacing: -0.02em;">
                PT Shafa Marwa Multiguna
            </span>
        </a>

        <!-- Mobile Toggler -->
        <button class="navbar-toggler border-0 shadow-none p-1" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
            <i class="fas fa-bars text-dark fs-5"></i>
        </button>

        <!-- Navbar Links -->
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-lg-1">
                <li class="nav-item">
                    <a class="nav-link px-3 py-2 fw-medium text-dark <?= ($current_page === 'beranda') ? 'fw-bold' : 'text-opacity-75' ?>" href="<?= base_url('index.php') ?>">Beranda</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-3 py-2 fw-medium text-dark <?= ($current_page === 'profil') ? 'fw-bold' : 'text-opacity-75' ?>" href="<?= base_url('frontend/pages/profil.php') ?>">Tentang Kami</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-3 py-2 fw-medium text-dark <?= ($current_page === 'katalog') ? 'fw-bold' : 'text-opacity-75' ?>" href="<?= base_url('frontend/pages/katalog.php') ?>">Katalog</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-3 py-2 fw-medium text-dark <?= ($current_page === 'lowongan') ? 'fw-bold' : 'text-opacity-75' ?>" href="<?= base_url('frontend/pages/lowongan.php') ?>">Karir</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-3 py-2 fw-medium text-dark <?= ($current_page === 'kontak') ? 'fw-bold' : 'text-opacity-75' ?>" href="<?= base_url('frontend/pages/kontak.php') ?>">Kontak</a>
                </li>
            </ul>

            <!-- Right Action Items -->
            <div class="d-flex align-items-center gap-2 mt-3 mt-lg-0">
                <a href="<?= base_url('frontend/pages/katalog.php') ?>" class="btn btn-light rounded-circle border-0 text-dark d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;" title="Cari Produk">
                    <i class="fas fa-search" style="font-size: 14px;"></i>
                </a>

                <?php if ($is_login): ?>
                    <div class="dropdown">
                        <button class="btn btn-outline-dark rounded-pill px-3 py-2 d-flex align-items-center gap-2 font-medium small dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
                            <i class="fas fa-user-circle fs-6"></i>
                            <span><?= e(explode(' ', $nama_user)[0]) ?></span>
                            <i class="fas fa-chevron-down opacity-50" style="font-size: 10px;"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border border-light-subtle py-2 mt-2" style="border-radius: 14px;">
                            <?php if (strcasecmp($role_user, 'Admin') === 0): ?>
                                <li>
                                    <a class="dropdown-item py-2 small" href="<?= base_url('frontend/admin/dashboard.php') ?>">
                                        <i class="fas fa-tachometer-alt me-2 text-primary"></i>Panel Admin
                                    </a>
                                </li>
                            <?php else: ?>
                                <li>
                                    <a class="dropdown-item py-2 small" href="<?= base_url('frontend/pengguna/dashboard.php') ?>">
                                        <i class="fas fa-th-large me-2 text-primary"></i>Dashboard Saya
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2 small" href="<?= base_url('frontend/pengguna/riwayat-pesanan.php') ?>">
                                        <i class="fas fa-receipt me-2 text-primary"></i>Riwayat Pesanan
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2 small" href="<?= base_url('frontend/pengguna/profil.php') ?>">
                                        <i class="fas fa-user-cog me-2 text-primary"></i>Pengaturan Akun
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <a class="dropdown-item py-2 small text-danger" href="<?= base_url('frontend/auth/logout.php') ?>">
                                    <i class="fas fa-sign-out-alt me-2"></i>Keluar
                                </a>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="<?= base_url('frontend/auth/login.php') ?>" class="btn btn-light rounded-pill px-3 py-2 text-dark fw-semibold small">
                        Masuk
                    </a>
                    <a href="<?= base_url('frontend/auth/register.php') ?>" class="btn rounded-pill px-4 py-2 text-white fw-semibold small" style="background:#1e3a5f; border-color:#1e3a5f; box-shadow: 0 4px 12px rgba(30, 58, 95, 0.25);">
                        Daftar
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- Global Flash Alert Notification -->
<?php if ($flash): ?>
    <div class="container mt-3">
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
        <div class="alert <?= $alert_class ?> alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">
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
    </div>
<?php endif; ?>

