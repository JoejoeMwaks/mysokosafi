// Minimal JS — Theme Toggle + Swiper Init
document.addEventListener('DOMContentLoaded', () => {

  // ── Theme Toggle ──────────────────────────────────────────
  const html = document.documentElement;
  const toggleBtn = document.getElementById('theme-toggle');
  const themeIcon = document.getElementById('theme-icon');
  const STORAGE_KEY = 'sokosafi-theme';

  function applyTheme(theme) {
    html.setAttribute('data-theme', theme);
    if (themeIcon) {
      themeIcon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    }
    try { localStorage.setItem(STORAGE_KEY, theme); } catch(e) {}
  }

  // Read saved theme (inline script in header already applied; sync icon)
  const savedTheme = (function() {
    try { return localStorage.getItem(STORAGE_KEY) || 'light'; } catch(e) { return 'light'; }
  })();
  applyTheme(savedTheme);

  if (toggleBtn) {
    toggleBtn.addEventListener('click', () => {
      const current = html.getAttribute('data-theme') || 'light';
      applyTheme(current === 'dark' ? 'light' : 'dark');
    });
  }

  // ── Swiper Init ───────────────────────────────────────────
  if (window.Swiper) {
    const mainEl = document.querySelector('.main-swiper');
    if (mainEl) {
      new Swiper(mainEl, {
        slidesPerView: 1,
        loop: true,
        speed: 500,
        pagination: { el: '.swiper-pagination', clickable: true },
        autoHeight: false,
        grabCursor: true,
      });
    }
    const heroEl = document.querySelector('.hero-swiper');
    if (heroEl) {
      new Swiper(heroEl, {
        loop: true,
        effect: 'fade',
        speed: 1000,
        autoplay: { delay: 5000, disableOnInteraction: false },
        pagination: { el: '.swiper-pagination', clickable: true },
      });
    }
  }
});