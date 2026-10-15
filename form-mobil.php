<?php // Form yang sama digunakan halaman tambah dan edit; penyimpanannya tetap terpisah. 
?>
<?php if ($pesan !== ''): ?>
    <div id="kesalahan" class="kesalahan" role="alert"><?= escapeHtml($pesan) ?></div>
<?php endif; ?>
<form method="post">
    <label for="kode">Kode mobil</label>
    <input id="kode" name="kode_mobil" maxlength="20" placeholder="Contoh: MBL-004" value="<?= escapeHtml($mobil['kode_mobil']) ?>">
    <label for="nama">Nama mobil</label>
    <input id="nama" name="nama_mobil" maxlength="100" placeholder="Contoh: Toyota Rush" value="<?= escapeHtml($mobil['nama_mobil']) ?>">
    <label for="brand">Brand</label>
    <input id="brand" name="brand_mobil" maxlength="50" placeholder="Contoh: Toyota" value="<?= escapeHtml($mobil['brand_mobil']) ?>">
    <label for="warna">Warna</label>
    <input id="warna" name="warna_mobil" maxlength="30" placeholder="Contoh: Hitam" value="<?= escapeHtml($mobil['warna_mobil']) ?>">
    <label for="tipe">Tipe (contoh: MPV, SUV, Sedan)</label>
    <input id="tipe" name="tipe_mobil" maxlength="20" placeholder="Contoh: SUV" value="<?= escapeHtml($mobil['tipe_mobil']) ?>">
    <label for="harga">Harga (angka saja, tanpa Rp dan titik)</label>
    <input id="harga" name="harga_mobil" inputmode="decimal" placeholder="Contoh: 295000000" value="<?= escapeHtml($mobil['harga_mobil']) ?>" aria-describedby="petunjuk-harga">
    <small id="petunjuk-harga">Semua kolom wajib diisi. Jika ada sen, gunakan koma, misalnya 295000000,50.</small>
    <br><button type="submit" name="simpan" value="1"><?= escapeHtml($teksTombol) ?></button>
</form>