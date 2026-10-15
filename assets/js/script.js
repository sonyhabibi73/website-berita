/**
 * FILE: assets/js/script.js
 * FUNGSI: Interaktivitas website — hamburger menu, dropdown mobile,
 *         dan efek-efek kecil lainnya.
 *
 * PERUBAHAN (BUG FIX):
 * - Breakpoint diubah dari 768px → 900px (sesuai CSS media query)
 * - Dropdown pakai max-height toggle (bukan display:none/block)
 * - Resize listener: tutup nav jika layar diperbesar ke desktop
 * - closeNav() juga clear dropdown agar tidak ghost-open saat re-open
 */

document.addEventListener('DOMContentLoaded', function () {

    // -------------------------------------------------------
    // 0. BREAKPOINT — harus sama dengan @media (max-width: 900px) di CSS
    // -------------------------------------------------------
    const MOBILE_BREAKPOINT = 900; // FIX: was 768

    // -------------------------------------------------------
    // 1. HAMBURGER MENU TOGGLE
    // -------------------------------------------------------
    const hamburgerBtn = document.getElementById('hamburgerBtn');
    const mainNav      = document.getElementById('mainNav');
    const navOverlay   = document.getElementById('navOverlay');

    function toggleNav() {
        const isOpen = mainNav.classList.toggle('nav-open');
        hamburgerBtn.classList.toggle('aktif', isOpen);
        navOverlay.classList.toggle('aktif', isOpen);
        hamburgerBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        document.body.style.overflow = isOpen ? 'hidden' : '';
    }

    function closeNav() {
        mainNav.classList.remove('nav-open');
        hamburgerBtn.classList.remove('aktif');
        navOverlay.classList.remove('aktif');
        hamburgerBtn.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';

        // FIX BUG 3: also reset any open dropdowns when nav closes
        const openDropdown = document.querySelector('.dropdown.open');
        if (openDropdown) openDropdown.classList.remove('open');
    }

    if (hamburgerBtn) hamburgerBtn.addEventListener('click', toggleNav);
    if (navOverlay)   navOverlay.addEventListener('click', closeNav);

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeNav();
    });

    // FIX: close the drawer if the user resizes the window to desktop width
    window.addEventListener('resize', function () {
        if (window.innerWidth > MOBILE_BREAKPOINT) {
            closeNav();
        }
    });


    // -------------------------------------------------------
    // 2. DROPDOWN KATEGORI TOGGLE (mobile only)
    //    FIX BUG 3: check against MOBILE_BREAKPOINT (was hardcoded 768)
    // -------------------------------------------------------
    const dropdownToggle = document.querySelector('.dropdown-toggle');
    const dropdown       = document.querySelector('.dropdown');

    if (dropdownToggle && dropdown) {
        dropdownToggle.addEventListener('click', function (e) {
            if (window.innerWidth <= MOBILE_BREAKPOINT) {   // FIX: was < 768
                e.preventDefault();
                e.stopPropagation();
                dropdown.classList.toggle('open');
            }
        });
    }

    // Close dropdown on outside-click (desktop & mobile)
    document.addEventListener('click', function (e) {
        if (dropdown && !dropdown.contains(e.target)) {
            dropdown.classList.remove('open');
        }
    });


    // -------------------------------------------------------
    // 3. CLOSE NAV ON NAV-LINK CLICK (mobile)
    // -------------------------------------------------------
    const navLinks = document.querySelectorAll('.main-nav .nav-link, .dropdown-item');
    navLinks.forEach(function (link) {
        link.addEventListener('click', function () {
            if (!this.classList.contains('dropdown-toggle')) {
                closeNav();
            }
        });
    });


    // -------------------------------------------------------
    // 4. SCROLL HEADER EFFECT
    // -------------------------------------------------------
    const siteHeader = document.querySelector('.site-header');

    if (siteHeader) {
        let ticking = false;
        window.addEventListener('scroll', function () {
            if (!ticking) {
                requestAnimationFrame(function () {
                    siteHeader.classList.toggle('scrolled', window.scrollY >= 50);
                    ticking = false;
                });
                ticking = true;
            }
        });
    }


    // -------------------------------------------------------
    // 5. TANPA ANIMASI SADAR-KARTU
    //    Konten berita selalu terlihat walau JavaScript gagal.
    //    Gerak hanya lewat transisi hover di CSS.
    // -------------------------------------------------------


    console.log('%cKimiNEWS — script.js loaded (wire desk)', 'color: #e8a020; font-weight: bold;');

}); // end DOMContentLoaded
