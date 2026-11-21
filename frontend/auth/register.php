<?php
// ============================================================
// FILE    : frontend/auth/register.php
// FUNGSI  : Halaman Registrasi Akun Pengguna Baru
// PROYEK  : Sistem Informasi PT Shafa Marwa Multiguna
// ============================================================
require_once __DIR__ . '/../../config/app.php';

if (isset($_SESSION['id_user']) && !empty($_SESSION['id_user'])) {
    redirect(base_url('index.php'));
}

$page_title = 'Daftar Akun Pengguna';
$flash = get_flash();
$old = $_SESSION['old_register'] ?? [];
unset($_SESSION['old_register']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?> - <?= APP_NAME ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <link rel="stylesheet" href="<?= asset_url('css/style.css') ?>">
    <link rel="stylesheet" href="<?= asset_url('css/responsive.css') ?>">
<style>
/* ================== UNIFIED REGISTER (MATCHING LOGIN DESIGN) ================== */
* {
    box-sizing: border-box;
}

body {
    background-color: #ffffff;
    font-family: 'Plus Jakarta Sans', sans-serif;
    color: #0f172a;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0;
    padding: 30px 16px;
}

/* Card Container */
.auth-card-container {
    width: 100%;
    max-width: 1020px;
    background: #ffffff;
    border-radius: 28px;
    overflow: hidden;
    border: 1px solid #edf2f7;
    box-shadow: 0 20px 60px -15px rgba(15, 23, 42, 0.08), 0 2px 10px rgba(15, 23, 42, 0.03);
}

/* Left Column: Full-Bleed Image matching login */
.auth-visual-side {
    position: relative;
    width: 100%;
    height: 100%;
    min-height: 600px;
    background: linear-gradient(175deg, rgba(10, 31, 56, 0.40) 0%, rgba(10, 31, 56, 0.88) 100%),
                url('<?= asset_url("images/smm-store.webp") ?>') center center / cover no-repeat;
    padding: 42px 38px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    color: #ffffff;
}

.auth-visual-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255, 255, 255, 0.22);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.35);
    padding: 6px 14px;
    border-radius: 999px;
    font-size: 11.5px;
    font-weight: 700;
    color: #ffffff;
    width: fit-content;
}

.auth-visual-heading {
    font-size: 1.85rem;
    font-weight: 800;
    line-height: 1.25;
    letter-spacing: -0.025em;
    color: #ffffff;
    margin-bottom: 12px;
}

.auth-visual-desc {
    font-size: 13px;
    line-height: 1.65;
    color: rgba(255, 255, 255, 0.85);
    margin-bottom: 20px;
    max-width: 380px;
}

.auth-visual-info-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 14px;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.16);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.25);
    color: #ffffff;
    font-size: 12px;
}

/* Right Column: Register Form */
.auth-form-side {
    padding: 38px 42px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    height: 100%;
}

.auth-brand-lockup {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 14px;
    text-decoration: none;
}

.auth-brand-lockup img {
    height: 32px;
    width: auto;
    object-fit: contain;
}

.auth-brand-name {
    font-size: 18px;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -0.02em;
}

.auth-form-title {
    font-size: 1.65rem;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -0.025em;
    margin-bottom: 4px;
}

.auth-form-sub {
    font-size: 13px;
    color: #64748b;
    margin-bottom: 1.35rem;
}

/* Form Fields */
.auth-field-label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: #334155;
    margin-bottom: 4px;
}

.auth-field-group {
    position: relative;
    margin-bottom: 12px;
}

.auth-field-input {
    width: 100%;
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 12px;
    padding: 10px 14px;
    font-size: 13px;
    color: #0f172a;
    transition: all 0.2s ease;
}

.auth-field-input:focus {
    background: #ffffff;
    border-color: #1e3a5f;
    box-shadow: 0 0 0 4px rgba(30, 58, 95, 0.08);
    outline: none;
}

.auth-field-input::placeholder {
    color: #94a3b8;
    font-size: 12.5px;
}

/* Primary Button */
.auth-btn-submit {
    background: #1e3a5f;
    color: #ffffff;
    border: none;
    border-radius: 12px;
    padding: 11px 20px;
    font-size: 14px;
    font-weight: 700;
    width: 100%;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 6px 18px rgba(30, 58, 95, 0.22);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-top: 4px;
    margin-bottom: 12px;
}

