<?php
// ============================================================
// FILE    : backend/admin/proses-produk.php
// FUNGSI  : Handler CRUD Produk (Tambah, Edit, Hapus)
// PROYEK  : Sistem Informasi PT Shafa Marwa Multiguna
// ============================================================
require_once __DIR__ . '/../../config/app.php';
cek_admin();

$aksi = $_POST['aksi'] ?? $_GET['aksi'] ?? '';

// --- AKSI: TAMBAH PRODUK ---
if ($aksi === 'tambah' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_kategori   = (int)($_POST['id_kategori'] ?? 0);
    $nama_produk   = sanitize($_POST['nama_produk'] ?? '');
    $deskripsi     = sanitize($_POST['deskripsi'] ?? '');
    $harga         = (float)($_POST['harga'] ?? 0);
    $stok          = (int)($_POST['stok'] ?? 0);
    $satuan        = sanitize($_POST['satuan'] ?? 'pcs');
    $status_produk = sanitize($_POST['status_produk'] ?? 'Aktif');
    $tampil_di_beranda = isset($_POST['tampil_di_beranda']) && $status_produk === 'Aktif' ? 1 : 0;

    if ($id_kategori <= 0 || empty($nama_produk) || $harga < 0 || $stok < 0) {
        flash('error', 'Semua kolom bertanda bintang wajib diisi dengan benar.');
        redirect(base_url('frontend/admin/produk/tambah.php'));
    }

    if ($tampil_di_beranda) {
        $jumlah_unggulan = (int)$pdo->query("SELECT COUNT(*) FROM produk WHERE tampil_di_beranda = 1")->fetchColumn();
        if ($jumlah_unggulan >= 8) {
            flash('error', 'Maksimal 8 produk dapat dipilih sebagai Produk Unggulan Beranda.');
            redirect(base_url('frontend/admin/produk/tambah.php'));
        }
    }

    $slug_base = buat_slug($nama_produk);
    $slug = $slug_base;
    $counter = 1;
    while (true) {
        $stmt = $pdo->prepare("SELECT id_produk FROM produk WHERE slug = ? LIMIT 1");
        $stmt->execute([$slug]);
        if (!$stmt->fetch()) {
            break;
        }
        $slug = $slug_base . '-' . $counter;
        $counter++;
    }

    $gambar_nama = null;
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $upload = upload_gambar($_FILES['gambar'], 'produk', 'prod');
        if ($upload) {
            $gambar_nama = $upload;
        }
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO produk (id_kategori, nama_produk, slug, deskripsi, harga, stok, satuan, gambar, status_produk, tampil_di_beranda)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $id_kategori,
            $nama_produk,
            $slug,
            !empty($deskripsi) ? $deskripsi : null,
            $harga,
            $stok,
            !empty($satuan) ? $satuan : 'pcs',
            $gambar_nama,
            $status_produk,
            $tampil_di_beranda
        ]);

        flash('sukses', 'Produk baru "' . $nama_produk . '" berhasil ditambahkan.');
        redirect(base_url('frontend/admin/produk/index.php'));
    } catch (PDOException $e) {
        error_log("Tambah produk error: " . $e->getMessage());
        flash('error', 'Gagal menambahkan produk: ' . $e->getMessage());
        redirect(base_url('frontend/admin/produk/tambah.php'));
    }
}

