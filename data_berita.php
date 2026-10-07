<?php
// =============================================================
//  FILE: data_berita.php
//  FUNGSI: SATU-SATUNYA sumber artikel untuk seluruh halaman
//          (beranda, kategori, indeks, galeri, detail).
//
//  DUA SUMBER, SATU KONTRAK:
//    A. APITube News API  -> dipakai kalau 'news_api_key' terisi
//       di api/config.php.  Hasil disimpan di cache/berita.json
//       supaya kuota free (100 request/hari) tidak cepat habis.
//    B. Data manual       -> BAGIAN 2 di bawah, dipakai kalau key
//       kosong, API gagal, atau kuota harian habis.
//       Jadi aman kalau API key belum diisi.
//
//  KONTRAK YANG TIDAK BOLEH BERUBAH (dipakai semua halaman):
//      $berita = [ ['id','judul','kategori','isi_singkat',
//                   'isi_lengkap','tanggal','gambar'], ... ]
//      getAllBerita()  getBeritaByKategori()  getBeritaById()
//      formatTanggal()  truncate()
//
//  DUA BATASAN TEKNIS, JANGAN DILANGGAR:
//   1. 'id' HARUS integer — detail.php memvalidasinya dengan
//      FILTER_VALIDATE_INT, string akan berakhir "tidak ditemukan".
//   2. 'kategori' HARUS satu dari enam nilai ini, karena halamannya
//      berupa kat-<kategori>.php:
//      politik | teknologi | olahraga | pendidikan | kesehatan | hiburan
// =============================================================


// =============================================================
//  BAGIAN 1A — KONFIGURASI
// =============================================================

/**
 * Baca api/config.php, lalu gabungkan dengan nilai bawaan.
 * Semua nilai bawah ini dipakai jika belum di-isi di config.
 */
function kimi_news_cfg()
{
    static $cfg = null;
    if ($cfg !== null) {
        return $cfg;
    }

    $cfg = [
        'news_api_key'      => '',
        'news_api_base'     => 'https://api.apitube.io',
        'news_filter'       => ['source.country.code' => 'id'],
        'news_per_category' => 10,
        'news_cache_ttl'    => 14400,
        'news_daily_budget' => 80,
    ];

    $file = __DIR__ . '/api/config.php';
    if (is_readable($file)) {
        $isi = require $file;
        if (is_array($isi)) {
            foreach ($cfg as $k => $v) {
                if (array_key_exists($k, $isi) && $isi[$k] !== '' && $isi[$k] !== null) {
                    $cfg[$k] = $isi[$k];
                }
            }
        }
    }

    return $cfg;
}

/**
 * Enam kategori situs  ->  kode IPTC Media Topics (level atas).
 * Daftar lengkap: GET /v1/suggest/categories atau dokumen
 * docs.apitube.io/platform/news-api/list-of-categories
 */
function kimi_news_kategori_map()
{
    return [
        'politik'    => 'medtop:11000000', // politics and government
        'teknologi'  => 'medtop:13000000', // science and technology
        'olahraga'   => 'medtop:15000000', // sport
        'pendidikan' => 'medtop:05000000', // education
        'kesehatan'  => 'medtop:07000000', // health
        'hiburan'    => 'medtop:01000000', // arts, culture, entertainment and media
    ];
}


// =============================================================
//  BAGIAN 1B — CACHE
//  Cache/berita.json berisi:
//    diperbarui    : timestamp segarkan terakhir
//    kouta_tanggal : tanggal hitungan kuota (YYYY-MM-DD)
//    kouta_dipakai : berapa request sudah dipakai hari ini
//    id_map        : id APITube (string) -> id integer untuk situs
//    artikel       : daftar artikel siap pakai
// =============================================================

/**
 * Folder penyimpanan cache — dipilih yang paling bisa ditulis.
 * XAMPP menjalankan Apache sebagai user lain (daemon/uid 1), jadi
 * folder 'cache/' yang dibuat user lokal sering tidak bisa ditulis
 * oleh Apache. Kalau itu terjadi, caching dialihkan ke folder
 * sementara sistem supaya kuota API tetap terjaga.
 */
function kimi_news_cache_dir()
{
    $proyek = __DIR__ . '/cache';
    if (!is_dir($proyek)) {
        @mkdir($proyek, 0777, true);
    }
    if (is_writable($proyek)) {
        return $proyek;
    }
    @chmod($proyek, 0777);              // berusaha, walau jarang berhasil
    if (is_writable($proyek)) {
        return $proyek;
    }

    $tmp = rtrim((string) sys_get_temp_dir(), '/') . '/kimi-news-cache';
    if (!is_dir($tmp)) {
        @mkdir($tmp, 0777, true);
    }
    if (is_writable($tmp)) {
        return $tmp;
    }

    return $proyek;                     // terakhir: biarkan gagal terlihat
}

function kimi_news_cache_file()
{
    return kimi_news_cache_dir() . '/berita.json';
}

function kimi_news_cache_baca()
{
    $f = kimi_news_cache_file();
    if (!is_readable($f)) {
        return null;
    }
    $d = json_decode((string)@file_get_contents($f), true);
    return is_array($d) ? $d : null;
}

function kimi_news_cache_tulis($d)
{
    $dir = kimi_news_cache_dir();
    if (!is_writable($dir)) {
        return false;
    }

    $json = json_encode($d, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        return false;
    }

    // Tulis ke berkas sementara, lalu rename(). rename() hanya butuh izin
    // tulis pada FOLDER — bukan pada berkas lamanya. Ini penting karena
    // XAMPP CLI dan Apache sering berjalan sebagai user berbeda: begitu
    // salah satunya membuat cache/berita.json dengan mode 644, lawannya
    // tidak akan pernah bisa menimpa (file_put_contents gagal) dan
    // API-nya diam-diam tak pernah terpakai lagi.
    $tmp = $dir . '/.berita.' . getmypid() . '.tmp';
    if (@file_put_contents($tmp, $json, LOCK_EX) === false) {
        return false;
    }
    if (!@rename($tmp, kimi_news_cache_file())) {
        @unlink($tmp);
        return false;
    }

    return true;
}


// =============================================================
//  BAGIAN 1C — PEMANGGILAN API
// =============================================================

