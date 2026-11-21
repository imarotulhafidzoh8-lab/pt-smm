<?php
// ============================================================
// FILE    : frontend/auth/google-login.php
// FUNGSI  : Inisiasi alur Google OAuth 2.0 Login
// PROYEK  : Sistem Informasi PT Shafa Marwa Multiguna
// ============================================================
require_once __DIR__ . '/../../config/app.php';

// Hindari mengirim permintaan kosong ke Google saat kredensial belum disetel.
if (GOOGLE_CLIENT_ID === '' || GOOGLE_CLIENT_SECRET === '') {
    error_log('[Google OAuth] Kredensial belum dikonfigurasi. Isi GOOGLE_CLIENT_ID dan GOOGLE_CLIENT_SECRET di file .env.');
    flash('error', 'Login Google belum dikonfigurasi. Silakan gunakan email dan kata sandi, atau hubungi administrator.');
    redirect(base_url('frontend/auth/login.php'));
}

// Jika sudah login, langsung redirect
if (isset($_SESSION['id_user']) && !empty($_SESSION['id_user'])) {
    if (strcasecmp($_SESSION['role'] ?? '', 'Admin') === 0) {
        redirect(base_url('frontend/admin/dashboard.php'));
    } else {
        redirect(base_url('frontend/pengguna/dashboard.php'));
    }
}

// Generate state token untuk mencegah CSRF
$state = bin2hex(random_bytes(16));
$_SESSION['google_oauth_state'] = $state;

// Parameter Google OAuth
$params = http_build_query([
    'client_id'     => GOOGLE_CLIENT_ID,
    'redirect_uri'  => GOOGLE_REDIRECT_URI,
    'response_type' => 'code',
    'scope'         => 'openid email profile',
    'state'         => $state,
    'access_type'   => 'online',
    'prompt'        => 'select_account', // selalu tampilkan pilihan akun Google
]);

$google_auth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . $params;

// Redirect ke halaman login Google
header('Location: ' . $google_auth_url);
exit;

