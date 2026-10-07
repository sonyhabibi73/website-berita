# KimiNEWS

Portal berita berbahasa Indonesia yang berjalan di atas XAMPP (PHP + MySQL), tanpa framework. Menyajikan berita otomatis dari API berita, lengkap dengan asisten AI yang hanya membantu topik berita.

## Fitur

```
Beranda headline, 6 kategori berita, dan arsip indeks per bulan.
Galeri foto berfilter, video, dan podcast.
Detail artikel dengan ringkasan, foto, dan tautan ke sumber asli.
Asisten Berita — chat AI untuk mencari dan merangkum berita terkini.
Form kontak & kirim tips, plus desain responsive dengan menu hamburger.
```

Enam kategori: **politik, teknologi, olahraga, pendidikan, kesehatan, hiburan**.

## Persyaratan

- XAMPP (Apache + MySQL + PHP 8.x)
- Koneksi internet (untuk API berita dan asisten AI)

## Cara Menjalankan

### 1. Salin ke folder htdocs

```
# Windows
C:\xampp\htdocs\website-berita

# Linux (XAMPP)
/opt/lampp/htdocs/website-berita
```

Lalu nyalakan **Apache** dan **MySQL** dari panel XAMPP.

### 2. Buat database

Database `PESAN` belum punya file dump. Buat dari halaman **phpMyAdmin** → Import, atau lewat terminal:

```sql
CREATE DATABASE IF NOT EXISTS PESAN
  DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

USE PESAN;

CREATE TABLE `kontak_pesan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subjek` varchar(200) NOT NULL,
  `pesan` text NOT NULL,
  `tanggal_kirim` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

### 3. Salin file konfigurasi

Dua file konfigurasi berisi rahasia dan **tidak ikut ter-commit**. Keduanya wajib disalin dari templatnya:

```bash
cp api/config.example.php api/config.php
cp koneksi.example.php     koneksi.php
```

Kalau `api/config.php` atau `koneksi.php` belum ada, halaman akan tetap jalan dengan data berita manual dan asisten AI nonaktif — tapi form kontak akan gagal karena tidak ada koneksi database.

### 4. Isi API key

| File | Isi | Dapatkan dari |
|---|---|---|
| `api/config.php` | `gemini_api_key` | <https://aistudio.google.com/apikey> |
| `api/config.php` | `news_api_key` | Dashboard <https://apitube.io> → API Keys |
| `api/config.php` | `openrouter_api_key` | Opsional, hanya cadangan |
| `koneksi.php` | `$username`, `$password`, `$database` | Pengaturan MySQL kamu |

### 5. Buka situsnya

```
http://localhost/website-berita
```

## Cara Berita Bekerja

`data_berita.php` adalah satu-satunya sumber artikel untuk seluruh halaman. Ia punya dua sumber dengan kontrak yang sama:

1. **APITube News API** — aktif kalau `news_api_key` terisi. Enam kategori ditarik sekali jalan lalu disimpan di `cache/` selama 4 jam (±36 request/hari, batas keras 80/hari).
2. **Data manual** — 13 artikel di dalam `data_berita.php` sendiri. Dipakai kalau key kosong, API gagal, atau kuota harian habis.

Karena itu aman mengisi key belakangan: situs tidak pernah kosong.

### Batas paket gratis APITube

Perlu diketahui sebelum mengandalkan hasilnya:

- **Hasil telat 12 jam** — bukan berita beberapa menit lalu
- Maksimal **10 artikel per request**, 10 request/menit, 100 request/hari
- Teks isi artikel **dipotong** oleh penyedia. Halaman detail karena itu menampilkan ringkasan, foto ilustrasi, dan tombol **"Baca selengkapnya di sumber"** yang mengarah ke artikel asli.

Sisa kuota bisa dicek kapan saja: `GET https://api.apitube.io/v1/balance`

Filter wilayah ada di `news_filter`. Bawaannya hanya sumber Indonesia:

```php
'news_filter' => [
    'source.country.code' => 'id',   // sumber dari Indonesia
    // 'language.code' => 'id',      // alternatif: berdasarkan bahasa
    // 'location.name' => 'Jakarta', // alternatif: berdasarkan wilayah yang dibahas
],
```

## Asisten Berita

Tombol mengambang di kanan bawah layar membuka popup chat. Asisten ini:

- **Hanya** mencari dan merangkum berita — pertanyaan di luar topik ditolak sopan
- Berjalan lewat proxy server (`api/chat.php`), sehingga **API key tidak pernah dikirim ke browser**
- Memakai Gemini dengan fallback otomatis ke model ringan saat model utama sedang penuh

## Struktur Proyek

```
website-berita/
├── api/
│   ├── chat.php               proxy permintaan AI
│   ├── config.example.php     templat konfigurasi (ikut ter-commit)
│   └── config.php             berisi key (di-gitignore)
├── assets/
│   ├── css/style.css          gaya seluruh situs
│   ├── js/script.js           menu responsif & filter galeri
│   ├── js/chat.js             logika popup asisten
│   └── img/                   aset gambar situs
├── includes/
│   ├── header.php  footer.php
│   └── chat.php    markup tombol & panel asisten
├── cache/                     cache berita (di-gitignore)
├── uploads/                   unggahan runtime (di-gitignore)
├── data_berita.php            sumber artikel + data manual cadangan
├── index.php                  beranda (headline + grid)
├── detail.php                 detail artikel
├── indeks.php                 arsip per bulan
├── galeri.php  video.php  podcast.php
├── kat-*.php                  6 halaman kategori
├── _template_kategori.php     template gabungan kategori
├── tentang.php  kontak.php  kirim-tips.php
├── koneksi.example.php        templat koneksi DB (ikut ter-commit)
└── koneksi.php                kredensial DB (di-gitignore)
```

## Keamanan

- `api/config.php` dan `koneksi.php` ada di `.gitignore` dan **tidak pernah boleh masuk repository**. Untuk mengeceknya sendiri:

  ```bash
  git ls-files | grep -E '(^|/)(config|koneksi)\.php$'   # harus kosong
  git grep --cached -iE 'AQ\.|api_live_|sk-or-v1-'       # harus 0 hasil
  ```

- Kalau key pernah bocor, langsung regenerate dari dashboard masing-masing (Google AI Studio, apitube.io, openrouter.ai) — file lama cukup ditimpa.
- Penyimpanan berita di `cache/` berisi data artikel saja, tanpa rahasia.
