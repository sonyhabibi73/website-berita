<?php
// =============================================================
//  FILE: indeks.php — INDEKS BERITA
//  FUNGSI: Menampilkan semua artikel berita dalam satu halaman,
//          diurutkan dari terbaru ke terlama. Berguna buat
//          pembaca yang pengen liat semua konten tanpa dikelompokin
//          per kategori.
// =============================================================

require_once 'data_berita.php';

$page_title    = 'Indeks Berita';
$halaman_aktif = 'indeks';

$semua = getAllBerita($berita);

// Kelompokkan berdasarkan bulan-tahun
$grouped = [];
foreach ($semua as $item) {
    $bln_thn = date('F Y', strtotime($item['tanggal']));
    $grouped[$bln_thn][] = $item;
}

include 'includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <h1 class="page-hero-title">Indeks <span>Berita</span></h1>
        <p class="page-hero-subtitle">Seluruh artikel KimiNEWS — diurutkan dari yang terbaru</p>
    </div>
</section>

<div class="container indeks-container">

    <!-- Info Bar: jumlah total berita -->
    <div class="indeks-info">
        <p>Menampilkan <strong><?= count($semua) ?> artikel</strong> dalam <strong><?= count($grouped) ?> periode</strong></p>
        <div class="indeks-cat-filter">
            <span class="filter-label">Lompat ke:</span>
            <?php foreach ($grouped as $bln => $items): ?>
                <a href="#<?= str_replace(' ', '-', strtolower($bln)) ?>" class="indeks-jump"><?= $bln ?></a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Daftar Berita per Kelompok Bulan -->
    <?php foreach ($grouped as $bln => $items): ?>
    <section class="indeks-bulan" id="<?= str_replace(' ', '-', strtolower($bln)) ?>">
        <div class="indeks-bulan-header">
            <h2 class="indeks-bulan-title"><?= $bln ?></h2>
            <span class="indeks-count"><?= count($items) ?> artikel</span>
        </div>

        <div class="indeks-list">
            <?php foreach ($items as $item): ?>
            <a href="detail.php?id=<?= $item['id'] ?>" class="indeks-item">
                <span class="badge badge-<?= $item['kategori'] ?> badge-lg"><?= ucfirst($item['kategori']) ?></span>
                <div class="indeks-item-body">
                    <span class="indeks-item-date"><?= formatTanggal($item['tanggal']) ?></span>
                    <h3 class="indeks-item-title"><?= htmlspecialchars($item['judul']) ?></h3>
                </div>
                <span class="indeks-arrow">→</span>
            </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endforeach; ?>

    <!-- Navigasi Back to Top -->
    <div class="indeks-top-wrap">
        <a href="#" class="btn btn-outline">↑ Kembali ke Atas</a>
    </div>

</div>

<?php include 'includes/footer.php'; ?>
