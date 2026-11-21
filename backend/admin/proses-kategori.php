<?php
// ============================================================
// FILE    : backend/admin/proses-kategori.php
// FUNGSI  : Handler CRUD Kategori Produk
// PROYEK  : Sistem Informasi PT Shafa Marwa Multiguna
// ============================================================
require_once __DIR__ . '/../../config/app.php';
cek_admin();

$aksi = $_POST['aksi'] ?? $_GET['aksi'] ?? '';

// --- AKSI: TAMBAH ---
if ($aksi === 'tambah' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_kategori = sanitize($_POST['nama_kategori'] ?? '');
    $deskripsi     = sanitize($_POST['deskripsi'] ?? '');

    if (empty($nama_kategori)) {
        flash('error', 'Nama kategori wajib diisi.');
        redirect(base_url('frontend/admin/kategori/tambah.php'));
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO kategori (nama_kategori, deskripsi) VALUES (?, ?)");
        $stmt->execute([$nama_kategori, !empty($deskripsi) ? $deskripsi : null]);

        flash('sukses', 'Kategori baru "' . $nama_kategori . '" berhasil ditambahkan.');
        redirect(base_url('frontend/admin/kategori/index.php'));
    } catch (PDOException $e) {
        error_log("Tambah kategori error: " . $e->getMessage());
        flash('error', 'Gagal menambahkan kategori. Pastikan nama kategori belum digunakan.');
        redirect(base_url('frontend/admin/kategori/tambah.php'));
    }
}

// --- AKSI: EDIT ---
if ($aksi === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_kategori   = (int)($_POST['id_kategori'] ?? 0);
    $nama_kategori = sanitize($_POST['nama_kategori'] ?? '');
    $deskripsi     = sanitize($_POST['deskripsi'] ?? '');

    if ($id_kategori <= 0 || empty($nama_kategori)) {
        flash('error', 'Nama kategori tidak boleh kosong.');
        redirect(base_url('frontend/admin/kategori/edit.php?id=' . $id_kategori));
    }

    try {
        $stmt = $pdo->prepare("UPDATE kategori SET nama_kategori = ?, deskripsi = ? WHERE id_kategori = ?");
        $stmt->execute([$nama_kategori, !empty($deskripsi) ? $deskripsi : null, $id_kategori]);

        flash('sukses', 'Kategori berhasil diperbarui.');
        redirect(base_url('frontend/admin/kategori/index.php'));
    } catch (PDOException $e) {
        error_log("Edit kategori error: " . $e->getMessage());
        flash('error', 'Gagal memperbarui kategori: ' . $e->getMessage());
        redirect(base_url('frontend/admin/kategori/edit.php?id=' . $id_kategori));
    }
}

// --- AKSI: HAPUS ---
if ($aksi === 'hapus') {
    $id_kategori = (int)($_GET['id'] ?? 0);
    if ($id_kategori <= 0) {
        flash('error', 'ID Kategori tidak valid.');
        redirect(base_url('frontend/admin/kategori/index.php'));
    }

    try {
        // Cek apakah ada produk yang masih memakai kategori ini
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM produk WHERE id_kategori = ?");
        $stmt->execute([$id_kategori]);
        $used = $stmt->fetchColumn();

        if ($used > 0) {
            flash('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh ' . $used . ' produk.');
        } else {
            $stmt = $pdo->prepare("DELETE FROM kategori WHERE id_kategori = ?");
            $stmt->execute([$id_kategori]);
            flash('sukses', 'Kategori berhasil dihapus.');
        }
    } catch (PDOException $e) {
        error_log("Hapus kategori error: " . $e->getMessage());
        flash('error', 'Gagal menghapus kategori.');
    }
    redirect(base_url('frontend/admin/kategori/index.php'));
}

redirect(base_url('frontend/admin/kategori/index.php'));

