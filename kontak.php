<?php
// =============================================================
//  FILE: kontak.php — HALAMAN 3: HUBUNGI REDAKSI (DATABASE ACTIVE)
// =============================================================
$page_title    = 'Hubungi Kami';
$halaman_aktif = 'kontak';

$pesan_sukses = false;
$errors       = [];

//  1. PANGGIL KONEKSI DATABASE
include 'koneksi.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil dan sanitasi input dari form
    $nama  = trim(htmlspecialchars($_POST['nama']  ?? ''));
    $email = trim(htmlspecialchars($_POST['email'] ?? ''));
    $subj  = trim(htmlspecialchars($_POST['subjek'] ?? ''));
    $pesan = trim(htmlspecialchars($_POST['pesan'] ?? ''));

    // Validasi sederhana
    if (empty($nama))             $errors[] = 'Nama wajib diisi.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Format email tidak valid.';
    if (empty($subj))             $errors[] = 'Subjek wajib diisi.';
    if (strlen($pesan) < 20)      $errors[] = 'Pesan minimal 20 karakter.';

    if (empty($errors)) {
        //  2. PROSES MASUKIN DATA KE DATABASE
        $query = "INSERT INTO kontak_pesan (nama, email, subjek, pesan) VALUES (?, ?, ?, ?)";
        
        // Menyiapkan query (Prepared Statement biar aman dari hacker)
        $stmt = mysqli_prepare($koneksi, $query);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssss", $nama, $email, $subj, $pesan);
            
            if (mysqli_stmt_execute($stmt)) {
                $pesan_sukses = true;
                // Kosongkan form setelah sukses kirim
                $nama = $email = $subj = $pesan = "";
            } else {
                $errors[] = "Gagal menyimpan data ke database. Detail: " . mysqli_stmt_error($stmt);
            }
            mysqli_stmt_close($stmt);
        } else {
            $errors[] = "Gagal menyiapkan database. Pastikan tabel 'kontak_pesan' sudah dibuat di phpMyAdmin. Detail: " . mysqli_error($koneksi);
        }
    }
}

include 'includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <h1 class="page-hero-title">Hubungi <span>Redaksi</span></h1>
        <p class="page-hero-subtitle">Ada pertanyaan, laporan, atau ingin jadi kontributor? Kami siap mendengar!</p>
    </div>
</section>

<div class="container kontak-container">

    <div class="kontak-info-grid">
        <div class="kontak-info-item">
            <h4>Alamat Redaksi</h4>
            <p>Jl. Sudirman No. 99, Jakarta Pusat 10220, Indonesia</p>
        </div>
        <div class="kontak-info-item">
            <h4>Telepon</h4>
            <p>(021) 5555-1234<br>Senin–Jumat, 08.00–17.00 WIB</p>
        </div>
        <div class="kontak-info-item">
            <h4>Email</h4>
            <p>redaksi@KimiNEWS.id<br>iklan@KimiNEWS.id</p>
        </div>
    </div>

    <div class="form-wrap">
        <h2>Kirim Pesan</h2>

        <?php if ($pesan_sukses): ?>
            <div class="alert alert-success">
                 Pesan Anda berhasil terkirim dan tersimpan ke Database!
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <strong>Terdapat kesalahan:</strong>
                <ul>
                    <?php foreach ($errors as $err): ?>
                        <li><?= $err ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="kontak.php" class="kontak-form">

            <div class="form-row">
                <div class="form-group">
                    <label for="nama">Nama Lengkap <span class="required">*</span></label>
                    <input type="text" id="nama" name="nama"
                           value="<?= isset($nama) ? $nama : '' ?>"
                           placeholder="Masukkan nama Anda" required>
                </div>
                <div class="form-group">
                    <label for="email">Alamat Email <span class="required">*</span></label>
                    <input type="email" id="email" name="email"
                           value="<?= isset($email) ? $email : '' ?>"
                           placeholder="contoh@email.com" required>
                </div>
            </div>

            <div class="form-group">
                <label for="subjek">Subjek <span class="required">*</span></label>
                <input type="text" id="subjek" name="subjek"
                       value="<?= isset($subj) ? $subj : '' ?>"
                       placeholder="Topik pesan Anda" required>
            </div>

            <div class="form-group">
                <label for="pesan">Pesan <span class="required">*</span></label>
                <textarea id="pesan" name="pesan" rows="6"
                          placeholder="Tulis pesan Anda di sini (minimal 20 karakter)..."
                          required><?= isset($pesan) ? $pesan : '' ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary btn-full">
                 Kirim Pesan
            </button>

        </form>
    </div>

</div>

<?php include 'includes/footer.php'; ?>