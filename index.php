<?php
require __DIR__ . '/koneksi.php';
$cari = inputTeks($_GET, 'cari');
$kataKunci = '%' . $cari . '%';
$statement = mysqli_prepare($conn, 'SELECT * FROM showroom_mobil WHERE nama_mobil LIKE ? OR brand_mobil LIKE ? ORDER BY id DESC');
mysqli_stmt_bind_param($statement, 'ss', $kataKunci, $kataKunci);
mysqli_stmt_execute($statement);
$hasil = mysqli_stmt_get_result($statement);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ShowRoom EAD</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <main>
        <p class="label">PRAKTIKUM PAW / MODUL 2</p>
        <h1>ShowRoom EAD</h1>
        <p>Kelola data mobil yang tersedia di showroom.</p>
        <?php if (inputTeks($_GET, 'pesan') === '1'): ?>
            <p class="pesan" role="status">Perubahan data berhasil disimpan.</p>
        <?php endif; ?>
        <p><a class="tombol" href="tambah.php">Tambah mobil</a></p>
        <form method="get" class="pencarian">
            <label for="cari">Cari nama atau brand</label>
            <input id="cari" name="cari" placeholder="Contoh: Honda" value="<?= escapeHtml($cari) ?>">
            <button type="submit">Cari</button> <a href="index.php">Reset</a>
        </form>
        <p>Jumlah hasil: <?= mysqli_num_rows($hasil) ?></p>
        <?php if (mysqli_num_rows($hasil) === 0): ?>
            <p>Data tidak ditemukan. Coba kata kunci lain atau <a href="tambah.php">tambah mobil</a>.</p>
        <?php endif; ?>
        <div class="daftar">
            <?php while ($mobil = mysqli_fetch_assoc($hasil)): ?>
                <article>
                    <p class="label"><?= escapeHtml($mobil['kode_mobil']) ?></p>
                    <h2><?= escapeHtml($mobil['nama_mobil']) ?></h2>
                    <p><?= escapeHtml($mobil['brand_mobil']) ?> &bull; <?= escapeHtml($mobil['tipe_mobil']) ?></p>
                    <p>Warna: <?= escapeHtml($mobil['warna_mobil']) ?></p>
                    <p>Rp <?= number_format((float) $mobil['harga_mobil'], 0, ',', '.') ?></p>
                    <a href="detail.php?id=<?= $mobil['id'] ?>">Detail</a>
                    <a href="edit.php?id=<?= $mobil['id'] ?>">Edit</a>
                    <a href="hapus.php?id=<?= $mobil['id'] ?>">Hapus</a>
                </article>
            <?php endwhile; ?>
        </div>
    </main>
</body>

</html>