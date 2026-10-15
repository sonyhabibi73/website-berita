<?php
// =============================================================
//  FILE: detail.php — HALAMAN 5: DETAIL ISI ARTIKEL LENGKAP
//  FUNGSI: Menampilkan isi lengkap satu berita berdasarkan
//          parameter 'id' yang dikirim lewat URL.
//
//  ALUR LOGIKA:
//  1. Baca parameter ?id=X dari URL menggunakan $_GET['id']
//  2. Validasi: id harus berupa angka positif
//  3. Cari berita dengan id tersebut di array $berita
//  4. Jika ditemukan → tampilkan. Jika tidak → tampilkan error.
//
//  CONTOH URL: detail.php?id=1 → menampilkan berita ber-id 1
//              detail.php?id=3 → menampilkan berita ber-id 3
// =============================================================

require_once 'data_berita.php';

// LANGKAH 1: Ambil dan sanitasi parameter 'id' dari URL
// filter_input() lebih aman daripada langsung $_GET['id']
// FILTER_VALIDATE_INT memastikan nilainya adalah integer
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// LANGKAH 2: Cari berita di array berdasarkan id
// Fungsi getBeritaById() ada di data_berita.php
$artikel = ($id !== false && $id !== null) ? getBeritaById($berita, $id) : null;

// LANGKAH 3: Siapkan variabel untuk header
if ($artikel) {
    $page_title = $artikel['judul'];  // Judul tab browser = judul berita
} else {
    $page_title = 'Berita Tidak Ditemukan';
}
$halaman_aktif = 'beranda'; // Tidak ada menu khusus 'detail', anggap di beranda

include 'includes/header.php';
?>

<div class="container detail-container">

    <?php if (!$artikel): ?>
        <!-- ====================================================
             KONDISI: Berita tidak ditemukan (id salah/tidak ada)
             ==================================================== -->
        <div class="not-found-box">
            <div class="not-found-icon"></div>
            <h2>Berita Tidak Ditemukan</h2>
            <p>
                Maaf, berita dengan ID <strong><?= htmlspecialchars((string)$id) ?></strong>
                tidak ada dalam database kami.
            </p>
            <a href="index.php" class="btn btn-primary">← Kembali ke Beranda</a>
        </div>

    <?php else: ?>
        <!-- ====================================================
             KONDISI: Berita ditemukan → tampilkan isi lengkap
             ==================================================== -->

        <!-- Breadcrumb navigasi: Beranda > Kategori > Judul -->
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="index.php">Beranda</a>
            <span>›</span>
            <a href="kat-<?= $artikel['kategori'] ?>.php">
                <?= ucfirst($artikel['kategori']) ?>
            </a>
            <span>›</span>
            <span><?= htmlspecialchars(truncate($artikel['judul'], 50)) ?></span>
        </nav>

        <!-- ARTIKEL UTAMA -->
        <div class="detail-layout">
        <article class="detail-article">

            <!-- Header artikel: kategori, tanggal, judul -->
            <header class="article-header">
                <span class="badge badge-<?= $artikel['kategori'] ?> badge-lg">
                    <?= ucfirst($artikel['kategori']) ?>
                </span>
                <time class="article-date">
                     <?= formatTanggal($artikel['tanggal']) ?>
                </time>
                <h1 class="article-title">
                    <?= htmlspecialchars($artikel['judul']) ?>
                </h1>
                <!-- Ringkasan / lead (isi_singkat tampil sebagai intro) -->
                <p class="article-lead">
                    <?= htmlspecialchars($artikel['isi_singkat']) ?>
                </p>
            </header>

            <!-- Gambar utama artikel -->
            <figure class="article-figure">
                <img
                    src="<?= htmlspecialchars($artikel['gambar']) ?>"
                    alt="<?= htmlspecialchars($artikel['judul']) ?>"
                    class="article-img"
                    onerror="this.src='assets/img/placeholder.jpg'"
                >
            </figure>

            <!-- ISI LENGKAP ARTIKEL
                 nl2br() mengubah newline (\n) menjadi tag <br>
                 htmlspecialchars() mencegah XSS
                 Keduanya dikombinasikan untuk tampilan yang aman & rapi -->
            <?php
            // Paket gratis API berita tidak menyediakan teks lengkap, jadi
            // isi_lengkap bisa sama persis dengan lead di atas. Kalau begitu,
            // menampilkan keduanya berarti teks yang sama muncul dua kali —
            // cukup lead saja. Data manual selalu berbeda isinya, jadi
            // halamannya tampil persis seperti biasanya.
            $ada_isi_lengkap = trim($artikel['isi_lengkap']) !== trim($artikel['isi_singkat']);
            $ada_tautan      = !empty($artikel['tautan']);
            ?>

            <?php if ($ada_isi_lengkap || $ada_tautan): ?>
            <div class="article-content<?= $ada_isi_lengkap ? '' : ' article-content--ringkas' ?>">
                <?php if ($ada_isi_lengkap): ?>
                    <?= nl2br(htmlspecialchars($artikel['isi_lengkap'])) ?>
                <?php endif; ?>

                <?php if ($ada_tautan): ?>
                    <!-- Tombol baca lengkap. Hanya tampil untuk artikel dari
                         API berita — data manual tidak punya 'tautan'. -->
                    <aside class="article-source">
                        <a class="article-source-btn"
                           href="<?= htmlspecialchars($artikel['tautan']) ?>"
                           target="_blank" rel="noopener noreferrer">
                            Baca selengkapnya di
                            <?= htmlspecialchars($artikel['sumber'] !== '' ? $artikel['sumber'] : $artikel['tautan']) ?>
                            <span aria-hidden="true">→</span>
                        </a>
                    </aside>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Tombol kembali ke halaman kategori -->
            <div class="article-footer-nav">
                <a href="kat-<?= $artikel['kategori'] ?>.php" class="btn btn-outline">
                    ← Berita <?= ucfirst($artikel['kategori']) ?> Lainnya
                </a>
                <a href="index.php" class="btn btn-outline">
                     Beranda
                </a>
            </div>

        </article>

        <!-- REL KANAN: se-kategori + terbaru -->
        <?php
        $sekategori = array_filter(
            getBeritaByKategori($berita, $artikel['kategori']),
            fn($b) => $b['id'] !== $artikel['id']
        );
        $terbaru = getAllBerita($berita);
        ?>
        <aside class="article-rail">
            <?php if (!empty($sekategori)): ?>
            <div class="rail-block">
                <h2 class="section-title">Lainnya di <?= ucfirst($artikel['kategori']) ?></h2>
                <ul class="mini-list">
                    <?php foreach (array_slice($sekategori, 0, 5) as $lain): ?>
                    <li>
                        <a href="detail.php?id=<?= $lain['id'] ?>">
                            <span class="mini-cat"><?= ucfirst($lain['kategori']) ?></span>
                            <span class="mini-title"><?= htmlspecialchars($lain['judul']) ?></span>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <div class="rail-block">
                <h2 class="section-title">Terbaru</h2>
                <ul class="mini-list">
                    <?php foreach (array_slice($terbaru, 0, 5) as $baru): ?>
                    <li>
                        <a href="detail.php?id=<?= $baru['id'] ?>">
                            <span class="mini-cat"><?= formatTanggal($baru['tanggal']) ?></span>
                            <span class="mini-title"><?= htmlspecialchars($baru['judul']) ?></span>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </aside>

        </div><!-- /detail-layout -->

    <?php endif; ?>

</div><!-- /container -->

<?php include 'includes/footer.php'; ?>
