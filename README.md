# Starterpack Website Satker Pengadilan Agama (Joomla 6.2)

Paket awal untuk membangun website resmi **Pengadilan Agama** yang mengikuti
**Keputusan Dirjen Badan Peradilan Agama Nomor 2722/DJA/SK.TI1/X/2026** tentang
Petunjuk Teknis Pembaruan, Tata Kelola, dan Evaluasi Website Satuan Kerja
(tanggal 12 Oktober 2026).

Isi paket: template Joomla 6.2 untuk tampilan publik, komponen penyedia data contoh
dan audit kepatuhan, tiga modul beranda, serta 35 halaman indikator SINOVA yang
sudah beralamat tetap dari menu item.

> Proyek komunitas, bukan produk resmi Mahkamah Agung RI atau Ditjen Badilag.
> Tampilan mengacu pada situs Badilag dan situs satker yang sudah berjalan.
> Logo Mahkamah Agung RI disertakan untuk kebutuhan situs resmi satuan kerja;
> unggah logo masing-masing satker melalui panel admin.

![Beranda](docs/screenshots/beranda.webp)
![Halaman artikel](docs/screenshots/artikel.webp)
![Halaman indikator SINOVA](docs/screenshots/halaman-sinova.webp)

## Daftar Isi

- [Fitur](#fitur)
- [Persyaratan](#persyaratan)
- [Instalasi pada situs Joomla 6.2 yang sudah ada](#instalasi-pada-situs-joomla-62-yang-sudah-ada)
- [Instalasi dari nol](#instalasi-dari-nol)
- [Jalur pengembang](#jalur-pengembang)
- [Langkah pertama setelah instalasi](#langkah-pertama-setelah-instalasi)
- [Memperbarui paket](#memperbarui-paket)
- [Lupa kata sandi admin](#lupa-kata-sandi-admin)
- [Keamanan dan checklist server](#keamanan-dan-checklist-server)
- [Struktur dan pengembangan](#struktur-dan-pengembangan)
- [Status dan rencana](#status-dan-rencana)
- [Kontribusi, lisensi, dan kredit](#kontribusi-lisensi-dan-kredit)

## Fitur

### Situs publik

- **Enam menu konsolidasi** sesuai Bab III huruf F: Beranda, Profil Pengadilan,
  Layanan Hukum, Transparansi, Layanan Publik & Pengaduan, Berita & Publikasi.
- **Satu URL tetap per kriteria SINOVA** (35 halaman), alamatnya lahir dari menu item
  dan bukan dari alias artikel. Judul artikel boleh berubah tanpa memindahkan tautan
  yang sudah diinput ke aplikasi SINOVA.
- **Beranda pola situs instansi**: kartu berita utama dengan ringkasan, pengumuman
  satu kolom berbadge tanggal, agenda sidang, galeri foto dengan lightbox, video
  klik-putar, sambutan Ketua, serta sidebar alih bahasa, jam kerja, survei, dan
  statistik kunjungan.
- **Halaman artikel pola Badilag**: judul di tengah, baris penulis, tanggal terbit,
  jumlah dibaca, gambar utama, isi satu kolom selebar konten.
- **Panel aksesibilitas**: perbesar teks, kontras tinggi, garis bawah tautan, dan
  pengaturan yang tersimpan antar halaman.
- **Tanpa CDN dan tanpa layanan berbayar**: CSS, JavaScript, ikon, dan font
  di-host sendiri; tidak ada permintaan ke domain pihak ketiga.
- **Placeholder terhitung mesin**: tautan dokumen yang belum ada ditandai
  `<a href="#" data-pa-kosong="1">`, sehingga jumlah dokumen kurang bisa dihitung
  otomatis.

### Komponen `com_pastarter` di panel admin

| Bagian | Keterangan |
|---|---|
| Seeder data contoh | Membuat kategori, artikel berita dan pengumuman, 35 halaman indikator, menu, dan modul beranda dari manifest. Idempoten: jalan kedua tidak menggandakan data |
| Audit kepatuhan | Memeriksa 35 URL indikator: status HTTP, keberadaan judul, dan jumlah placeholder; menampilkan estimasi skor per kelompok penilaian |
| Manifest terbaca mesin | `manifest/indicators.json` (35 butir) dan `manifest/sections.json` (5 seksi) menjadi sumber tunggal untuk halaman, menu, dan pemeriksaan |

### Modul beranda

| Modul | Fungsi |
|---|---|
| `mod_pa_agenda` | Jadwal sidang dengan subform tanggal, jam, ruang, dan jenis perkara |
| `mod_pa_jamkerja` | Status layanan buka atau tutup berdasarkan hari dan jam kerja satker |
| `mod_pa_bahasa` | Tautan alih bahasa ke kanal resmi berbahasa Inggris |

## Persyaratan

| Komponen | Versi |
|---|---|
| Joomla | 6.2.x |
| PHP | 8.3 atau lebih baru untuk Joomla (diuji pada PHP 8.4), ekstensi `intl`, `mbstring`, `gd`, `mysqli`, `json`, `fileinfo`, `zip` |
| Database | MariaDB 10.4+ atau MySQL 8.0.13+ (`utf8mb4_unicode_ci`) |
| Web server | Apache 2.4 dengan `mod_rewrite`, Nginx, atau LiteSpeed |
| Composer | Hanya untuk pengembang (menjalankan tes) |

## Instalasi pada situs Joomla 6.2 yang sudah ada

Cara ini tidak memerlukan terminal. Cocok untuk satker yang situsnya sudah jalan
dan hanya ingin mengganti tampilan serta menambah halaman indikator.

1. Unduh paket rilis dari halaman
   [**Releases**](https://github.com/ariefcoolzz/pa-starterpack/releases/latest)
   (atau bangun sendiri dari sumber dengan `php tools/build.php --docs --release`):
   - **Disarankan (1x unggah):** `pkg_pastarter.zip` — memasang template, komponen,
     tiga modul, dan aset contoh sekaligus.
   - **Arsip lengkap:** `pa-starterpack-ekstensi-v1.0.0.zip` — berisi `pkg_pastarter.zip`,
     folder `ekstensi/` (5 zip terpisah bila batas unggah server kecil), `SINOVA-URL-MAP.csv`,
     `CHECKSUMS.sha256`, dan `PANDUAN-INSTALASI.txt`.
2. Masuk ke panel admin Joomla, buka **System → Install → Extensions**.
3. Unggah `pkg_pastarter.zip` pada tab *Upload Package File* (atau unggah kelima
   berkas di `ekstensi/` satu per satu). Aset gambar bawaan otomatis disalin ke
   folder `/images/` tanpa menimpa berkas milik satker yang sudah ada.
4. Buka **System → Site Template Styles**, pilih `pa_satker - Default`, lalu klik **Default**.
5. Buka **Components → PA Starter → Pasang Konten Awal**, lalu klik **Pasang konten awal**.
   Proses ini membuat kategori, 35 halaman indikator, halaman tambahan, berita contoh,
   menu, dan modul beranda secara idempoten.
6. Buka **Components → PA Starter → Audit Kesiapan SINOVA**, lalu periksa hasilnya. Halaman
   yang masih memuat placeholder ditandai agar bisa dilengkapi.

Data contoh memakai nama "Pengadilan Agama Contoh". Ganti identitas satker sebelum
situs dibuka ke publik. Rincian lengkap tersedia di `PANDUAN-INSTALASI.txt` di dalam
arsip unduhan.

## Instalasi dari nol

### Jalur 1 — Paket Quickstart (disarankan, siap pakai)

1. Unduh `pa-starterpack-quickstart-joomla62-v1.0.0.zip` dari halaman
   [**Releases**](https://github.com/ariefcoolzz/pa-starterpack/releases/latest)
   (atau bangun dari sumber dengan `php tools/build-quickstart.php`).
2. Buat database kosong, misalnya `pa_satker`, dengan collation `utf8mb4_unicode_ci`.
3. Ekstrak seluruh isi folder `joomla/` ke document root situs
   (misalnya `public_html` atau `C:\xampp\htdocs\pa-contoh`), dan salin/ubah nama
   `htaccess.txt` menjadi `.htaccess` (untuk Apache/LiteSpeed).
4. Buka alamat situs di browser dan ikuti wizard pemasangan Joomla 6 (nama situs,
   akun Super User, serta kredensial database). Template, komponen, modul, aset, dan
   35 halaman indikator SINOVA terpasang otomatis pada tahap instalasi database.
5. Klik **Complete & Remove Installation Folder** di layar akhir (atau hapus folder
   `installation/` secara manual), lalu masuk ke `/administrator/` dan pastikan
   **System → Global Configuration → Site → SEO** mengaktifkan *Search Engine Friendly URLs*
   dan *Use URL Rewriting*.

### Jalur 2 — Joomla 6.2 polos + `pkg_pastarter.zip`

1. Unduh Joomla 6.2 dari situs resmi Joomla, ekstrak ke document root situs, lalu
   selesaikan wizard pemasangan hingga folder `installation/` terhapus.
2. Pasang `pkg_pastarter.zip` melalui **System → Install → Extensions**, lalu ikuti
   langkah 4–6 pada bagian [Instalasi pada situs Joomla 6.2 yang sudah ada](#instalasi-pada-situs-joomla-62-yang-sudah-ada).

Untuk server Linux Nginx, atur document root ke folder tempat Joomla dipasang dan
arahkan seluruh permintaan ke `index.php`:

```nginx
server {
    listen 443 ssl;
    server_name pa-contoh.go.id;
    root /var/www/pa-contoh;
    index index.php;
    client_max_body_size 32m;

    location / {
        try_files $uri $uri/ /index.php?$args;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
    }

    location ~* /(cache|logs|tmp|administrator/logs)/ {
        deny all;
    }
}
```

## Jalur pengembang

Prasyarat: PHP 8.4 (sesuai `composer.json` untuk tes dan pratinjau), Composer, MariaDB atau MySQL, dan Git.

```bash
git clone <alamat-repositori> pa-starterpack
cd pa-starterpack
composer install                           # hanya untuk PHPUnit

php tools/build.php --docs --release       # membangun dist/*.zip, pkg_pastarter.zip, dan arsip ekstensi
php tools/build-quickstart.php             # membangun dist/pa-starterpack-quickstart-joomla62-v1.0.0.zip
vendor/bin/phpunit                         # tes logika murni & invarian manifest (26 tes, 58 asersi)
python tools/assets.py --out=assets        # membuat ulang aset contoh
php tools/preview.php                      # pratinjau statis tanpa Joomla
```

Memasang paket ke situs uji dan menjalankan seeder/audit lewat CLI Joomla (salin
`tools/pa.php` ke `cli/pa.php` di folder situs uji, atau set variabel `JOOMLA_ROOT`):

```bash
php cli/joomla.php extension:install --path=/path/ke/pa-starterpack/dist/pkg_pastarter.zip

PA_MANIFEST=/path/ke/pa-starterpack/manifest PA_REFRESH=1 php cli/pa.php seed
PA_MANIFEST=/path/ke/pa-starterpack/manifest php cli/pa.php audit http://pa-contoh.test
```

`PA_REFRESH=1` menulis ulang isi artikel dan modul dari manifest, berguna saat
menyunting teks contoh. Tanpa variabel itu, seeder hanya mengisi data yang belum ada.

## Langkah pertama setelah instalasi

1. **System → Global Configuration → Site**: nama situs, meta description, dan
   alamat email resmi.
2. **System → Site Template Styles → pa_satker - Default**: periksa logo Mahkamah Agung RI,
   unggah logo satker, lalu isi baris instansi dan jam layanan pada parameter template.
3. **Components → PA Starter → Audit Kesiapan SINOVA**: lihat butir yang belum selesai, lengkapi
   halaman yang masih memuat placeholder (daftar URL tetap tersedia di `SINOVA-URL-MAP.csv`).
4. **Content → Articles**: ganti artikel contoh dengan berita kegiatan satker.
   Sertakan gambar utama (*Intro Image*) agar kartu beranda tidak kosong.
5. **Menus → Main Menu**: sesuaikan label menu bila struktur satker berbeda.
   Alamat halaman indikator jangan diubah karena sudah dipakai sebagai bukti dukung
   di aplikasi SINOVA.
6. **Content → Site Modules**: isi agenda sidang, jam kerja, dan tautan survei pada posisi
   `home-side`, `home-agenda`, dan `footer-2`.
7. **System → Manage → Plugins**: pastikan `System - SEF` aktif dan atur **System → Global
   Configuration → Site → SEO** (*Search Engine Friendly URLs* dan *Use URL Rewriting* = Yes).
8. Buka halaman beranda di ponsel untuk memastikan menu dan kartu berita tetap rapi.

## Memperbarui paket

- **Dari rilis resmi:** unduh `pkg_pastarter.zip` versi terbaru dari halaman
  [**Releases**](https://github.com/ariefcoolzz/pa-starterpack/releases/latest), lalu
  unggah lewat **System → Install → Extensions**.
- **Dari repositori Git:**

```bash
git pull
php tools/build.php --docs --release
```

Lalu unggah ulang `dist/pkg_pastarter.zip` (atau berkas di `dist/` yang berubah) lewat
**System → Install → Extensions**. Seeder bersifat idempoten sehingga konten yang sudah
disunting tidak tertimpa. Cadangkan database sebelum memperbarui.

## Lupa kata sandi admin

Joomla menyediakan jalur resmi tanpa menyentuh database:

```bash
php cli/joomla.php user:reset-password --username=admin
```

Bila CLI tidak tersedia, buka `administrator/index.php`, klik **Lupa kata sandi**,
lalu ikuti tautan yang dikirim ke email admin. Alternatif terakhir: sunting tabel
`#__users` lewat phpMyAdmin dengan hash bcrypt baru dari
`password_hash('sandi-baru', PASSWORD_BCRYPT)`.

## Keamanan dan checklist server

Yang sudah ditangani paket:

| Ketentuan | Implementasi |
|---|---|
| Tanpa komponen pihak ketiga berbayar | Hanya ekstensi yang dibangun di repositori ini, semuanya berlisensi terbuka |
| Tanpa CDN | CSS, JavaScript, ikon, dan font berada di folder template |
| Validasi keluaran | Seluruh teks dinamis dicetak dengan `htmlspecialchars`, isi artikel melewati `content.prepare` Joomla |
| CSRF dan otentikasi | Memakai inti Joomla (token formulir, otentikasi, dan kontrol akses berbasis grup) |
| Placeholder terukur | Dokumen yang belum diunggah ditandai atribut, bukan tautan mati yang menyesatkan |
| Aksesibilitas | Struktur heading berurutan, teks alternatif gambar, panel perbesar teks dan kontras tinggi |

Tanggung jawab tim TI satker:

- PHP dan Joomla versi terbaru, tanpa ekstensi usang.
- HTTPS aktif dengan pengalihan dari HTTP, TLS 1.2 atau lebih baru.
- Database hanya mendengarkan `127.0.0.1` dan memakai akun khusus, bukan root.
- `display_errors` mati di produksi, log galat aktif.
- Backup berkas dan database terjadwal, disimpan di luar server.
- Panel admin dibatasi (IP allowlist atau autentikasi tambahan di web server).
- Folder `cache/`, `logs/`, `tmp/` tidak dapat diakses publik.

## Struktur dan pengembangan

```
manifest/
  indicators.json        # 35 butir SINOVA: id, seksi, path, kriteria, blok isi
  sections.json          # 5 seksi menu + halaman tambahan
  modules.json           # modul beranda: posisi, urutan, parameter
  *.html                 # fragmen HTML untuk modul kustom
packages/
  tpl_pa_satker/         # template: index.php, css, js, override com_content, modul
  com_pastarter/         # komponen admin: Seeder, Auditor, script auto-copy aset
  pkg_pastarter/         # manifest paket gabungan 1x unggah
  mod_pa_agenda/         # jadwal sidang
  mod_pa_jamkerja/       # status jam layanan
  mod_pa_bahasa/         # alih bahasa
assets/                  # gambar dan ikon hasil tools/assets.py
tools/                   # build.php, build-quickstart.php, pa.php, assets.py, preview.php, split-intro.sql
tests/                   # PHPUnit untuk logika murni & invarian manifest
docs/
  PANDUAN-INSTALASI-EKSTENSI.txt
  PANDUAN-INSTALASI-QUICKSTART.txt
  SINOVA-URL-MAP.csv
  screenshots/           # gambar pratinjau untuk README
dist/                    # hasil build & arsip rilis .zip (tidak masuk repositori; tersedia di Releases)
```

Menambah satu butir indikator:

1. Tambahkan objek baru pada `manifest/indicators.json`: `id`, `no`, `title`,
   `section`, `path`, `criterion`, `checks`, dan `blocks`.
2. Jalankan `PA_MANIFEST=... php cli/pa.php seed` pada situs uji. Halaman, menu, dan
   artikel dibuat otomatis.
3. Jalankan `php cli/pa.php audit <url>` untuk memastikan halaman merespons 200 dan
   tidak menyisakan placeholder.

Mengubah tampilan:

1. Sunting `packages/tpl_pa_satker/css/template.css` atau override di
   `packages/tpl_pa_satker/html/`.
2. Jalankan `php tools/build.php`.
3. Unggah ulang `dist/tpl_pa_satker.zip` lewat **System → Install → Extensions**.
   Template langsung memakai berkas baru tanpa perlu diaktifkan ulang.

## Status dan rencana

| Bagian | Status |
|---|---|
| Manifest 35 butir + 5 seksi menu | selesai |
| Logika murni & invarian manifest (26 tes, 58 asersi) | selesai |
| Template `pa_satker`: beranda, daftar kategori, halaman artikel, halaman indikator | selesai |
| Seeder idempoten: kategori, artikel + workflow, menu, modul | selesai |
| Auditor 35 URL dan estimasi skor | selesai (35 URL 200, 11 halaman tanpa placeholder) |
| Paket ekstensi & 1-klik (`pkg_pastarter.zip`, `pa-starterpack-ekstensi-v1.0.0.zip`) | selesai (rilis v1.0.0) |
| Quickstart Joomla 6.2 (`pa-starterpack-quickstart-joomla62-v1.0.0.zip`) | selesai (rilis v1.0.0) |
| Indeks pencarian `com_finder` | belum dibangun pada data contoh |
| Cakupan PTA | belum, menunggu parameter `satker_type` pada versi 1.1 |

## Kontribusi, lisensi, dan kredit

Koreksi dan tambahan dipersilakan. Untuk perubahan besar, buka issue terlebih
dahulu agar arahnya disepakati. Sertakan langkah uji yang sudah dijalankan
(`vendor/bin/phpunit`, `php cli/pa.php audit`) pada usulan perubahan.

Dirilis di bawah **GNU General Public License v2.0 atau lebih baru**, mengikuti
lisensi Joomla. Lihat berkas [LICENSE](LICENSE).

Kredit: Joomla (GPL-2.0-or-later) sebagai inti aplikasi. Desain tampilan mengacu
pada situs Direktorat Jenderal Badan Peradilan Agama dan situs satker yang sudah
berjalan. Gambar contoh selain logo dibuat melalui `tools/assets.py`; logo resmi
Mahkamah Agung RI disertakan untuk kebutuhan situs resmi satuan kerja, sedangkan
logo masing-masing satker diunggah melalui panel admin.
