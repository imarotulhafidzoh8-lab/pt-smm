<?php
// ============================================================
// FILE    : backend/admin/proses-pengguna.php
// FUNGSI  : Handler Pengelolaan Akun Pengguna & Admin
// PROYEK  : Sistem Informasi PT Shafa Marwa Multiguna
// ============================================================
require_once __DIR__ . '/../../config/app.php';
cek_admin();

$aksi = $_POST['aksi'] ?? $_GET['aksi'] ?? '';

// --- AKSI: UBAH STATUS AKUN (Aktif / Nonaktif) ---
if ($aksi === 'status') {
    $id_user = (int)($_GET['id'] ?? 0);
    $status  = sanitize($_GET['set'] ?? '');

    if ($id_user <= 0 || !in_array($status, ['Aktif', 'Nonaktif'], true)) {
        flash('error', 'Parameter tidak valid.');
        redirect(base_url('frontend/admin/pengguna/index.php'));
    }

    // Cegah admin menonaktifkan akun sendiri
    if ($id_user === (int)$_SESSION['id_user']) {
        flash('error', 'Anda tidak dapat mengubah status akun Anda sendiri.');
        redirect(base_url('frontend/admin/pengguna/index.php'));
    }

    try {
        $stmt = $pdo->prepare("UPDATE users SET status_akun = ? WHERE id_user = ?");
        $stmt->execute([$status, $id_user]);

        flash('sukses', 'Status akun pengguna berhasil diubah menjadi "' . $status . '".');
    } catch (PDOException $e) {
        error_log("Ubah status user error: " . $e->getMessage());
        flash('error', 'Gagal memperbarui status akun.');
    }
    redirect(base_url('frontend/admin/pengguna/index.php'));
}

// --- AKSI: TAMBAH ADMIN BARU ---
if ($aksi === 'tambah_admin' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lengkap = sanitize($_POST['nama_lengkap'] ?? '');
    $email        = sanitize($_POST['email'] ?? '');
    $no_hp        = sanitize($_POST['no_hp'] ?? '');
    $password     = $_POST['password'] ?? '';

    if (empty($nama_lengkap) || empty($email) || empty($password) || strlen($password) < 6) {
        flash('error', 'Nama, email valid, dan password minimal 6 karakter wajib diisi.');
        redirect(base_url('frontend/admin/pengguna/index.php'));
    }

    try {
        $stmt = $pdo->prepare("SELECT id_user FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            flash('error', 'Email tersebut sudah terdaftar.');
            redirect(base_url('frontend/admin/pengguna/index.php'));
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("
            INSERT INTO users (nama_lengkap, email, password, no_hp, role, status_akun)
            VALUES (?, ?, ?, ?, 'Admin', 'Aktif')
        ");
        $stmt->execute([$nama_lengkap, $email, $hash, $no_hp]);

        flash('sukses', 'Akun Administrator baru berhasil didaftarkan.');
    } catch (PDOException $e) {
        error_log("Tambah admin error: " . $e->getMessage());
        flash('error', 'Gagal mendaftarkan akun admin: ' . $e->getMessage());
    }
    redirect(base_url('frontend/admin/pengguna/index.php'));
}

redirect(base_url('frontend/admin/pengguna/index.php'));

