<?php
// ============================================================
// FILE    : frontend/admin/lowongan/edit.php
// FUNGSI  : Formulir Ubah Lowongan Karir (Admin)
// PROYEK  : Sistem Informasi PT Shafa Marwa Multiguna
// ============================================================
require_once __DIR__ . '/../../../config/app.php';
cek_admin();

$id_lowongan = (int)($_GET['id'] ?? 0);
if ($id_lowongan <= 0) {
    flash('error', 'Lowongan tidak ditemukan.');
    redirect(base_url('frontend/admin/lowongan/index.php'));
}

$lowongan = null;
try {
    $stmt = $pdo->prepare("SELECT * FROM lowongan WHERE id_lowongan = ? LIMIT 1");
    $stmt->execute([$id_lowongan]);
    $lowongan = $stmt->fetch();
} catch (PDOException $e) {}

if (!$lowongan) {
    flash('error', 'Lowongan tidak ditemukan dalam database.');
    redirect(base_url('frontend/admin/lowongan/index.php'));
}

$page_title = 'Edit Lowongan: ' . $lowongan['posisi'];
$admin_page = 'lowongan';

require_once __DIR__ . '/../../includes/admin-header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-navy mb-0">Ubah Data Lowongan</h4>
        <small class="text-muted">ID: #<?= $lowongan['id_lowongan'] ?> &bull; <?= e($lowongan['judul_lowongan']) ?></small>
    </div>
    <a href="<?= base_url('frontend/admin/lowongan/index.php') ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
    </a>
</div>

<div class="admin-card">
    <div class="admin-card-body">
        <form action="<?= base_url('backend/admin/proses-lowongan.php') ?>" method="POST">
            <input type="hidden" name="aksi" value="edit">
            <input type="hidden" name="id_lowongan" value="<?= $lowongan['id_lowongan'] ?>">

            <div class="row g-4">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label class="form-label">Judul Lowongan <span class="text-danger">*</span></label>
                        <input type="text" name="judul_lowongan" class="form-control" value="<?= e($lowongan['judul_lowongan']) ?>" required autofocus>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Posisi / Jabatan <span class="text-danger">*</span></label>
                            <input type="text" name="posisi" class="form-control" value="<?= e($lowongan['posisi']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Lokasi Penempatan <span class="text-danger">*</span></label>
                            <input type="text" name="lokasi" class="form-control" value="<?= e($lowongan['lokasi']) ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi Pekerjaan <span class="text-danger">*</span></label>
                        <textarea name="deskripsi" class="form-control" rows="5" required><?= e($lowongan['deskripsi']) ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Persyaratan & Kualifikasi</label>
                        <textarea name="kualifikasi" class="form-control" rows="5"><?= e($lowongan['kualifikasi']) ?></textarea>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Batas Akhir Pendaftaran</label>
                        <input type="date" name="batas_lamaran" class="form-control" value="<?= e($lowongan['batas_lamaran']) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status Lowongan</label>
                        <select name="status_lowongan" class="form-select">
                            <option value="Dibuka" <?= ($lowongan['status_lowongan'] === 'Dibuka') ? 'selected' : '' ?>>Dibuka (Aktif)</option>
                            <option value="Ditutup" <?= ($lowongan['status_lowongan'] === 'Ditutup') ? 'selected' : '' ?>>Ditutup (Arsip)</option>
                        </select>
                    </div>

                    <div class="pt-3 border-top">
                        <button type="submit" class="btn btn-secondary-custom w-100 py-2 mb-2">
                            <i class="fas fa-save me-1"></i> Simpan Perubahan
                        </button>
                        <a href="<?= base_url('frontend/admin/lowongan/index.php') ?>" class="btn btn-outline-secondary w-100">
                            Batal
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>

