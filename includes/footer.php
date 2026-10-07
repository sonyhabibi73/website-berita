<?php
// =============================================================
//  FILE: includes/footer.php
//  FUNGSI: Menampilkan bagian bawah (footer) yang sama di
//          semua halaman — link navigasi, info hak cipta, dll.
//
//  CARA PAKAI: Di setiap halaman .php, tulis PALING BAWAH:
//      <?php include 'includes/footer.php'; ?>


</main><!-- /site-main — ditutup dari header.php -->

<!-- ============================================================
     FOOTER
     ============================================================ -->
<footer class="site-footer">
    <div class="container">

        <!-- GRID FOOTER: 3 kolom di desktop, stack di HP -->
        <div class="footer-grid">

            <!-- Kolom 1: Tentang Singkat -->
            <div class="footer-col">
                <h3 class="footer-logo">Kimi<strong>NEWS</strong></h3>
                <p>Media berita digital terpercaya yang menyajikan informasi akurat, cepat, dan berimbang untuk masyarakat Indonesia.</p>
            </div>

            <!-- Kolom 2: Navigasi Cepat -->
            <div class="footer-col">
                <h4>Navigasi</h4>
                <ul class="footer-links">
                    <li><a href="index.php">Beranda</a></li>
                    <li><a href="tentang.php">Tentang Kami</a></li>
                    <li><a href="galeri.php">Galeri</a></li>
                    <li><a href="video.php">Video</a></li>
                    <li><a href="indeks.php">Indeks</a></li>
                    <li><a href="kirim-tips.php">Kirim Tips</a></li>
                    <li><a href="kontak.php">Kontak</a></li>
                </ul>
            </div>

            <!-- Kolom 3: Kategori Berita -->
            <div class="footer-col">
                <h4>Kategori Berita</h4>
                <ul class="footer-links">
                    <li><a href="kat-teknologi.php">Teknologi</a></li>
                    <li><a href="kat-olahraga.php">Olahraga</a></li>
                    <li><a href="kat-hiburan.php">Hiburan</a></li>
                    <li><a href="kat-politik.php">Politik</a></li>
                    <li><a href="kat-kesehatan.php">Kesehatan</a></li>
                    <li><a href="kat-pendidikan.php">Pendidikan</a></li>
                </ul>
            </div>

        </div><!-- /footer-grid -->

        <!-- Garis pemisah & hak cipta -->
        <div class="footer-bottom">
            <p>
                &copy; <?= date('Y') ?> KimiNEWS.
                Dibuat oleh Tim KimiNEWS. Semua hak cipta dilindungi.
                Seluruh konten bersifat fiktif untuk keperluan pembelajaran.
            </p>
        </div>

    </div><!-- /container -->
</footer>

<?php include 'includes/chat.php'; ?>

</body>
</html>
