<?php
// ============================================================
// FILE    : frontend/admin/produk/index.php
// FUNGSI  : Manajemen Katalog Produk (Admin)
// PROYEK  : Sistem Informasi PT Shafa Marwa Multiguna
// ============================================================
require_once __DIR__ . '/../../../config/app.php';
cek_admin();

$page_title = 'Kelola Produk';
$admin_page = 'produk';

// Filter Kategori & Pencarian
$filter_kategori = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;
$keyword         = isset($_GET['q']) ? sanitize($_GET['q']) : '';

// Ambil Kategori untuk filter dropdown
$kategori_list = [];
try {
    $stmt = $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori ASC");
    $kategori_list = $stmt->fetchAll();
} catch (PDOException $e) {}

// Query Produk
$produk_list = [];
try {
    $sql = "
        SELECT p.*, k.nama_kategori
        FROM produk p
        JOIN kategori k ON p.id_kategori = k.id_kategori
        WHERE 1=1
    ";
    $params = [];

    if ($filter_kategori > 0) {
        $sql .= " AND p.id_kategori = ?";
        $params[] = $filter_kategori;
    }

    if ($keyword !== '') {
        $sql .= " AND p.nama_produk LIKE ?";
        $params[] = '%' . $keyword . '%';
    }

    $sql .= " ORDER BY p.dibuat_pada DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $produk_list = $stmt->fetchAll();
} catch (PDOException $e) {
    $produk_list = [];
}

require_once __DIR__ . '/../../includes/admin-header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h4 class="fw-bold text-navy mb-0">Daftar Produk PT SMM</h4>
        <small class="text-muted">Kelola 16+ produk dalam 5 kategori resmi perusahaan</small>
    </div>
    <a href="<?= base_url('frontend/admin/produk/tambah.php') ?>" class="btn btn-secondary-custom">
        <i class="fas fa-plus-circle me-1"></i> Tambah Produk Baru
    </a>
</div>

<!-- Filter Bar -->
<div class="admin-card mb-4">
    <div class="p-3">
        <form action="<?= base_url('frontend/admin/produk/index.php') ?>" method="GET" class="row g-2 align-items-center">
            <div class="col-md-5">
                <input type="text" name="q" class="form-control" placeholder="Cari nama produk..." value="<?= e($keyword) ?>">
            </div>
            <div class="col-md-4">
                <select name="kategori" class="form-select">
                    <option value="0">-- Semua Kategori --</option>
                    <?php foreach ($kategori_list as $kat): ?>
                        <option value="<?= $kat['id_kategori'] ?>" <?= ($filter_kategori === (int)$kat['id_kategori']) ? 'selected' : '' ?>>
                            <?= e($kat['nama_kategori']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill">
                    <i class="fas fa-search me-1"></i> Cari
                </button>
                <?php if ($filter_kategori > 0 || $keyword !== ''): ?>
                    <a href="<?= base_url('frontend/admin/produk/index.php') ?>" class="btn btn-outline-secondary" title="Reset">
                        <i class="fas fa-undo"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Table Produk -->
<div class="admin-card">
    <div class="table-responsive">
        <table class="table admin-table align-middle">
            <thead>
                <tr>
                    <th style="width: 70px;">Foto</th>
                    <th>Nama Produk</th>
                    <th>Kategori</th>
                    <th>Harga Satuan</th>
                    <th>Stok Barang</th>
                    <th>Status</th>
                    <th class="text-end" style="width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($produk_list)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-box-open fa-3x mb-3 d-block opacity-50"></i>
                            Tidak ada produk yang cocok dengan pencarian.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($produk_list as $p): ?>
                        <tr>
                            <td>
                                <img src="<?= upload_url('produk', $p['gambar']) ?>" alt="<?= e($p['nama_produk']) ?>" class="rounded border" style="width: 50px; height: 50px; object-fit: cover;">
                            </td>
                            <td>
                                <div class="fw-bold text-navy"><?= e($p['nama_produk']) ?></div>
                                <?php if (!empty($p['tampil_di_beranda'])): ?>
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle mt-1"><i class="fas fa-star me-1"></i>Unggulan Beranda</span>
                                <?php endif; ?>
                                <small class="text-muted">Slug: <?= e($p['slug']) ?></small>
                            </td>
                            <td>
                                <span class="badge bg-subtle-blue text-navy"><?= e($p['nama_kategori']) ?></span>
                            </td>
                            <td>
                                <span class="fw-bold text-navy"><?= format_rupiah($p['harga']) ?></span>
                                <small class="text-muted">/<?= e($p['satuan']) ?></small>
                            </td>
                            <td>
                                <?php if ($p['stok'] > 10): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle"><?= $p['stok'] ?> <?= e($p['satuan']) ?></span>
                                <?php elseif ($p['stok'] > 0): ?>
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle"><?= $p['stok'] ?> (Menipis)</span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Habis</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($p['status_produk'] === 'Aktif'): ?>
                                    <span class="badge bg-success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Tidak Aktif</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="btn-group">
                                    <a href="<?= base_url('frontend/admin/produk/edit.php?id=' . $p['id_produk']) ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?= base_url('backend/admin/proses-produk.php?aksi=hapus&id=' . $p['id_produk']) ?>" class="btn btn-sm btn-outline-danger btn-confirm-delete" data-confirm="Hapus produk '<?= e($p['nama_produk']) ?>'?" title="Hapus">
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

