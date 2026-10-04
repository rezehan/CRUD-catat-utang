<?php
// edit_utang.php - Edit data utang
require 'inc/config.php';
check_login();

$judul = 'Edit utang';

// NIK dari URL (dipakai sebagai teks, bukan angka, supaya angka 0 di depan tidak hilang)
$token = $_GET['id'] ?? '';
$nik   = decrypt_id($token);
if ($nik === null || !preg_match('/^\d{1,16}$/', $nik)) {
    $_SESSION['error'] = 'NIK tidak valid.';
    header('Location: data_utang.php');
    exit;
}

// Ambil data lama dari database (sekaligus memastikan datanya ada)
$stmt = mysqli_prepare($conn, "SELECT nama, alamat, total_utang FROM tb_utang WHERE nik = ?");
mysqli_stmt_bind_param($stmt, "s", $nik);
mysqli_stmt_execute($stmt);
$row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$row) {
    $_SESSION['error'] = 'Data utang tidak ditemukan.';
    header('Location: data_utang.php');
    exit;
}

$nama        = $row['nama'];
$alamat      = $row['alamat'];
$total_utang = $row['total_utang'];

// Proses form saat disubmit (sebelum header.php agar redirect aman)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama        = trim($_POST['nama'] ?? '');
    $alamat      = trim($_POST['alamat'] ?? '');
    $total_utang = trim($_POST['total_utang'] ?? '');

    if ($nama === '' || $alamat === '' || $total_utang === '') {
        $_SESSION['error'] = 'Semua field wajib diisi.';
    } elseif (!is_numeric($total_utang) || (float) $total_utang < 0) {
        $_SESSION['error'] = 'Total utang harus berupa angka 0 atau lebih.';
    } elseif (mb_strlen($nama) > 100) {
        $_SESSION['error'] = 'Nama maksimal 100 karakter.';
    } else {
        $total = (float) $total_utang;

        $stmt = mysqli_prepare($conn, "UPDATE tb_utang SET nama = ?, alamat = ?, total_utang = ? WHERE nik = ?");
        mysqli_stmt_bind_param($stmt, "ssds", $nama, $alamat, $total, $nik);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['message'] = 'Data utang berhasil diperbarui.';
            mysqli_stmt_close($stmt);
            header('Location: data_utang.php');
            exit;
        }

        $_SESSION['error'] = 'Gagal memperbarui data utang.';
        mysqli_stmt_close($stmt);
    }
}

require 'templates/header.php';
?>

<div class="mx-auto" style="max-width: 940px;">
    <h1 class="h4 mb-4">Edit utang</h1>
    <div class="card">
        <div class="card-body">
            <form action="edit_utang.php?id=<?= htmlspecialchars($token) ?>" method="post">
                <div class="mb-3">
                    <label for="nik" class="form-label">NIK</label>
                    <input type="text" class="form-control" id="nik" value="<?= htmlspecialchars($nik) ?>" disabled>
                    <div class="form-text">NIK tidak bisa diubah.</div>
                </div>

                <div class="mb-3">
                    <label for="nama" class="form-label">Nama</label>
                    <input type="text" class="form-control" id="nama" name="nama" maxlength="100"
                        value="<?= htmlspecialchars($nama) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="alamat" class="form-label">Alamat</label>
                    <textarea class="form-control" id="alamat" name="alamat" rows="3" required><?= htmlspecialchars($alamat) ?></textarea>
                </div>

                <div class="mb-4">
                    <label for="total_utang" class="form-label">Total utang</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" class="form-control" id="total_utang" name="total_utang"
                            min="0" step="0.01" value="<?= htmlspecialchars($total_utang) ?>" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Update</button>
                <a href="data_utang.php" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>

<?php
mysqli_close($conn);
require 'templates/footer.php';
?>