<?php
// =============================================================
//  FILE: includes/chat.php
//  FUNGSI: Asisten Berita KimiNEWS — tombol mengambang (FAB)
//          di pojok kanan bawah + panel percakapan.
//
//  CARA PAKAI: Dipanggil dari includes/footer.php tepat sebelum
//              tag </body>.
//
//  BATASAN: Asisten hanya menjawab topik berita terkini
//           (dipaksa lewat system instruction di api/config.php).
// ============================================================= */
?>

<!-- TOMBOL MENGAMBANG: buka/tutup panel asisten -->
<button type="button"
        class="chat-fab"
        id="chatFab"
        aria-expanded="false"
        aria-controls="chatPanel">
    <span class="chat-fab-label">Asisten Berita</span>
</button>

<!-- PANEL PERCAKAPAN -->
<section class="chat-panel"
         id="chatPanel"
         role="dialog"
         aria-label="Asisten Berita KimiNEWS"
         aria-modal="false"
         hidden>

    <header class="chat-head">
        <div class="chat-head-text">
            <p class="chat-title">Asisten Berita</p>
            <p class="chat-sub">Cari dan rangkum berita terkini</p>
        </div>
        <button type="button" class="chat-close" id="chatClose" aria-label="Tutup asisten">
            Tutup
        </button>
    </header>

    <div class="chat-body" id="chatBody" role="log" aria-live="polite" aria-relevant="additions">

        <div class="chat-msg chat-msg-bot">
            <p>Halo. Saya membantu mencari dan merangkum berita terkini. Pilih topik di bawah, atau ketik pertanyaan berita Anda.</p>
        </div>

        <div class="chat-suggest" id="chatSuggest">
            <button type="button" class="chat-chip">Berita politik terkini</button>
            <button type="button" class="chat-chip">Kabar teknologi hari ini</button>
            <button type="button" class="chat-chip">Update olahraga terbaru</button>
        </div>

    </div>

    <form class="chat-form" id="chatForm" autocomplete="off">
        <label class="chat-label" for="chatInput"><span class="chat-sr-only">Pertanyaan berita Anda</span></label>
        <div class="chat-row">
            <textarea id="chatInput"
                      class="chat-input"
                      rows="1"
                      maxlength="500"
                      placeholder="Contoh: apa berita terbaru soal UU PDP?"
                      required></textarea>
            <button type="submit" class="chat-send" id="chatSend">Kirim</button>
        </div>
        <p class="chat-note">Asisten hanya menjawab topik berita terkini.</p>
    </form>

</section>

<script src="assets/js/chat.js?v=20261016c" defer></script>
