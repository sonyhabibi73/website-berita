<?php
// =============================================================
//  FILE: podcast.php — HALAMAN PODCAST KIMINEWS
//  FUNGSI: Menampilkan daftar episode podcast, audio player,
//          dan profil host. Bedanya sama berita biasa: ini
//          format AUDIO, bukan teks.
// =============================================================
$page_title    = 'Podcast';
$halaman_aktif = 'podcast';

// Data podcast — bisa diganti/ditambah sendiri
$episodes = [
    [
        'episode'  => 12,
        'judul'    => 'Coding Itu Susah? Mitos vs Fakta Bersama Full-Stack Developer',
        'host'     => 'Rina & Bima',
        'durasi'   => '42:15',
        'tanggal'  => '29 Mei 2026',
        'deskripsi'=> 'Gua ngobrol santai sama developer yang udah 10 tahun di industri. Bahas mulai dari cara pertama kali belajar coding sampe mental health di dunia IT. Spoiler: ternyata senior sekalipun masih stackoverflow tiap hari.',
        'kategori' => 'teknologi',
    ],
    [
        'episode'  => 11,
        'judul'    => 'Kenapa Pemain Bola Lokal Kurang Kreatif? Analisis dari Mantan Pelatih Timnas',
        'host'     => 'Bima & Coki',
        'durasi'   => '55:30',
        'tanggal'  => '22 Mei 2026',
        'deskripsi'=> 'Ngebahas akar masalah sepak bola Indonesia dari sudut pandang taktik dan pembinaan usia muda. Bukan melulu soal PSSI, tapi bagaimana kebiasaan latihan dari kecil ngebentuk gaya main pemain.',
        'kategori' => 'olahraga',
    ],
    [
        'episode'  => 10,
        'judul'    => 'Film Horor Indonesia: Antara Teriakan dan Kualitas Cerita',
        'host'     => 'Rina & Dika',
        'durasi'   => '38:50',
        'tanggal'  => '15 Mei 2026',
        'deskripsi'=> 'Review film horor Indonesia yang tayang tahun ini. Kenapa penonton masih aja dibikin teriak pakai jump scare murahan? Ada kok film horor kita yang ceritanya beneran mateng.',
        'kategori' => 'hiburan',
    ],
    [
        'episode'  => 9,
        'judul'    => 'Gara-Gara Undang-Undang PDP, Startup Pada Pusing. Wawancara Eksklusif!',
        'host'     => 'Bima & Sari',
        'durasi'   => '47:20',
        'tanggal'  => '8 Mei 2026',
        'deskripsi'=> 'Ngobrol langsung sama founder startup lokal soal dampak UU Perlindungan Data Pribadi. Ada yang udah siap, ada yang masih kocar-kacir. Bahas juga soal denda Rp 60 M yang bikin CEO gelisah.',
        'kategori' => 'politik',
    ],
    [
        'episode'  => 8,
        'judul'    => 'Skincare Vs Mental Health: Dokter Kulit Buka Suara',
        'host'     => 'Sari & dr. Dewi',
        'durasi'   => '34:10',
        'tanggal'  => '1 Mei 2026',
        'deskripsi'=> 'Ternyata tren skincare yang kelewatan bisa berdampak ke kesehatan mental. Dari body dysmorphia sampe FOMO produk. Dokter kulit ngasih perspektif yang jarang dibahas di sosmed.',
        'kategori' => 'kesehatan',
    ],
    [
        'episode'  => 7,
        'judul'    => 'Kurikulum Baru: Anak SD Belajar Coding, Emang Siap?',
        'host'     => 'Rina & Pak Guru',
        'durasi'   => '41:05',
        'tanggal'  => '24 April 2026',
        'deskripsi'=> 'Diskusi seru bareng guru SD yang udah ikut pelatihan coding. Apa iya anak kelas 4 bisa nulis Python? Atau cuma jadi beban baru? Bahas juga nasib guru di daerah 3T.',
        'kategori' => 'pendidikan',
    ],
];

include 'includes/header.php';
?>