.auth-btn-submit:hover {
    background: #142a45;
    transform: translateY(-1px);
    box-shadow: 0 8px 22px rgba(30, 58, 95, 0.30);
    color: #ffffff;
}

/* Divider */
.auth-divider-line {
    display: flex;
    align-items: center;
    margin: 8px 0 12px;
}

.auth-divider-line::before,
.auth-divider-line::after {
    content: '';
    flex: 1;
    border-bottom: 1px solid #eef2f6;
}

.auth-divider-line span {
    padding: 0 12px;
    font-size: 12px;
    color: #94a3b8;
}

/* Google Button */
.auth-btn-google {
    border: 1.5px solid #e2e8f0;
    background: #ffffff;
    color: #334155;
    border-radius: 12px;
    padding: 10px 16px;
    font-size: 13px;
    font-weight: 600;
    width: 100%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    text-decoration: none;
    transition: all 0.15s ease;
    margin-bottom: 1.25rem;
}

.auth-btn-google:hover {
    background: #ffffff;
    border-color: #1e3a5f;
    color: #0f172a;
    box-shadow: 0 2px 8px rgba(30, 58, 95, 0.08);
}

/* Responsive Mobile Rules */
@media (max-width: 767px) {
    body {
        padding: 16px 12px;
        background: #ffffff;
    }
    .auth-card-container {
        max-width: 480px;
        border-radius: 20px;
        box-shadow: 0 8px 25px rgba(15, 23, 42, 0.08);
        border: 1px solid #e2e8f0;
    }
    .auth-form-side {
        padding: 28px 18px;
    }
    .auth-form-title {
        font-size: 1.45rem;
    }
}
</style>
</head>
<body>

