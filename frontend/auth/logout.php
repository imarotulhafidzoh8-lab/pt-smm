<?php
// ============================================================
// FILE    : frontend/auth/logout.php
// FUNGSI  : Logout Session
// PROYEK  : Sistem Informasi PT Shafa Marwa Multiguna
// ============================================================
require_once __DIR__ . '/../../config/app.php';

// Hapus seluruh data session
$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

session_destroy();

// Mulai session baru hanya untuk flash message
session_start();
flash('sukses', 'Anda telah berhasil keluar dari sistem.');
redirect(base_url('frontend/auth/login.php'));