<!-- HERO PODCAST -->
<section class="podcast-hero">
    <div class="container">
        <div class="podcast-hero-inner">
            <div class="podcast-hero-text">
                <span class="podcast-live-badge">LIVE NOW</span>
                <h1 class="podcast-hero-title">Kimi<strong>Talk</strong></h1>
                <p class="podcast-hero-desc">
                    Podcast ngobrol santai bareng redaksi. Bahas berita yang lagi viral, 
                    ngulik isu dalem, dan ngobrol sama tokoh-tokoh seru. 
                    Rilis setiap hari <strong>Kamis</strong> jam 19:00 WIB.
                </p>
                <div class="podcast-hero-stats">
                    <span>12 Episode</span>
                    <span>Rata-rata 45 menit</span>
                    <span>Season 2</span>
                </div>
            </div>
            <div class="podcast-hero-visual">
                <div class="podcast-now-playing">
                    <div class="np-cover">
                        <span></span>
                    </div>
                    <div class="np-info">
                        <span class="np-label">Sedang Diputar</span>
                        <span class="np-title">Episode 12: Coding Itu Susah?</span>
                        <span class="np-host">Bersama Rina & Bima</span>
                    </div>
                    <div class="np-waveform">
                        <span></span><span></span><span></span><span></span>
                        <span></span><span></span><span></span><span></span>
                        <span></span><span></span><span></span><span></span>
                        <span></span><span></span><span></span><span></span>
                        <span></span><span></span><span></span><span></span>
                        <span></span><span></span><span></span><span></span>
                        <span></span><span></span><span></span><span></span>
                        <span></span><span></span><span></span><span></span>
                        <span></span><span></span><span></span><span></span>
                        <span></span><span></span><span></span><span></span>
                    </div>
                    <div class="np-controls">
                        <button class="np-btn" title="Sebelumnya" aria-label="Episode sebelumnya">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="19 20 9 12 19 4 19 20"/><line x1="5" y1="19" x2="5" y2="5"/></svg>
                        </button>
                        <button class="np-btn np-play" title="Play/Pause">▶</button>
                        <button class="np-btn" title="Selanjutnya" aria-label="Episode selanjutnya">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="5 4 15 12 5 20 5 4"/><line x1="19" y1="5" x2="19" y2="19"/></svg>
                        </button>
                    </div>
                    <div class="np-progress">
                        <span class="np-time">12:30</span>
                        <div class="np-bar"><div class="np-bar-fill" style="width: 29%;"></div></div>
                        <span class="np-time">42:15</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- DAFTAR EPISODE -->
<section class="podcast-episodes">
    <div class="container">
        <div class="podcast-header">
            <h2 class="section-title">Semua Episode</h2>
            <div class="podcast-filter">
                <button class="filter-btn aktif" data-filter="semua">Semua</button>
                <button class="filter-btn" data-filter="teknologi">Teknologi</button>
                <button class="filter-btn" data-filter="olahraga">Olahraga</button>
                <button class="filter-btn" data-filter="hiburan">Hiburan</button>
                <button class="filter-btn" data-filter="politik">Politik</button>
                <button class="filter-btn" data-filter="kesehatan">Kesehatan</button>
                <button class="filter-btn" data-filter="pendidikan">Pendidikan</button>
            </div>
        </div>

        <div class="episode-list">
            <?php foreach ($episodes as $ep): ?>
            <div class="episode-card" data-kategori="<?= $ep['kategori'] ?>">
                <div class="episode-num"><?= str_pad($ep['episode'], 2, '0', STR_PAD_LEFT) ?></div>
                <div class="episode-body">
                    <div class="episode-meta">
                        <span class="ep-date"><?= $ep['tanggal'] ?></span>
                        <span class="ep-durasi"><?= $ep['durasi'] ?></span>
                        <span class="badge badge-<?= $ep['kategori'] ?>"><?= ucfirst($ep['kategori']) ?></span>
                    </div>
                    <h3 class="ep-title"><?= htmlspecialchars($ep['judul']) ?></h3>
                    <p class="ep-desc"><?= htmlspecialchars($ep['deskripsi']) ?></p>
                    <div class="ep-footer">
                        <span class="ep-host">Host: <?= $ep['host'] ?></span>
                        <button class="btn btn-primary btn-sm" onclick="alert('Putar episode <?= $ep['episode'] ?> (simulasi)')">
                            ▶ Putar Episode
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- HOST SECTION -->
<section class="podcast-hosts">
    <div class="container">
        <h2 class="section-title">Host Podcast</h2>
        <div class="hosts-grid">
            <div class="host-card">
                <div class="host-avatar">R</div>
                <h4>Rina</h4>
                <p>Jurnalis Teknologi. Suka ngulik gadget dan ngomongin AI. Host paling cerewet di studio.</p>
            </div>
            <div class="host-card">
                <div class="host-avatar">B</div>
                <h4>Bima</h4>
                <p>Reporter Olahraga & Politik. Mantan atlet yang nyasar ke jurnalisme. Spesialis debat panas.</p>
            </div>
            <div class="host-card">
                <div class="host-avatar">S</div>
                <h4>Sari</h4>
                <p>Jurnalis Kesehatan. Always bringing the facts. Pernah jadi relawan bencana di 5 provinsi.</p>
            </div>
        </div>
    </div>
</section>

<!-- INLINE JS FILTER -->
<script>
const filterBtns = document.querySelectorAll('.podcast-filter .filter-btn');
const epCards = document.querySelectorAll('.episode-card');
filterBtns.forEach(btn => {
    btn.addEventListener('click', function() {
        filterBtns.forEach(b => b.classList.remove('aktif'));
        this.classList.add('aktif');
        const filter = this.dataset.filter;
        epCards.forEach(card => {
            card.style.display = (filter === 'semua' || card.dataset.kategori === filter) ? 'flex' : 'none';
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
