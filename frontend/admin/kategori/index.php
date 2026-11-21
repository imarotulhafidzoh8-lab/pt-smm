<?php
// ============================================================
// FILE    : frontend/admin/kategori/index.php
// FUNGSI  : Kelola Kategori Produk (Admin)
// PROYEK  : Sistem Informasi PT Shafa Marwa Multiguna
// ============================================================
require_once __DIR__ . '/../../../config/app.php';
cek_admin();

$page_title = 'Kategori Produk';
$admin_page = 'kategori';

// Ambil semua kategori beserta jumlah produk yang terhubung
$kategori_list = [];
try {
    $stmt = $pdo->query("
        SELECT k.*, COUNT(p.id_produk) AS total_produk
        FROM kategori k
        LEFT JOIN produk p ON k.id_kategori = p.id_kategori
        GROUP BY k.id_kategori, k.nama_kategori, k.deskripsi, k.dibuat_pada
        ORDER BY k.id_kategori ASC
    ");
    $kategori_list = $stmt->fetchAll();
} catch (PDOException $e) {
    $kategori_list = [];
}

require_once __DIR__ . '/../../includes/admin-header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h4 class="fw-bold text-navy mb-0">Kategori Produk PT SMM</h4>
        <small class="text-muted">Kelola 5 kategori utama pengelompokan produk perusahaan</small>
    </div>
    <a href="<?= base_url('frontend/admin/kategori/tambah.php') ?>" class="btn btn-secondary-custom">
        <i class="fas fa-plus-circle me-1"></i> Tambah Kategori Baru
    </a>
</div>

<div class="admin-card">
    <div class="table-responsive">
        <table class="table admin-table align-middle">
            <thead>
                <tr>
                    <th style="width: 60px;">ID</th>
                    <th>Nama Kategori</th>
                    <th>Deskripsi Kategori</th>
                    <th class="text-center">Jumlah Produk</th>
                    <th class="text-end" style="width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($kategori_list)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">
                            Belum ada kategori yang tercatat.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($kategori_list as $k): ?>
                        <tr>
                            <td><span class="badge bg-light text-navy border">#<?= $k['id_kategori'] ?></span></td>
                            <td>
                                <strong class="text-navy"><?= e($k['nama_kategori']) ?></strong>
                            </td>
                            <td>
                                <span class="text-muted small"><?= e($k['deskripsi'] ?? '-') ?></span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-subtle-blue text-navy fw-semibold px-3 py-2">
                                    <?= $k['total_produk'] ?> Produk
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group">
                                    <a href="<?= base_url('frontend/admin/kategori/edit.php?id=' . $k['id_kategori']) ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?= base_url('backend/admin/proses-kategori.php?aksi=hapus&id=' . $k['id_kategori']) ?>" class="btn btn-sm btn-outline-danger btn-confirm-delete" data-confirm="Hapus kategori '<?= e($k['nama_kategori']) ?>'?" title="Hapus">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>

