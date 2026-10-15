<?php
// =============================================================
//  FILE: galeri.php — HALAMAN 4: FOTO & MEDIA BERITA
//  FUNGSI: Menampilkan semua gambar dari berita yang ada,
//          ditambah galeri foto liputan khusus.
// =============================================================
require_once 'data_berita.php';

$page_title    = 'Galeri Foto & Media';
$halaman_aktif = 'galeri';

// Ambil semua berita untuk diambil gambarnya
$semua = getAllBerita($berita);

include 'includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <h1 class="page-hero-title">Galeri <span>Foto & Media</span></h1>
        <p class="page-hero-subtitle">Kumpulan foto dan visual dari liputan berita KimiNEWS</p>
    </div>
</section>

<div class="container galeri-container">

    <!-- Filter Kategori (JavaScript sederhana untuk filter tampilan) -->
    <div class="galeri-filter">
        <button class="filter-btn aktif" data-filter="semua">Semua</button>
        <button class="filter-btn" data-filter="teknologi">Teknologi</button>
        <button class="filter-btn" data-filter="olahraga">Olahraga</button>
        <button class="filter-btn" data-filter="hiburan">Hiburan</button>
        <button class="filter-btn" data-filter="politik">Politik</button>
        <button class="filter-btn" data-filter="kesehatan">Kesehatan</button>
        <button class="filter-btn" data-filter="pendidikan">Pendidikan</button>
    </div>

    <!-- Grid Galeri — diambil dari gambar berita yang ada -->
    <div class="galeri-grid" id="galeriGrid">
        <?php foreach ($semua as $item): ?>
        <!-- data-kategori digunakan oleh JavaScript untuk filter -->
        <div class="galeri-item" data-kategori="<?= $item['kategori'] ?>">
            <a href="detail.php?id=<?= $item['id'] ?>" class="galeri-link">
                <span class="galeri-img-wrap">
                    <img
                        src="<?= htmlspecialchars($item['gambar']) ?>"
                        alt="<?= htmlspecialchars($item['judul']) ?>"
                        class="galeri-img"
                        loading="lazy"
                        onerror="this.src='assets/img/placeholder.jpg'"
                    >
                </span>
                <!-- Keterangan di bawah foto, bukan timpa di atasnya -->
                <span class="galeri-cap">
                    <span class="badge"><?= ucfirst($item['kategori']) ?></span>
                    <span class="galeri-caption">
                        <?= htmlspecialchars(truncate($item['judul'], 60)) ?>
                    </span>
                    <span class="galeri-read">Baca Artikel →</span>
                </span>
            </a>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Pesan jika tidak ada foto setelah filter -->
    <div class="galeri-empty" id="galeriEmpty" style="display:none;">
        <p>Tidak ada foto untuk kategori ini.</p>
    </div>

</div><!-- /container -->

<!-- JavaScript Inline untuk Filter Galeri
     (Fungsi sederhana: sembunyikan item yang tidak cocok) -->
<script>
// Ambil semua tombol filter dan semua item galeri
const filterBtns = document.querySelectorAll('.filter-btn');
const galeriItems = document.querySelectorAll('.galeri-item');
const galeriEmpty = document.getElementById('galeriEmpty');

filterBtns.forEach(btn => {
    btn.addEventListener('click', function() {
        // Hapus class 'aktif' dari semua tombol, pasang di yang diklik
        filterBtns.forEach(b => b.classList.remove('aktif'));
        this.classList.add('aktif');

        const filter = this.getAttribute('data-filter');
        let terlihat = 0;

        galeriItems.forEach(item => {
            // Jika filter 'semua' atau kategori cocok → tampilkan
            if (filter === 'semua' || item.getAttribute('data-kategori') === filter) {
                item.style.display = 'block';
                terlihat++;
            } else {
                item.style.display = 'none';
            }
        });

        // Tampilkan pesan kosong jika tidak ada yang cocok
        galeriEmpty.style.display = terlihat === 0 ? 'block' : 'none';
    });
});
</script>

<?php include 'includes/footer.php'; ?>
