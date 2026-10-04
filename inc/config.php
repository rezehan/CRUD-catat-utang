<?php
// Mulai session di sini agar semua halaman (termasuk yang butuh $_SESSION
// sebelum header.php dipanggil) bisa memakainya.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Pengaturan Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'db_catat_utang');

// Membuat koneksi
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Cek koneksi
if (!$conn) {
    die("Koneksi Gagal: " . mysqli_connect_error());
}
mysqli_set_charset($conn, 'utf8mb4');

// Fungsi untuk mengecek apakah user sudah login
function check_login()
{
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        header("Location: login.php");
        exit;
    }
}


define('APP_SECRET', '13b6c67a82b34000a9a7ba7ec823f98a2235b4976fd1d10f2c961652449e28c0');

function encrypt_id(string $plain): string
{
    $key    = hash('sha256', APP_SECRET, true);   // jadikan kunci 32 byte
    $iv     = random_bytes(12);                   // nilai acak, beda tiap enkripsi
    $cipher = openssl_encrypt($plain, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);

    // gabungkan iv + tag + cipher, lalu ubah ke base64 yang aman untuk URL
    return rtrim(strtr(base64_encode($iv . $tag . $cipher), '+/', '-_'), '=');
}

// Kebalikannya. Mengembalikan null kalau token rusak / dimanipulasi
function decrypt_id(string $token): ?string
{
    $raw = base64_decode(strtr($token, '-_', '+/'));
    if ($raw === false || strlen($raw) < 29) {
        return null;
    }

    $iv     = substr($raw, 0, 12);
    $tag    = substr($raw, 12, 16);
    $cipher = substr($raw, 28);
    $key    = hash('sha256', APP_SECRET, true);

    $plain = openssl_decrypt($cipher, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
    return $plain === false ? null : $plain;
}
