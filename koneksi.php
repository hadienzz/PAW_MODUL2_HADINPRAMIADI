<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Default sesuai XAMPP. Environment variable dapat dipakai untuk database tes.
try {
    $conn = mysqli_connect(
        getenv('DB_HOST') ?: '127.0.0.1',
        getenv('DB_USER') ?: 'root',
        getenv('DB_PASSWORD') === false ? '' : getenv('DB_PASSWORD'),
        getenv('DB_NAME') ?: 'paw_modul2_showroom',
        (int) (getenv('DB_PORT') ?: 3306)
    );
    mysqli_set_charset($conn, 'utf8mb4');
} catch (mysqli_sql_exception $error) {
    error_log($error->getMessage());
    http_response_code(503);
    exit('Database belum tersedia. Aktifkan MySQL, import database.sql, lalu periksa pengaturan koneksi.php.');
}

require_once __DIR__ . '/fungsi.php';
