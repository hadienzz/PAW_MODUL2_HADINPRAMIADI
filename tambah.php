<?php
require __DIR__ . '/koneksi.php';
$mobil = bacaIsianMobil([]);
$pesan = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mobil = bacaIsianMobil($_POST);
    $pesan = validasiMobil($mobil);
    if ($pesan === '' && kodeSudahDipakai($conn, $mobil['kode_mobil'])) {
        $pesan = 'Kode mobil sudah dipakai.';
    }
    if ($pesan === '') {
        try {
            $statement = mysqli_prepare($conn, 'INSERT INTO showroom_mobil (kode_mobil, nama_mobil, brand_mobil, warna_mobil, tipe_mobil, harga_mobil) VALUES (?, ?, ?, ?, ?, ?)');
            $harga = str_replace(',', '.', $mobil['harga_mobil']);
            mysqli_stmt_bind_param($statement, 'ssssss', $mobil['kode_mobil'], $mobil['nama_mobil'], $mobil['brand_mobil'], $mobil['warna_mobil'], $mobil['tipe_mobil'], $harga);
            mysqli_stmt_execute($statement);
            header('Location: index.php?pesan=1', true, 303);
            exit;
        } catch (mysqli_sql_exception $error) {
            error_log($error->getMessage());
            $pesan = $error->getCode() === 1062 ? 'Kode mobil sudah dipakai.' : 'Mobil belum tersimpan. Periksa koneksi database lalu coba lagi.';
        }
    }
}
$teksTombol = 'Simpan mobil';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah mobil - ShowRoom EAD</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <main>
        <p class="label">SHOWROOM EAD</p>
        <h1>Tambah mobil</h1>
        <p><a href="index.php">Kembali ke daftar</a></p>
        <?php require __DIR__ . '/form-mobil.php'; ?>
    </main>
</body>

</html>