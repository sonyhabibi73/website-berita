/**
 * FILE: assets/js/chat.js
 * FUNGSI: Asisten Berita KimiNEWS — membuka/menutup panel,
 *         mengirim pertanyaan ke api/chat.php, lalu menampilkan
 *         balasan Gemini.
 *
 * BATASAN: Asisten hanya untuk topik berita terkini.
 *          Pembatasan topik dilakukan di server
 *          (system instruction pada api/chat.php), bukan di sini.
 *
 * CATATAN: API key TIDAK pernah disimpan di file ini. Semua
 *          permintaan lewat api/chat.php agar key tetap aman.
 */

document.addEventListener('DOMContentLoaded', function () {

    const fab     = document.getElementById('chatFab');
    const panel   = document.getElementById('chatPanel');
    const closeBt = document.getElementById('chatClose');
    const body    = document.getElementById('chatBody');
    const form    = document.getElementById('chatForm');
    const input   = document.getElementById('chatInput');
    const sendBt  = document.getElementById('chatSend');
    const suggest = document.getElementById('chatSuggest');

    if (!fab || !panel || !form || !input) return;

    /** Riwayat percakapan untuk dikirim ke server */
    const riwayat = [];

    let sedangKirim = false;


    // -------------------------------------------------------
    // 1. BUKA / TUTUP PANEL
    // -------------------------------------------------------
    function bukaPanel() {
        panel.hidden = false;
        // paksa reflow supaya transisi opacity jalan
        void panel.offsetWidth;
        panel.classList.add('buka');
        fab.setAttribute('aria-expanded', 'true');
        fab.classList.add('tersembunyi');
        input.focus();
    }

    function tutupPanel() {
        panel.classList.remove('buka');
        fab.setAttribute('aria-expanded', 'false');
        fab.classList.remove('tersembunyi');

        const selesai = function () {
            panel.hidden = true;
            panel.removeEventListener('transitionend', selesai);
        };
        panel.addEventListener('transitionend', selesai);
        // jaring pengaman bila transisi tidak terjadi
        setTimeout(function () { if (!panel.classList.contains('buka')) panel.hidden = true; }, 300);
    }

    fab.addEventListener('click', function () {
        if (panel.hidden) bukaPanel(); else tutupPanel();
    });

    if (closeBt) closeBt.addEventListener('click', tutupPanel);

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !panel.hidden) tutupPanel();
    });


    // -------------------------------------------------------
    // 2. UTILITAS RENDER PESAN (aman dari XSS)
    // -------------------------------------------------------
    function tambahPesan(teks, tipe) {
        const wrap = document.createElement('div');
        wrap.className = 'chat-msg chat-msg-' + tipe;

        const p = document.createElement('p');
        p.textContent = teks;          // textContent, bukan innerHTML
        wrap.appendChild(p);

        body.appendChild(wrap);
        gulirBawah();
        return wrap;
    }

    function gulirBawah() {
        body.scrollTop = body.scrollHeight;
    }

    function gantiTeks(elemen, teks) {
        let p = elemen.querySelector('p');

        // Indikator loading hanya berisi <span>, bukan <p>. Tanpa blok ini
        // balasan AI tidak akan pernah tampil (elemen <p> tidak ditemukan).
        if (!p) {
            elemen.textContent = '';          // buang titik-titik loading
            p = document.createElement('p');
            elemen.appendChild(p);
        }

        p.textContent = teks;
    }


    // -------------------------------------------------------
    // 3. TIPEKAN "SEDANG MENGETIK" (tanpa emoji)
    // -------------------------------------------------------
    function indikatorKirim() {
        const wrap = document.createElement('div');
        wrap.className = 'chat-msg chat-msg-bot chat-loading';
        wrap.innerHTML = '<span></span><span></span><span></span>';
        body.appendChild(wrap);
        gulirBawah();
        return wrap;
    }


    // -------------------------------------------------------
    // 4. KIRIM PERTANYAAN KE SERVER
    // -------------------------------------------------------
    function kirim(pertanyaan) {
        pertanyaan = (pertanyaan || '').trim();
        if (pertanyaan === '' || sedangKirim) return;

        sedangKirim = true;
        sendBt.disabled = true;
        sendBt.textContent = 'Mengirim';

        tambahPesan(pertanyaan, 'user');
        riwayat.push({ role: 'user', text: pertanyaan });

        if (suggest) suggest.remove();

        const loading = indikatorKirim();

        // Kontrak api/config.php copy ini: { message, history }
        // -> dibalas { success, response } | { success, error }
        fetch('api/chat.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                message: pertanyaan,
                history: riwayat.slice(0, -1)   // pesan user terakhir sudah dikirim terpisah
            })
        })
        .then(function (res) {
            return res.json().catch(function () {
                throw new Error('Respons server tidak terbaca. Coba lagi sebentar.');
            }).then(function (data) {
                if (!res.ok || data.success === false) {
                    throw new Error(data.error || 'Permintaan gagal. Coba lagi sebentar.');
                }
                return data;
            });
        })
        .then(function (data) {
            if (!data.response) {
                throw new Error('Asisten tidak memberi jawaban. Coba ulangi pertanyaannya.');
            }
            loading.classList.remove('chat-loading');
            gantiTeks(loading, data.response);
            riwayat.push({ role: 'model', text: data.response });
            gulirBawah();
        })
        .catch(function (err) {
            loading.classList.remove('chat-loading');
            loading.classList.add('chat-msg-error');
            gantiTeks(
                loading,
                err && err.message
                    ? err.message
                    : 'Maaf, terjadi gangguan saat menghubungi asisten. Coba lagi sebentar lagi.'
            );
            // pesan gagal tidak masuk riwayat
            gulirBawah();
        })
        .finally(function () {
            sedangKirim = false;
            sendBt.disabled = false;
            sendBt.textContent = 'Kirim';
            input.focus();
        });
    }


    // -------------------------------------------------------
    // 5. EVENT: SUBMIT FORM
    // -------------------------------------------------------
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const teks = input.value;
        input.value = '';
        autoResize();
        kirim(teks);
    });

    // Enter kirim, Shift+Enter bikin baris baru
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            form.requestSubmit ? form.requestSubmit() : form.dispatchEvent(new Event('submit', { cancelable: true }));
        }
    });

    // Auto-resize textarea ikut isi teks
    function autoResize() {
        input.style.height = 'auto';
        input.style.height = Math.min(input.scrollHeight, 120) + 'px';
    }
    input.addEventListener('input', autoResize);


    // -------------------------------------------------------
    // 6. SARAN TOPIK (topik berita saja)
    // -------------------------------------------------------
    if (suggest) {
        suggest.addEventListener('click', function (e) {
            const btn = e.target.closest('.chat-chip');
            if (!btn) return;
            kirim(btn.textContent);
        });
    }

});
