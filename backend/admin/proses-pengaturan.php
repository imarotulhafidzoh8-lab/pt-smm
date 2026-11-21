<?php
// ============================================================
// FILE    : backend/admin/proses-pengaturan.php
// FUNGSI  : Handler Pembaruan Data Profil Perusahaan
// PROYEK  : Sistem Informasi PT Shafa Marwa Multiguna
// ============================================================
require_once __DIR__ . '/../../config/app.php';
cek_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    flash('error', 'Metode permintaan tidak valid.');
    redirect(base_url('frontend/admin/pengaturan/profil-perusahaan.php'));
}

$nama_perusahaan = sanitize($_POST['nama_perusahaan'] ?? '');
$deskripsi       = sanitize($_POST['deskripsi'] ?? '');
$alamat          = sanitize($_POST['alamat'] ?? '');
$email           = sanitize($_POST['email'] ?? '');
$no_telepon      = sanitize($_POST['no_telepon'] ?? '');
$website         = sanitize($_POST['website'] ?? '');

if (empty($nama_perusahaan)) {
    flash('error', 'Nama perusahaan tidak boleh kosong.');
    redirect(base_url('frontend/admin/pengaturan/profil-perusahaan.php'));
}

try {
    // Cek apakah ada record di profil_perusahaan
    $stmt = $pdo->query("SELECT id_profil FROM profil_perusahaan ORDER BY id_profil ASC LIMIT 1");
    $profil = $stmt->fetch();

    if ($profil) {
        $stmt_update = $pdo->prepare("
            UPDATE profil_perusahaan 
            SET nama_perusahaan = ?, deskripsi = ?, alamat = ?, email = ?, no_telepon = ?, website = ?
            WHERE id_profil = ?
        ");
        $stmt_update->execute([
            $nama_perusahaan,
            $deskripsi,
            $alamat,
            $email,
            $no_telepon,
            $website,
            $profil['id_profil']
        ]);
    } else {
        $stmt_insert = $pdo->prepare("
            INSERT INTO profil_perusahaan (nama_perusahaan, deskripsi, alamat, email, no_telepon, website)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt_insert->execute([$nama_perusahaan, $deskripsi, $alamat, $email, $no_telepon, $website]);
    }

    flash('sukses', 'Profil informasi perusahaan PT SMM berhasil diperbarui.');
} catch (PDOException $e) {
    error_log("Pengaturan profil error: " . $e->getMessage());
    flash('error', 'Gagal memperbarui profil: ' . $e->getMessage());
}

redirect(base_url('frontend/admin/pengaturan/profil-perusahaan.php'));

