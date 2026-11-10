/* Shared theme switcher: public site + admin panel */
(function () {
    'use strict';

    const mq = window.matchMedia('(prefers-color-scheme: dark)');
    const ICONS = { light: 'bi-sun-fill', dark: 'bi-moon-stars-fill', auto: 'bi-circle-half' };

    const savedMode = () => {
        try { return localStorage.getItem('theme') || 'auto'; }
        catch (e) { return 'auto'; }
    };

    function applyTheme(mode) {
        const real = mode === 'auto' ? (mq.matches ? 'dark' : 'light') : mode;
        document.documentElement.setAttribute('data-bs-theme', real);

        const icon = document.getElementById('themeIcon');
        if (icon) icon.className = 'bi ' + ICONS[mode];

        document.querySelectorAll('[data-theme]').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.theme === mode);
        });
    }

    // Click handler: [data-theme] button-e click korle theme change
    document.addEventListener('click', e => {
        const btn = e.target.closest('[data-theme]');
        if (!btn) return;
        try { localStorage.setItem('theme', btn.dataset.theme); } catch (err) {}
        applyTheme(btn.dataset.theme);
    });

    // OS theme change hole, auto mode-e thakle abar apply
    mq.addEventListener('change', () => {
        if (savedMode() === 'auto') applyTheme('auto');
    });

    applyTheme(savedMode());
})();