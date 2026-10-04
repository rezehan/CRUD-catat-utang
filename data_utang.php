<?php
require 'inc/config.php';
require 'templates/header.php';
check_login();

$judul  = 'Data utang';
$result = mysqli_query($conn, "SELECT nik, nama, alamat, total_utang FROM tb_utang ORDER BY nama ASC");

?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Data utang</h1>
    <a href="tambah_utang.php" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Tambah utang
    </a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>NIK</th>
                    <th>Nama</th>
                    <th>Alamat</th>
                    <th class="text-end">Total utang</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($utang = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= htmlspecialchars($utang['nik']) ?></td>
                            <td><?= htmlspecialchars($utang['nama']) ?></td>
                            <td><?= htmlspecialchars($utang['alamat']) ?></td>
                            <td class="text-end">Rp <?= number_format($utang['total_utang'], 0, ',', '.') ?></td>
                            <td class="text-center text-nowrap">
                                <a href="edit_utang.php?id=<?= encrypt_id($utang['nik']) ?>"
                                    class="btn btn-warning btn-sm">Edit</a>
                                <a href="hapus_utang.php?id=<?= encrypt_id($utang['nik']) ?>"
                                    class="btn btn-danger btn-sm btn-hapus"
                                    data-nama="<?= htmlspecialchars($utang['nama']) ?>">Hapus</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">Belum ada data utang.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    window.addEventListener('load', function() {
        document.querySelectorAll('.btn-hapus').forEach(function(tombol) {
            tombol.addEventListener('click', function(e) {
                e.preventDefault();
                const url = this.getAttribute('href');
                const nama = this.dataset.nama;

                Swal.fire({
                    title: 'Hapus data?',
                    text: 'Data utang milik ' + nama + ' akan dihapus permanen.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Hapus',
                    cancelButtonText: 'Batal'
                }).then(function(hasil) {
                    if (hasil.isConfirmed) {
                        window.location.href = url;
                    }
                });
            });
        });
    });
</script>

<?php
mysqli_close($conn);
require 'templates/footer.php';
?>