// --- AKSI: EDIT PRODUK ---
if ($aksi === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_produk     = (int)($_POST['id_produk'] ?? 0);
    $id_kategori   = (int)($_POST['id_kategori'] ?? 0);
    $nama_produk   = sanitize($_POST['nama_produk'] ?? '');
    $deskripsi     = sanitize($_POST['deskripsi'] ?? '');
    $harga         = (float)($_POST['harga'] ?? 0);
    $stok          = (int)($_POST['stok'] ?? 0);
    $satuan        = sanitize($_POST['satuan'] ?? 'pcs');
    $status_produk = sanitize($_POST['status_produk'] ?? 'Aktif');
    $tampil_di_beranda = isset($_POST['tampil_di_beranda']) && $status_produk === 'Aktif' ? 1 : 0;

    if ($id_produk <= 0 || $id_kategori <= 0 || empty($nama_produk) || $harga < 0 || $stok < 0) {
        flash('error', 'Semua kolom bertanda bintang wajib diisi.');
        redirect(base_url('frontend/admin/produk/edit.php?id=' . $id_produk));
    }

    if ($tampil_di_beranda) {
        $stmt_unggulan = $pdo->prepare("SELECT COUNT(*) FROM produk WHERE tampil_di_beranda = 1 AND id_produk <> ?");
        $stmt_unggulan->execute([$id_produk]);
        if ((int)$stmt_unggulan->fetchColumn() >= 8) {
            flash('error', 'Maksimal 8 produk dapat dipilih sebagai Produk Unggulan Beranda.');
            redirect(base_url('frontend/admin/produk/edit.php?id=' . $id_produk));
        }
    }

    // Ambil data lama
    $stmt = $pdo->prepare("SELECT gambar FROM produk WHERE id_produk = ? LIMIT 1");
    $stmt->execute([$id_produk]);
    $old_prod = $stmt->fetch();
    if (!$old_prod) {
        flash('error', 'Produk tidak ditemukan.');
        redirect(base_url('frontend/admin/produk/index.php'));
    }

    $gambar_nama = $old_prod['gambar'];
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $upload = upload_gambar($_FILES['gambar'], 'produk', 'prod');
        if ($upload) {
            if (!empty($old_prod['gambar'])) {
                hapus_gambar($old_prod['gambar'], 'produk');
            }
            $gambar_nama = $upload;
        }
    }

    try {
        $stmt = $pdo->prepare("
            UPDATE produk 
            SET id_kategori = ?, nama_produk = ?, deskripsi = ?, harga = ?, stok = ?, satuan = ?, gambar = ?, status_produk = ?, tampil_di_beranda = ?
            WHERE id_produk = ?
        ");
        $stmt->execute([
            $id_kategori,
            $nama_produk,
            !empty($deskripsi) ? $deskripsi : null,
            $harga,
            $stok,
            !empty($satuan) ? $satuan : 'pcs',
            $gambar_nama,
            $status_produk,
            $tampil_di_beranda,
            $id_produk
        ]);

        flash('sukses', 'Produk "' . $nama_produk . '" berhasil diperbarui.');
        redirect(base_url('frontend/admin/produk/index.php'));
    } catch (PDOException $e) {
        error_log("Edit produk error: " . $e->getMessage());
        flash('error', 'Gagal memperbarui produk: ' . $e->getMessage());
        redirect(base_url('frontend/admin/produk/edit.php?id=' . $id_produk));
    }
}

// --- AKSI: HAPUS PRODUK ---
if ($aksi === 'hapus') {
    $id_produk = (int)($_GET['id'] ?? 0);
    if ($id_produk <= 0) {
        flash('error', 'ID Produk tidak valid.');
        redirect(base_url('frontend/admin/produk/index.php'));
    }

    try {
        // Cek apakah produk pernah dipesan
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM detail_pesanan WHERE id_produk = ?");
        $stmt->execute([$id_produk]);
        $used = $stmt->fetchColumn();

        if ($used > 0) {
            // Ubah status jadi tidak aktif agar riwayat transaksi tetap aman
            $stmt = $pdo->prepare("UPDATE produk SET status_produk = 'Tidak Aktif', tampil_di_beranda = 0 WHERE id_produk = ?");
            $stmt->execute([$id_produk]);
            flash('warning', 'Produk ini memiliki riwayat pesanan, sehingga statusnya diubah menjadi "Tidak Aktif" dan tidak dihapus permanen.');
        } else {
            $stmt = $pdo->prepare("SELECT gambar FROM produk WHERE id_produk = ? LIMIT 1");
            $stmt->execute([$id_produk]);
            $p = $stmt->fetch();
            if ($p && !empty($p['gambar'])) {
                hapus_gambar($p['gambar'], 'produk');
            }

            $stmt = $pdo->prepare("DELETE FROM produk WHERE id_produk = ?");
            $stmt->execute([$id_produk]);
            flash('sukses', 'Produk berhasil dihapus secara permanen.');
        }
    } catch (PDOException $e) {
        error_log("Hapus produk error: " . $e->getMessage());
        flash('error', 'Gagal menghapus produk.');
    }
    redirect(base_url('frontend/admin/produk/index.php'));
}

redirect(base_url('frontend/admin/produk/index.php'));

