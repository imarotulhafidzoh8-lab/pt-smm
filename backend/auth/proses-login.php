<?php
// ============================================================
// FILE    : backend/auth/proses-login.php
// FUNGSI  : Proses Autentikasi Login (Admin & Pengguna)
// PROYEK  : Sistem Informasi PT Shafa Marwa Multiguna
// ============================================================
require_once __DIR__ . '/../../config/app.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    flash('error', 'Metode permintaan tidak valid.');
    redirect(base_url('frontend/auth/login.php'));
}

$email    = sanitize($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

$_SESSION['old_login_email'] = $email;

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash('error', 'Silakan masukkan alamat email yang valid.');
    redirect(base_url('frontend/auth/login.php'));
}

if (empty($password)) {
    flash('error', 'Kata sandi wajib diisi.');
    redirect(base_url('frontend/auth/login.php'));
}

try {
    $stmt = $pdo->prepare("
        SELECT id_user, nama_lengkap, email, password, no_hp, alamat, role, status_akun
        FROM users
        WHERE email = ?
        LIMIT 1
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        flash('error', 'Email atau kata sandi tidak sesuai.');
        redirect(base_url('frontend/auth/login.php'));
    }

    if (strcasecmp($user['status_akun'], 'Aktif') !== 0) {
        flash('error', 'Akun Anda sedang dinonaktifkan. Silakan hubungi Administrator.');
        redirect(base_url('frontend/auth/login.php'));
    }

    if (!password_verify($password, $user['password'])) {
        flash('error', 'Email atau kata sandi tidak sesuai.');
        redirect(base_url('frontend/auth/login.php'));
    }

    // Login Sukses
    session_regenerate_id(true);

    $_SESSION['id_user']      = (int)$user['id_user'];
    $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
    $_SESSION['email']        = $user['email'];
    $_SESSION['role']         = $user['role'];
    $_SESSION['no_hp']        = $user['no_hp'];
    $_SESSION['alamat']       = $user['alamat'];

    unset($_SESSION['old_login_email']);

    if (strcasecmp($user['role'], 'Admin') === 0) {
        flash('sukses', 'Selamat datang kembali, Administrator ' . $user['nama_lengkap'] . '!');
        redirect(base_url('frontend/admin/dashboard.php'));
    } else {
        flash('sukses', 'Selamat datang kembali, ' . $user['nama_lengkap'] . '!');
        redirect(base_url('frontend/pengguna/dashboard.php'));
    }

} catch (PDOException $e) {
    error_log("Login error: " . $e->getMessage());
    flash('error', 'Terjadi gangguan sistem. Silakan coba kembali.');
    redirect(base_url('frontend/auth/login.php'));
}
