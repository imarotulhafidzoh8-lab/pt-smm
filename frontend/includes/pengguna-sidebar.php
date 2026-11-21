<?php
// ============================================================
// FILE    : frontend/includes/pengguna-sidebar.php
// FUNGSI  : Sidebar Menu Dashboard Pengguna
// PROYEK  : Sistem Informasi PT Shafa Marwa Multiguna
// ============================================================
$user_page = $user_page ?? '';
?>
<div class="p-4 bg-white border border-light-subtle rounded-4 shadow-xs mb-4">
    <div class="text-center pb-3 mb-3 border-bottom border-light-subtle">
        <div class="rounded-circle bg-light border border-light-subtle text-dark d-flex align-items-center justify-content-center mx-auto mb-2 fw-bold fs-4" style="width: 58px; height: 58px;">
            <?= strtoupper(substr($nama_user, 0, 1)) ?>
        </div>
        <h6 class="fw-bold mb-0 text-dark" style="letter-spacing: -0.01em;"><?= e($nama_user) ?></h6>
        <small class="text-muted" style="font-size: 12px;"><?= e($email_user) ?></small>
        <div class="mt-2">
            <span class="badge bg-dark text-white rounded-pill px-3 py-1 fw-normal" style="font-size: 11px;">Pelanggan PT SMM</span>
        </div>
    </div>

    <nav class="nav flex-column gap-1">
        <a class="d-flex align-items-center gap-2 px-3 py-2 rounded-pill text-decoration-none small fw-semibold <?= ($user_page === 'dashboard') ? 'bg-dark text-white' : 'text-muted bg-transparent' ?>" href="<?= base_url('frontend/pengguna/dashboard.php') ?>" style="transition: all 0.2s;">
            <i class="fas fa-th-large" style="width: 18px;"></i> Dashboard
        </a>
        <a class="d-flex align-items-center gap-2 px-3 py-2 rounded-pill text-decoration-none small fw-semibold <?= ($user_page === 'riwayat') ? 'bg-dark text-white' : 'text-muted bg-transparent' ?>" href="<?= base_url('frontend/pengguna/riwayat-pesanan.php') ?>" style="transition: all 0.2s;">
            <i class="fas fa-receipt" style="width: 18px;"></i> Riwayat Pesanan
        </a>
        <a class="d-flex align-items-center gap-2 px-3 py-2 rounded-pill text-decoration-none small fw-semibold <?= ($user_page === 'pemesanan') ? 'bg-dark text-white' : 'text-muted bg-transparent' ?>" href="<?= base_url('frontend/pengguna/pemesanan.php') ?>" style="transition: all 0.2s;">
            <i class="fas fa-cart-plus" style="width: 18px;"></i> Form Pemesanan
        </a>
        <a class="d-flex align-items-center gap-2 px-3 py-2 rounded-pill text-decoration-none small fw-semibold text-muted bg-transparent" href="<?= base_url('frontend/pages/katalog.php') ?>" style="transition: all 0.2s;">
            <i class="fas fa-box-open" style="width: 18px;"></i> Katalog Produk
        </a>
        <a class="d-flex align-items-center gap-2 px-3 py-2 rounded-pill text-decoration-none small fw-semibold <?= ($user_page === 'profil') ? 'bg-dark text-white' : 'text-muted bg-transparent' ?>" href="<?= base_url('frontend/pengguna/profil.php') ?>" style="transition: all 0.2s;">
            <i class="fas fa-user-edit" style="width: 18px;"></i> Edit Profil
        </a>
        <hr class="my-2 border-light-subtle">
        <a class="d-flex align-items-center gap-2 px-3 py-2 rounded-pill text-decoration-none small fw-semibold text-danger bg-transparent" href="<?= base_url('frontend/auth/logout.php') ?>">
            <i class="fas fa-sign-out-alt" style="width: 18px;"></i> Keluar
        </a>
    </nav>
</div>

