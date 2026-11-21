<?php
// ============================================================
// FILE    : backend/pengguna/proses-profil.php
// FUNGSI  : Proses Pembaruan Profil Akun Pengguna
// PROYEK  : Sistem Informasi PT Shafa Marwa Multiguna
// ============================================================
require_once __DIR__ . '/../../config/app.php';
cek_pengguna();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    flash('error', 'Metode permintaan tidak valid.');
    redirect(base_url('frontend/pengguna/profil.php'));
}

$id_user      = (int)$_SESSION['id_user'];
$nama_lengkap = sanitize($_POST['nama_lengkap'] ?? '');
$no_hp        = sanitize($_POST['no_hp'] ?? '');
$alamat       = sanitize($_POST['alamat'] ?? '');
$password_baru = $_POST['password_baru'] ?? '';

if (empty($nama_lengkap) || mb_strlen($nama_lengkap) < 3) {
    flash('error', 'Nama lengkap minimal 3 karakter.');
    redirect(base_url('frontend/pengguna/profil.php'));
}

try {
    if (!empty($password_baru)) {
        if (strlen($password_baru) < 6) {
            flash('error', 'Kata sandi baru minimal 6 karakter.');
            redirect(base_url('frontend/pengguna/profil.php'));
        }
        $hash = password_hash($password_baru, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("
            UPDATE users
            SET nama_lengkap = ?, no_hp = ?, alamat = ?, password = ?
            WHERE id_user = ?
        ");
        $stmt->execute([$nama_lengkap, $no_hp, $alamat, $hash, $id_user]);
    } else {
        $stmt = $pdo->prepare("
            UPDATE users
            SET nama_lengkap = ?, no_hp = ?, alamat = ?
            WHERE id_user = ?
        ");
        $stmt->execute([$nama_lengkap, $no_hp, $alamat, $id_user]);
    }

    // Perbarui session
    $_SESSION['nama_lengkap'] = $nama_lengkap;
    $_SESSION['no_hp']        = $no_hp;
    $_SESSION['alamat']       = $alamat;

    flash('sukses', 'Profil akun Anda berhasil diperbarui.');
} catch (PDOException $e) {
    error_log("Update profil error: " . $e->getMessage());
    flash('error', 'Gagal memperbarui profil.');
}

redirect(base_url('frontend/pengguna/profil.php'));

