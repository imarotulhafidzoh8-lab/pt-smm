<?php
// ============================================================
// FILE    : frontend/pages/kontak.php
// FUNGSI  : Halaman Hubungi Kami PT SMM
// PROYEK  : Sistem Informasi PT Shafa Marwa Multiguna
// ============================================================
require_once __DIR__ . '/../../config/app.php';

$current_page = 'kontak';
$page_title   = 'Hubungi Kami';

catat_pengunjung($pdo, 'kontak');
$company      = get_company_profile($pdo);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<style>
@media (max-width: 767.98px) {
    .contact-section { padding-top: 2rem !important; padding-bottom: 2rem !important; }
    .contact-section > .container { padding-left: 16px; padding-right: 16px; }
    .contact-section > .container.py-2 { padding-top: 0 !important; padding-bottom: 0 !important; }
    .contact-section .row { --bs-gutter-x: .85rem; --bs-gutter-y: 1rem; }
    .contact-card { padding: 1rem !important; border-radius: 16px !important; }
    .contact-card h3 { font-size: 1.15rem; line-height: 1.35; }
    .contact-card p { overflow-wrap: anywhere; }
    .contact-card .form-control { min-height: 44px; font-size: 16px; }
    .contact-card textarea.form-control { min-height: 130px; }
}

@media (max-width: 380px) {
    .contact-section > .container { padding-left: 13px; padding-right: 13px; }
    .contact-card { padding: .85rem !important; }
}
</style>