<!-- Outer Card Container -->
<div class="auth-card-container">
    <div class="row g-0 align-items-stretch">
        
        <!-- Left Column: Full-Bleed Image (Identical to Login) -->
        <div class="col-md-5 col-lg-5 d-none d-md-flex">
            <div class="auth-visual-side">
                
                <!-- Top Badge -->
                <div class="auth-visual-badge">
                    <i class="fas fa-certificate text-warning"></i>
                    <span>PT Shafa Marwa Multiguna</span>
                </div>

                <!-- Bottom Captions -->
                <div>
                    <h2 class="auth-visual-heading">
                        Pusat Distribusi Komoditas &amp; Logistik Terpadu.
                    </h2>
                    <p class="auth-visual-desc">
                        Akses katalog produk resmi, lacak status pengiriman real-time, dan kelola transaksi bisnis dengan mudah dalam satu platform terpercaya.
                    </p>
                    <div class="auth-visual-info-pill">
                        <i class="fas fa-location-dot text-warning"></i>
                        <span>SMM Store &amp; Lounge &bull; Way Halim, Bandar Lampung</span>
                    </div>
                </div>

            </div>
        </div>

        <!-- Right Column: Clean White Register Form -->
        <div class="col-md-7 col-lg-7 d-flex align-items-center">
            <div class="auth-form-side w-100">
                
                <!-- Center Header -->
                <div class="text-center">
                    <a href="<?= base_url('index.php') ?>" class="auth-brand-lockup">
                        <img src="<?= asset_url('images/logo-smm-brand.png') ?>" alt="Logo PT SMM">
                        <span class="auth-brand-name">PT Shafa Marwa</span>
                    </a>
                    <h1 class="auth-form-title">Daftar Akun Baru</h1>
                    <p class="auth-form-sub">Lengkapi formulir di bawah untuk membuat akun</p>
                </div>

                <!-- Flash Alert (if any) -->
                <?php if ($flash): ?>
                    <?php
                    $alert_class = 'alert-info';
                    $alert_icon  = 'fa-info-circle';
                    if ($flash['tipe'] === 'sukses' || $flash['tipe'] === 'success') {
                        $alert_class = 'alert-success';
                        $alert_icon  = 'fa-check-circle';
                    } elseif ($flash['tipe'] === 'error' || $flash['tipe'] === 'danger') {
                        $alert_class = 'alert-danger';
                        $alert_icon  = 'fa-exclamation-triangle';
                    }
                    ?>
                    <div class="alert <?= $alert_class ?> alert-dismissible fade show d-flex align-items-center small py-2 px-3 rounded-3 mb-3" style="font-size: 11.5px;" role="alert">
                        <i class="fas <?= $alert_icon ?> me-2"></i>
                        <div><?= e($flash['pesan']) ?></div>
                        <button type="button" class="btn-close shadow-none py-2" data-bs-dismiss="alert"></button>
                    </div>
                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        if (typeof Swal !== 'undefined') {
                            const iconType = '<?= ($flash['tipe'] === 'sukses' || $flash['tipe'] === 'success') ? 'success' : (($flash['tipe'] === 'error' || $flash['tipe'] === 'danger') ? 'error' : 'info') ?>';
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: iconType,
                                title: <?= json_encode($flash['pesan']) ?>,
                                showConfirmButton: false,
                                timer: 3500,
                                timerProgressBar: true
                            });
                        }
                    });
                    </script>
                <?php endif; ?>

                <!-- Form Register -->
                <form action="<?= base_url('backend/auth/proses-register.php') ?>" method="POST">
                    
                    <!-- Nama Lengkap -->
                    <div class="auth-field-group">
                        <label class="auth-field-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="nama_lengkap" class="auth-field-input" placeholder="Contoh: Budi Santoso" value="<?= e($old['nama_lengkap'] ?? '') ?>" required autofocus>
                    </div>

                    <!-- Email & No HP -->
                    <div class="row g-2 mb-2">
                        <div class="col-sm-6">
                            <label class="auth-field-label">Email Aktif <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="auth-field-input" placeholder="budi@email.com" value="<?= e($old['email'] ?? '') ?>" required>
                        </div>
                        <div class="col-sm-6">
                            <label class="auth-field-label">Nomor WhatsApp / HP <span class="text-danger">*</span></label>
                            <input type="tel" name="no_hp" class="auth-field-input" placeholder="081234567890" value="<?= e($old['no_hp'] ?? '') ?>" required>
                        </div>
                    </div>

                    <!-- Password & Konfirmasi -->
                    <div class="row g-2 mb-2">
                        <div class="col-sm-6">
                            <label class="auth-field-label">Kata Sandi (Min. 6) <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="auth-field-input" placeholder="Minimal 6 karakter" required>
                        </div>
                        <div class="col-sm-6">
                            <label class="auth-field-label">Ulangi Kata Sandi <span class="text-danger">*</span></label>
                            <input type="password" name="konfirmasi_password" class="auth-field-input" placeholder="Ketik ulang password" required>
                        </div>
                    </div>

                    <!-- Alamat Pengiriman -->
                    <div class="auth-field-group mb-3">
                        <label class="auth-field-label">Alamat Pengiriman Default <span class="text-muted fw-normal">(Opsional)</span></label>
                        <textarea name="alamat" class="auth-field-input" rows="2" style="resize: none;" placeholder="Nama jalan, RT/RW, kelurahan, kecamatan, kota..."><?= e($old['alamat'] ?? '') ?></textarea>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="auth-btn-submit">
                        <span>Buat Akun Sekarang</span>
                        <i class="fas fa-arrow-right" style="font-size: 12px;"></i>
                    </button>
                </form>

                <!-- Divider -->
                <div class="auth-divider-line">
                    <span>Atau daftar cepat dengan</span>
                </div>

                <!-- Google Sign-in Button -->
                <a href="<?= base_url('frontend/auth/google-login.php') ?>" class="auth-btn-google">
                    <svg width="18" height="18" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                        <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                        <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                        <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                        <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                    </svg>
                    <span>Daftar dengan Google</span>
                </a>

                <!-- Footer Links -->
                <div class="text-center" style="font-size: 12.5px;">
                    <p class="text-muted mb-1">
                        Sudah memiliki akun? <a href="<?= base_url('frontend/auth/login.php') ?>" class="fw-bold text-dark text-decoration-none">Masuk di Sini</a>
                    </p>
                    <a href="<?= base_url('index.php') ?>" class="text-muted text-decoration-none" style="font-size: 12px;">
                        <i class="fas fa-arrow-left me-1"></i> Kembali ke Beranda
                    </a>
                </div>

            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
