<?php
function inputTeks(array $input, string $nama): string
{
    return isset($input[$nama]) && is_string($input[$nama]) ? trim($input[$nama]) : '';
}

function escapeHtml(string $nilai): string
{
    return htmlspecialchars($nilai, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function bacaIsianMobil(array $input): array
{
    $mobil = [];
    foreach (['kode_mobil', 'nama_mobil', 'brand_mobil', 'warna_mobil', 'tipe_mobil', 'harga_mobil'] as $kolom) {
        $mobil[$kolom] = inputTeks($input, $kolom);
    }
    return $mobil;
}

function validasiMobil(array $mobil): string
{
    $batas = ['kode_mobil' => 20, 'nama_mobil' => 100, 'brand_mobil' => 50, 'warna_mobil' => 30, 'tipe_mobil' => 20];
    foreach ($batas as $kolom => $panjang) {
        if ($mobil[$kolom] === '') {
            return 'Semua kolom wajib diisi.';
        }
        if (mb_strlen($mobil[$kolom], 'UTF-8') > $panjang) {
            return 'Isian ' . str_replace('_', ' ', $kolom) . ' maksimal ' . $panjang . ' karakter.';
        }
    }

    // Harga ditulis tanpa pemisah ribuan; koma opsional untuk maksimal dua digit sen.
    $harga = $mobil['harga_mobil'];
    if (
        !preg_match('/^[0-9]+(?:,[0-9]{1,2})?$/D', $harga)
        || (float) str_replace(',', '.', $harga) <= 0
        || (float) str_replace(',', '.', $harga) > 99999999999
    ) {
        return 'Harga harus berupa angka positif, tanpa Rp dan titik (maksimal 99999999999; sen memakai koma).';
    }
    return '';
}

function cariMobil(mysqli $conn, array $query): array
{
    $id = filter_var(inputTeks($query, 'id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $statement = mysqli_prepare($conn, 'SELECT * FROM showroom_mobil WHERE id = ?');
    mysqli_stmt_bind_param($statement, 'i', $id);
    mysqli_stmt_execute($statement);
    $mobil = mysqli_fetch_assoc(mysqli_stmt_get_result($statement));
    if (!$mobil) {
        http_response_code(404);
        exit('<!DOCTYPE html><html lang="id"><meta charset="UTF-8"><title>Mobil tidak ditemukan</title><link rel="stylesheet" href="style.css"><main><h1>Mobil tidak ditemukan.</h1><a href="index.php">Kembali ke daftar mobil</a></main></html>');
    }
    return $mobil;
}

function kodeSudahDipakai(mysqli $conn, string $kode, int $id = 0): bool
{
    $statement = mysqli_prepare($conn, 'SELECT id FROM showroom_mobil WHERE kode_mobil = ? AND id <> ?');
    mysqli_stmt_bind_param($statement, 'si', $kode, $id);
    mysqli_stmt_execute($statement);
    return mysqli_num_rows(mysqli_stmt_get_result($statement)) > 0;
}
