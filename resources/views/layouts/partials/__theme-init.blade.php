<script>
(() => {
    const root = document.documentElement;

    if (root.getAttribute('data-lte-color-mode') === 'off') {
        return;
    }

    let savedTheme = null;

    try {
        savedTheme = localStorage.getItem('lte-theme');
    } catch (e) {}

    const declaredTheme = root.getAttribute('data-bs-theme');

    const theme =
        ['light', 'dark'].includes(savedTheme) ? savedTheme :
        ['light', 'dark'].includes(declaredTheme) ? declaredTheme :
        (window.matchMedia('(prefers-color-scheme: dark)').matches
            ? 'dark'
            : 'light');

    root.setAttribute('data-bs-theme', theme);
    root.style.colorScheme = theme;
})();
</script>