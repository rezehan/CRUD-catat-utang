<?php
// hapus_utang.php - Hapus data utang berdasarkan NIK
require 'inc/config.php';
check_login();

$token = $_GET['id'] ?? '';
$nik   = decrypt_id($token);

if ($nik === null || !preg_match('/^\d{1,16}$/', $nik)) {
    $_SESSION['error'] = 'NIK tidak valid.';
    header('Location: data_utang.php');
    exit;
}

$stmt = $conn->prepare("DELETE FROM tb_utang WHERE nik = ?");
$stmt->bind_param("s", $nik);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    $_SESSION['message'] = 'Data utang berhasil dihapus!';
} else {
    $_SESSION['error'] = 'Gagal menghapus data (data tidak ditemukan).';
}

$stmt->close();
$conn->close();

header('Location: data_utang.php');
exit;
