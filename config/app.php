<?php
// ============================================================
// FILE    : config/app.php
// FUNGSI  : Konfigurasi Global & Inisialisasi Aplikasi
// PROYEK  : Sistem Informasi PT Shafa Marwa Multiguna (PT SMM)
// ============================================================

/** Read deployment configuration from the process environment or a local .env file. */
if (!function_exists('app_env')) {
    function app_env($name, $default = '')
    {
        static $local = null;
        if ($local === null) {
            $local = [];
            $env_file = dirname(__DIR__) . '/.env';
            if (is_readable($env_file)) {
                foreach (file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                    $line = trim($line);
                    if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) continue;
                    [$key, $value] = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    if ($value !== '' && (($value[0] === '"' && substr($value, -1) === '"') || ($value[0] === "'" && substr($value, -1) === "'"))) {
                        $value = substr($value, 1, -1);
                    }
                    $local[$key] = $value;
                }
            }
        }

        $value = getenv($name);
        if ($value !== false) return $value;
        return array_key_exists($name, $local) ? $local[$name] : $default;
    }
}

if (session_status() !== PHP_SESSION_ACTIVE && !headers_sent()) {
    $is_https = (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    session_set_cookie_params([
        'httponly' => true,
        'secure' => $is_https,
        'samesite' => 'Lax',
        'path' => '/',
    ]);
    session_start();
}

if (!defined('APP_NAME')) {
    define('APP_NAME', 'PT Shafa Marwa Multiguna');
}
if (!defined('APP_SHORT')) {
    define('APP_SHORT', 'PT SMM');
}
if (!defined('APP_TAGLINE')) {
    define('APP_TAGLINE', 'Digital Marketing & Distribusi Produk Unggulan');
}

// Menentukan Base URL yang fleksibel (bekerja baik pada subfolder localhost XAMPP)
if (!defined('BASE_URL')) {
    $script_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    // Cari posisi folder /pt-smm/
    $base_pos = strpos($script_dir, '/pt-smm');
    if ($base_pos !== false) {
        define('BASE_URL', '/pt-smm/');
    } else {
        define('BASE_URL', '/pt-smm/');
    }
}

// ============================================================
// Google OAuth 2.0 Configuration
// ============================================================
if (!defined('GOOGLE_CLIENT_ID')) {
    define('GOOGLE_CLIENT_ID', app_env('GOOGLE_CLIENT_ID'));
}
if (!defined('GOOGLE_CLIENT_SECRET')) {
    define('GOOGLE_CLIENT_SECRET', app_env('GOOGLE_CLIENT_SECRET'));
}
if (!defined('GOOGLE_REDIRECT_URI')) {
    define('GOOGLE_REDIRECT_URI', app_env('GOOGLE_REDIRECT_URI', 'http://localhost/pt-smm/frontend/auth/google-callback.php'));
}

// ============================================================
// Midtrans Payment Gateway Configuration (Mode SANDBOX)
// ============================================================
if (!defined('MIDTRANS_SERVER_KEY')) {
    define('MIDTRANS_SERVER_KEY', app_env('MIDTRANS_SERVER_KEY'));
}
if (!defined('MIDTRANS_CLIENT_KEY')) {
    define('MIDTRANS_CLIENT_KEY', app_env('MIDTRANS_CLIENT_KEY'));
}
// Mode Sandbox (Uji Coba Kampus)
if (!defined('MIDTRANS_IS_PRODUCTION')) {
    define('MIDTRANS_IS_PRODUCTION', filter_var(app_env('MIDTRANS_IS_PRODUCTION', 'false'), FILTER_VALIDATE_BOOLEAN));
}
if (!defined('MIDTRANS_SNAP_API_URL')) {
    define('MIDTRANS_SNAP_API_URL', app_env('MIDTRANS_SNAP_API_URL', MIDTRANS_IS_PRODUCTION ? 'https://app.midtrans.com/snap/v1/transactions' : 'https://app.sandbox.midtrans.com/snap/v1/transactions'));
}
if (!defined('MIDTRANS_SNAP_JS_URL')) {
    define('MIDTRANS_SNAP_JS_URL', app_env('MIDTRANS_SNAP_JS_URL', MIDTRANS_IS_PRODUCTION ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js'));
}

// ============================================================
// Biteship Shipping API Configuration (Mode TEST/Sandbox)
// DOCS: https://docs.biteship.com
// ============================================================
if (!defined('BITESHIP_API_KEY')) {
    define('BITESHIP_API_KEY', app_env('BITESHIP_API_KEY'));
}
if (!defined('BITESHIP_API_URL')) {
    define('BITESHIP_API_URL', app_env('BITESHIP_API_URL', 'https://api.biteship.com'));
}
// Kurir yang didukung (comma-separated, lihat: https://docs.biteship.com/reference)
if (!defined('BITESHIP_COURIERS')) {
    define('BITESHIP_COURIERS', 'jne,jnt,sicepat,pos,anteraja');
}
// Informasi Gudang / Asal Pengiriman PT SMM
if (!defined('BITESHIP_ORIGIN_POSTAL')) {
    define('BITESHIP_ORIGIN_POSTAL', app_env('BITESHIP_ORIGIN_POSTAL', '35141')); // Way Halim, Bandar Lampung
}
if (!defined('BITESHIP_ORIGIN_ADDRESS')) {
    define('BITESHIP_ORIGIN_ADDRESS', app_env('BITESHIP_ORIGIN_ADDRESS', 'Jl. Kimaja No. 45, Way Halim, Bandar Lampung'));
}
if (!defined('BITESHIP_ORIGIN_PHONE')) {
    define('BITESHIP_ORIGIN_PHONE', app_env('BITESHIP_ORIGIN_PHONE', '+6281234567890'));
}
if (!defined('BITESHIP_ORIGIN_EMAIL')) {
    define('BITESHIP_ORIGIN_EMAIL', app_env('BITESHIP_ORIGIN_EMAIL', 'kontak@shafamarwa.com'));
}

// Panggil helpers (urutan penting: helper dulu, lalu biteship, midtrans, database)
require_once __DIR__ . '/../backend/helpers/helper.php';
// Panggil helper Biteship
require_once __DIR__ . '/../backend/helpers/biteship.php';
// Panggil helper Midtrans
require_once __DIR__ . '/../backend/helpers/midtrans.php';
// Panggil koneksi database
require_once __DIR__ . '/database.php';
