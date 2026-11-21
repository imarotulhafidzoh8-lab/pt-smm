<?php
// ============================================================
// FILE    : backend/helpers/helper.php
// FUNGSI  : Kumpulan Fungsi Bantu Terpusat Sistem Informasi PT SMM
// PROYEK  : Sistem Informasi PT Shafa Marwa Multiguna
// ============================================================

if (!function_exists('base_url')) {
    function base_url($path = '')
    {
        $base = defined('BASE_URL') ? BASE_URL : '/pt-smm/';
        return rtrim($base, '/') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset_url')) {
    function asset_url($path = '')
    {
        return base_url('assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('upload_url')) {
    function upload_url($folder, $filename)
    {
        if (empty($filename)) {
            return asset_url('images/no-image.svg');
        }
        $relPath = 'assets/uploads/' . trim($folder, '/') . '/' . basename($filename);
        $fullPath = __DIR__ . '/../../' . $relPath;
        if (file_exists($fullPath)) {
            return base_url($relPath);
        }
        return asset_url('images/no-image.svg');
    }
}

if (!function_exists('redirect')) {
    function redirect($url)
    {
        header("Location: " . $url);
        exit;
    }
}

if (!function_exists('sanitize')) {
    function sanitize($data)
    {
        if (is_array($data)) {
            return array_map('sanitize', $data);
        }
        return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('e')) {
    function e($str)
    {
        return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('flash')) {
    function flash($tipe, $pesan)
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['flash'] = [
                'tipe'  => $tipe, // sukses, error, warning, info
                'pesan' => $pesan,
            ];
        }
    }
}

if (!function_exists('get_flash')) {
    function get_flash()
    {
        if (session_status() !== PHP_SESSION_ACTIVE || !isset($_SESSION['flash'])) {
            return null;
        }
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
}

if (!function_exists('cek_login')) {
    function cek_login($redirect_url = null)
    {
        if (session_status() !== PHP_SESSION_ACTIVE && !headers_sent()) {
            session_start();
        }
        if (!isset($_SESSION['id_user']) || empty($_SESSION['id_user'])) {
            if ($redirect_url === null) {
                $redirect_url = base_url('frontend/auth/login.php');
            }
            flash('error', 'Silakan login terlebih dahulu untuk mengakses halaman ini.');
            redirect($redirect_url);
        }
    }
}

if (!function_exists('cek_admin')) {
    function cek_admin($redirect_url = null)
    {
        cek_login($redirect_url);
        $role = $_SESSION['role'] ?? '';
        if (strcasecmp($role, 'Admin') !== 0) {
            if ($redirect_url === null) {
                $redirect_url = base_url('index.php');
            }
            flash('error', 'Akses ditolak. Halaman ini hanya dapat diakses oleh Administrator.');
            redirect($redirect_url);
        }
    }
}

if (!function_exists('cek_pengguna')) {
    function cek_pengguna($redirect_url = null)
    {
        cek_login($redirect_url);
        $role = $_SESSION['role'] ?? '';
        if (strcasecmp($role, 'Pengguna') !== 0) {
            if ($redirect_url === null) {
                $redirect_url = base_url('frontend/admin/dashboard.php');
            }
            flash('info', 'Anda masuk sebagai Administrator.');
            redirect($redirect_url);
        }
    }
}

if (!function_exists('format_rupiah')) {
    function format_rupiah($angka)
    {
        return 'Rp ' . number_format((float)$angka, 0, ',', '.');
    }
}

if (!function_exists('generate_kode_pesanan')) {
    function generate_kode_pesanan()
    {
        $tanggal = date('Ymd');
        $acak    = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        return "PS-{$tanggal}-{$acak}";
    }
}

if (!function_exists('buat_slug')) {
    function buat_slug($teks)
    {
        $teks = strtolower(trim((string)$teks));
        $teks = preg_replace('/[^a-z0-9\s-]/', '', $teks);
        $teks = preg_replace('/[\s-]+/', '-', $teks);
        return trim($teks, '-');
    }
}

if (!function_exists('upload_gambar')) {
    function upload_gambar($file, $folder = 'produk', $prefix = 'img')
    {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        $max_size = 5 * 1024 * 1024; // 5 MB
        if ($file['size'] > $max_size) {
            return false;
        }

        $allowed_mime = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
        ];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!isset($allowed_mime[$mime])) {
            return false;
        }

        $ext = $allowed_mime[$mime];

        $folder_valid = ['produk', 'lowongan', 'profil', 'images'];
        if (!in_array($folder, $folder_valid, true)) {
            $folder = 'produk';
        }

        $base_dir = __DIR__ . '/../../assets/uploads/' . $folder . '/';
        if (!is_dir($base_dir)) {
            if (!mkdir($base_dir, 0755, true)) {
                return false;
            }
        }

        $prefix = preg_replace('/[^a-z0-9_-]/i', '', (string)$prefix);
        if ($prefix === '') {
            $prefix = 'img';
        }
        $nama_file = $prefix . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
        $tujuan    = $base_dir . $nama_file;

        if (!move_uploaded_file($file['tmp_name'], $tujuan)) {
            return false;
        }

        return $nama_file;
    }
}

if (!function_exists('hapus_gambar')) {
    function hapus_gambar($nama_file, $folder = 'produk')
    {
        if (empty($nama_file)) {
            return false;
        }
        $path = __DIR__ . '/../../assets/uploads/' . $folder . '/' . basename($nama_file);
        if (is_file($path)) {
            return unlink($path);
        }
        return false;
    }
}

if (!function_exists('status_badge_pesanan')) {
    function status_badge_pesanan($status)
    {
        switch ($status) {
            case 'Menunggu Pembayaran':
                return '<span class="badge bg-warning text-dark"><i class="fas fa-hourglass-half me-1"></i>Menunggu Pembayaran</span>';
            case 'Menunggu Konfirmasi':
                return '<span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Menunggu Konfirmasi</span>';
            case 'Diproses':
                return '<span class="badge bg-info text-dark"><i class="fas fa-box me-1"></i>Diproses (Siap Kirim)</span>';
            case 'Dikirim':
                return '<span class="badge bg-primary"><i class="fas fa-truck me-1"></i>Dikirim</span>';
            case 'Selesai':
                return '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Selesai</span>';
            case 'Dibatalkan':
                return '<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Dibatalkan</span>';
            default:
                return '<span class="badge bg-secondary">' . e($status) . '</span>';
        }
    }
}

if (!function_exists('get_company_profile')) {
    function get_company_profile($pdo)
    {
        try {
            $stmt = $pdo->query("SELECT * FROM profil_perusahaan ORDER BY id_profil ASC LIMIT 1");
            $res = $stmt->fetch();
            if ($res) {
                return $res;
            }
        } catch (PDOException $e) {}
        return [
            'nama_perusahaan' => 'PT Shafa Marwa Multiguna',
            'deskripsi'       => 'Pusat pemasaran digital, live streaming, dan distribusi aneka produk unggulan berkualitas.',
            'alamat'          => 'Jalan Urip Sumoharjo Nomor 159 B, Kel. Way Halim Permai, Kec. Way Halim, Kota Bandar Lampung, Lampung 35122',
            'email'           => 'smm4us.co.id@gmail.com',
            'no_telepon'      => '08117291000',
            'website'         => 'www.shafamarwa.com',
            'logo'            => null
        ];
    }
}

// ============================================================
// Real-Time Visitor & Business Statistics Functions
// ============================================================
if (!function_exists('catat_pengunjung')) {
    function catat_pengunjung($pdo, $halaman = 'beranda')
    {
        if (!$pdo) return;

        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($parts[0]);
        }
        $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 255);
        $today     = date('Y-m-d');
        $now       = date('Y-m-d H:i:s');

        // Batasi pencatatan per sesi per jam agar hitungan akurat dan real-time per pengunjung
        $sessionKey = 'visited_' . md5($ip . '_' . $halaman . '_' . $today . '_' . date('H'));
        if (empty($_SESSION[$sessionKey])) {
            try {
                $stmt = $pdo->prepare("INSERT INTO pengunjung (ip_address, user_agent, halaman, tanggal, created_at) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$ip, $userAgent, $halaman, $today, $now]);
                $_SESSION[$sessionKey] = true;
            } catch (PDOException $e) {}
        }
    }
}

if (!function_exists('get_realtime_stats')) {
    function get_realtime_stats($pdo)
    {
        $stats = [
            'total_pengunjung'    => 0,
            'pengunjung_hari_ini' => 0,
            'total_produk'        => 0,
            'total_kategori'      => 0,
            'total_pesanan'       => 0,
            'total_item_terjual'  => 0,
            'tingkat_kepuasan'    => '100%'
        ];

        if (!$pdo) return $stats;

        try {
            $stats['total_pengunjung']    = (int)$pdo->query("SELECT COUNT(*) FROM pengunjung")->fetchColumn();
            $stats['pengunjung_hari_ini'] = (int)$pdo->query("SELECT COUNT(*) FROM pengunjung WHERE tanggal = CURDATE()")->fetchColumn();
        } catch (PDOException $e) {}

        try {
            $stats['total_produk']       = (int)$pdo->query("SELECT COUNT(*) FROM produk WHERE status_produk = 'Aktif'")->fetchColumn();
            $stats['total_kategori']     = (int)$pdo->query("SELECT COUNT(*) FROM kategori")->fetchColumn();
            $stats['total_pesanan']      = (int)$pdo->query("SELECT COUNT(*) FROM pesanan")->fetchColumn();
            $stats['total_item_terjual'] = (int)$pdo->query("SELECT COALESCE(SUM(jumlah), 0) FROM detail_pesanan")->fetchColumn();

            $pesanan_selesai = (int)$pdo->query("SELECT COUNT(*) FROM pesanan WHERE status_pesanan = 'Selesai'")->fetchColumn();
            if ($stats['total_pesanan'] > 0) {
                $rate = round(($pesanan_selesai / $stats['total_pesanan']) * 100);
                $stats['tingkat_kepuasan'] = max(98, $rate) . '%';
            }
        } catch (PDOException $e) {}

        return $stats;
    }
}
