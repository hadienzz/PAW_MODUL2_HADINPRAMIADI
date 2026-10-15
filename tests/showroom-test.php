<?php
// Jalankan: php tests/showroom-test.php. Hanya membuat dan menghapus database tes acak.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$database = 'paw_showroom_test_' . bin2hex(random_bytes(6));
$conn = mysqli_connect(getenv('DB_HOST') ?: '127.0.0.1', getenv('DB_USER') ?: 'root', getenv('DB_PASSWORD') ?: '', '', (int) (getenv('DB_PORT') ?: 3306));
$server = null;
$log = tempnam(sys_get_temp_dir(), 'paw-showroom-');
$jumlah = 0;

function periksa(bool $benar, string $pesan): void
{
    global $jumlah;
    if (!$benar) {
        throw new RuntimeException($pesan);
    }
    $jumlah++;
}

function request(string $halaman, ?array $data = null): array
{
    global $url;
    $curl = curl_init($url . '/' . $halaman);
    curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 5, CURLOPT_HEADER => true]);
    if ($data !== null) {
        curl_setopt_array($curl, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => http_build_query($data)]);
    }
    $response = curl_exec($curl);
    if ($response === false) {
        $error = curl_error($curl);
        curl_close($curl);
        throw new RuntimeException($error);
    }
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $headerLength = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
    curl_close($curl);
    return [$status, substr($response, $headerLength), substr($response, 0, $headerLength)];
}

