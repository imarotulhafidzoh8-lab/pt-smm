<?php
// ============================================================
// FILE    : frontend/admin/lowongan/tambah.php
// FUNGSI  : Formulir Tambah Lowongan Baru (Admin)
// PROYEK  : Sistem Informasi PT Shafa Marwa Multiguna
// ============================================================
require_once __DIR__ . '/../../../config/app.php';
cek_admin();

$page_title = 'Tambah Lowongan Karir';
$admin_page = 'lowongan';

require_once __DIR__ . '/../../includes/admin-header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-navy mb-0">Tambah Lowongan Baru</h4>
        <small class="text-muted">Isi informasi kualifikasi dan persyaratan posisi pekerjaan</small>
    </div>
    <a href="<?= base_url('frontend/admin/lowongan/index.php') ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
    </a>
</div>

<div class="admin-card">
    <div class="admin-card-body">
        <form action="<?= base_url('backend/admin/proses-lowongan.php') ?>" method="POST">
            <input type="hidden" name="aksi" value="tambah">

            <div class="row g-4">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label class="form-label">Judul Lowongan <span class="text-danger">*</span></label>
                        <input type="text" name="judul_lowongan" class="form-control" placeholder="Contoh: Host Live Streaming TikTok Shop" required autofocus>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Posisi / Jabatan <span class="text-danger">*</span></label>
                            <input type="text" name="posisi" class="form-control" placeholder="Contoh: Host Live Streaming" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Lokasi Penempatan <span class="text-danger">*</span></label>
                            <input type="text" name="lokasi" class="form-control" value="Bandar Lampung" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi Pekerjaan <span class="text-danger">*</span></label>
                        <textarea name="deskripsi" class="form-control" rows="5" placeholder="Tuliskan tugas, peran, dan tanggung jawab utama posisi ini..." required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Persyaratan & Kualifikasi</label>
                        <textarea name="kualifikasi" class="form-control" rows="5" placeholder="Tuliskan syarat pendidikan, usia, keahlian khusus, pengalaman..."></textarea>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Batas Akhir Pendaftaran</label>
                        <input type="date" name="batas_lamaran" class="form-control" value="<?= date('Y-m-d', strtotime('+30 days')) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status Lowongan</label>
                        <select name="status_lowongan" class="form-select">
                            <option value="Dibuka" selected>Dibuka (Aktif)</option>
                            <option value="Ditutup">Ditutup (Arsip)</option>
                        </select>
                    </div>

                    <div class="pt-3 border-top">
                        <button type="submit" class="btn btn-secondary-custom w-100 py-2 mb-2">
                            <i class="fas fa-save me-1"></i> Terbitkan Lowongan
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

