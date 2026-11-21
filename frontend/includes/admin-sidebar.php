<?php
// ============================================================
// FILE    : frontend/includes/admin-sidebar.php
// FUNGSI  : Sidebar Menu Admin Panel PT SMM
// PROYEK  : Sistem Informasi PT Shafa Marwa Multiguna
// ============================================================
$admin_page = $admin_page ?? '';
?>
<aside class="admin-sidebar">
    <a href="<?= base_url('frontend/admin/dashboard.php') ?>" class="admin-sidebar-brand">
        <div class="admin-brand-icon">
            <i class="fas fa-cube"></i>
        </div>
        <div>
            <div style="font-size: 0.95rem; line-height: 1.2;">ADMIN SMM</div>
            <div style="font-size: 0.72rem; color: var(--admin-accent); font-weight: 500;">PT SHAFA MARWA</div>
        </div>
    </a>

    <div class="admin-sidebar-menu">
        <div class="admin-menu-header">Utama</div>
        <a href="<?= base_url('frontend/admin/dashboard.php') ?>" class="admin-nav-link <?= ($admin_page === 'dashboard') ? 'active' : '' ?>">
            <i class="fas fa-chart-pie"></i>
            <span>Dashboard</span>
        </a>

        <div class="admin-menu-header">Katalog & Transaksi</div>
        <a href="<?= base_url('frontend/admin/produk/index.php') ?>" class="admin-nav-link <?= ($admin_page === 'produk') ? 'active' : '' ?>">
            <i class="fas fa-box-open"></i>
            <span>Kelola Produk</span>
        </a>
        <a href="<?= base_url('frontend/admin/kategori/index.php') ?>" class="admin-nav-link <?= ($admin_page === 'kategori') ? 'active' : '' ?>">
            <i class="fas fa-tags"></i>
            <span>Kategori Produk</span>
        </a>
        <a href="<?= base_url('frontend/admin/pesanan/index.php') ?>" class="admin-nav-link <?= ($admin_page === 'pesanan') ? 'active' : '' ?>">
            <i class="fas fa-shopping-bag"></i>
            <span>Kelola Pesanan</span>
        </a>

        <div class="admin-menu-header">Informasi & Akses</div>
        <a href="<?= base_url('frontend/admin/lowongan/index.php') ?>" class="admin-nav-link <?= ($admin_page === 'lowongan') ? 'active' : '' ?>">
            <i class="fas fa-briefcase"></i>
            <span>Lowongan Karir</span>
        </a>
        <a href="<?= base_url('frontend/admin/pengguna/index.php') ?>" class="admin-nav-link <?= ($admin_page === 'pengguna') ? 'active' : '' ?>">
            <i class="fas fa-users-cog"></i>
            <span>Kelola Pengguna</span>
        </a>
        <a href="<?= base_url('frontend/admin/pengaturan/profil-perusahaan.php') ?>" class="admin-nav-link <?= ($admin_page === 'pengaturan') ? 'active' : '' ?>">
            <i class="fas fa-building"></i>
            <span>Profil Perusahaan</span>
        </a>
    </div>

    <div class="admin-sidebar-footer">
        <div class="admin-user-pill justify-content-between">
            <div class="d-flex align-items-center gap-2 overflow-hidden">
                <div class="admin-avatar">
                    <?= strtoupper(substr($admin_name ?? 'A', 0, 1)) ?>
                </div>
                <div class="text-truncate">
                    <div class="small fw-bold text-truncate"><?= e($admin_name ?? 'Admin') ?></div>
                    <div style="font-size: 0.72rem; color: rgba(255,255,255,0.6);">Administrator</div>
                </div>
            </div>
            <a href="<?= base_url('frontend/auth/logout.php') ?>" class="text-danger fs-6 p-2" title="Keluar">
                <i class="fas fa-power-off"></i>
            </a>
        </div>
    </div>
</aside>

