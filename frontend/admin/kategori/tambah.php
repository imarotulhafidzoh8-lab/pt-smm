<?php
// ============================================================
// FILE    : frontend/admin/kategori/tambah.php
// FUNGSI  : Tambah Kategori Produk (Admin)
// PROYEK  : Sistem Informasi PT Shafa Marwa Multiguna
// ============================================================
require_once __DIR__ . '/../../../config/app.php';
cek_admin();

$page_title = 'Tambah Kategori Produk';
$admin_page = 'kategori';

require_once __DIR__ . '/../../includes/admin-header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-navy mb-0">Tambah Kategori Baru</h4>
        <small class="text-muted">Masukkan nama kategori dan deskripsi singkat</small>
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
                    <input type="hidden" name="aksi" value="tambah">

                    <div class="mb-3">
                        <label class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" name="nama_kategori" class="form-control" placeholder="Contoh: Pupuk Organik Pertanian" required autofocus>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Deskripsi Kategori</label>
                        <textarea name="deskripsi" class="form-control" rows="4" placeholder="Jelaskan jenis-jenis produk yang termasuk dalam kategori ini..."></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= base_url('frontend/admin/kategori/index.php') ?>" class="btn btn-outline-secondary">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-secondary-custom">
                            <i class="fas fa-save me-1"></i> Simpan Kategori
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>

