# Starterpack Website Satker Peradilan Agama (Joomla 6.2)

Paket awal (starterpack) untuk website **Pengadilan Agama** yang mengikuti
**Keputusan Dirjen Badan Peradilan Agama Nomor 2722/DJA/SK.TI1/X/2026**
tentang Petunjuk Teknis Pembaruan, Tata Kelola, dan Evaluasi Website Satuan Kerja
(tanggal 12 Oktober 2026).

Dua bentuk distribusi:

1. **Quickstart** — inti Joomla 6.2.0 + data contoh siap pakai + installer satu klik.
2. **Paket ekstensi** — template `pa_satker`, komponen `com_pastarter`, modul
   `mod_pa_kunjungan` untuk situs yang sudah memakai Joomla 6.2.

## Prinsip

- **Satu butir penilaian = satu URL tetap.** URL lahir dari menu item, bukan alias
  artikel, sehingga judul artikel boleh berubah tanpa memindahkan tautan.
- **Sumber kebenaran tunggal**: `manifest/indicators.json` (35 butir) dan
  `manifest/sections.json` (seksi + halaman tambahan). Seeder, auditor, dan
  dokumen pemetaan dibangkitkan dari berkas itu.
- **Placeholder bisa dihitung mesin**: `<a href="#" data-pa-kosong="1">` menandai
  dokumen yang belum diunggah. Halaman dianggap siap bila jumlahnya nol.
- **Tanpa CDN dan tanpa layanan berlangganan**: CSS sendiri, font lokal, ikon lokal,
  aksesibilitas memakai plugin inti Joomla.

## Struktur

```
manifest/     indicators.json, sections.json — sumber kebenaran
assets/       slider, ikon aplikasi, flyer, logo (hasil tools/assets.py)
packages/     tpl_pa_satker, com_pastarter, mod_pa_kunjungan
quickstart/   install.php (wizard web + CLI) untuk paket quickstart
tests/        PHPUnit untuk logika murni
tools/        assets.py, preview.php, build.php, verify.sh
docs/         panduan instalasi, operator, server, peta SINOVA
```

## Perintah

```bash
# dependensi pengujian
composer install

# pengujian logika murni (manifest, kesiapan, skor, perender blok)
vendor/bin/phpunit

# membuat aset visual contoh (deterministik)
python tools/assets.py --out=D:/projects/pa-starterpack/assets

# pratinjau statis 47 halaman (tanpa Joomla)
php tools/preview.php     # → dist/preview/index.html
```

## Status

| Bagian | Status |
|---|---|
| Manifest 35 butir + 6 halaman tambahan | selesai |
| Logika murni + tes (23 tes) | selesai |
| Aset visual contoh | selesai |
| Template CSS + pratinjau statis | selesai (menunggu persetujuan visual) |
| Foto asli satker (gedung, PTSP, mediasi) + pipeline crop | selesai |
| Panel aksesibilitas (teks, kontras, garis bawah, persistensi) | selesai (teruji di pratinjau) |
| Template Joomla `index.php` + override | belum |
| Seeder Joomla + auditor | belum |
| Paket ekstensi + quickstart | belum |

## Lisensi

GPL-2.0-or-later. Logo Mahkamah Agung RI berasal dari sumber resmi Badilag dan
digunakan hanya untuk situs resmi satuan kerja.
