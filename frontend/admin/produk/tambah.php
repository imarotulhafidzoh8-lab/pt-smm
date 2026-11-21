<?php
// ============================================================
// FILE    : frontend/admin/produk/tambah.php
// FUNGSI  : Formulir Tambah Produk Baru (Admin)
// PROYEK  : Sistem Informasi PT Shafa Marwa Multiguna
// ============================================================
require_once __DIR__ . '/../../../config/app.php';
cek_admin();

$page_title = 'Tambah Produk Baru';
$admin_page = 'produk';

// Ambil Kategori untuk dropdown
$kategori_list = [];
try {
    $stmt = $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori ASC");
    $kategori_list = $stmt->fetchAll();
} catch (PDOException $e) {}
$jumlah_produk_unggulan = (int)$pdo->query("SELECT COUNT(*) FROM produk WHERE tampil_di_beranda = 1")->fetchColumn();

require_once __DIR__ . '/../../includes/admin-header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-navy mb-0">Tambah Produk Baru</h4>
        <small class="text-muted">Lengkapi rincian produk untuk ditambahkan ke dalam katalog PT SMM</small>
    </div>
    <a href="<?= base_url('frontend/admin/produk/index.php') ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
    </a>
</div>

<div class="admin-card">
    <div class="admin-card-body">
        <form action="<?= base_url('backend/admin/proses-produk.php') ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="aksi" value="tambah">

            <div class="row g-4">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label class="form-label">Nama Produk <span class="text-danger">*</span></label>
                        <input type="text" name="nama_produk" class="form-control" placeholder="Contoh: Pupuk Organik Cair Bio-Harvest 1 Liter" required autofocus>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Kategori Produk <span class="text-danger">*</span></label>
                            <select name="id_kategori" class="form-select" required>
                                <option value="">-- Pilih Kategori --</option>
                                <?php foreach ($kategori_list as $kat): ?>
                                    <option value="<?= $kat['id_kategori'] ?>"><?= e($kat['nama_kategori']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Satuan Kemasan <span class="text-danger">*</span></label>
                            <input type="text" name="satuan" class="form-control" placeholder="Contoh: botol, sak, pouch, box" value="pcs" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Harga Satuan (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" step="100" name="harga" class="form-control" placeholder="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Jumlah Stok Awal <span class="text-danger">*</span></label>
                            <input type="number" name="stok" class="form-control" placeholder="0" min="0" value="0" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi Lengkap Produk</label>
                        <textarea name="deskripsi" class="form-control" rows="6" placeholder="Jelaskan kandungan, manfaat, cara pemakaian, dan legalitas izin produk..."></textarea>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="p-3 bg-subtle-blue rounded-3 border mb-3">
                        <label class="form-label">Foto Produk</label>
                        <input type="file" name="gambar" class="form-control mb-2" accept="image/jpeg,image/png,image/webp">
                        <small class="text-muted d-block">Format: JPG, PNG, atau WEBP. Maksimal ukuran file 5 MB.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status Produk</label>
                        <select name="status_produk" class="form-select">
                            <option value="Aktif" selected>Aktif (Tampil di Katalog)</option>
                            <option value="Tidak Aktif">Tidak Aktif (Disembunyikan)</option>
                        </select>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="tampil_di_beranda" name="tampil_di_beranda" value="1">
                        <label class="form-check-label fw-semibold" for="tampil_di_beranda">Tampilkan sebagai Produk Unggulan</label>
                        <small class="form-text text-muted d-block">Maksimal 8 pilihan. Saat ini <?= $jumlah_produk_unggulan ?>/8 dipilih. Hanya produk aktif yang tampil di Beranda.</small>
                    </div>

                    <div class="pt-3 border-top">
                        <button type="submit" class="btn btn-secondary-custom w-100 py-2 mb-2">
                            <i class="fas fa-save me-1"></i> Simpan Produk
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