try {
    $sql = str_replace('paw_modul2_showroom', $database, file_get_contents(__DIR__ . '/../database.sql'));
    mysqli_multi_query($conn, $sql);
    do {
        $result = mysqli_store_result($conn);
        if ($result) {
            mysqli_free_result($result);
        }
    } while (mysqli_more_results($conn) && mysqli_next_result($conn));

    $socket = stream_socket_server('tcp://127.0.0.1:0', $errorCode, $errorMessage);
    if (!$socket) {
        throw new RuntimeException($errorMessage);
    }
    $address = stream_socket_get_name($socket, false);
    fclose($socket);
    $url = 'http://' . $address;
    $environment = getenv();
    $environment['DB_NAME'] = $database;
    $server = proc_open([PHP_BINARY, '-S', $address, '-t', dirname(__DIR__)], [['pipe', 'r'], ['file', $log, 'a'], ['file', $log, 'a']], $pipes, dirname(__DIR__), $environment);
    if (!is_resource($server)) {
        throw new RuntimeException('Server PHP tes gagal dijalankan.');
    }
    fclose($pipes[0]);
    $siap = false;
    for ($i = 0; $i < 40; $i++) {
        $probe = @fsockopen('127.0.0.1', (int) substr(strrchr($address, ':'), 1));
        if ($probe) {
            fclose($probe);
            $siap = true;
            break;
        }
        usleep(100000);
    }
    periksa($siap, 'Server tes tidak siap.');
    [$status, $body] = request('cek_koneksi.php');
    periksa($status === 200 && $body === 'Koneksi berhasil.', 'Koneksi database gagal.');
    [$status, $body] = request('index.php');
    periksa($status === 200 && str_contains($body, 'Jumlah hasil: 3'), 'Tiga mobil awal tidak tampil.');
    periksa(strpos($body, 'Suzuki Baleno') < strpos($body, 'Honda City'), 'Urutan terbaru salah.');
    [, $body] = request('index.php?cari=Honda');
    periksa(str_contains($body, 'Jumlah hasil: 1') && str_contains($body, 'Honda City'), 'Pencarian Honda gagal.');
    [, $body] = request('index.php?cari=tidak-ada');
    periksa(str_contains($body, 'Data tidak ditemukan.'), 'Hasil pencarian kosong gagal.');
    [, $body] = request('index.php?cari=' . urlencode("' OR 1=1 --"));
    periksa(str_contains($body, 'Jumlah hasil: 0'), 'Pencarian tidak memperlakukan SQL sebagai teks.');
    [, $body] = request('index.php?cari=' . urlencode('<script>alert(1)</script>'));
    periksa(!str_contains($body, '<script>') && str_contains($body, '&lt;script&gt;'), 'Pencarian belum di-escape.');

    $mobil = ['kode_mobil' => 'MBL-DEMO', 'nama_mobil' => 'Toyota Rush', 'brand_mobil' => 'Toyota', 'warna_mobil' => 'Hitam', 'tipe_mobil' => 'SUV', 'harga_mobil' => '295000000', 'simpan' => '1'];
    [$status, , $headers] = request('tambah.php', $mobil);
    periksa($status === 303 && str_contains($headers, 'Location: index.php?pesan=1'), 'Tambah tidak redirect setelah berhasil.');
    $id = (int) mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM showroom_mobil WHERE kode_mobil = 'MBL-DEMO'"))['id'];
    [, $body] = request('index.php');
    periksa(str_contains($body, 'Jumlah hasil: 4'), 'Jumlah setelah tambah salah.');
    [, $body] = request('tambah.php', $mobil);
    periksa(str_contains($body, 'Kode mobil sudah dipakai.') && str_contains($body, 'value="Toyota Rush"'), 'Kode duplikat atau input bertahan gagal.');
    foreach (['', '-5', '0', '295.000.000', 'Rp295000000', '1e8', '100000000000', '0,001'] as $harga) {
        [, $body] = request('tambah.php', array_replace($mobil, ['kode_mobil' => 'MBL-INVALID', 'harga_mobil' => $harga]));
        periksa(str_contains($body, 'Harga harus berupa angka positif'), 'Harga tidak valid diterima: ' . $harga);
    }
    [, $body] = request('tambah.php', array_replace($mobil, ['nama_mobil' => '']));
    periksa(str_contains($body, 'Semua kolom wajib diisi.'), 'Kolom kosong diterima.');
    [, $body] = request('tambah.php', array_replace($mobil, ['kode_mobil' => ['rusak']]));
    periksa(str_contains($body, 'Semua kolom wajib diisi.'), 'Payload array tidak ditangani.');
    [, $body] = request('tambah.php', array_replace($mobil, ['kode_mobil' => str_repeat('A', 21)]));
    periksa(str_contains($body, 'maksimal 20 karakter'), 'Batas panjang server gagal.');
    [, $body] = request('detail.php?id=' . $id);
    periksa(str_contains($body, 'Toyota Rush') && str_contains($body, '295.000.000'), 'Detail mobil salah.');
    foreach (['abc', '-1', '999999', '1abc', ''] as $idTidakValid) {
        [$status, $body] = request('detail.php?id=' . $idTidakValid);
        periksa($status === 404 && str_contains($body, 'Kembali ke daftar mobil'), 'ID tidak valid tidak ditangani.');
    }
    [$status] = request('edit.php?id=abc');
    periksa($status === 404, 'Edit ID tidak valid gagal.');
    [$status] = request('hapus.php?id=abc');
    periksa($status === 404, 'Hapus ID tidak valid gagal.');
    [, $body] = request('edit.php?id=' . $id);
    periksa(str_contains($body, 'value="Toyota Rush"') && str_contains($body, 'value="Hitam"'), 'Form edit tidak memuat data lama.');
    [, $body] = request('edit.php?id=' . $id, array_replace($mobil, ['kode_mobil' => 'MBL-001']));
    periksa(str_contains($body, 'Kode mobil sudah dipakai mobil lain.'), 'Edit menerima kode mobil lain.');
    [, $body] = request('edit.php?id=' . $id, array_replace($mobil, ['harga_mobil' => '-5']));
    periksa(str_contains($body, 'Harga harus berupa angka positif'), 'Edit menerima harga tidak valid.');
    $mobil['warna_mobil'] = 'Putih';
    $mobil['harga_mobil'] = '300000000';
    [$status] = request('edit.php?id=' . $id, $mobil);
    periksa($status === 303, 'Edit dengan kode sendiri gagal.');
    [, $body] = request('detail.php?id=' . $id);
    periksa(str_contains($body, 'Putih') && str_contains($body, '300.000.000'), 'Perubahan tidak tersimpan.');
    [, $body] = request('index.php');
    periksa(str_contains($body, 'Jumlah hasil: 4'), 'Edit mengubah jumlah mobil.');
    $mobil['nama_mobil'] = 'Rush "O\'Neil" <script>alert(1)</script>';
    $mobil['harga_mobil'] = '300000000,50';
    [$status] = request('edit.php?id=' . $id, $mobil);
    periksa($status === 303, 'Tanda kutip atau harga sen gagal disimpan.');
    [, $body] = request('detail.php?id=' . $id);
    periksa(!str_contains($body, '<script>') && str_contains($body, '&lt;script&gt;') && str_contains($body, 'O&#039;Neil'), 'Output mobil tidak di-escape.');
    $tersimpan = mysqli_fetch_assoc(mysqli_query($conn, 'SELECT harga_mobil FROM showroom_mobil WHERE id = ' . $id));
    periksa($tersimpan['harga_mobil'] === '300000000.50', 'Harga sen tidak tersimpan persis.');
    [, $body] = request('hapus.php?id=' . $id);
    periksa(str_contains($body, 'Ya, hapus mobil') && str_contains($body, 'detail.php?id=' . $id), 'Konfirmasi atau tautan Batal salah.');
    [$status] = request('detail.php?id=' . $id);
    periksa($status === 200, 'GET konfirmasi menghapus mobil.');
    [$status] = request('hapus.php?id=' . $id, ['hapus' => '1']);
    periksa($status === 303, 'Konfirmasi POST gagal menghapus.');
    [$status] = request('detail.php?id=' . $id);
    periksa($status === 404, 'Mobil masih ada setelah hapus.');
    [, $body] = request('index.php');
    periksa(str_contains($body, 'Jumlah hasil: 3'), 'Jumlah akhir tidak kembali tiga.');
    mysqli_query($conn, 'DELETE FROM showroom_mobil');
    [, $body] = request('index.php');
    periksa(str_contains($body, 'Jumlah hasil: 0') && str_contains($body, 'tambah.php'), 'Database kosong tidak dapat ditampilkan.');
    [$status] = request('tambah.php', array_replace($mobil, ['kode_mobil' => 'MBL-BARU']));
    periksa($status === 303, 'Tidak dapat menambah dari database kosong.');
    $isiLog = file_get_contents($log);
    periksa(!preg_match('/PHP (Warning|Fatal error|Parse error)/', $isiLog), 'Ada error PHP di log server.');
    echo 'PASS: ' . $jumlah . " pemeriksaan CRUD, validasi, pencarian, redirect, escaping, dan database kosong.\n";
} catch (Throwable $error) {
    fwrite(STDERR, 'FAIL: ' . $error->getMessage() . "\n");
    if (is_file($log)) {
        fwrite(STDERR, file_get_contents($log));
    }
    $gagal = true;
} finally {
    if (is_resource($server)) {
        proc_terminate($server);
        proc_close($server);
    }
    mysqli_query($conn, 'DROP DATABASE IF EXISTS `' . $database . '`');
    mysqli_close($conn);
    unlink($log);
}
exit(isset($gagal) ? 1 : 0);
