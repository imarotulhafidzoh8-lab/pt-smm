<?php
// ============================================================
// FILE    : frontend/admin/pengaturan/profil-perusahaan.php
// FUNGSI  : Pengaturan Informasi & Profil PT SMM (Admin)
// PROYEK  : Sistem Informasi PT Shafa Marwa Multiguna
// ============================================================
require_once __DIR__ . '/../../../config/app.php';
cek_admin();

$page_title = 'Pengaturan Profil Perusahaan';
$admin_page = 'pengaturan';

$company = get_company_profile($pdo);

require_once __DIR__ . '/../../includes/admin-header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-navy mb-0">Pengaturan Profil Perusahaan</h4>
        <small class="text-muted">Informasi ini ditampilkan pada halaman depan, footer, dan kontak resmi</small>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="admin-card">
            <div class="admin-card-header">
                <h5 class="admin-card-title">Identitas Resmi PT Shafa Marwa Multiguna</h5>
            </div>
            <div class="admin-card-body">
                <form action="<?= base_url('backend/admin/proses-pengaturan.php') ?>" method="POST">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Resmi Perusahaan <span class="text-danger">*</span></label>
                            <input type="text" name="nama_perusahaan" class="form-control" value="<?= e($company['nama_perusahaan']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Website Resmi</label>
                            <input type="text" name="website" class="form-control" value="<?= e($company['website'] ?? '') ?>" placeholder="www.shafamarwa.com">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Alamat Email Resmi <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" value="<?= e($company['email']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nomor Telepon / WhatsApp CS <span class="text-danger">*</span></label>
                            <input type="text" name="no_telepon" class="form-control" value="<?= e($company['no_telepon']) ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Alamat Kantor & Studio (Way Halim) <span class="text-danger">*</span></label>
                        <textarea name="alamat" class="form-control" rows="3" required><?= e($company['alamat']) ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Deskripsi Singkat Profil Perusahaan</label>
                        <textarea name="deskripsi" class="form-control" rows="5"><?= e($company['deskripsi']) ?></textarea>
                        <small class="text-muted">Teks ini muncul di bagian overview halaman profil dan ringkasan footer.</small>
                    </div>

                    <div class="pt-3 border-top text-end">
                        <button type="submit" class="btn btn-secondary-custom">
                            <i class="fas fa-save me-1"></i> Simpan Perubahan Profil
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>

