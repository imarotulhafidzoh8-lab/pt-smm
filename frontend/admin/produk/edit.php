<?php
// ============================================================
// FILE    : frontend/admin/produk/edit.php
// FUNGSI  : Formulir Ubah Produk (Admin)
// PROYEK  : Sistem Informasi PT Shafa Marwa Multiguna
// ============================================================
require_once __DIR__ . '/../../../config/app.php';
cek_admin();

$id_produk = (int)($_GET['id'] ?? 0);
if ($id_produk <= 0) {
    flash('error', 'Produk tidak ditemukan.');
    redirect(base_url('frontend/admin/produk/index.php'));
}

// Ambil Data Produk
$produk = null;
try {
    $stmt = $pdo->prepare("SELECT * FROM produk WHERE id_produk = ? LIMIT 1");
    $stmt->execute([$id_produk]);
    $produk = $stmt->fetch();
} catch (PDOException $e) {}

if (!$produk) {
    flash('error', 'Produk tidak ditemukan dalam database.');
    redirect(base_url('frontend/admin/produk/index.php'));
}

// Ambil Kategori untuk dropdown
$kategori_list = [];
try {
    $stmt = $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori ASC");
    $kategori_list = $stmt->fetchAll();
} catch (PDOException $e) {}
$jumlah_produk_unggulan = (int)$pdo->query("SELECT COUNT(*) FROM produk WHERE tampil_di_beranda = 1")->fetchColumn();

$page_title = 'Edit Produk: ' . $produk['nama_produk'];
$admin_page = 'produk';

require_once __DIR__ . '/../../includes/admin-header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-navy mb-0">Ubah Data Produk</h4>
        <small class="text-muted">ID: #<?= $produk['id_produk'] ?> &bull; <?= e($produk['nama_produk']) ?></small>
    </div>
    <a href="<?= base_url('frontend/admin/produk/index.php') ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
    </a>
</div>

<div class="admin-card">
    <div class="admin-card-body">
        <form action="<?= base_url('backend/admin/proses-produk.php') ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="aksi" value="edit">
            <input type="hidden" name="id_produk" value="<?= $produk['id_produk'] ?>">

            <div class="row g-4">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label class="form-label">Nama Produk <span class="text-danger">*</span></label>
                        <input type="text" name="nama_produk" class="form-control" value="<?= e($produk['nama_produk']) ?>" required autofocus>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Kategori Produk <span class="text-danger">*</span></label>
                            <select name="id_kategori" class="form-select" required>
                                <option value="">-- Pilih Kategori --</option>
                                <?php foreach ($kategori_list as $kat): ?>
                                    <option value="<?= $kat['id_kategori'] ?>" <?= ($produk['id_kategori'] == $kat['id_kategori']) ? 'selected' : '' ?>>
                                        <?= e($kat['nama_kategori']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Satuan Kemasan <span class="text-danger">*</span></label>
                            <input type="text" name="satuan" class="form-control" value="<?= e($produk['satuan']) ?>" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Harga Satuan (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" step="100" name="harga" class="form-control" value="<?= (int)$produk['harga'] ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Jumlah Stok Barang <span class="text-danger">*</span></label>
                            <input type="number" name="stok" class="form-control" min="0" value="<?= (int)$produk['stok'] ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi Lengkap Produk</label>
                        <textarea name="deskripsi" class="form-control" rows="6"><?= e($produk['deskripsi']) ?></textarea>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="p-3 bg-subtle-blue rounded-3 border mb-3">
                        <label class="form-label d-block">Foto Produk Saat Ini</label>
                        <div class="mb-2 text-center">
                            <img src="<?= upload_url('produk', $produk['gambar']) ?>" alt="Preview" class="img-thumbnail rounded" style="max-height: 140px;">
                        </div>
                        <label class="form-label small">Ganti Foto (Opsional)</label>
                        <input type="file" name="gambar" class="form-control form-control-sm" accept="image/jpeg,image/png,image/webp">
                        <small class="text-muted d-block mt-1">Kosongkan jika tidak ingin mengubah foto produk.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status Produk</label>
                        <select name="status_produk" class="form-select">
                            <option value="Aktif" <?= ($produk['status_produk'] === 'Aktif') ? 'selected' : '' ?>>Aktif (Tampil di Katalog)</option>
                            <option value="Tidak Aktif" <?= ($produk['status_produk'] === 'Tidak Aktif') ? 'selected' : '' ?>>Tidak Aktif (Disembunyikan)</option>
                        </select>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="tampil_di_beranda" name="tampil_di_beranda" value="1" <?= !empty($produk['tampil_di_beranda']) ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="tampil_di_beranda">Tampilkan sebagai Produk Unggulan</label>
                        <small class="form-text text-muted d-block">Maksimal 8 pilihan. Saat ini <?= $jumlah_produk_unggulan ?>/8 dipilih. Hanya produk aktif yang tampil di Beranda.</small>
                    </div>

                    <div class="pt-3 border-top">
                        <button type="submit" class="btn btn-secondary-custom w-100 py-2 mb-2">
                            <i class="fas fa-save me-1"></i> Simpan Perubahan
                        </button>
                        <a href="<?= base_url('frontend/admin/produk/index.php') ?>" class="btn btn-outline-secondary w-100">
                            Batal
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>