<div class="page-hero-banner contact-hero-banner">
    <div class="container">
        <div class="row align-items-center page-hero-layout">
            <div class="col-lg-7 page-hero-content">
                <div class="page-hero-breadcrumb">
                    <a href="<?= base_url('index.php') ?>">Beranda</a>
                    <i class="fas fa-chevron-right" style="font-size: 9px;"></i>
                    <span>Kontak</span>
                </div>
                <div class="page-hero-kicker"><i class="fas fa-headset"></i> Layanan &amp; Informasi</div>
                <h1 class="page-hero-title">Hubungi Tim PT SMM</h1>
                <p class="page-hero-lead">Kami siap membantu pertanyaan seputar produk, kemitraan, promosi live streaming, maupun konfirmasi pesanan.</p>
            </div>
            <div class="col-lg-5 page-hero-visual">
                <div class="page-hero-visual-card">
                    <span class="page-hero-visual-icon"><i class="fas fa-comments"></i></span>
                    <h2 class="page-hero-visual-title">Kami Siap Terhubung</h2>
                    <p class="page-hero-visual-copy">Pilih kanal resmi yang paling nyaman untuk menghubungi tim layanan PT SMM.</p>
                    <div class="page-hero-visual-tags">
                        <span><i class="fab fa-whatsapp"></i> WhatsApp</span>
                        <span><i class="fas fa-envelope"></i> Email Resmi</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="py-5 bg-white contact-section">
    <div class="container py-2">
        <div class="row g-5">
            <!-- Left Info Column -->
            <div class="col-lg-5">
                <div class="p-4 p-md-5 bg-white border border-light-subtle rounded-4 h-100 shadow-xs contact-card">
                    <span class="badge bg-light text-dark border rounded-pill px-3 py-1 mb-2 fw-normal" style="font-size: 12px; letter-spacing: 0.5px;">
                        INFORMASI KOMUNIKASI
                    </span>
                    <h3 class="fw-bold text-dark mb-3" style="letter-spacing: -0.02em;">Terhubung Bersama Tim Layanan PT SMM</h3>
                    <p class="text-muted small mb-4" style="line-height: 1.7;">
                        Silakan hubungi kami untuk pertanyaan produk, konsultasi kemitraan promosi live streaming, atau konfirmasi pesanan Anda.
                    </p>

                    <div class="d-flex flex-column gap-3 mb-4">
                        <div class="d-flex align-items-start gap-3 p-3 bg-light rounded-3 border border-light-subtle">
                            <div class="d-flex align-items-center justify-content-center bg-white border border-light-subtle text-dark rounded-circle flex-shrink-0" style="width: 40px; height: 40px;">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <strong class="text-dark small d-block mb-1">Alamat Kantor & Studio</strong>
                                <p class="text-muted small mb-0"><?= e($company['alamat']) ?></p>
                            </div>
                        </div>

                        <div class="d-flex align-items-start gap-3 p-3 bg-light rounded-3 border border-light-subtle">
                            <div class="d-flex align-items-center justify-content-center bg-white border border-light-subtle text-dark rounded-circle flex-shrink-0" style="width: 40px; height: 40px;">
                                <i class="fas fa-phone-alt"></i>
                            </div>
                            <div>
                                <strong class="text-dark small d-block mb-1">Telepon & WhatsApp</strong>
                                <p class="text-muted small mb-0"><?= e($company['no_telepon']) ?></p>
                                <small class="text-muted" style="font-size: 11px;">Senin - Sabtu: 08.00 - 17.00 WIB</small>
                            </div>
                        </div>

                        <div class="d-flex align-items-start gap-3 p-3 bg-light rounded-3 border border-light-subtle">
                            <div class="d-flex align-items-center justify-content-center bg-white border border-light-subtle text-dark rounded-circle flex-shrink-0" style="width: 40px; height: 40px;">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div>
                                <strong class="text-dark small d-block mb-1">Email Resmi</strong>
                                <p class="text-muted small mb-0"><?= e($company['email']) ?></p>
                                <small class="text-muted" style="font-size: 11px;">Respon dalam 1x24 jam kerja</small>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 bg-white border border-light-subtle rounded-3">
                        <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fab fa-whatsapp text-success fs-3"></i>
                                <div>
                                    <strong class="text-dark small d-block">Butuh respon kilat?</strong>
                                    <span class="text-muted" style="font-size: 11px;">Customer Care WhatsApp</span>
                                </div>
                            </div>
                            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $company['no_telepon']) ?>" target="_blank" class="btn btn-outline-dark rounded-pill btn-sm px-3">
                                Chat Sekarang <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Form Column -->
            <div class="col-lg-7">
                <div class="p-4 p-md-5 bg-white border border-light-subtle rounded-4 shadow-xs contact-card">
                    <span class="badge bg-light text-dark border rounded-pill px-3 py-1 mb-2 fw-normal" style="font-size: 12px; letter-spacing: 0.5px;">
                        FORMULIR PESAN
                    </span>
                    <h3 class="fw-bold text-dark mb-3" style="letter-spacing: -0.02em;">Kirimkan Pesan atau Pertanyaan</h3>
                    <p class="text-muted small mb-4">
                        Isi formulir di bawah ini dan perwakilan tim kami akan segera menghubungi Anda melalui email atau telepon.
                    </p>

                    <form action="<?= base_url('backend/proses/proses-kontak.php') ?>" method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-dark">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" name="nama" class="form-control" placeholder="Nama lengkap Anda" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-dark">Alamat Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" placeholder="nama@email.com" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-semibold text-dark">Subjek Pesan <span class="text-danger">*</span></label>
                                <input type="text" name="subjek" class="form-control" placeholder="Contoh: Kemitraan Live Streaming / Pertanyaan Produk" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-semibold text-dark">Isi Pesan <span class="text-danger">*</span></label>
                                <textarea name="pesan" class="form-control" rows="5" placeholder="Tuliskan rincian pesan, pertanyaan, atau penawaran kerjasama Anda di sini..." required></textarea>
                            </div>

                            <div class="col-12 pt-2">
                                <button type="submit" class="btn rounded-pill px-4 py-2 w-100 d-inline-flex align-items-center justify-content-center gap-2 text-white" style="background:#1e3a5f; border-color:#1e3a5f; box-shadow: 0 4px 12px rgba(30, 58, 95, 0.25);">
                                    <span>Kirim Pesan Sekarang</span>
                                    <i class="fas fa-arrow-right" style="transform: rotate(-45deg); font-size: 13px;"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

