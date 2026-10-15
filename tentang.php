<?php
// =============================================================
//  FILE: tentang.php — HALAMAN 2: TENTANG KAMI
// =============================================================
$page_title    = 'Tentang Kami';
$halaman_aktif = 'tentang';
include 'includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <h1 class="page-hero-title">Tentang <span>KimiNEWS</span></h1>
        <p class="page-hero-subtitle">Media digital terpercaya untuk informasi Indonesia</p>
    </div>
</section>

<div class="container about-container">

    <!-- Tentang Singkat -->
    <section class="about-section">
        <div class="about-text">
            <h2>Siapa Kami?</h2>
            <p>
                <strong>KimiNEWS</strong> adalah platform berita digital yang lahir dari semangat
                memberikan informasi yang akurat, berimbang, dan mudah diakses oleh seluruh lapisan
                masyarakat Indonesia. Didirikan pada 2020, kami telah melayani jutaan pembaca setia
                dari Sabang sampai Merauke.
            </p>
            <p>
                Kami percaya bahwa informasi yang benar adalah hak setiap warga negara. Oleh karena itu,
                seluruh tim redaksi kami berkomitmen penuh pada prinsip-prinsip jurnalisme yang
                bertanggung jawab: fakta, verifikasi, dan keberimbangan.
            </p>
        </div>
        <div class="about-stats">
            <div class="stat-item">
                <span class="stat-number">5 Juta+</span>
                <span class="stat-label">Pembaca Bulanan</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">50+</span>
                <span class="stat-label">Jurnalis Aktif</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">6</span>
                <span class="stat-label">Kategori Berita</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">2020</span>
                <span class="stat-label">Tahun Berdiri</span>
            </div>
        </div>
    </section>

    <!-- Visi Misi -->
    <section class="visi-misi-section">
        <div class="visi-card">
            <h3>Visi</h3>
            <p>Menjadi portal berita digital nomor satu di Indonesia yang dipercaya dan dicintai oleh masyarakat dari semua kalangan.</p>
        </div>
        <div class="misi-card">
            <h3>Misi</h3>
            <ul>
                <li>Menyajikan berita yang akurat dan terverifikasi</li>
                <li>Mengedepankan prinsip keberimbangan dalam setiap liputan</li>
                <li>Memanfaatkan teknologi untuk mempercepat distribusi informasi</li>
                <li>Memberdayakan jurnalis lokal di seluruh pelosok Indonesia</li>
            </ul>
        </div>
    </section>

    <!-- Tim Redaksi -->
<section class="tim-section">
        <h2 class="section-title">Tim Redaksi</h2>
        <div class="tim-grid">
            <?php
            // Mengubah 'emoji' menjadi 'foto' yang mengarah ke folder assets/img/
            $tim = [
                ['nama' => 'Erwin Smith',     'jabatan' => 'Pemimpin Redaksi',    'foto' => 'assets/img/erwin.jpg'],
                ['nama' => 'Hange Zoe',     'jabatan' => 'Editor Senior',       'foto' => 'assets/img/kebakar.jpg'],
                ['nama' => 'Dr. Vegapunk',    'jabatan' => 'Reporter Teknologi',  'foto' => 'assets/img/dr.vegapunk.jpeg'],
                ['nama' => 'Chopper',    'jabatan' => 'Reporter Kesehatan',  'foto' => 'assets/img/chopper.jpeg'],
                ['nama' => 'Hinata Shoyo',    'jabatan' => 'Reporter Olahraga',   'foto' => 'assets/img/hinata-shoyo.jpeg'],
                ['nama' => 'Nakami Ganta', 'jabatan' => 'Fotografer',          'foto' => 'assets/img/ganta-potografer.jpg'],
            ];
            foreach ($tim as $anggota): ?>
            <div class="tim-card">
                <div class="tim-avatar" style="width: 80px; height: 80px; margin: 0 auto 15px; overflow: hidden;">
                    <img src="<?= htmlspecialchars($anggota['foto']) ?>" 
                         alt="Foto <?= htmlspecialchars($anggota['nama']) ?>" 
                         style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                <h4><?= htmlspecialchars($anggota['nama']) ?></h4>
                <p><?= htmlspecialchars($anggota['jabatan']) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

</div><!-- /container -->

<?php include 'includes/footer.php'; ?>
