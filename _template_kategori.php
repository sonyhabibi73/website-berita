<?php
// =============================================================
//  FILE: _template_kategori.php  (FILE REFERENSI — JANGAN DIAKSES LANGSUNG)
//  Ini adalah TEMPLATE yang digunakan oleh semua halaman kategori.
//  Setiap halaman kategori (kat-*.php) menyiapkan variabelnya
//  sendiri, lalu me-include file ini.
//
//  VARIABEL YANG HARUS DISIAPKAN SEBELUM INCLUDE FILE INI:
//    $nama_kategori  → string, misal: 'teknologi'
//    $label_kategori → string, misal: 'Teknologi'
//    $deskripsi_kategori → string, deskripsi singkat kategori
// =============================================================

require_once 'data_berita.php';

// Ambil berita yang cocok dengan kategori ini
// Fungsi getBeritaByKategori() ada di data_berita.php — melakukan filter foreach+if
$hasil_berita = getBeritaByKategori($berita, $nama_kategori);

$page_title    = 'Kategori ' . $label_kategori;
$halaman_aktif = 'kategori'; // Aktifkan dropdown "Kategori" di nav

include 'includes/header.php';
?>

<!-- HEADER KATEGORI -->
<section class="kat-header kat-header-<?= $nama_kategori ?>">
    <div class="container">
        <div class="kat-hero">
            <div>
                <h1 class="kat-title"><?= $label_kategori ?></h1>
                <p class="kat-desc"><?= htmlspecialchars($deskripsi_kategori) ?></p>
            </div>
        </div>
    </div>
</section>

<!-- DAFTAR BERITA KATEGORI -->
<section class="grid-section">
    <div class="container">

        <!-- Info jumlah berita ditemukan -->
        <div class="kat-info-bar">
            <h2 class="section-title">
                Semua Berita <?= $label_kategori ?>
            </h2>
            <span class="berita-count">
                <?= count($hasil_berita) ?> berita ditemukan
            </span>
        </div>

        <?php if (empty($hasil_berita)): ?>
            <!-- KONDISI: Tidak ada berita untuk kategori ini -->
            <div class="empty-state">
                <h3>Belum Ada Berita</h3>
                <p>
                    Belum ada berita dalam kategori <strong><?= $label_kategori ?></strong>.
                    Tambahkan berita baru di <code>data_berita.php</code> dengan
                    kategori <code><?= $nama_kategori ?></code>.
                </p>
                <a href="index.php" class="btn btn-primary">← Kembali ke Beranda</a>
            </div>

        <?php else: ?>
            <!-- LOOP FOREACH: tampilkan semua berita yang cocok
                 Setiap $item adalah satu baris array dari $hasil_berita -->
            <div class="berita-grid">
                <?php foreach ($hasil_berita as $item): ?>
                <article class="berita-card">
                    <a href="detail.php?id=<?= $item['id'] ?>" class="card-img-link">
                        <img
                            src="<?= htmlspecialchars($item['gambar']) ?>"
                            alt="<?= htmlspecialchars($item['judul']) ?>"
                            class="card-img"
                            onerror="this.src='assets/img/placeholder.jpg'"
                        >
                        <span class="badge badge-<?= $item['kategori'] ?>">
                            <?= ucfirst($item['kategori']) ?>
                        </span>
                    </a>
                    <div class="card-body">
                        <time class="card-date"><?= formatTanggal($item['tanggal']) ?></time>
                        <h3 class="card-title">
                            <a href="detail.php?id=<?= $item['id'] ?>">
                                <?= htmlspecialchars($item['judul']) ?>
                            </a>
                        </h3>
                        <p class="card-excerpt">
                            <?= htmlspecialchars(truncate($item['isi_singkat'], 110)) ?>
                        </p>
                        <a href="detail.php?id=<?= $item['id'] ?>" class="card-link">
                            Selengkapnya →
                        </a>
                    </div>
                </article>
                <?php endforeach; ?>
            </div><!-- /berita-grid -->

        <?php endif; ?>

    </div>
</section>

<?php include 'includes/footer.php'; ?>
