<?php
// =============================================================
//  FILE: index.php — HALAMAN 1: BERANDA / HIGHLIGHT
//  FUNGSI: Menampilkan berita terbaru sebagai HEADLINE besar,
//          dan sisa berita lainnya dalam grid kartu di bawahnya.
//
//  ALUR LOGIKA:
//  1. Include data_berita.php → dapat array $berita & fungsi helper
//  2. Urutkan semua berita dari terbaru ke terlama
//  3. Ambil berita pertama (index 0) → dijadikan HEADLINE
//  4. Sisa berita (index 1 dst) → tampil di GRID kartu
// =============================================================

// Sertakan file data berita (path relatif dari root/index.php)
require_once 'data_berita.php';

// Variabel untuk header.php: judul tab browser & menu aktif
$page_title    = 'Beranda';
$halaman_aktif = 'beranda';

// LANGKAH 1: Urutkan semua berita dari terbaru ke terlama
$semua_berita = getAllBerita($berita); // fungsi dari data_berita.php

// LANGKAH 2: Pisahkan headline, rel terkini, dan berita grid
// array_shift() mengambil & menghapus elemen PERTAMA dari array
$headline    = array_shift($semua_berita); // berita terbaru → berita utama
$rail_berita = array_slice($semua_berita, 0, 6); // 6 terbaru → rel samping
$grid_berita = array_slice($semua_berita, 6);     // sisanya → grid kartu

// Sertakan header (harus setelah variabel $page_title & $halaman_aktif didefinisikan)
include 'includes/header.php';
?>

<!-- ============================================================
     SECTION HEADLINE — BERITA UTAMA BESAR
     ============================================================ -->
<section class="headline-section">
    <div class="container">
        <div class="front-grid">

            <article class="headline-card">
                <!-- Gambar Headline -->
                <a href="detail.php?id=<?= $headline['id'] ?>" class="headline-img-wrap">
                    <img
                        src="<?= htmlspecialchars($headline['gambar']) ?>"
                        alt="<?= htmlspecialchars($headline['judul']) ?>"
                        class="headline-img"
                        onerror="this.src='assets/img/placeholder.jpg'"
                    >
                </a>

                <!-- Isi Teks Headline -->
                <div class="headline-body">
                    <!-- Satu-satunya eyebrow di halaman ini: kategori berita utama -->
                    <div class="headline-badge"><?= ucfirst($headline['kategori']) ?></div>

                    <time class="headline-date">
                         <?= formatTanggal($headline['tanggal']) ?>
                    </time>

                    <!-- htmlspecialchars() untuk mencegah XSS attack -->
                    <h1 class="headline-title">
                        <a href="detail.php?id=<?= $headline['id'] ?>">
                            <?= htmlspecialchars($headline['judul']) ?>
                        </a>
                    </h1>

                    <p class="headline-excerpt">
                        <?= htmlspecialchars($headline['isi_singkat']) ?>
                    </p>

                    <!-- Link ke halaman detail, kirim id berita via URL query string -->
                    <a href="detail.php?id=<?= $headline['id'] ?>" class="btn btn-primary">
                        Baca Selengkapnya →
                    </a>
                </div>
            </article>

            <!-- REL TERKINI — baris berpagar garis, bukan kartu -->
            <aside class="rail">
                <h2 class="rail-head">Terkini <span><?= count($rail_berita) ?> berita</span></h2>

                <ol class="rail-list">
                    <?php foreach ($rail_berita as $i => $artikel): ?>
                    <li class="rail-item">
                        <a href="detail.php?id=<?= $artikel['id'] ?>">
                            <span class="rail-idx"><?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?></span>
                            <span>
                                <span class="rail-cat"><?= ucfirst($artikel['kategori']) ?></span>
                                <span class="rail-title"><?= htmlspecialchars($artikel['judul']) ?></span>
                                <time class="rail-time"><?= formatTanggal($artikel['tanggal']) ?></time>
                            </span>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ol>

                <a href="indeks.php" class="rail-more">Lihat semua berita →</a>
            </aside>

        </div><!-- /front-grid -->
    </div>
</section>

<!-- ============================================================
     SECTION GRID BERITA LAINNYA
     ============================================================ -->
<section class="grid-section">
    <div class="container">
            <?php if (empty($grid_berita)): ?>
            <!-- Jika tidak ada berita selain headline & rel terkini -->
            <p class="empty-msg">Belum ada berita lain. Tambahkan data di data_berita.php!</p>

        <?php else: ?>
            <!-- KEPALA DEPARTEMEN: garis tebal, bukan pil emas -->
            <h2 class="section-title">Berita Terkini <span><?= count($grid_berita) ?> artikel</span></h2>
            <!-- GRID KARTU BERITA
                 Loop foreach menampilkan setiap berita dalam $grid_berita
                 sebagai kartu yang bisa diklik menuju halaman detail -->
            <div class="berita-grid">
                <?php foreach ($grid_berita as $artikel): ?>
                <article class="berita-card">
                    <!-- Gambar kartu (tanpa label di atas foto) -->
                    <a href="detail.php?id=<?= $artikel['id'] ?>" class="card-img-link">
                        <img
                            src="<?= htmlspecialchars($artikel['gambar']) ?>"
                            alt="<?= htmlspecialchars($artikel['judul']) ?>"
                            class="card-img"
                            onerror="this.src='assets/img/placeholder.jpg'"
                        >
                    </a>

                    <!-- Isi kartu -->
                    <div class="card-body">
                        <div class="card-meta">
                            <span class="badge"><?= ucfirst($artikel['kategori']) ?></span>
                            <time class="card-date">
                                <?= formatTanggal($artikel['tanggal']) ?>
                            </time>
                        </div>
                        <h3 class="card-title">
                            <a href="detail.php?id=<?= $artikel['id'] ?>">
                                <?= htmlspecialchars($artikel['judul']) ?>
                            </a>
                        </h3>
                        <p class="card-excerpt">
                            <?= htmlspecialchars(truncate($artikel['isi_singkat'], 110)) ?>
                        </p>
                        <a href="detail.php?id=<?= $artikel['id'] ?>" class="card-link">
                            Selengkapnya →
                        </a>
                    </div>
                </article>
                <?php endforeach; ?>
            </div><!-- /berita-grid -->

        <?php endif; ?>

    </div>
</section>

<?php
// Sertakan footer — selalu di paling bawah sebelum tag tutup
include 'includes/footer.php';
?>