/**
 * GET JSON dari APITube.
 * @return array|null  array respons, atau null kalau gagal.
 *
 * CATATAN: curl_close() sengaja TIDAK dipanggil — sudah usang sejak
 * PHP 8.5 dan pada runtime ini berujung merusak keluaran JSON.
 * Objek CurlHandle dibebaskan sendiri saat fungsi selesai.
 */
function kimi_news_http($url, $key)
{
    if (!function_exists('curl_init')) {
        return null;
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT  => 5,
        CURLOPT_TIMEOUT         => 15,
        CURLOPT_FOLLOWLOCATION  => true,
        CURLOPT_MAXREDIRS       => 2,
        CURLOPT_USERAGENT       => 'KimiNEWS/1.0',
        CURLOPT_HTTPHEADER      => [
            'X-API-Key: ' . $key,
            'Accept: application/json',
        ],
    ]);

    $isi  = curl_exec($ch);
    $kode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // Bukan 2xx (401 key salah, 429 kuota habis, 500 dsb) -> anggap gagal.
    // Galatnya dicatat supaya bisa dilihat di cache/berita.json, karena
    // tanpa ini kategori yang kosong tidak akan pernah ketahuan sebabnya.
    if ($isi === false || $kode < 200 || $kode >= 300) {
        $err = curl_errno($ch);
        kimi_news_galat_catat(sprintf(
            'HTTP %d%s: %s',
            $kode,
            $err ? ' (curl ' . $err . ')' : '',
            mb_substr(trim((string) $isi), 0, 200)
        ));
        return null;
    }

    $d = json_decode((string) $isi, true);
    if (!is_array($d)) {
        kimi_news_galat_catat('respons bukan JSON: ' . mb_substr((string) $isi, 0, 200));
        return null;
    }
    if (($d['status'] ?? 'ok') !== 'ok') {
        kimi_news_galat_catat('status not_ok: ' . mb_substr((string) json_encode($d['errors'] ?? []), 0, 200));
    }
    return $d;
}

/**
 * Catatan galat pemanggilan API, disimpan ke cache agar mudah diperiksa.
 */
function kimi_news_galat_catat($pesan)
{
    $GLOBALS['kimi_news_galat'][] = $pesan;
}

function kimi_news_galat_ambil()
{
    return $GLOBALS['kimi_news_galat'] ?? [];
}

/**
 * Buang sisa pemotongan teks pada paket gratis APITube.
 * Hasilnya berbentuk: "...Wakil Presid...(+2127 chars hidden)...[Upgrade subscription plan]"
 *
 * @return string  teks bersih; dikosongkan kalau sisanya terpotong di
 *                 tengah kalimat (pemanggil akan memakai deskripsi)
 */
function kimi_news_bersihkan($isi)
{
    $isi = (string) $isi;
    $tanda = ['chars hidden', '[Upgrade subscription plan]'];

    $ketahuan = false;
    foreach ($tanda as $t) {
        $p = strpos($isi, $t);
        if ($p !== false) {
            $isi     = substr($isi, 0, $p);
            $ketahuan = true;
        }
    }
    if (!$ketahuan) {
        return $isi;        // tidak terpotong — teks asli dipakai apa adanya
    }

    // Sisa akhirnya berhenti di tengah kata ("...Wakil Presid"). Kalau tidak
    // diakhiri tanda baca, anggap tidak layak tampil daripada memotong
    // kalimat secara kasar.
    $isi = rtrim($isi, " \t\n\r(.:-");
    if (!preg_match('/[.!?]/u', substr(trim($isi), -1))) {
        return '';
    }
    return trim($isi);
}

/**
 * Ubah satu artikel APITube menjadi entri yang dipahami halaman.
 * id_map dan next_id dilewatkan lewat referensi supaya id integer
 * yang sama dipakai ulang setiap kali cache diperbarui — kalau tidak,
 * semua tautan detail.php?id= berubah-ubah dan jadi rusak.
 *
 * @return array|null null kalau artikel tidak layak tampil
 */
function kimi_news_ubah($a, $slug, &$id_map, &$next_id)
{
    if (!is_array($a)) {
        return null;
    }

    $uid   = trim((string) ($a['id'] ?? ''));
    $judul = trim((string) ($a['title'] ?? ''));
    if ($uid === '' || $judul === '') {
        return null;
    }

    if (!isset($id_map[$uid])) {
        $id_map[$uid] = $next_id++;
    }

    $deskripsi = trim((string) ($a['description'] ?? ''));
    $isi       = kimi_news_bersihkan($a['body'] ?? '');
    if ($isi === '') {
        $isi = $deskripsi;      // body terpotong -> pakai lead yang utuh
    }
    if ($isi === '') {
        $isi = $judul;
    }

    $ts = strtotime((string) ($a['published_at'] ?? ''));

    $gambar = trim((string) ($a['image'] ?? ''));
    if ($gambar === '') {
        $gambar = 'assets/img/placeholder.jpg';
    }

    return [
        'uid'         => $uid,
        'id'          => (int) $id_map[$uid],
        'judul'       => $judul,
        'kategori'    => $slug,
        'isi_singkat' => $deskripsi !== '' ? $deskripsi : $judul,
        'isi_lengkap' => $isi,
        'tanggal'     => $ts ? date('Y-m-d', $ts) : date('Y-m-d'),
        'gambar'      => $gambar,
        'sumber'      => trim((string) ($a['source']['domain'] ?? '')),
        'tautan'      => trim((string) ($a['href'] ?? '')),
    ];
}

/**
 * Panggil API untuk enam kategori, lalu tulis hasilnya ke cache.
 * @param array $cache isi cache sekarang (boleh null)
 * @return array cache baru
 */
