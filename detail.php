<?php
require __DIR__ . '/koneksi.php';
$mobil = cariMobil($conn, $_GET);
$id = (int) $mobil['id'];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detail mobil - ShowRoom EAD</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <main>
        <p class="label">SHOWROOM EAD / DETAIL</p>
        <h1><?= escapeHtml($mobil['nama_mobil']) ?></h1>
        <dl>
            <dt>Kode</dt>
            <dd><?= escapeHtml($mobil['kode_mobil']) ?></dd>
            <dt>Brand</dt>
            <dd><?= escapeHtml($mobil['brand_mobil']) ?></dd>
            <dt>Warna</dt>
            <dd><?= escapeHtml($mobil['warna_mobil']) ?></dd>
            <dt>Tipe</dt>
            <dd><?= escapeHtml($mobil['tipe_mobil']) ?></dd>
            <dt>Harga</dt>
            <dd>Rp <?= number_format((float) $mobil['harga_mobil'], 0, ',', '.') ?></dd>
        </dl>
        <a href="index.php">Daftar mobil</a>
        <a href="edit.php?id=<?= $id ?>">Edit mobil</a>
        <a href="hapus.php?id=<?= $id ?>">Hapus mobil</a>
    </main>
</body>

</html>