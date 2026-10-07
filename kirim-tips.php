<?php
// =============================================================
//  FILE: kirim-tips.php — KIRIM TIPS BERITA
//  FUNGSI: Form khusus buat pembaca ngirim tips/info ke redaksi.
//          Beda sama Kontak (formal), ini lebih santai & spesifik
//          buat lead berita.
// =============================================================
$page_title    = 'Kirim Tips Berita';
$halaman_aktif = 'kirim-tips';

$pesan_sukses = false;
$errors       = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama    = trim(htmlspecialchars($_POST['nama'] ?? ''));
    $email   = trim(htmlspecialchars($_POST['email'] ?? ''));
    $kategori = trim($_POST['kategori_tips'] ?? '');
    $judul_tips = trim(htmlspecialchars($_POST['judul_tips'] ?? ''));
    $cerita    = trim(htmlspecialchars($_POST['cerita'] ?? ''));

    if (empty($nama)) $errors[] = 'Nama atau inisial wajib diisi.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email gak valid bro, cek lagi.';
    if (empty($kategori)) $errors[] = 'Pilih kategori tipsnya.';
    if (empty($judul_tips)) $errors[] = 'Judul tipsnya jangan kosong dong.';
    if (strlen($cerita) < 15) $errors[] = 'Ceritanya masih kurang panjang, minimal 15 karakter ya.';

    if (empty($errors)) {
        // Simulasi sukses — nanti bisa disambungin ke database
        $pesan_sukses = true;
    }
}

include 'includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <h1 class="page-hero-title">Kirim <span>Tips Berita</span></h1>
        <p class="page-hero-subtitle">Punya info menarik, gosip panas, atau laporan langsung dari lapangan? Kasih tahu redaksi!</p>
    </div>
</section>

<div class="container tips-container">
    <div class="tips-layout">
        <!-- Kolom Kiri: Form -->
        <div class="tips-form-col">
            <div class="form-wrap">
                <h2>Form Tips</h2>
                <p class="form-subtitle">Isi aja sesuai yang lo tahu. Identitas lo bakal dirahasiakan kalau mau.</p>

                <?php if ($pesan_sukses): ?>
                    <div class="alert alert-success">
                         Tips lo berhasil dikirim ke redaksi! Tim kami bakal ngecek dan follow up lewat email. Makasih kontribusinya!
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error">
                        <strong>Eh, cek lagi bro:</strong>
                        <ul>
                            <?php foreach ($errors as $err): ?>
                                <li><?= $err ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="kirim-tips.php" class="kontak-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nama">Nama / Inisial <span class="required">*</span></label>
                            <input type="text" id="nama" name="nama" placeholder="Boleh nama asli atau inisial aja" value="<?= $_POST['nama'] ?? '' ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email <span class="required">*</span></label>
                            <input type="email" id="email" name="email" placeholder="Biar kami bisa hubungi balik" value="<?= $_POST['email'] ?? '' ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="kategori_tips">Kategori Tips <span class="required">*</span></label>
                        <select id="kategori_tips" name="kategori_tips" required>
                            <option value="">— Pilih kategori —</option>
                            <option value="teknologi" <?= (($_POST['kategori_tips'] ?? '') === 'teknologi') ? 'selected' : '' ?>>Teknologi</option>
                            <option value="olahraga" <?= (($_POST['kategori_tips'] ?? '') === 'olahraga') ? 'selected' : '' ?>>Olahraga</option>
                            <option value="hiburan" <?= (($_POST['kategori_tips'] ?? '') === 'hiburan') ? 'selected' : '' ?>>Hiburan</option>
                            <option value="politik" <?= (($_POST['kategori_tips'] ?? '') === 'politik') ? 'selected' : '' ?>>Politik</option>
                            <option value="kesehatan" <?= (($_POST['kategori_tips'] ?? '') === 'kesehatan') ? 'selected' : '' ?>>Kesehatan</option>
                            <option value="pendidikan" <?= (($_POST['kategori_tips'] ?? '') === 'pendidikan') ? 'selected' : '' ?>>Pendidikan</option>
                            <option value="lainnya" <?= (($_POST['kategori_tips'] ?? '') === 'lainnya') ? 'selected' : '' ?>>Lainnya</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="judul_tips">Judul Tips <span class="required">*</span></label>
                        <input type="text" id="judul_tips" name="judul_tips" placeholder="Contoh: 'Penemuan vaksin baru di Unibraw'" value="<?= $_POST['judul_tips'] ?? '' ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="cerita">Ceritain Detailnya <span class="required">*</span></label>
                        <textarea id="cerita" name="cerita" rows="6" placeholder="Ceritain selengkap mungkin: siapa, apa, kapan, di mana, kenapa penting? Info tambahan kayak foto atau link juga boleh ditempel di sini." required><?= $_POST['cerita'] ?? '' ?></textarea>
                    </div>

                    <div class="tips-privacy">
                        <input type="checkbox" id="rahasiakan" name="rahasiakan" value="1" <?= isset($_POST['rahasiakan']) ? 'checked' : '' ?>>
                        <label for="rahasiakan">Jangan sebut identitas saya di publik (saya pengirim anonim)</label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-full">
                         Kirim Tips
                    </button>
                </form>
            </div>
        </div>

        <!-- Kolom Kanan: Informasi -->
        <div class="tips-info-col">
            <div class="tips-info-card">
                <h3>Sebelum Kirim</h3>
                <ul>
                    <li>Tips harus <strong>faktual</strong> dan bisa dipertanggungjawabkan</li>
                    <li>Sertakan sumber atau bukti pendukung kalau ada</li>
                    <li>Tim redaksi bakal verifikasi sebelum menayangkan</li>
                    <li>Jangan kirim hoax atau informasi yang belum jelas kebenarannya</li>
                    <li>Tidak menerima konten SARA atau fitnah</li>
                </ul>
            </div>

            <div class="tips-info-card tips-info-card-alt">
                <h3>Soal Identitas</h3>
                <p>Kalau lo centang opsi <strong>anonim</strong>, identitas lo bakal kami rahasia. Nama lo gak bakal muncul di artikel. Tapi tetep isi email yang bener ya, biar kami bisa konfirmasi kalau butuh info tambahan.</p>
            </div>

            <div class="tips-info-card">
                <h3>Tips Terbaik Bulan Ini</h3>
                <div class="tip-of-month">
                    <span class="tip-emoji"></span>
                    <div>
                        <strong>Bocoran Stadion Baru</strong>
                        <p>Dari: Anonim — Terverifikasi </p>
                        <p class="tip-quote">"Stadion di daerah gua udah kelar dibangun tapi belum diresmikan setahun. Ada apa?"</p>
                        <span class="tip-result">→ Udah ditindaklanjuti & jadi artikel minggu lalu</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
