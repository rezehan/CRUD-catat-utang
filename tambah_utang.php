<?php
// tambah_utang.php - Form tambah utang
require 'inc/config.php';
check_login();

$judul  = 'Tambah utang';
$errors = [];
$old    = ['nik' => '', 'nama' => '', 'alamat' => '', 'total_utang' => ''];

// Proses form SEBELUM header.php dipanggil, supaya redirect (header Location) tidak error
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['nik']         = trim($_POST['nik'] ?? '');
    $old['nama']        = trim($_POST['nama'] ?? '');
    $old['alamat']      = trim($_POST['alamat'] ?? '');
    $old['total_utang'] = trim($_POST['total_utang'] ?? '');

    // Validasi
    if (!preg_match('/^\d{16}$/', $old['nik'])) {
        $errors[] = 'NIK harus berupa 16 digit angka.';
    }
    if ($old['nama'] === '' || mb_strlen($old['nama']) > 100) {
        $errors[] = 'Nama wajib diisi (maksimal 100 karakter).';
    }
    if ($old['alamat'] === '') {
        $errors[] = 'Alamat wajib diisi.';
    }
    if (!is_numeric($old['total_utang']) || (float) $old['total_utang'] <= 0) {
        $errors[] = 'Jumlah utang harus berupa angka lebih dari 0.';
    }

    if (!$errors) {
        $jumlah = (float) $old['total_utang'];

        // NIK belum ada  -> tambah baris baru.
        // NIK sudah ada  -> jumlah utang ditambahkan ke total yang lama.
        $sql  = "INSERT INTO tb_utang (nik, nama, alamat, total_utang)
                 VALUES (?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE total_utang = total_utang + VALUES(total_utang)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssd', $old['nik'], $old['nama'], $old['alamat'], $jumlah);

        if ($stmt->execute()) {
            // affected_rows: 1 = data baru, 2 = NIK sudah ada (total ditambah)
            $_SESSION['message'] = ($stmt->affected_rows === 1)
                ? 'Data utang ' . $old['nama'] . ' berhasil ditambahkan!'
                : 'Utang ' . $old['nama'] . ' berhasil ditambahkan ke total utang sebelumnya.';
            $stmt->close();
            header('Location: data_utang.php');
            exit;
        }

        $_SESSION['error'] = 'Data gagal disimpan: ' . $stmt->error;
        $stmt->close();
    }
}

require 'templates/header.php';
?>



<?php if ($errors): ?>
    <div class="alert alert-danger" role="alert">
        <ul class="mb-0">
            <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="mx-auto" style="max-width: 940px;">
    <h1 class="h4 mb-4">Tambah utang</h1>
    <div class="card mx-auto">
        <div class="card-body">
            <form action="tambah_utang.php" method="post" novalidate>
                <div class="mb-3">
                    <label for="nik" class="form-label">NIK</label>
                    <input type="text" class="form-control" id="nik" name="nik" inputmode="numeric"
                        maxlength="16" pattern="\d{16}" required
                        value="<?= htmlspecialchars($old['nik']) ?>">
                    <div class="form-text">16 digit angka. Jika NIK sudah terdaftar, utang ditambahkan ke totalnya.</div>
                </div>

                <div class="mb-3">
                    <label for="nama" class="form-label">Nama</label>
                    <input type="text" class="form-control" id="nama" name="nama" maxlength="100" required
                        value="<?= htmlspecialchars($old['nama']) ?>">
                </div>

                <div class="mb-3">
                    <label for="alamat" class="form-label">Alamat</label>
                    <textarea class="form-control" id="alamat" name="alamat" rows="3" required><?= htmlspecialchars($old['alamat']) ?></textarea>
                </div>

                <div class="mb-4">
                    <label for="total_utang" class="form-label">Jumlah utang</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" class="form-control" id="total_utang" name="total_utang"
                            min="1" step="1" required
                            value="<?= htmlspecialchars($old['total_utang']) ?>">
                    </div>
                </div>

                <button type="submit" class="btn btn-success">Simpan</button>
                <a href="data_utang.php" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>

<?php
$conn->close();
require 'templates/footer.php';
?>