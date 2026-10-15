# Praktikum PAW - Modul 2, halaman 34-52

Nama: Hadin Pramiadi  
NIM: 102042500177

Aplikasi **ShowRoom EAD** menggunakan PHP, MySQL/MariaDB, dan CSS sesuai praktik pada modul. Fitur: daftar dan pencarian mobil, tambah, detail, edit, serta konfirmasi hapus. Tiga data awal: Avanza Veloz, Honda City, dan Suzuki Baleno.

## Menjalankan

1. Aktifkan MySQL di XAMPP.
2. Import `database.sql` sekali melalui phpMyAdmin. Database yang dibuat adalah `paw_modul2_showroom`.
3. Pengaturan bawaan di `koneksi.php`: host `127.0.0.1`, port `3306`, akun `root`, password kosong. Sesuaikan jika berbeda.
4. Buka terminal di folder proyek dan jalankan:

   ```powershell
   php -S 127.0.0.1:8177
   ```

5. Buka [ShowRoom EAD](http://127.0.0.1:8177/index.php). Untuk mengecek koneksi, buka [cek koneksi](http://127.0.0.1:8177/cek_koneksi.php).

Alternatif: salin folder proyek ke `C:\xampp\htdocs`, aktifkan Apache dan MySQL, lalu buka `http://localhost/PAW_HadinPramiadi_102042500177_Week5/index.php`.

Environment variable opsional: `DB_HOST`, `DB_PORT`, `DB_USER`, `DB_PASSWORD`, dan `DB_NAME`. Tanpa pengaturan tambahan, aplikasi menggunakan koneksi bawaan di atas. Jangan import ulang SQL ke tabel yang sudah berisi data; berkas tersebut sengaja mengikuti langkah import sekali pada modul.

## Berkas

| Berkas | Fungsi |
| --- | --- |
| `database.sql` | Membuat database, tabel, dan tiga data awal. |
| `koneksi.php` | Menyiapkan koneksi MySQL dan charset UTF-8. |
| `style.css` | Tampilan bersama sesuai modul. |
| `index.php` | Daftar, pencarian nama/brand, dan pesan hasil penyimpanan. |
| `tambah.php` | Form tambah dan query INSERT. |
| `detail.php` | Menampilkan satu mobil berdasarkan ID. |
| `edit.php` | Form edit dan query UPDATE. |
| `hapus.php` | Konfirmasi dan query DELETE setelah POST. |
| `cek_koneksi.php` | Pemeriksaan koneksi seperti contoh halaman 36. |
| `fungsi.php` | Pembacaan input, validasi, escaping, pencarian ID, dan pemeriksaan kode unik. |
| `form-mobil.php` | Form bersama untuk tambah dan edit. |
| `LAPORAN.md` | Penjelasan hubungan antarfile dan alur tombol Simpan untuk halaman 52. |
| `tests/showroom-test.php` | Uji HTTP dengan database tes terpisah. |

## Validasi dan penyesuaian

Semua kolom wajib diisi. Harga harus positif dan maksimal `99999999999`, tanpa `Rp` dan tanpa titik. Contoh: `295000000`. Untuk sen, gunakan koma, misalnya `295000000,50`; aplikasi mengubahnya ke format DECIMAL saat menyimpan. Kode mobil harus unik, termasuk ketika diedit. Kegagalan validasi mempertahankan isian form.

Alur dan desain mengikuti modul. Tambahan kecil: prepared statement untuk query, escaping HTML ketika menampilkan isian, respons 404 dengan tautan kembali untuk ID tidak ditemukan, redirect 303 setelah penyimpanan, serta pesan koneksi database. Form dan validasi yang sama dipakai bersama agar tambah dan edit konsisten.

## Pemeriksaan

```powershell
Get-ChildItem -Recurse -Filter *.php | ForEach-Object { php -l $_.FullName }
php tests/showroom-test.php
```

Tes memerlukan PHP dengan `mysqli`, `mbstring`, dan `curl`, serta MySQL yang berjalan. Akun database harus dapat membuat/menghapus database tes. Tes membuat database `paw_showroom_test_<acak>`, menjalankan server PHP sementara, lalu menghentikan server dan menghapus database tes. Data aplikasi tidak digunakan oleh tes.

Hasil eksekusi: **47 pemeriksaan lulus**, meliputi tiga data awal, pencarian, tambah, validasi harga/kolom/panjang, kode duplikat, detail, ID tidak valid, edit, input bertahan, escaping HTML, konfirmasi hapus, redirect, dan database kosong yang tetap dapat ditambah.
