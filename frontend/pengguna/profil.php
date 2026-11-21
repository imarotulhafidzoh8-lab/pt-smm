<?php
// ============================================================
// FILE    : frontend/pengguna/profil.php
// FUNGSI  : Profil & Pengaturan Akun Pelanggan
// PROYEK  : Sistem Informasi PT Shafa Marwa Multiguna
// ============================================================
require_once __DIR__ . '/../../config/app.php';
cek_pengguna();

$page_title = 'Pengaturan Profil Akun';
$user_page  = 'profil';
$id_user    = (int)$_SESSION['id_user'];

$user = null;
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id_user = ? LIMIT 1");
    $stmt->execute([$id_user]);
    $user = $stmt->fetch();
} catch (PDOException $e) {}

if (!$user) {
    flash('error', 'Data akun tidak ditemukan.');
    redirect(base_url('frontend/auth/login.php'));
}

require_once __DIR__ . '/../includes/pengguna-header.php';
?>

<div class="p-4 p-md-5 bg-white border border-light-subtle rounded-4 shadow-xs mb-4">
    <div class="border-bottom border-light-subtle pb-3 mb-4">
        <h4 class="fw-bold text-dark mb-1" style="letter-spacing: -0.01em;">Pengaturan Profil & Keamanan Akun</h4>
        <small class="text-muted">Perbarui data kontak pengiriman default dan kata sandi akun Anda</small>
    </div>

    <form action="<?= base_url('backend/pengguna/proses-profil.php') ?>" method="POST">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-dark">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="nama_lengkap" class="form-control" value="<?= e($user['nama_lengkap']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-dark">Alamat Email</label>
                    <input type="email" class="form-control bg-light border-light-subtle text-muted" value="<?= e($user['email']) ?>" readonly>
                    <small class="text-muted" style="font-size: 11px;">Alamat email terdaftar dan tidak dapat diubah.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-dark">Nomor WhatsApp / HP Aktif <span class="text-danger">*</span></label>
                    <input type="tel" name="no_hp" class="form-control" value="<?= e($user['no_hp'] ?? '') ?>" placeholder="08xxxxxxxxxx" required>
                </div>
            </div>

            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-dark">Alamat Lengkap Pengiriman Default</label>
                    <textarea name="alamat" class="form-control" rows="3" placeholder="Jalan, nomor rumah, RT/RW, kelurahan, kecamatan, kota..."><?= e($user['alamat'] ?? '') ?></textarea>
                    <small class="text-muted" style="font-size: 11px;">Alamat ini akan otomatis digunakan saat Anda memesan produk.</small>
                </div>

                <div class="mb-3 p-3 bg-light rounded-3 border border-light-subtle">
                    <label class="form-label fw-bold text-dark small mb-1">Ganti Kata Sandi (Opsional)</label>
                    <input type="password" name="password_baru" class="form-control bg-white" placeholder="Ketik kata sandi baru jika ingin mengubah">
                    <small class="text-muted d-block mt-1" style="font-size: 11px;">Kosongkan jika Anda tidak ingin mengubah kata sandi.</small>
                </div>
            </div>

            <div class="col-12 pt-3 border-top border-light-subtle d-grid d-md-flex justify-content-md-end">
                <button type="submit" class="btn rounded-pill px-4 py-2 d-inline-flex align-items-center gap-2 text-white" style="background:#1e3a5f; border-color:#1e3a5f; box-shadow: 0 4px 12px rgba(30, 58, 95, 0.25);">
                    <i class="fas fa-save me-1"></i> Simpan Perubahan Profil
                </button>
            </div>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/pengguna-footer.php'; ?>

