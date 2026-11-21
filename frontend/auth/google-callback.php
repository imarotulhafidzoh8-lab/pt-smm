<?php
// ============================================================
// FILE    : frontend/auth/google-callback.php
// FUNGSI  : Callback handler Google OAuth 2.0
// PROYEK  : Sistem Informasi PT Shafa Marwa Multiguna
// ============================================================
require_once __DIR__ . '/../../config/app.php';

if (GOOGLE_CLIENT_ID === '' || GOOGLE_CLIENT_SECRET === '') {
    error_log('[Google OAuth] Callback diterima tanpa konfigurasi kredensial.');
    flash('error', 'Login Google belum dikonfigurasi. Silakan gunakan email dan kata sandi, atau hubungi administrator.');
    redirect(base_url('frontend/auth/login.php'));
}

// --- 1. Validasi state (anti-CSRF) ---
$state_session = $_SESSION['google_oauth_state'] ?? '';
$state_param   = $_GET['state'] ?? '';
unset($_SESSION['google_oauth_state']);

if (empty($state_session) || !hash_equals($state_session, $state_param)) {
    flash('error', 'Permintaan login tidak valid (state mismatch). Silakan coba lagi.');
    redirect(base_url('frontend/auth/login.php'));
}

// --- 2. Cek apakah ada error dari Google ---
if (isset($_GET['error'])) {
    flash('error', 'Login Google dibatalkan atau terjadi kesalahan: ' . e($_GET['error']));
    redirect(base_url('frontend/auth/login.php'));
}

// --- 3. Ambil authorization code ---
$code = $_GET['code'] ?? '';
if (empty($code)) {
    flash('error', 'Kode otorisasi dari Google tidak diterima. Silakan coba lagi.');
    redirect(base_url('frontend/auth/login.php'));
}

// --- 4. Tukar code dengan access token ---
$token_url  = 'https://oauth2.googleapis.com/token';
$token_data = [
    'code'          => $code,
    'client_id'     => GOOGLE_CLIENT_ID,
    'client_secret' => GOOGLE_CLIENT_SECRET,
    'redirect_uri'  => GOOGLE_REDIRECT_URI,
    'grant_type'    => 'authorization_code',
];

$ch = curl_init($token_url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query($token_data),
    CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2,
]);
$token_response = curl_exec($ch);
$curl_error     = curl_error($ch);
curl_close($ch);

if ($curl_error) {
    error_log('Google OAuth token error: ' . $curl_error);
    flash('error', 'Gagal menghubungi server Google. Periksa koneksi internet server.');
    redirect(base_url('frontend/auth/login.php'));
}

$token_json = json_decode($token_response, true);

if (empty($token_json['access_token'])) {
    error_log('Google OAuth - tidak ada access_token: ' . $token_response);
    flash('error', 'Gagal mendapatkan token dari Google. Silakan coba lagi.');
    redirect(base_url('frontend/auth/login.php'));
}

$access_token = $token_json['access_token'];

// --- 5. Ambil data profil user dari Google ---
$userinfo_url = 'https://www.googleapis.com/oauth2/v2/userinfo';

$ch = curl_init($userinfo_url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $access_token],
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2,
]);
$userinfo_response = curl_exec($ch);
$curl_error        = curl_error($ch);
curl_close($ch);

if ($curl_error) {
    error_log('Google OAuth userinfo error: ' . $curl_error);
    flash('error', 'Gagal mengambil data profil dari Google.');
    redirect(base_url('frontend/auth/login.php'));
}

$google_user = json_decode($userinfo_response, true);

if (empty($google_user['id']) || empty($google_user['email'])) {
    error_log('Google OAuth - data user tidak lengkap: ' . $userinfo_response);
    flash('error', 'Data akun Google tidak lengkap. Pastikan akun Google Anda memiliki email yang terverifikasi.');
    redirect(base_url('frontend/auth/login.php'));
}

// --- 6. Proses login / registrasi otomatis ke database ---
$google_id  = $google_user['id'];
$email      = $google_user['email'];
$nama       = $google_user['name']          ?? $google_user['email'];
$avatar_url = $google_user['picture']       ?? null;

try {
    // Cari user berdasarkan google_id ATAU email
    $stmt = $pdo->prepare("
        SELECT id_user, nama_lengkap, email, role, status_akun, google_id, no_hp, alamat
        FROM users
        WHERE google_id = ? OR email = ?
        LIMIT 1
    ");
    $stmt->execute([$google_id, $email]);
    $user = $stmt->fetch();

    if ($user) {
        // --- USER SUDAH ADA ---

        // Cek status akun
        if (strcasecmp($user['status_akun'], 'Aktif') !== 0) {
            flash('error', 'Akun Anda sedang dinonaktifkan. Silakan hubungi Administrator.');
            redirect(base_url('frontend/auth/login.php'));
        }

        // Jika login via email biasa sebelumnya, hubungkan google_id-nya sekarang
        if (empty($user['google_id'])) {
            $stmt = $pdo->prepare("
                UPDATE users SET google_id = ?, avatar_url = ? WHERE id_user = ?
            ");
            $stmt->execute([$google_id, $avatar_url, $user['id_user']]);
        } else {
            // Update avatar jika berubah
            $stmt = $pdo->prepare("UPDATE users SET avatar_url = ? WHERE id_user = ?");
            $stmt->execute([$avatar_url, $user['id_user']]);
        }

    } else {
        // --- USER BARU: Daftar otomatis ---
        $stmt = $pdo->prepare("
            INSERT INTO users (nama_lengkap, email, google_id, avatar_url, password, role, status_akun)
            VALUES (?, ?, ?, ?, NULL, 'Pengguna', 'Aktif')
        ");
        $stmt->execute([$nama, $email, $google_id, $avatar_url]);

        $new_id = (int)$pdo->lastInsertId();

        // Ambil data user yang baru dibuat
        $stmt = $pdo->prepare("SELECT id_user, nama_lengkap, email, role, status_akun, no_hp, alamat FROM users WHERE id_user = ?");
        $stmt->execute([$new_id]);
        $user = $stmt->fetch();
    }

    // --- 7. Set session (sama persis dengan login biasa) ---
    session_regenerate_id(true);

    $_SESSION['id_user']      = (int)$user['id_user'];
    $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
    $_SESSION['email']        = $user['email'];
    $_SESSION['role']         = $user['role'];
    $_SESSION['no_hp']        = $user['no_hp'] ?? null;
    $_SESSION['alamat']       = $user['alamat'] ?? null;
    $_SESSION['avatar_url']   = $avatar_url;

    // --- 8. Redirect sesuai role ---
    if (strcasecmp($user['role'], 'Admin') === 0) {
        flash('sukses', 'Selamat datang, Administrator ' . $user['nama_lengkap'] . '! (Login via Google)');
        redirect(base_url('frontend/admin/dashboard.php'));
    } else {
        flash('sukses', 'Selamat datang, ' . $user['nama_lengkap'] . '! Anda berhasil masuk dengan akun Google.');
        redirect(base_url('frontend/pengguna/dashboard.php'));
    }

} catch (PDOException $e) {
    error_log('Google OAuth DB error: ' . $e->getMessage());
    flash('error', 'Terjadi kesalahan sistem saat memproses login Google. Silakan coba lagi.');
    redirect(base_url('frontend/auth/login.php'));
}