function kimi_news_segarkan($cfg, $key, $cache)
{
    if (!is_array($cache)) {
        $cache = [];
    }
    if (!isset($cache['artikel']) || !is_array($cache['artikel'])) {
        $cache['artikel'] = [];
    }
    if (!isset($cache['id_map']) || !is_array($cache['id_map'])) {
        $cache['id_map'] = [];
    }

    // --- kuota harian -------------------------------------------------
    $hari = date('Y-m-d');
    if (($cache['kouta_tanggal'] ?? '') !== $hari) {
        $cache['kouta_tanggal']  = $hari;
        $cache['kouta_dipakai'] = 0;
    }
    $budget  = max(0, (int) $cfg['news_daily_budget']);
    $dipakai = (int) $cache['kouta_dipakai'];
    if ($dipakai >= $budget) {
        // Sudah mentok: kembalikan isi cache apa adanya, TANPA http.
        return $cache;
    }

    // --- penyiapan ----------------------------------------------------
    // Paket free membatasi per_page maksimal 10.
    $per     = max(1, min(10, (int) $cfg['news_per_category']));
    $batas   = 60;   // jangan menumpuk tanpa batas
    $next_id = 1;
    foreach ($cache['id_map'] as $n) {
        $n = (int) $n;
        if ($n >= $next_id) {
            $next_id = $n + 1;
        }
    }

    $punya = [];
    foreach ($cache['artikel'] as $it) {
        if (!empty($it['uid'])) {
            $punya[$it['uid']] = true;
        }
    }

    // --- enam kali panggil, urutannya bebas, semua muat di 10 req/mnt -
    foreach (kimi_news_kategori_map() as $slug => $kategori_id) {
        if ($dipakai >= $budget || count($cache['artikel']) >= $batas) {
            break;
        }

        $q = array_merge((array) $cfg['news_filter'], [
            'category.id' => $kategori_id,
            'per_page'    => $per,
            'sort.by'     => 'published_at',
            'sort.order'  => 'desc',
        ]);
        $url = rtrim((string) $cfg['news_api_base'], '/')
             . '/v1/news/everything?' . http_build_query($q);

        $dipakai++;
        $d = kimi_news_http($url, $key);
        if ($d === null) {
            continue;   // kategori ini gagal; kategori berikut dicoba
        }

        foreach ((array) ($d['results'] ?? []) as $a) {
            if (count($cache['artikel']) >= $batas) {
                break;
            }
            $uid = trim((string) ($a['id'] ?? ''));
            if ($uid === '' || isset($punya[$uid])) {
                continue;   // sudah ada, jangan diduplikasi
            }
            $item = kimi_news_ubah($a, $slug, $cache['id_map'], $next_id);
            if ($item === null) {
                continue;
            }
            $cache['artikel'][] = $item;
            $punya[$uid] = true;
        }
    }

    $cache['kouta_dipakai'] = $dipakai;
    $cache['diperbarui']    = time();
    $cache['galat']         = kimi_news_galat_ambil();

    // Kalau sama sekali nihil, jangan menunggu TTL penuh — tapi jangan
    // juga mencoba lagi tiap kunjungan. Jeda 15 menit menyeimbangkan
    // pemulihan cepat dengan kesehatan kuota harian.
    if (empty($cache['artikel'])) {
        $ttl                = (int) $cfg['news_cache_ttl'];
        $cache['diperbarui'] = time() - max(60, $ttl - 900);
    }

    usort($cache['artikel'], function ($a, $b) {
        return (int) strtotime($b['tanggal']) - (int) strtotime($a['tanggal']);
    });

    kimi_news_cache_tulis($cache);
    return $cache;
}

/**
 * Ambil berita dari APITube.
 * @return array  [] kalau API belum aktif / gagal / kuota habis
 */
function kimi_berita_api()
{
    $cfg = kimi_news_cfg();
    $key = trim((string) $cfg['news_api_key']);
    if ($key === '') {
        return [];      // key belum diisi -> memakai data manual
    }

    // Jangan sentuh API kalau hasilnya tidak bisa disimpan. Tanpa cache,
    // penghitung kuota tidak pernah tercatat dan setiap muat halaman akan
    // memanggil API lagi — persis celah yang bikin kuota 100/hari habis
    // dalam belasan klik. Lebih baik memakai data manual.
    if (!is_writable(kimi_news_cache_dir())) {
        return [];
    }

    $cache = kimi_news_cache_baca();
    $ttl   = max(60, (int) $cfg['news_cache_ttl']);

    // Catatan: 'empty($cache['artikel'])' sengaja TIDAK dipakai sebagai
    // pemicu di sini. Kalau API balik kosong, kondisi itu membuat refresh
    // dipanggil di SETIAP muat halaman — 6 request tiap kunjungan, kuota
    // 100/hari habis dalam belasan klik. Biar TTL-lah yang memutuskan;
    // kimi_news_segarkan sudah mengatur jeda 15 menit untuk hasil nihil.
    $usang = !$cache
        || (time() - (int) ($cache['diperbarui'] ?? 0)) >= $ttl;

    if ($usang) {
        $cache = kimi_news_segarkan($cfg, $key, $cache);
    }

    $keluar = $cache['artikel'] ?? [];
    return is_array($keluar) ? $keluar : [];
}

// =============================================================
//  BAGIAN 2 — DATA MANUAL (CADANGAN)
//  Aktif kalau API key belum diisi, API gagal, atau kuota
//  harian habis. Isinya sengaja dibiarkan apa adanya.
// =============================================================

