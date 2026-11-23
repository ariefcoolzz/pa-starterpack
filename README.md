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
| Beranda v2: kartu berita headline, widget sidebar, jam kerja, statistik, footer 4 kolom | selesai |
| Halaman artikel pola Badilag (judul tengah, meta penulis/terbit/dibaca, gambar utama, satu kolom) | selesai |
| Pengumuman beranda sebagai bilah ringkas (`strip`) | selesai |
| Menu daftar kategori (Berita Terkini, Pengumuman) + tautan artikel SEF | selesai — 0 tautan internal 404 |
| Agenda sidang beranda (`mod_pa_agenda`, subform) | selesai |
| Galeri foto dengan lightbox + video klik-putar | selesai |
| Modul `mod_pa_jamkerja` (status buka/tutup) | selesai |
| Template Joomla `index.php` + override | selesai (terpasang di sandbox) |
| Seeder Joomla (kategori, artikel, menu, modul) | selesai — idempoten (`dibuat 0` pada jalan kedua) |
| Auditor 35 URL + estimasi skor | selesai (35 URL 200; 11 halaman tanpa placeholder) |
| Paket ekstensi (`dist/*.zip`) | selesai |
| Quickstart (inti + data + installer) | belum |

## Lingkungan uji

```
situs   : http://pa-satker-sandbox.test   (Herd, PHP 8.4 via `herd isolate 8.4`)
folder  : D:\projects\pa-satker-sandbox
database: pa_starter @ 127.0.0.1:3307 (MariaDB 11.6.2), user pa_starter, prefix jos_
admin   : user `admin` — sandi dari perintah instalasi (lihat riwayat sesi)
CLI     : php cli/pa.php seed | audit http://pa-satker-sandbox.test
```

Catatan dev: `herd isolate` wajib dijalankan di folder sandbox, kalau tidak Herd memakai PHP 7.4
dan Joomla menolak jalan. Kelas komponen admin berada di namespace `PA\Starter\Administrator\`
(konvensi Joomla 6 untuk sisi administrator).

## Diketahui belum beres

- 5 artikel duplikat sisa seed pertama (id 53–57, landing seksi) masih ada di DB uji; aman
  dihapus manual. Seeder sudah diperbaiki agar tidak terulang (pencarian alias global).
- Sidebar halaman dalam belum diisi modul (halaman tampil lebar penuh).
- Pencarian memakai `com_finder`; indeks belum dibangun (`finder:index`) sehingga hasil kosong.
- Quickstart, dokumentasi operator, dan uji instalasi bersih belum dikerjakan.

## Lisensi

GPL-2.0-or-later. Logo Mahkamah Agung RI berasal dari sumber resmi Badilag dan
digunakan hanya untuk situs resmi satuan kerja.
