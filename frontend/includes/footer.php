<?php
// ============================================================
// FILE    : frontend/includes/footer.php
// FUNGSI  : Reusable Global Footer
// PROYEK  : Sistem Informasi PT Shafa Marwa Multiguna
// ============================================================
?>
<footer class="py-5" style="background: #111111; color: #a1a1aa; font-family: 'Plus Jakarta Sans', sans-serif; border-top: 1px solid #222222;">
    <div class="container py-3">
        <div class="row g-4 justify-content-between mb-5">
            <!-- Col 1: Brand & Bio -->
            <div class="col-lg-4 col-md-6">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <img src="<?= asset_url('images/logo-smm-brand.png') ?>" alt="Logo PT SMM" style="height: 36px; width: auto; object-fit: contain; background: #ffffff; border-radius: 8px; padding: 3px;">
                    <span class="fw-bold text-white fs-5" style="letter-spacing: -0.02em;">
                        PT Shafa Marwa Multiguna
                    </span>
                </div>
                <p class="small text-secondary mb-4" style="line-height: 1.6; max-width: 320px;">
                    <?= e($company['deskripsi']) ?>
                </p>
                <div class="d-flex gap-2">
                    <a href="https://tiktok.com" target="_blank" class="btn btn-dark btn-sm rounded-circle d-flex align-items-center justify-content-center text-white-50 p-0" style="width: 34px; height: 34px; background: #222;" title="TikTok">
                        <i class="fab fa-tiktok"></i>
                    </a>
                    <a href="https://instagram.com" target="_blank" class="btn btn-dark btn-sm rounded-circle d-flex align-items-center justify-content-center text-white-50 p-0" style="width: 34px; height: 34px; background: #222;" title="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $company['no_telepon']) ?>" target="_blank" class="btn btn-dark btn-sm rounded-circle d-flex align-items-center justify-content-center text-white-50 p-0" style="width: 34px; height: 34px; background: #222;" title="WhatsApp">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                </div>
            </div>

            <!-- Col 2: Navigasi -->
            <div class="col-lg-2 col-md-3 col-6">
                <h6 class="text-white fw-bold mb-3 small" style="letter-spacing: 0.05em; text-transform: uppercase;">Menu</h6>
                <ul class="list-unstyled small d-flex flex-column gap-2 mb-0">
                    <li><a href="<?= base_url('index.php') ?>" class="text-decoration-none text-secondary text-hover-white">Beranda</a></li>
                    <li><a href="<?= base_url('frontend/pages/katalog.php') ?>" class="text-decoration-none text-secondary text-hover-white">Katalog Produk</a></li>
                    <li><a href="<?= base_url('frontend/pages/profil.php') ?>" class="text-decoration-none text-secondary text-hover-white">Tentang Kami</a></li>
                    <li><a href="<?= base_url('frontend/pages/lowongan.php') ?>" class="text-decoration-none text-secondary text-hover-white">Peluang Karir</a></li>
                    <li><a href="<?= base_url('frontend/pages/kontak.php') ?>" class="text-decoration-none text-secondary text-hover-white">Hubungi Kami</a></li>
                </ul>
            </div>

            <!-- Col 3: Kategori -->
            <div class="col-lg-3 col-md-3 col-6">
                <h6 class="text-white fw-bold mb-3 small" style="letter-spacing: 0.05em; text-transform: uppercase;">Kategori</h6>
                <ul class="list-unstyled small d-flex flex-column gap-2 mb-0">
                    <li><a href="<?= base_url('frontend/pages/katalog.php?kategori=3') ?>" class="text-decoration-none text-secondary text-hover-white">Herbal Alami & Madu</a></li>
                    <li><a href="<?= base_url('frontend/pages/katalog.php?kategori=1') ?>" class="text-decoration-none text-secondary text-hover-white">Pupuk & Pertanian</a></li>
                    <li><a href="<?= base_url('frontend/pages/katalog.php?kategori=2') ?>" class="text-decoration-none text-secondary text-hover-white">Nutrisi Perikanan</a></li>
                    <li><a href="<?= base_url('frontend/pages/katalog.php?kategori=4') ?>" class="text-decoration-none text-secondary text-hover-white">Kopi Nusantara</a></li>
                    <li><a href="<?= base_url('frontend/pages/katalog.php?kategori=5') ?>" class="text-decoration-none text-secondary text-hover-white">Vitamin Keluarga</a></li>
                </ul>
            </div>

            <!-- Col 4: Kontak Singkat -->
            <div class="col-lg-3 col-md-6">
                <h6 class="text-white fw-bold mb-3 small" style="letter-spacing: 0.05em; text-transform: uppercase;">Kantor Operasional</h6>
                <p class="small text-secondary mb-2">
                    <i class="fas fa-map-marker-alt me-2 text-white-50"></i><?= e($company['alamat']) ?>
                </p>
                <p class="small text-secondary mb-2">
                    <i class="fas fa-phone me-2 text-white-50"></i><?= e($company['no_telepon']) ?>
                </p>
                <p class="small text-secondary mb-3">
                    <i class="fas fa-envelope me-2 text-white-50"></i><?= e($company['email']) ?>
                </p>
                <a href="<?= base_url('frontend/auth/login.php') ?>" class="btn btn-outline-light btn-sm rounded-pill px-3 py-1 text-white-50 border-secondary small" style="font-size:12px;">
                    Portal Akses Masuk
                </a>
            </div>
        </div>

        <!-- Bottom Divider & Copyright -->
        <div class="pt-4 border-top border-dark d-flex justify-content-between align-items-center flex-wrap gap-2 small text-secondary" style="font-size: 13px;">
            <div>
                &copy; <?= date('Y') ?> <strong>PT Shafa Marwa Multiguna</strong>. All rights reserved.
            </div>
            <div class="d-flex gap-3">
                <a href="<?= base_url('frontend/pages/kontak.php') ?>" class="text-decoration-none text-secondary">Bantuan</a>
                <span>&bull;</span>
                <a href="<?= base_url('frontend/pages/profil.php') ?>" class="text-decoration-none text-secondary">Kebijakan Privasi</a>
                <span>&bull;</span>
                <a href="<?= base_url('frontend/pages/profil.php') ?>" class="text-decoration-none text-secondary">Syarat & Ketentuan</a>
            </div>
        </div>
    </div>
</footer>

<style>
.text-hover-white:hover {
    color: #ffffff !important;
    transition: color 0.2s ease;
}
</style>

<!-- Bootstrap 5 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom Main JS -->
<script src="<?= asset_url('js/main.js') ?>?v=<?= filemtime(__DIR__ . '/../../assets/js/main.js') ?>"></script>
</body>
</html>

