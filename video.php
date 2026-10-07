<?php
// =============================================================
//  FILE: video.php
//  FUNGSI: Halaman Video Berita — menampilkan kumpulan video
//          YouTube dari channel berita Indonesia resmi.
// =============================================================

$page_title    = 'Video Berita';
$halaman_aktif = 'video';

include 'includes/header.php';
?>

<!-- ============================================================
     HERO — Video Berita
     ============================================================ -->
<section class="page-hero video-hero">
    <div class="container">
        <h1 class="page-hero-title"><span>Video</span> Berita</h1>
        <p class="page-hero-subtitle">
            Kumpulan video terkini dari kanal berita resmi Indonesia —
            KompasTV, MetroTV, CNN Indonesia, dan lainnya
        </p>
    </div>
</section>

<div class="container video-container">

    <!-- FILTER CHANNEL -->
    <div class="video-filter" id="videoFilter">
        <button class="filter-btn aktif" data-filter="semua">Semua</button>
        <button class="filter-btn" data-filter="kompastv">KompasTV</button>
        <button class="filter-btn" data-filter="metrotv">MetroTV</button>
        <button class="filter-btn" data-filter="cnn">CNN Indonesia</button>
        <button class="filter-btn" data-filter="cnbc">CNBC Indonesia</button>
        <button class="filter-btn" data-filter="tvone">tvOne</button>
        <button class="filter-btn" data-filter="tribun">Tribunnews</button>
    </div>

    <!-- VIDEO GRID -->
    <div class="video-grid" id="videoGrid">

        <!-- =========================================================
             KOMPASTV
             ========================================================= -->
        <div class="video-card" data-channel="kompastv">
            <div class="video-embed-wrap">
                <iframe
                    src="https://www.youtube.com/embed/Lo02DeFW7OM"
                    title="Presiden Prabowo Tiba di Tanah Air usai Kunjungan ke Prancis"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen
                    loading="lazy">
                </iframe>
            </div>
            <div class="video-body">
                <div class="video-meta">
                    <span class="video-channel-badge badge-kompastv">KompasTV</span>
                    <span class="video-date">Juni 2026</span>
                </div>
                <h3 class="video-title">
                    <a href="https://www.youtube.com/watch?v=Lo02DeFW7OM" target="_blank" rel="noopener">
                        Presiden Prabowo Tiba di Tanah Air usai Kunjungan Kenegaraan di Prancis
                    </a>
                </h3>
                <p class="video-desc">
                    KompasTV melaporkan kepulangan Presiden Prabowo Subianto ke Indonesia
                    setelah menyelesaikan rangkaian kunjungan resmi kenegaraan di Prancis.
                </p>
            </div>
        </div>

        <div class="video-card" data-channel="kompastv">
            <div class="video-embed-wrap">
                <iframe
                    src="https://www.youtube.com/embed/uP8F1Sh6U-g"
                    title="Perkembangan Terbaru Diplomasi Indonesia"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen
                    loading="lazy">
                </iframe>
            </div>
            <div class="video-body">
                <div class="video-meta">
                    <span class="video-channel-badge badge-kompastv">KompasTV</span>
                    <span class="video-date">Terkini</span>
                </div>
                <h3 class="video-title">
                    <a href="https://www.youtube.com/watch?v=uP8F1Sh6U-g" target="_blank" rel="noopener">
                        Perkembangan Terbaru Diplomasi Indonesia di Kancah Internasional
                    </a>
                </h3>
                <p class="video-desc">
                    KompasTV menyajikan laporan terbaru seputar perkembangan diplomasi
                    Indonesia di tengah dinamika geopolitik global.
                </p>
            </div>
        </div>

        <!-- =========================================================
             METROTV
             ========================================================= -->
        <div class="video-card" data-channel="metrotv">
            <div class="video-embed-wrap">
                <iframe
                    src="https://www.youtube.com/embed/ANYjy5YY_YE"
                    title="Amerika Serikat Peringatkan Kemampuan Perang dengan Iran"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen
                    loading="lazy">
                </iframe>
            </div>
            <div class="video-body">
                <div class="video-meta">
                    <span class="video-channel-badge badge-metrotv">MetroTV</span>
                    <span class="video-date">Juni 2026</span>
                </div>
                <h3 class="video-title">
                    <a href="https://www.youtube.com/watch?v=ANYjy5YY_YE" target="_blank" rel="noopener">
                        Amerika Serikat Peringatkan Kemampuan Perang dengan Iran
                    </a>
                </h3>
                <p class="video-desc">
                    MetroTV mengulas peringatan keras AS terkait ketegangan militer
                    yang meningkat di kawasan Timur Tengah.
                </p>
            </div>
        </div>

        <!-- =========================================================
             CNN INDONESIA
             ========================================================= -->
        <div class="video-card" data-channel="cnn">
            <div class="video-embed-wrap">
                <iframe
                    src="https://www.youtube.com/embed/cPZ1nrnkW-w"
                    title="Rupiah Masih Tertekan Meski Ekonomi Indonesia Stabil"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen
                    loading="lazy">
                </iframe>
            </div>
            <div class="video-body">
                <div class="video-meta">
                    <span class="video-channel-badge badge-cnn">CNN Indonesia</span>
                    <span class="video-date">Terkini</span>
                </div>
                <h3 class="video-title">
                    <a href="https://www.youtube.com/watch?v=cPZ1nrnkW-w" target="_blank" rel="noopener">
                        Rupiah Masih Tertekan Meski Ekonomi Indonesia Stabil
                    </a>
                </h3>
                <p class="video-desc">
                    CNN Indonesia menganalisis kondisi nilai tukar rupiah yang masih
                    tertekan meskipun fundamental ekonomi nasional menunjukkan stabilitas.
                </p>
            </div>
        </div>

        <div class="video-card" data-channel="cnn">
            <div class="video-embed-wrap">
                <iframe
                    src="https://www.youtube.com/embed/duGg-pQtHQw"
                    title="Upacara Penyambutan Resmi Kenegaraan"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen
                    loading="lazy">
                </iframe>
            </div>
            <div class="video-body">
                <div class="video-meta">
                    <span class="video-channel-badge badge-cnn">CNN Indonesia</span>
                    <span class="video-date">Terkini</span>
                </div>
                <h3 class="video-title">
                    <a href="https://www.youtube.com/watch?v=duGg-pQtHQw" target="_blank" rel="noopener">
                        Liputan Acara Kenegaraan di Istana Kepresidenan
                    </a>
                </h3>
                <p class="video-desc">
                    CNN Indonesia meliput rangkaian acara kenegaraan yang digelar
                    di Istana Kepresidenan.
                </p>
            </div>
        </div>

        <!-- =========================================================
             CNBC INDONESIA
             ========================================================= -->
        <div class="video-card" data-channel="cnbc">
            <div class="video-embed-wrap">
                <iframe
                    src="https://www.youtube.com/embed/Yk8MkX-TwMc"
                    title="Konferensi Pers Rapat Koordinasi Ekonomi"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen
                    loading="lazy">
                </iframe>
            </div>
            <div class="video-body">
                <div class="video-meta">
                    <span class="video-channel-badge badge-cnbc">CNBC Indonesia</span>
                    <span class="video-date">Terkini</span>
                </div>
                <h3 class="video-title">
                    <a href="https://www.youtube.com/watch?v=Yk8MkX-TwMc" target="_blank" rel="noopener">
                        Konferensi Pers — Rapat Koordinasi Ekonomi Nasional
                    </a>
                </h3>
                <p class="video-desc">
                    CNBC Indonesia melaporkan hasil rapat koordinasi ekonomi yang
                    membahas langkah strategis menjaga stabilitas perekonomian nasional.
                </p>
            </div>
        </div>

        <!-- =========================================================
             TVONE
             ========================================================= -->
        <div class="video-card" data-channel="tvone">
            <div class="video-embed-wrap">
                <iframe
                    src="https://www.youtube.com/embed/Aozop8oLpYQ"
                    title="Analisis Pakar Terkait Konflik di Timur Tengah"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen
                    loading="lazy">
                </iframe>
            </div>
            <div class="video-body">
                <div class="video-meta">
                    <span class="video-channel-badge badge-tvone">tvOne</span>
                    <span class="video-date">Terkini</span>
                </div>
                <h3 class="video-title">
                    <a href="https://www.youtube.com/watch?v=Aozop8oLpYQ" target="_blank" rel="noopener">
                        Analisis Pakar Terkait Konflik Terkini di Timur Tengah
                    </a>
                </h3>
                <p class="video-desc">
                    tvOne menghadirkan analisis para pakar hubungan internasional
                    terkait eskalasi konflik di kawasan Timur Tengah.
                </p>
            </div>
        </div>

        <!-- =========================================================
             TRIBUNNEWS
             ========================================================= -->
        <div class="video-card" data-channel="tribun">
            <div class="video-embed-wrap">
                <iframe
                    src="https://www.youtube.com/embed/Suo7HI13IiM"
                    title="Kondisi Terkini Dampak Serangan Rudal"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen
                    loading="lazy">
                </iframe>
            </div>
            <div class="video-body">
                <div class="video-meta">
                    <span class="video-channel-badge badge-tribun">Tribunnews</span>
                    <span class="video-date">Terkini</span>
                </div>
                <h3 class="video-title">
                    <a href="https://www.youtube.com/watch?v=Suo7HI13IiM" target="_blank" rel="noopener">
                        Kondisi Terkini Wilayah Terdampak Serangan Rudal
                    </a>
                </h3>
                <p class="video-desc">
                    Tribunnews melaporkan kondisi terkini di wilayah yang terdampak
                    konflik serta upaya evakuasi yang dilakukan.
                </p>
            </div>
        </div>

    </div><!-- /video-grid -->
</div><!-- /video-container -->

<script>
document.addEventListener('DOMContentLoaded', function () {
    const filterBtns = document.querySelectorAll('#videoFilter .filter-btn');
    const videoCards = document.querySelectorAll('#videoGrid .video-card');

    filterBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            // Update active class
            filterBtns.forEach(function (b) { b.classList.remove('aktif'); });
            this.classList.add('aktif');

            const filter = this.getAttribute('data-filter');

            videoCards.forEach(function (card) {
                if (filter === 'semua' || card.getAttribute('data-channel') === filter) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
