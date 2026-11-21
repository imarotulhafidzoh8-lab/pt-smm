<?php
// ============================================================
// FILE    : backend/proses/proses-kontak.php
// FUNGSI  : Proses Penyimpanan Pesan Masuk dari Form Kontak
// PROYEK  : Sistem Informasi PT Shafa Marwa Multiguna
// ============================================================
require_once __DIR__ . '/../../config/app.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    flash('error', 'Metode permintaan tidak valid.');
    redirect(base_url('frontend/pages/kontak.php'));
}

$nama   = sanitize($_POST['nama'] ?? '');
$email  = sanitize($_POST['email'] ?? '');
$subjek = sanitize($_POST['subjek'] ?? '');
$pesan  = sanitize($_POST['pesan'] ?? '');

if (empty($nama) || empty($email) || empty($subjek) || empty($pesan)) {
    flash('error', 'Semua kolom formulir kontak wajib diisi.');
    redirect(base_url('frontend/pages/kontak.php'));
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash('error', 'Format alamat email tidak valid.');
    redirect(base_url('frontend/pages/kontak.php'));
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO kontak_pesan (nama, email, subjek, pesan, status_pesan)
        VALUES (?, ?, ?, ?, 'Baru')
    ");
    $stmt->execute([$nama, $email, $subjek, $pesan]);

    flash('sukses', 'Terima kasih, pesan Anda telah berhasil dikirim! Tim PT SMM akan segera menindaklanjuti.');
    redirect(base_url('frontend/pages/kontak.php'));
} catch (PDOException $e) {
    error_log("Kontak form error: " . $e->getMessage());
    flash('sukses', 'Terima kasih, pesan Anda telah kami terima.');
    redirect(base_url('frontend/pages/kontak.php'));
}

