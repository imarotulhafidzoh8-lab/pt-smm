<?php
// ============================================================
// FILE    : backend/auth/proses-register.php
// FUNGSI  : Proses Registrasi Akun Pelanggan (Pengguna)
// PROYEK  : Sistem Informasi PT Shafa Marwa Multiguna
// ============================================================
require_once __DIR__ . '/../../config/app.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    flash('error', 'Metode permintaan tidak valid.');
    redirect(base_url('frontend/auth/register.php'));
}

$nama_lengkap        = sanitize($_POST['nama_lengkap'] ?? '');
$email               = sanitize($_POST['email'] ?? '');
$no_hp               = sanitize($_POST['no_hp'] ?? '');
$alamat              = sanitize($_POST['alamat'] ?? '');
$password            = $_POST['password'] ?? '';
$konfirmasi_password = $_POST['konfirmasi_password'] ?? '';

$_SESSION['old_register'] = [
    'nama_lengkap' => $nama_lengkap,
    'email'        => $email,
    'no_hp'        => $no_hp,
    'alamat'       => $alamat
];

// Validasi
if (empty($nama_lengkap) || mb_strlen($nama_lengkap) < 3) {
    flash('error', 'Nama lengkap minimal 3 karakter.');
    redirect(base_url('frontend/auth/register.php'));
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash('error', 'Alamat email tidak valid.');
    redirect(base_url('frontend/auth/register.php'));
}

if (empty($no_hp) || !preg_match('/^[0-9+\-\s]{8,20}$/', $no_hp)) {
    flash('error', 'Nomor telepon/HP harus berupa format nomor yang valid (8-20 digit).');
    redirect(base_url('frontend/auth/register.php'));
}

if (empty($password) || strlen($password) < 6) {
    flash('error', 'Kata sandi minimal 6 karakter.');
    redirect(base_url('frontend/auth/register.php'));
}

if ($password !== $konfirmasi_password) {
    flash('error', 'Konfirmasi kata sandi tidak cocok.');
    redirect(base_url('frontend/auth/register.php'));
}

try {
    // Periksa apakah email sudah terdaftar
    $stmt = $pdo->prepare("SELECT id_user FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        flash('error', 'Alamat email sudah terdaftar. Silakan gunakan email lain atau masuk.');
        redirect(base_url('frontend/auth/register.php'));
    }

    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("
        INSERT INTO users (nama_lengkap, email, password, no_hp, alamat, role, status_akun)
        VALUES (?, ?, ?, ?, ?, 'Pengguna', 'Aktif')
    ");
    $stmt->execute([
        $nama_lengkap,
        $email,
        $hashed_password,
        $no_hp,
        !empty($alamat) ? $alamat : null
    ]);

    unset($_SESSION['old_register']);

    flash('sukses', 'Pendaftaran berhasil! Akun Anda telah aktif. Silakan masuk menggunakan email dan password Anda.');
    redirect(base_url('frontend/auth/login.php'));

} catch (PDOException $e) {
    error_log("Register error: " . $e->getMessage());
    flash('error', 'Terjadi kesalahan saat membuat akun. Silakan coba kembali.');
    redirect(base_url('frontend/auth/register.php'));
}
