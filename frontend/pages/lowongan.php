<?php
// ============================================================
// FILE    : frontend/pages/lowongan.php
// FUNGSI  : Halaman Informasi Lowongan Karir PT SMM
// PROYEK  : Sistem Informasi PT Shafa Marwa Multiguna
// ============================================================
require_once __DIR__ . '/../../config/app.php';

$current_page = 'lowongan';
$page_title   = 'Lowongan Karir';

catat_pengunjung($pdo, 'lowongan');
$company      = get_company_profile($pdo);

// Ambil Seluruh Lowongan Dibuka
$lowongan_list = [];
try {
    $stmt = $pdo->query("
        SELECT * FROM lowongan
        WHERE status_lowongan = 'Dibuka'
        ORDER BY dibuat_pada DESC
    ");
    $lowongan_list = $stmt->fetchAll();
} catch (PDOException $e) {
    $lowongan_list = [];
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<style>
@media (max-width: 767.98px) {
    .page-header { padding: 14px 0 12px; }
    .page-header > .container,
    .career-section > .container { padding-left: 16px; padding-right: 16px; }
    .page-header h1 { font-size: clamp(1.15rem, 5.5vw, 1.45rem); line-height: 1.25; }
    .career-section { padding-top: 2rem !important; padding-bottom: 2rem !important; }
    .career-section > .container.py-2 { padding-top: 0 !important; padding-bottom: 0 !important; }
    .career-section .row { --bs-gutter-x: .85rem; --bs-gutter-y: 1rem; }
    .career-intro { margin-bottom: 1.5rem !important; }
    .career-intro h2 { font-size: clamp(1.35rem, 6vw, 1.7rem); line-height: 1.25; }
    .career-intro p { font-size: .85rem; line-height: 1.6; }
    .career-card { padding: 1rem !important; border-radius: 16px !important; }
    .career-card h3 { font-size: 1.15rem !important; line-height: 1.3; overflow-wrap: anywhere; }
    .career-card p,
    .career-card div { overflow-wrap: anywhere; }
    .career-card .career-card-meta { align-items: flex-start !important; }
    .career-card .career-apply-row { align-items: stretch !important; }
    .career-card .career-apply-destination { flex: 1 1 100%; min-width: 0; }
    .career-card .career-apply-row > a { justify-content: center; width: 100%; }
    .career-guide { padding: 1rem !important; margin-top: 1.5rem !important; border-radius: 16px !important; }
    .career-guide-heading { align-items: flex-start !important; gap: 8px !important; margin-bottom: 1rem !important; }
    .career-guide-heading h4 { font-size: 1rem; line-height: 1.35; }
    .career-guide .row { --bs-gutter-y: .9rem; }
    .career-guide code { white-space: normal; overflow-wrap: anywhere; }
}

@media (max-width: 380px) {
    .page-header > .container,
    .career-section > .container { padding-left: 13px; padding-right: 13px; }
    .career-card, .career-guide { padding: .85rem !important; }
}
</style>

<div class="page-hero-banner career-hero-banner">
    <div class="container">
        <div class="row align-items-center page-hero-layout">
            <div class="col-lg-7 page-hero-content">
                <div class="page-hero-breadcrumb">
                    <a href="<?= base_url('index.php') ?>">Beranda</a>
                    <i class="fas fa-chevron-right" style="font-size: 9px;"></i>
                    <span>Karir</span>
                </div>
                <div class="page-hero-kicker"><i class="fas fa-briefcase"></i> Karir &amp; Pengembangan</div>
                <h1 class="page-hero-title">Tumbuh Bersama PT Shafa Marwa Multiguna</h1>
                <p class="page-hero-lead">Jelajahi peluang untuk berkembang bersama tim yang bergerak di bidang live streaming, pemasaran digital, dan distribusi produk.</p>
            </div>
            <div class="col-lg-5 page-hero-visual">
                <div class="page-hero-visual-card">
                    <span class="page-hero-visual-icon"><i class="fas fa-user-tie"></i></span>
                    <h2 class="page-hero-visual-title"><?= count($lowongan_list) ?> Posisi Terbuka</h2>
                    <p class="page-hero-visual-copy">Temukan posisi yang sesuai dan kirimkan berkas lamaran melalui email resmi perusahaan.</p>
                    <div class="page-hero-visual-tags">
                        <span><i class="fas fa-video"></i> Live Commerce</span>
                        <span><i class="fas fa-boxes-stacked"></i> Distribusi</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="py-5 bg-white career-section">
    <div class="container py-2">
        <div class="text-center mx-auto mb-5 career-intro" style="max-width: 680px;">
            <span class="badge bg-light text-dark border rounded-pill px-3 py-1 mb-2 fw-normal" style="font-size: 12px; letter-spacing: 0.5px;">
                BERGABUNG BERSAMA KAMI
            </span>
            <h2 class="fw-bold text-dark" style="letter-spacing: -0.02em;">Tumbuh Bersama PT Shafa Marwa Multiguna</h2>
            <p class="text-muted small">
                Kami mencari individu yang kreatif, bersemangat, dan siap berkembang di ekosistem digital live streaming, talent management, dan e-commerce modern.
            </p>
        </div>

        <div class="row g-4">
            <?php if (empty($lowongan_list)): ?>
                <div class="col-12">
                    <div class="p-5 text-center bg-white border border-light-subtle rounded-4 shadow-xs">
                        <i class="fas fa-briefcase fa-3x text-muted mb-3 opacity-50"></i>
                        <h4 class="fw-bold text-dark">Saat Ini Belum Ada Lowongan Terbuka</h4>
                        <p class="text-muted small mb-3">
                            Seluruh posisi saat ini telah terisi. Pantau terus halaman ini atau media sosial kami untuk informasi lowongan periode berikutnya.
                        </p>
                        <a href="<?= base_url('index.php') ?>" class="btn btn-outline-dark rounded-pill px-4">
                            Kembali ke Beranda
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($lowongan_list as $low): ?>
                    <div class="col-lg-6">
                        <div class="p-4 p-md-5 h-100 bg-white border border-light-subtle rounded-4 d-flex flex-column shadow-xs transition-hover career-card" style="transition: all 0.25s ease;">
                            <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2 career-card-meta">
                                <span class="badge text-white rounded-pill px-3 py-2 fw-medium" style="font-size: 12px; background: #1e3a5f;">
                                    <?= e($low['posisi']) ?>
                                </span>
                                <?php if (!empty($low['batas_lamaran'])): ?>
                                    <span class="badge bg-light text-muted border rounded-pill px-3 py-2" style="font-size: 11px;">
                                        <i class="fas fa-calendar-alt me-1"></i> Batas: <?= date('d M Y', strtotime($low['batas_lamaran'])) ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <h3 class="fw-bold text-dark mb-2 fs-4" style="letter-spacing: -0.02em;"><?= e($low['judul_lowongan']) ?></h3>
                            <p class="text-muted small mb-3">
                                <i class="fas fa-map-pin text-dark me-1"></i> Penempatan: <strong><?= e($low['lokasi'] ?? 'Way Halim, Bandar Lampung') ?></strong>
                            </p>

                            <div class="mb-3">
                                <div class="fw-bold text-dark small text-uppercase mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Tanggung Jawab:</div>
                                <p class="text-muted small" style="line-height: 1.7;">
                                    <?= nl2br(e($low['deskripsi'])) ?>
                                </p>
                            </div>

                            <?php if (!empty($low['kualifikasi'])): ?>
                                <div class="mb-4">
                                    <div class="fw-bold text-dark small text-uppercase mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Kualifikasi:</div>
                                    <div class="text-muted small" style="line-height: 1.7;">
                                        <?= nl2br(e($low['kualifikasi'])) ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2 career-apply-row">
                                <div class="career-apply-destination">
                                    <span class="text-muted small d-block">Kirim berkas lamaran ke</span>
                                    <strong class="text-dark small"><?= e($company['email']) ?></strong>
                                    <small class="text-muted d-block" style="font-size: 10px;">Tombol akan membuka aplikasi email Anda.</small>
                                </div>
                                <a href="mailto:<?= e($company['email']) ?>?subject=<?= rawurlencode('Lamaran Posisi ' . $low['posisi'] . ' - ' . $low['judul_lowongan']) ?>&amp;body=<?= rawurlencode("Yth. Tim Rekrutmen PT SMM,\n\nSaya bermaksud melamar untuk posisi " . $low['judul_lowongan'] . ".\n\nNama:\nNomor telepon:\n\nTerima kasih.") ?>" class="btn rounded-pill px-3 py-2 btn-sm d-inline-flex align-items-center gap-2 text-white" style="background: #1e3a5f; border-color: #1e3a5f; box-shadow: 0 4px 12px rgba(30, 58, 95, 0.25);">
                                    <i class="fas fa-envelope"></i>
                                    <span>Kirim Lamaran via Email</span>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- How to Apply Card -->
        <div class="p-4 p-md-5 bg-white border border-light-subtle rounded-4 shadow-xs mt-5 career-guide">
            <div class="d-flex align-items-center gap-2 mb-4 career-guide-heading">
                <span class="badge bg-light text-dark border rounded-pill px-3 py-1 fw-normal" style="font-size: 12px; letter-spacing: 0.5px;">PANDUAN</span>
                <h4 class="fw-bold text-dark mb-0" style="letter-spacing: -0.02em;">Cara Pengiriman Berkas Lamaran</h4>
            </div>
            <div class="row g-4 text-muted">
                <div class="col-md-4">
                    <div class="d-flex gap-3">
                        <div class="rounded-circle text-white fw-bold d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px; font-size: 13px; background: #1e3a5f;">1</div>
                        <div>
                            <strong class="text-dark d-block mb-1">Siapkan Dokumen</strong>
                            <p class="small mb-0">Siapkan CV terbaru, portofolio (jika ada), serta foto diri dalam format file PDF rapi.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex gap-3">
                        <div class="rounded-circle text-white fw-bold d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px; font-size: 13px; background: #1e3a5f;">2</div>
                        <div>
                            <strong class="text-dark d-block mb-1">Kirim Email Resmi</strong>
                            <p class="small mb-0">Tuliskan subjek: <code>Lamaran - [Nama Posisi] - [Nama Anda]</code> ke <strong><?= e($company['email']) ?></strong>.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex gap-3">
                        <div class="rounded-circle text-white fw-bold d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px; font-size: 13px; background: #1e3a5f;">3</div>
                        <div>
                            <strong class="text-dark d-block mb-1">Proses Seleksi</strong>
                            <p class="small mb-0">Kandidat yang lolos administrasi akan dipanggil wawancara dan audisi di studio Way Halim.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

