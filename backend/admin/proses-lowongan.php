<?php
// ============================================================
// FILE    : backend/admin/proses-lowongan.php
// FUNGSI  : Handler CRUD Lowongan Karir PT SMM
// PROYEK  : Sistem Informasi PT Shafa Marwa Multiguna
// ============================================================
require_once __DIR__ . '/../../config/app.php';
cek_admin();

$aksi = $_POST['aksi'] ?? $_GET['aksi'] ?? '';

// --- AKSI: TAMBAH ---
if ($aksi === 'tambah' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul_lowongan  = sanitize($_POST['judul_lowongan'] ?? '');
    $posisi          = sanitize($_POST['posisi'] ?? '');
    $deskripsi       = sanitize($_POST['deskripsi'] ?? '');
    $kualifikasi     = sanitize($_POST['kualifikasi'] ?? '');
    $lokasi          = sanitize($_POST['lokasi'] ?? 'Bandar Lampung');
    $batas_lamaran   = !empty($_POST['batas_lamaran']) ? $_POST['batas_lamaran'] : null;
    $status_lowongan = sanitize($_POST['status_lowongan'] ?? 'Dibuka');

    if (empty($judul_lowongan) || empty($posisi) || empty($deskripsi)) {
        flash('error', 'Judul, posisi, dan deskripsi lowongan wajib diisi.');
        redirect(base_url('frontend/admin/lowongan/tambah.php'));
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO lowongan (judul_lowongan, posisi, deskripsi, kualifikasi, lokasi, batas_lamaran, status_lowongan)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $judul_lowongan,
            $posisi,
            $deskripsi,
            !empty($kualifikasi) ? $kualifikasi : null,
            $lokasi,
            $batas_lamaran,
            $status_lowongan
        ]);

        flash('sukses', 'Lowongan pekerjaan baru berhasil diterbitkan.');
        redirect(base_url('frontend/admin/lowongan/index.php'));
    } catch (PDOException $e) {
        error_log("Tambah lowongan error: " . $e->getMessage());
        flash('error', 'Gagal menambahkan lowongan: ' . $e->getMessage());
        redirect(base_url('frontend/admin/lowongan/tambah.php'));
    }
}

// --- AKSI: EDIT ---
if ($aksi === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_lowongan     = (int)($_POST['id_lowongan'] ?? 0);
    $judul_lowongan  = sanitize($_POST['judul_lowongan'] ?? '');
    $posisi          = sanitize($_POST['posisi'] ?? '');
    $deskripsi       = sanitize($_POST['deskripsi'] ?? '');
    $kualifikasi     = sanitize($_POST['kualifikasi'] ?? '');
    $lokasi          = sanitize($_POST['lokasi'] ?? 'Bandar Lampung');
    $batas_lamaran   = !empty($_POST['batas_lamaran']) ? $_POST['batas_lamaran'] : null;
    $status_lowongan = sanitize($_POST['status_lowongan'] ?? 'Dibuka');

    if ($id_lowongan <= 0 || empty($judul_lowongan) || empty($posisi) || empty($deskripsi)) {
        flash('error', 'Semua kolom wajib diisi dengan benar.');
        redirect(base_url('frontend/admin/lowongan/edit.php?id=' . $id_lowongan));
    }

    try {
        $stmt = $pdo->prepare("
            UPDATE lowongan 
            SET judul_lowongan = ?, posisi = ?, deskripsi = ?, kualifikasi = ?, lokasi = ?, batas_lamaran = ?, status_lowongan = ?
            WHERE id_lowongan = ?
        ");
        $stmt->execute([
            $judul_lowongan,
            $posisi,
            $deskripsi,
            !empty($kualifikasi) ? $kualifikasi : null,
            $lokasi,
            $batas_lamaran,
            $status_lowongan,
            $id_lowongan
        ]);

        flash('sukses', 'Data lowongan karir berhasil diperbarui.');
        redirect(base_url('frontend/admin/lowongan/index.php'));
    } catch (PDOException $e) {
        error_log("Edit lowongan error: " . $e->getMessage());
        flash('error', 'Gagal memperbarui lowongan: ' . $e->getMessage());
        redirect(base_url('frontend/admin/lowongan/edit.php?id=' . $id_lowongan));
    }
}

// --- AKSI: HAPUS ---
if ($aksi === 'hapus') {
    $id_lowongan = (int)($_GET['id'] ?? 0);
    if ($id_lowongan <= 0) {
        flash('error', 'ID lowongan tidak valid.');
        redirect(base_url('frontend/admin/lowongan/index.php'));
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM lowongan WHERE id_lowongan = ?");
        $stmt->execute([$id_lowongan]);
        flash('sukses', 'Lowongan pekerjaan berhasil dihapus.');
    } catch (PDOException $e) {
        error_log("Hapus lowongan error: " . $e->getMessage());
        flash('error', 'Gagal menghapus lowongan.');
    }
    redirect(base_url('frontend/admin/lowongan/index.php'));
}

redirect(base_url('frontend/admin/lowongan/index.php'));

