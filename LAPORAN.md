# Laporan Praktikum PAW - Modul 2

Nama: Hadin Pramiadi  
NIM: 102042500177  
Materi: Praktik Showroom, halaman 34-52

## Tujuan

Membuat aplikasi sederhana untuk mengelola data mobil menggunakan PHP dan MySQL. Operasi yang diterapkan adalah Create, Read, Update, dan Delete (CRUD), ditambah pencarian berdasarkan nama atau brand mobil.

## Database

Database `paw_modul2_showroom` memiliki tabel `showroom_mobil`. Kolom `id` menjadi primary key dengan AUTO_INCREMENT. Kolom `kode_mobil` memiliki aturan UNIQUE agar dua mobil tidak menggunakan kode yang sama. Nama, brand, warna, dan tipe disimpan sebagai VARCHAR, sedangkan harga memakai DECIMAL(13,2). Tiga mobil awal dimasukkan melalui `database.sql`.

## Hubungan antarfile

Semua halaman memuat `koneksi.php` untuk mendapatkan koneksi database dalam variabel `$conn`. Berkas itu juga memuat `fungsi.php` yang berisi pemeriksaan isian dan fungsi pendukung. Tampilan setiap halaman menggunakan `style.css`.

`index.php` menjadi halaman utama. Dari halaman ini, pengguna dapat menuju `tambah.php`, `detail.php`, `edit.php`, atau `hapus.php`. Halaman tambah dan edit menggunakan `form-mobil.php` untuk menampilkan enam kolom isian yang sama. Query penyimpanannya tetap ditulis terpisah pada halaman masing-masing.

## Alur tombol Simpan mobil

1. Pengguna membuka `tambah.php` dan mengisi kode, nama, brand, warna, tipe, serta harga.
2. Tombol **Simpan mobil** mengirim form dengan metode POST ke `tambah.php`.
3. PHP membaca dan merapikan spasi di awal/akhir isian. Isian bukan teks ditangani sebagai isian kosong.
4. PHP memeriksa semua kolom wajib, panjang teks, dan harga. Harga harus positif, tanpa Rp/titik, dan tidak melebihi batas.
5. Query SELECT memeriksa apakah kode mobil sudah digunakan. Constraint UNIQUE pada database juga mencegah kode ganda.
6. Jika valid, query `INSERT INTO showroom_mobil (...) VALUES (...)` menyimpan satu mobil baru. Nilai isian dikirim melalui parameter query.
7. Setelah INSERT berhasil, `header('Location: index.php?pesan=1', true, 303)` mengarahkan browser ke halaman daftar. `exit` menghentikan kode halaman tambah.
8. `index.php` membaca ulang data dan menampilkan pesan **Perubahan data berhasil disimpan.** Mobil terbaru muncul paling atas karena `ORDER BY id DESC`.

Jika validasi gagal, INSERT tidak dijalankan. Pesan kesalahan muncul di atas form dan isian sebelumnya tetap ditampilkan agar dapat diperbaiki.

## Alur tombol Simpan perubahan

Pengguna membuka `edit.php?id=...`. PHP menjalankan SELECT berdasarkan ID, lalu mengisi form menggunakan data lama. Setelah tombol **Simpan perubahan** diklik, form mengirim POST kembali ke halaman edit dengan ID yang sama.

Pemeriksaan isian sama dengan tambah. Pemeriksaan kode memakai kondisi `kode_mobil = ? AND id <> ?`, sehingga kode milik mobil yang sedang diedit tetap diperbolehkan. Jika valid, query `UPDATE showroom_mobil SET ... WHERE id = ?` mengubah mobil tersebut. Setelah berhasil, browser diarahkan ke `index.php?pesan=1`. Jumlah mobil tetap sama karena UPDATE tidak menambah baris baru.

## Daftar, pencarian, dan detail

`index.php` membaca kata kunci dari parameter GET `cari`. Query SELECT menggunakan `LIKE` pada `nama_mobil` atau `brand_mobil`. Pencarian Honda menghasilkan Honda City. Jika tidak ada kecocokan, halaman menampilkan **Data tidak ditemukan** dan tautan tambah mobil.

`detail.php` menerima ID melalui GET, mencari satu mobil, lalu menampilkan kode, nama, brand, warna, tipe, dan harga. ID yang tidak valid atau tidak ditemukan menghasilkan status 404 beserta tautan kembali ke daftar.

## Konfirmasi hapus

Membuka `hapus.php?id=...` hanya menampilkan konfirmasi. Tautan **Batal** kembali ke detail tanpa menghapus data. Tombol **Ya, hapus mobil** mengirim POST dengan kolom `hapus`, kemudian PHP menjalankan `DELETE FROM showroom_mobil WHERE id = ?`. Setelah berhasil, browser kembali ke daftar dan jumlah mobil berkurang satu.

## Hasil pengujian

Pengujian HTTP otomatis pada database terpisah menghasilkan **47 pemeriksaan lulus**. Skenario modul berhasil dijalankan: tiga data awal tampil; pencarian Honda menghasilkan satu data; Toyota Rush dapat ditambah menjadi empat data; input kosong, harga negatif, dan kode duplikat ditolak; warna dan harga Rush dapat diubah tanpa menambah jumlah; membuka konfirmasi tidak menghapus data; POST konfirmasi menghapus Rush sehingga jumlah kembali tiga.

Pemeriksaan tambahan meliputi ID tidak valid, batas panjang teks, payload array, karakter kutip, escaping HTML, harga dengan sen, serta kondisi database kosong yang tetap menampilkan halaman dan mengizinkan penambahan mobil. Semua berkas PHP juga diperiksa dengan `php -l`.
