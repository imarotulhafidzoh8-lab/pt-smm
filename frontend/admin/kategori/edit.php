<?php
// ============================================================
// FILE    : frontend/admin/kategori/edit.php
// FUNGSI  : Edit Kategori Produk (Admin)
// PROYEK  : Sistem Informasi PT Shafa Marwa Multiguna
// ============================================================
require_once __DIR__ . '/../../../config/app.php';
cek_admin();

$id_kategori = (int)($_GET['id'] ?? 0);
if ($id_kategori <= 0) {
    flash('error', 'Kategori tidak ditemukan.');
    redirect(base_url('frontend/admin/kategori/index.php'));
}

$kategori = null;
try {
    $stmt = $pdo->prepare("SELECT * FROM kategori WHERE id_kategori = ? LIMIT 1");
    $stmt->execute([$id_kategori]);
    $kategori = $stmt->fetch();
} catch (PDOException $e) {}

if (!$kategori) {
    flash('error', 'Kategori tidak ditemukan.');
    redirect(base_url('frontend/admin/kategori/index.php'));
}

$page_title = 'Edit Kategori: ' . $kategori['nama_kategori'];
$admin_page = 'kategori';

require_once __DIR__ . '/../../includes/admin-header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-navy mb-0">Ubah Data Kategori</h4>
        <small class="text-muted">ID: #<?= $kategori['id_kategori'] ?> &bull; <?= e($kategori['nama_kategori']) ?></small>
    </div>
    <a href="<?= base_url('frontend/admin/kategori/index.php') ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="admin-card">
            <div class="admin-card-body">
                <form action="<?= base_url('backend/admin/proses-kategori.php') ?>" method="POST">
                    <input type="hidden" name="aksi" value="edit">
                    <input type="hidden" name="id_kategori" value="<?= $kategori['id_kategori'] ?>">

                    <div class="mb-3">
                        <label class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" name="nama_kategori" class="form-control" value="<?= e($kategori['nama_kategori']) ?>" required autofocus>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Deskripsi Kategori</label>
                        <textarea name="deskripsi" class="form-control" rows="4"><?= e($kategori['deskripsi']) ?></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= base_url('frontend/admin/kategori/index.php') ?>" class="btn btn-outline-secondary">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-secondary-custom">
                            <i class="fas fa-save me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>