$data_manual = [

    // -----------------------------------------------------------------
    // BERITA #1
    // -----------------------------------------------------------------
    [
        'id'          => 1,                          // Nomor unik, jangan diulang
        'judul'       => 'Pemerintah Luncurkan Program AI Nasional untuk Percepat Transformasi Digital',
        'kategori'    => 'teknologi',                // Pilihan: teknologi | olahraga | hiburan | politik | kesehatan
        'isi_singkat' => 'Pemerintah Indonesia resmi meluncurkan program kecerdasan buatan nasional yang bertujuan mempercepat transformasi digital di berbagai sektor, mulai dari pendidikan hingga layanan publik.',
        'isi_lengkap' => 'Pemerintah Indonesia melalui Kementerian Komunikasi dan Informatika secara resmi meluncurkan Program Kecerdasan Buatan Nasional (AI Nasional) pada Selasa lalu di Jakarta. Program ambisius ini dirancang untuk mempercepat transformasi digital di seluruh sektor strategis Indonesia, mencakup pendidikan, kesehatan, pertanian, dan layanan publik.

Menteri Kominfo menyatakan bahwa investasi dalam teknologi AI merupakan langkah krusial agar Indonesia tidak tertinggal dalam persaingan global. "Kita harus bergerak cepat. Negara-negara tetangga sudah selangkah lebih maju, dan kita tidak boleh hanya menjadi penonton," ujarnya dalam konferensi pers.

Program ini mencakup tiga pilar utama: pertama, pembangunan infrastruktur data nasional berupa pusat data (data center) berskala besar di beberapa kota; kedua, pengembangan sumber daya manusia melalui beasiswa AI untuk 10.000 pelajar dan mahasiswa; serta ketiga, kemitraan strategis dengan perusahaan teknologi global seperti Google, Microsoft, dan sejumlah startup lokal.

Dalam tahap awal, pemerintah akan mengalokasikan anggaran sebesar Rp 2,5 triliun yang bersumber dari APBN 2025. Dana ini akan digunakan untuk membangun ekosistem AI yang inklusif dan berkelanjutan. Para ahli teknologi menyambut positif inisiatif ini, meski mengingatkan pentingnya regulasi yang kuat agar pemanfaatan AI tetap etis dan bertanggung jawab.',
        'tanggal'     => '2025-06-15',               // Format: YYYY-MM-DD
        'gambar'      => 'assets/img/ai-nasional.jpg', // Path relatif dari root proyek
    ],

    // -----------------------------------------------------------------
    // BERITA #2
    // -----------------------------------------------------------------
    [
        'id'          => 2,
        'judul'       => 'Timnas Indonesia Raih Kemenangan Dramatis 3-2 atas Vietnam di Final AFF',
        'kategori'    => 'olahraga',
        'isi_singkat' => 'Timnas Indonesia berhasil meraih gelar juara AFF 2024 setelah mengalahkan Vietnam dengan skor dramatis 3-2 melalui gol penentu di menit ke-89 oleh Marselino Ferdinan.',
        'isi_lengkap' => 'Stadion Gelora Bung Karno, Jakarta — Ribuan suporter yang memadati stadion berkapasitas 77.000 penonton itu meledak dalam kegembiraan ketika peluit panjang wasit berbunyi. Indonesia resmi menjadi juara AFF 2024 usai mengalahkan Vietnam dengan skor 3-2 dalam pertandingan final yang berlangsung menegangkan hingga detik terakhir.

Pertandingan dimulai dengan Vietnam langsung menekan dan berhasil unggul lebih dahulu melalui gol Nguyen Quang Hai pada menit ke-12. Namun Indonesia tidak menyerah. Striker muda Hokky Caraka menyamakan kedudukan menjadi 1-1 pada menit ke-27 melalui sundulan akrobatik yang disambut riuh penonton.

Babak kedua semakin panas. Vietnam kembali memimpin lewat gol bunuh diri bek Indonesia, sebelum kapten tim, Asnawi Mangkualam, mencetak gol spektakuler dari jarak 25 meter untuk menyamakan skor menjadi 2-2 pada menit ke-70. Drama puncak terjadi di menit ke-89 ketika Marselino Ferdinan, yang masuk sebagai pemain pengganti, melepaskan tendangan keras yang tak mampu dibendung kiper Vietnam.

Pelatih Shin Tae-yong menyebut ini sebagai pencapaian terbesar dalam kariernya melatih di Indonesia. "Semua pemain berjuang keras, mereka adalah pahlawan sejati," ucapnya sambil berkaca-kaca. Trofi AFF ini menjadi yang kedua bagi Indonesia setelah sebelumnya meraih pada 2016.',
        'tanggal'     => '2025-06-10',
        'gambar'      => 'assets/img/timnas-aff.jpg',
    ],

    // -----------------------------------------------------------------
    // BERITA #3
    // -----------------------------------------------------------------
// -----------------------------------------------------------------
    // BERITA #3: FILM ANIMASI BIOSKOP LOKAL (KATEGORI: HIBURAN)
    // -----------------------------------------------------------------
    [
        'id'          => 3,
        'judul'       => 'Senggol Isu Ril Anak Sekolah, Film Animasi Bioskop "Jumbo" Panen Pujian Gara-Gara Visualnya yang Niat Abis',
        'kategori'    => 'hiburan',
        'isi_singkat' => 'Bukan buatan AI, film animasi layar lebar garapan Visinema ini sukses bikin bangga industri lokal karena kualitas animasinya yang halus dan ceritanya yang menyentuh.',
        'isi_lengkap' => 'Layar bioskop Indonesia akhirnya kedatangan sebuah karya animasi layar lebar yang bener-bener digarap dengan standar tinggi, yaitu "Jumbo". Diproduksi oleh Visinema Animation dan disutradarai langsung oleh Ryan Adriandhy, film ini menjadi angin segar sekaligus pembuktian kalau kreator Indonesia mampu bikin film animasi konvensional yang megah tanpa perlu shortcut AI generatif malas yang lagi marak didebatkan netizen.

Mengangkat cerita tentang petualangan magis dan persahabatan anak sekolah, "Jumbo" secara berani menyenggol isu sensitif yang dekat dengan kehidupan anak muda jaman sekarang, seperti perundungan (bullying) dan dampak lari ke dunia fantasi. Penonton yang memadati bioskop XXI dan CGV dibuat kagum dengan kualitas pergerakan karakter yang sangat halus, detail ekspresi wajah yang hidup, serta scoring musik yang megah khas film animasi kelas dunia.

Banyak kritikus film dan netizen di media sosial X ngasih rating tinggi karena naskahnya yang matang dan gak menggurui. Kehadiran film "Jumbo" di layar lebar membuktikan bahwa kreativitas manusia, dedikasi ratusan animator lokal, dan penceritaan yang jujur bakal selalu menghasilkan karya yang jauh lebih berjiwa dan dicintai penonton dibanding hasil generate mesin instan.',
        'tanggal'     => '2026-05-26', // Format YYYY-MM-DD fresh tanggal hari ini
        'gambar'      => 'assets/img/film-jumbo.jpg',
    ],

    // -----------------------------------------------------------------
    // BERITA #4
    // -----------------------------------------------------------------
    [
        'id'          => 4,
        'judul'       => 'DPR Sahkan UU Perlindungan Data Pribadi: Era Baru Keamanan Digital Indonesia',
        'kategori'    => 'politik',
        'isi_singkat' => 'Dewan Perwakilan Rakyat secara resmi mengesahkan Undang-Undang Perlindungan Data Pribadi yang telah lama dinantikan, memberikan payung hukum bagi jutaan pengguna internet di Indonesia.',
        'isi_lengkap' => 'Jakarta — Setelah melalui proses pembahasan panjang selama hampir tiga tahun, Dewan Perwakilan Rakyat (DPR) Republik Indonesia akhirnya mengesahkan Undang-Undang Perlindungan Data Pribadi (UU PDP) dalam Sidang Paripurna yang digelar pada Kamis lalu. Pengesahan ini disambut lega oleh para pegiat keamanan siber dan masyarakat umum.

UU PDP ini mengatur secara komprehensif tentang hak-hak warga negara atas data pribadinya. Beberapa poin krusial dalam undang-undang ini antara lain: perusahaan yang mengumpulkan data wajib mendapatkan persetujuan eksplisit dari pemilik data; warga berhak meminta penghapusan data mereka (right to be forgotten); serta sanksi pidana bagi pelaku kebocoran data yang mencapai maksimal 6 tahun penjara dan denda hingga Rp 60 miliar.

Ketua Komisi I DPR menyatakan bahwa UU ini adalah jawaban atas maraknya kasus kebocoran data yang terjadi beberapa tahun terakhir. "Warga negara berhak mendapatkan perlindungan yang nyata di dunia digital. Ini adalah kewajiban negara," tegasnya.

Namun sejumlah kalangan bisnis menyatakan kekhawatiran terkait kesiapan teknis perusahaan dalam mengimplementasikan regulasi baru ini. Pemerintah memberikan masa transisi 2 tahun bagi perusahaan untuk menyesuaikan sistem mereka dengan ketentuan UU PDP.',
        'tanggal'     => '2025-06-05',
        'gambar'      => 'assets/img/uu-pdp.jpg',
    ],

    // -----------------------------------------------------------------
    // BERITA #5
    // -----------------------------------------------------------------
    [
        'id'          => 5,
        'judul'       => 'Studi Terbaru: Konsumsi Kopi 2 Cangkir Per Hari Terbukti Turunkan Risiko Alzheimer',
        'kategori'    => 'kesehatan',
        'isi_singkat' => 'Penelitian yang melibatkan lebih dari 400.000 responden di 12 negara menemukan bahwa kebiasaan minum kopi dua cangkir per hari secara signifikan menurunkan risiko terkena penyakit Alzheimer hingga 28 persen.',
        'isi_lengkap' => 'Sebuah penelitian monumental yang dipublikasikan dalam jurnal medis internasional The Lancet Neurology mengungkap temuan mengejutkan: konsumsi kopi sebanyak dua cangkir per hari secara teratur terbukti dapat menurunkan risiko terkena penyakit Alzheimer hingga 28 persen. Studi ini melibatkan lebih dari 400.000 partisipan dari 12 negara berbeda dan berlangsung selama 10 tahun.

Para peneliti dari Universitas Harvard, Johns Hopkins, dan beberapa universitas terkemuka di Eropa menemukan bahwa kandungan kafein dan antioksidan dalam kopi, terutama senyawa chlorogenic acid, berperan aktif dalam melindungi sel-sel neuron di otak dari kerusakan oksidatif yang menjadi salah satu pemicu Alzheimer.

Dr. Sarah Chen, ketua tim peneliti, menjelaskan bahwa efek protektif ini hanya didapat dari konsumsi kopi yang moderat — yakni satu hingga tiga cangkir per hari. "Konsumsi berlebihan justru bisa menimbulkan efek negatif seperti kecemasan berlebih, gangguan tidur, dan tekanan darah tinggi," peringatnya.

Para dokter di Indonesia menyambut temuan ini dengan hati-hati. Dokter Spesialis Saraf dari RS Cipto Mangunkusumo, dr. Bambang Widodo, Sp.N, menyarankan agar masyarakat tidak menjadikan temuan ini sebagai alasan untuk mengonsumsi kopi secara berlebihan. "Gaya hidup sehat secara keseluruhan, termasuk olahraga rutin dan pola makan seimbang, tetap menjadi kunci utama pencegahan demensia," ujarnya.',
        'tanggal'     => '2025-06-01',
        'gambar'      => 'assets/img/kopi-alzheimer.jpg',
    ],

 [
        'id'          => 6, 
        'judul'       => 'Siap-Siap Kena Mental, Harga RAM dan SSD Naik Parah Gara-Gara Pabrik Fokus Bikin Chip AI',
        'kategori'    => 'teknologi', 
        'isi_singkat' => 'Buat lo yang lagi nabung mau rakit PC atau upgrade laptop, mending buruan checkout sekarang sebelum harganya makin gak ngotak gara-gara kelangkaan chip global.',
        'isi_lengkap' => 'Kabar buruk buat kaum mendang-mending dan anak spek kere hore. Kalau lo ada rencana mau upgrade RAM ke 16GB atau beli SSD NVMe baru dalam waktu dekat, siap-siap boncos lebih gede. Laporan terbaru dari pasar hardware global ngelepeh data kalau harga memori RAM dan SSD diprediksi bakal melonjak naik sampe 20-30% mulai bulan ini.

Biang keroknya ternyata gara-gara demam Artificial Intelligence (AI) yang makin gila-gilaan. Pabrik-pabrik semikonduktor raksasa kayak Samsung, SK Hynix, sama Micron lebih milih ngalihin jalur produksi mereka buat bikin komponen server AI raksasa (High Bandwidth Memory/HBM) yang untungnya jauh lebih gede. Efek sampingnya, jatah produksi buat chip RAM PC dan komponen laptop biasa jadi dikorbanin dan makin langka di pasaran.

Para distributor lokal udah mulai ngasih ancang-ancang kalau stok sisa di gudang mereka bakal abis dalam hitungan minggu. Jadi, daripada lo nyesel belakangan liat harga RAM yang tadinya 500 ribuan berubah jadi nyaris sejuta, mending langsung sikat checkout sekarang sebelum dompet lo makin menangis berjamaah.',
        'tanggal'     => '2026-05-26', // Format YYYY-MM-DD (26 Mei 2026)
        'gambar'      => 'assets/img/Ram-DDR5.jpg',
    ],

    [
        'id'          => 7,
        'judul'       => 'Rating Usia Game Bikinan Komdigi Menuai Sorotan, Netizen: Banyak yang Asal-Asalan Klasifikasinya!',
        'kategori'    => 'teknologi',
        'isi_singkat' => 'Langkah Komdigi yang makin gencar ngetok palu aturan Indonesia Game Rating System (IGRS) bikin komunitas gamers lokal geleng-geleng kepala.',
        'isi_lengkap' => 'Kementerian Komunikasi dan Digital (Komdigi) lagi gencar-gencarnya mensosialisasikan sistem klasifikasi umur game lokal lewat platform IGRS. Aturan ini dibuat biar game yang beredar di Indonesia punya batasan umur yang jelas. Tapi alih-alih dapet pujian, sistem rating manual dari Komdigi ini malah memicu perdebatan kocak sekaligus kritik pedas dari komunitas gamers tanah air.

Banyak netizen di platform X yang nemuin klasifikasi umur yang dinilai agak "asal-asalan" dan gak sinkron sama standar internasional. Beberapa game strategi yang punya jalan cerita rumit dan penuh intrik politik malah dapet rating remaja, sementara game kasual yang grafiknya kotak-kotak lucu justru dicap dewasa hanya karena ada fitur transaksi mikro atau skin karakter yang dinilai kurang pas.

Para gamers lokal menyindir kalau tim penilai dari pemerintah kemungkinan besar gak beneran mainin game-nya sampai selesai, melainkan cuma liat trailer sekilas lalu asal nembak rating. Komunitas berharap Komdigi bisa lebih merangkul para pegiat esports dan gamers sejati dalam menentukan standarisasi ini, biar sistem rating IGRS bisa beneran akurat dan gak jadi bahan lelucon di internet.',
        'tanggal'     => '2026-05-26', // Format YYYY-MM-DD aman jaya
        'gambar'      => 'assets/img/komdigi-rating.jpg',
    ],

    // -----------------------------------------------------------------
    // BERITA #10: SEPAK BOLA INTERNASIONAL (KATEGORI: OLAHRAGA)
    // -----------------------------------------------------------------
    // -----------------------------------------------------------------
    // BERITA #10: BOLA VOLI INTERNASIONAL (KATEGORI: OLAHRAGA)
    // -----------------------------------------------------------------
    [
        'id'          => 8,
        'judul'       => 'Puncaki Klasemen Internasional, Taktik Kombinasi Cepat Timnas Voli Jepang Sulit Dibendung',
        'kategori'    => 'olahraga',
        'isi_singkat' => 'Tim nasional bola voli Jepang terus melanjutkan tren positif di kancah dunia lewat kemenangan beruntun berkat pertahanan rapat dan serangan balik cepat.',
        'isi_lengkap' => 'Tim Nasional Bola Voli Jepang kembali menunjukkan kelasnya sebagai salah satu kekuatan utama di kancah internasional. Dalam lanjutan turnamen voli dunia yang berlangsung semalam, skuad asal Negeri Sakura tersebut sukses menumbangkan tim tangguh Eropa lewat pertandingan sengit. Kemenangan ini membuat posisi Jepang semakin kokoh di papan atas klasemen sementara.

Kunci keberhasilan Jepang dalam meredam keunggulan postur fisik lawan terletak pada disiplin *receive* dan *digging* yang sangat rapat. Alur serangan yang dimotori oleh setter utama mereka mampu mendistribusikan bola dengan variasi *pipe attack* dan kombinasi cepat yang mengecoh barisan *blocker* lawan. Efektivitas serangan balik dari posisi sayap juga menjadi pembeda dalam mendulang poin-poin krusial di akhir set.

Dengan performa yang kian matang, Jepang kini menjadi salah satu tim yang paling diwaspadai dalam perebutan gelar juara musim ini. Para pengamat bola voli internasional memuji konsistensi permainan kolektif yang ditunjukkan oleh skuad Jepang, yang membuktikan bahwa keterbatasan postur tubuh dapat ditutupi dengan kecepatan taktik dan kualitas teknik tingkat tinggi.',
        'tanggal'     => '2026-05-27', // Format YYYY-MM-DD
        'gambar'      => 'assets/img/voli.jpg',
    ],

    // -----------------------------------------------------------------
    // BERITA #6: NASIONAL / POLITIK (KATEGORI: POLITIK)
    // -----------------------------------------------------------------
    [
        'id'          => 9,
        'judul'       => 'Sebut Warga Desa Tidak Pakai Dollar, Pernyataan Prabowo Terkait Ketahanan Ekonomi Menuai Diskusi',
        'kategori'    => 'politik', // Cocok masuk kategori politik lo yang warnanya merah
        'isi_singkat' => 'Presiden Prabowo Subianto menegaskan pentingnya penguatan ekonomi domestik dengan menyebut bahwa pergerakan mata uang asing tidak berdampak langsung bagi masyarakat desa.',
        'isi_lengkap' => 'Pernyataan Presiden Prabowo Subianto terkait kondisi ekonomi nasional kembali memicu diskusi hangat di ruang publik. Dalam pidato terbarunya mengenai ketahanan pangan dan ekonomi domestik, Prabowo menekankan bahwa stabilitas ekonomi di tingkat akar rumput, khususnya di pedesaan, jauh lebih dipengaruhi oleh produktivitas lokal ketimbang fluktuasi nilai tukar mata uang asing seperti dollar Amerika Serikat. "Warga desa kita tidak pakai dollar, mereka butuh pupuk, air, dan pasar yang adil," ujar Prabowo dalam forum tersebut.

Pernyataan tersebut disampaikan untuk menegaskan fokus pemerintah dalam memperkuat sektor pertanian, peternakan, dan UMKM yang menjadi tulang punggung masyarakat daerah. Menurut Prabowo, kemandirian bangsa hanya bisa dicapai jika kebutuhan dasar masyarakat di desa-desa dapat terpenuhi secara mandiri tanpa harus bergantung pada barang-barang impor yang harganya sangat sensitif terhadap pergerakan mata uang global.

Menanggapi hal itu, sejumlah pengamat ekonomi memberikan pandangan yang beragam. Di satu sisi, pernyataan tersebut dinilai sebagai sinyal positif bahwa pemerintah berkomitmen penuh pada ekonomi kerakyatan dan pembangunan dari pinggiran. Namun di sisi lain, beberapa analis mengingatkan bahwa secara makro, stabilitas nilai tukar tetap krusial karena biaya logistik dan harga sebagian bahan baku sektor penunjang pertanian masih memiliki keterkaitan dengan jalur perdagangan internasional.',
        'tanggal'     => '2026-05-27', // Fresh format YYYY-MM-DD hari ini
        'gambar'      => 'assets/img/prabowo-pidato.jpg',
    ],

    // -----------------------------------------------------------------
    // BERITA #12: KESEHATAN / PSIKOLOGI (KATEGORI: KESEHATAN)
    // -----------------------------------------------------------------
    [
        'id'          => 10,
        'judul'       => 'Sering Dikira Malas, Ini Ciri Gangguan Jiwa Masa Awal yang Kerap Tidak Disadari',
        'kategori'    => 'kesehatan', // Masuk ke rubrik kesehatan lo yang warna hijau/biru muda
        'isi_singkat' => 'Banyak orang terlambat menyadari penurunan kondisi kesehatan mental karena gejalanya sering kali mirip dengan kelelahan fisik atau perubahan emosi biasa.',
        'isi_lengkap' => 'Kesadaran masyarakat terhadap kesehatan mental (mental health) memang terus meningkat, namun masih banyak gejala awal gangguan jiwa ringan yang kerap luput dari perhatian. Pakar psikologi klinis mengingatkan bahwa fase awal gangguan kesehatan mental sering kali tidak muncul dalam bentuk ledakan emosi yang ekstrem, melainkan melalui perubahan perilaku halus yang sayangnya sering kali dicap negatif sebagai bentuk "kemalasan" atau sikap "antisosial".

Salah satu ciri utama yang jarang disadari adalah penurunan fungsi diri secara perlahan, seperti mendadak kesulitan fokus menyelesaikan tugas harian, hilangnya minat pada hobi yang sebelumnya sangat disukai (anhedonia), hingga perubahan pola tidur dan makan yang drastis. Selain itu, rasa lelah berkepanjangan yang tidak kunjung hilang meski sudah beristirahat cukup sering kali menjadi indikator bahwa otak sedang mengalami tekanan mental atau stres berat.

Para ahli menegaskan pentingnya melakukan deteksi dini dan tidak mendiagnosis diri sendiri (self-diagnosis) secara sembarangan lewat media sosial. Jika perubahan suasana hati yang murung, kecemasan berlebih, atau keinginan untuk terus mengisolasi diri dari lingkungan sosial sudah berlangsung lebih dari dua minggu, berkonsultasi dengan profesional seperti psikolog atau psikiater adalah langkah bijak demi mencegah perburukan kondisi psikologis ke tahap yang lebih serius.',
        'tanggal'     => '2026-05-27', // Format YYYY-MM-DD fresh tanggal hari ini
        'gambar'      => 'assets/img/kesehatan-mental.jpg',
    ],

    // -----------------------------------------------------------------
    // BERITA #13: FILM / HIBURAN (KATEGORI: HIBURAN)
    // -----------------------------------------------------------------
    [
        'id'          => 11,
        'judul'       => 'Tembus 10 Juta Penonton, Film Komedi "Agak Laen" Cetak Sejarah Baru di Bioskop Indonesia',
        'kategori'    => 'hiburan', // Sesuai kategori hiburan lo yang warna ungu
        'isi_singkat' => 'Gak ada lawan, film komedi horor garapan sutradara Bene Dion sukses memecahkan rekor sebagai salah satu film Indonesia terlaris sepanjang masa.',
        'isi_lengkap' => 'Industri perfilman tanah air kembali dikejutkan oleh pencapaian luar biasa dari film komedi horor domestik berjudul "Agak Laen". Diprodusi oleh Imajinari dan disutradarai oleh Bene Dion Rajagukguk, film yang dibintangi oleh kuartet komika Boris Bokir, Indra Jegel, Bene Dion, dan Oki Rengga ini secara resmi berhasil menembus angka fantastis lebih dari 10 juta penonton di seluruh jaringan bioskop Indonesia, mulai dari XXI, CGV, hingga Cinepolis.

Pencapaian ini menempatkan "Agak Laen" di posisi elite sebagai film lokal terlaris kedua sepanjang sejarah sinema Indonesia, tepat di bawah film horor "KKN di Desa Penari". Kesuksesan luar biasa ini dinilai banyak pengamat film sebagai bukti bahwa formula komedi yang segar, natural, dan dekat dengan realitas kehidupan masyarakat sehari-hari memiliki daya tarik yang sangat masif di mata penonton layar lebar.

Keberhasilan film ini juga memicu fenomena unik di media sosial, di mana banyak dialog ikonik dari film tersebut dijadikan tren audio dan meme oleh netizen. Para kritikus film memuji rumah produksi yang berani memberikan ruang bagi para komika lokal untuk mengeksplorasi cerita, membuktikan bahwa genre komedi murni yang digarap dengan naskah solid dan kematangan karakter mampu bersaing ketat dengan dominasi film-film bergenre horor murni di pasar bioskop tanah air.',
        'tanggal'     => '2026-05-27', // Format YYYY-MM-DD fresh hari ini
        'gambar'      => 'assets/img/film-agak-laen.jpg',
    ],

    // -----------------------------------------------------------------
    // BERITA #14: PENDIDIKAN — KURIKULUM BARU
    // -----------------------------------------------------------------
    [
        'id'          => 12,
        'judul'       => 'Kemendikdasmen Resmi Luncurkan Kurikulum Baru 2026: Fokus pada Literasi Digital dan Coding Sejak SD',
        'kategori'    => 'pendidikan',
        'isi_singkat' => 'Kurikulum baru yang mulai diterapkan tahun ajaran 2026/2027 ini mewajibkan mata pelajaran coding dan AI dasar mulai dari kelas 4 SD, sebagai bekal generasi emas Indonesia 2045.',
        'isi_lengkap' => 'Kementerian Pendidikan Dasar dan Menengah (Kemendikdasmen) secara resmi meluncurkan Kurikulum Merdeka Plus 2026 dalam sebuah acara di Jakarta, Senin lalu. Kurikulum ini membawa perubahan signifikan dengan menambahkan mata pelajaran wajib baru: Dasar-Dasar Literasi Digital, Algoritma dan Coding, serta Pengenalan Kecerdasan Buatan (AI) yang akan diajarkan mulai dari jenjang Sekolah Dasar kelas 4.

Menteri Pendidikan Dasar dan Menengah dalam sambutannya menegaskan bahwa perubahan ini adalah respons terhadap percepatan transformasi digital global. "Kita tidak bisa lagi mengajarkan anak-anak kita dengan cara yang sama seperti 20 tahun lalu. Mereka harus siap menghadapi dunia yang didominasi teknologi," ujarnya.

Kurikulum baru ini dirancang secara bertahap. Untuk SD kelas 4-6, siswa akan belajar logika dasar pemrograman melalui aplikasi visual berbasis blok seperti Scratch dan pengenalan internet sehat. Di jenjang SMP, siswa akan mulai belajar bahasa pemrograman sederhana seperti Python dan HTML/CSS dasar. Sedangkan di jenjang SMA, tersedia peminatan coding lanjutan, pengembangan aplikasi mobile, dan proyek AI sederhana.

Selain itu, kurikulum ini juga menekankan penguatan karakter melalui pembelajaran berbasis proyek (project-based learning) dan pengembangan kemampuan berpikir kritis. Jam pelajaran untuk Pendidikan Pancasila dan Kewarganegaraan juga ditambah untuk memperkuat identitas nasional di tengah arus globalisasi.

Para guru akan menjalani pelatihan intensif selama libur panjang semester ini agar siap mengimplementasikan kurikulum baru pada tahun ajaran depan. Kemendikdasmen juga menyediakan platform pembelajaran digital gratis yang bisa diakses oleh seluruh sekolah, termasuk di daerah terpencil.',
        'tanggal'     => '2026-05-28',
        'gambar'      => 'assets/img/kurikulum-coding.jpg',
    ],

    // -----------------------------------------------------------------
    // BERITA #15: PENDIDIKAN — BEASISWA LPDP
    // -----------------------------------------------------------------
    [
        'id'          => 13,
        'judul'       => 'Beasiswa LPDP 2026 Dibuka: Kuota 15.000 Penerima dengan Fokus pada Riset dan Bidang STEM',
        'kategori'    => 'pendidikan',
        'isi_singkat' => 'Pendaftaran beasiswa LPDP periode 2026 resmi dibuka dengan kuota terbesar dalam sejarah, mengutamakan bidang STEM, riset iklim, dan pengembangan sumber daya manusia untuk IKN.',
        'isi_lengkap' => 'Kabar gembira bagi para pelajar dan mahasiswa Indonesia! Lembaga Pengelola Dana Pendidikan (LPDP) secara resmi membuka pendaftaran beasiswa untuk periode 2026 dengan kuota mencapai 15.000 penerima — jumlah terbesar sepanjang sejarah program ini. Pendaftaran dibuka mulai 1 Juni 2026 dan akan berlangsung dalam tiga gelombang.

Menteri Keuangan dalam konferensi pers menjelaskan bahwa alokasi anggaran beasiswa tahun ini meningkat signifikan menjadi Rp 18 triliun. Fokus utama beasiswa LPDP 2026 adalah pada pengembangan sumber daya manusia di bidang Science, Technology, Engineering, and Mathematics (STEM), riset perubahan iklim, energi terbarukan, serta pengembangan Ibu Kota Nusantara (IKN).

Beasiswa ini mencakup program magister (S2) dan doktoral (S3) di universitas-universitas terbaik dunia maupun dalam negeri. Selain biaya pendidikan penuh, penerima beasiswa juga mendapatkan tunjangan biaya hidup, asuransi kesehatan, dan biaya riset.

Yang menarik tahun ini, LPDP membuka jalur khusus bagi para guru dan tenaga pendidik di daerah 3T (Terdepan, Terluar, Tertinggal) untuk melanjutkan studi tanpa harus meninggalkan kewajiban mengajar, melalui program blended learning dan kelas jarak jauh.

Pendaftaran dilakukan secara online melalui portal resmi LPDP. Para pendaftar akan melalui seleksi administrasi, tes potensi akademik, dan wawancara. LPDP juga memberikan pendampingan bagi pendaftar dari keluarga kurang mampu untuk membantu proses administrasi.',
        'tanggal'     => '2026-05-29',
        'gambar'      => 'assets/img/beasiswa-lpdp.jpg',
    ],
]; // <-- Jangan hapus kurung kurawal dan titik koma penutup ini!

