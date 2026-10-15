<?php
require __DIR__ . '/koneksi.php';
$mobil = cariMobil($conn, $_GET);
$id = (int) $mobil['id'];
$pesan = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus'])) {
    try {
        $statement = mysqli_prepare($conn, 'DELETE FROM showroom_mobil WHERE id = ?');
        mysqli_stmt_bind_param($statement, 'i', $id);
        mysqli_stmt_execute($statement);
        header('Location: index.php?pesan=1', true, 303);
        exit;
    } catch (mysqli_sql_exception $error) {
        error_log($error->getMessage());
        $pesan = 'Mobil belum terhapus. Periksa koneksi database lalu coba lagi.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hapus mobil - ShowRoom EAD</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <main>
        <p class="label">SHOWROOM EAD / KONFIRMASI</p>
        <h1>Hapus mobil?</h1>
        <?php if ($pesan !== ''): ?>
            <p class="kesalahan" role="alert"><?= escapeHtml($pesan) ?></p>
        <?php endif; ?>
        <p><?= escapeHtml($mobil['nama_mobil']) ?> (<?= escapeHtml($mobil['kode_mobil']) ?>) akan dihapus.</p>
        <p>Data yang sudah dihapus tidak bisa dikembalikan.</p>
        <form method="post">
            <button type="submit" name="hapus" value="1" class="bahaya">Ya, hapus mobil</button>
            <a href="detail.php?id=<?= $id ?>">Batal</a>
        </form>
    </main>
</body>

</html>