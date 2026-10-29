<script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.16.0/browser/overlayscrollbars.browser.es6.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js"></script>

<script src="{{ asset('assets/js/adminlte.js') }}"></script>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.1/dist/chart.umd.min.js"></script>

<script src="{{ asset('js/dashboard.js') }}"></script>


<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-local]').forEach(el => {
            const date = new Date(el.dataset.local);

            if (isNaN(date.getTime())) return;

            const format = el.dataset.format || 'datetime';

            const options = {
                date: {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric'
                },
                time: {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                },
                datetime: {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                }
            };

            el.textContent = new Intl.DateTimeFormat(
                navigator.language,
                options[format] || options.datetime
            ).format(date);
        });
    });
</script>