// =============================================================
//  BAGIAN 3 — PILIH SUMBER
// =============================================================
$berita = kimi_berita_api();
if (empty($berita)) {
    $berita = $data_manual;
}

// =============================================================
//  FUNGSI PEMBANTU (Helper Functions)
//  Fungsi-fungsi di bawah ini digunakan oleh halaman lain
//  untuk mengambil data dari array $berita di atas.
// =============================================================

/**
 * Ambil semua berita, diurutkan dari yang terbaru (tanggal terbesar duluan).
 */
function getAllBerita($berita) {
    // usort = mengurutkan array berdasarkan kriteria kustom
    usort($berita, function($a, $b) {
        return strtotime($b['tanggal']) - strtotime($a['tanggal']);
    });
    return $berita;
}

/**
 * Ambil berita berdasarkan kategori tertentu.
 * Contoh: getBeritaByKategori($berita, 'teknologi')
 */
function getBeritaByKategori($berita, $kategori) {
    $hasil = [];
    foreach ($berita as $item) {
        // Cocokkan kategori (huruf kecil semua agar konsisten)
        if (strtolower($item['kategori']) === strtolower($kategori)) {
            $hasil[] = $item;
        }
    }
    return $hasil;
}

/**
 * Ambil satu berita berdasarkan id-nya.
 * Contoh: getBeritaById($berita, 3)  → mengembalikan berita id=3
 * Jika tidak ditemukan, mengembalikan null.
 */
function getBeritaById($berita, $id) {
    foreach ($berita as $item) {
        if ($item['id'] == $id) {
            return $item;
        }
    }
    return null; // tidak ditemukan
}

/**
 * Format tanggal dari YYYY-MM-DD ke format Indonesia.
 * Contoh: '2025-06-15' → '15 Juni 2025'
 */
function formatTanggal($tanggal) {
    $bulan = [
        1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April',
        5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus',
        9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember'
    ];
    $ts = strtotime($tanggal);
    return date('d', $ts) . ' ' . $bulan[(int)date('m', $ts)] . ' ' . date('Y', $ts);
}

/**
 * Potong teks panjang agar tidak meluap di kartu berita.
 * Contoh: truncate('Teks panjang...', 100) → 100 karakter + '...'
 */
function truncate($teks, $maks = 120) {
    if (strlen($teks) <= $maks) return $teks;
    return substr($teks, 0, $maks) . '...';
}
?>
