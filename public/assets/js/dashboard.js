
'use strict';

document.addEventListener('DOMContentLoaded', function () {

    // ==========================================
    // OverlayScrollbars
    // ==========================================

    const sidebarWrapper = document.querySelector('.sidebar-wrapper');

    if (
        sidebarWrapper &&
        window.innerWidth > 992 &&
        window.OverlayScrollbarsGlobal?.OverlayScrollbars
    ) {
        window.OverlayScrollbarsGlobal.OverlayScrollbars(
            sidebarWrapper,
            {
                scrollbars: {
                    theme: 'os-theme-light',
                    autoHide: 'leave',
                    clickScroll: true
                }
            }
        );
    }

    // ==========================================
    // Chart.js theme configuration
    // ==========================================

    if (typeof Chart === 'undefined') {
        return;
    }

    const root = document.documentElement;

    const cssVar = (name, fallback = '') =>
        getComputedStyle(root).getPropertyValue(name).trim() || fallback;

    const alpha = (color, opacity) => {
        let hex = String(color).trim().replace(/^#/, '');

        if (!/^[\da-f]{3}([\da-f]{3})?$/i.test(hex)) {
            return color;
        }

        if (hex.length === 3) {
            hex = [...hex].map(c => c + c).join('');
        }

        const [r, g, b] = [0, 2, 4].map(
            i => parseInt(hex.slice(i, i + 2), 16)
        );

        return `rgba(${r}, ${g}, ${b}, ${opacity})`;
    };

    const areaFill = (color, from = 0.35, to = 0) => context => {
        const { ctx, chartArea } = context.chart;

        if (!chartArea) {
            return alpha(color, from);
        }

        const gradient = ctx.createLinearGradient(
            0,
            chartArea.top,
            0,
            chartArea.bottom
        );

        gradient.addColorStop(0, alpha(color, from));
        gradient.addColorStop(1, alpha(color, to));

        return gradient;
    };

    let applied = [];

    const applyChartTheme = () => {
        const text = cssVar('--bs-secondary-color', '#6c757d');
        const body = cssVar('--bs-body-color', '#212529');
        const surface = cssVar('--bs-body-bg', '#fff');
        const grid = `rgba(${cssVar('--bs-emphasis-color-rgb', '0, 0, 0')}, 0.08)`;
        const rtl = root.getAttribute('dir') === 'rtl';
        const defaults = Chart.defaults;

        applied = [text, grid];

        defaults.font.family = cssVar(
            '--bs-body-font-family',
            defaults.font.family
        );

        defaults.font.size = 12;
        defaults.color = text;
        defaults.borderColor = grid;
        defaults.maintainAspectRatio = false;

        defaults.scale.grid.color = grid;
        defaults.scale.border.color = grid;
        defaults.scale.ticks.padding = 8;

        defaults.set('scales.linear', {
            border: { display: false }
        });

        defaults.set('scales.category', {
            grid: { display: false }
        });

        defaults.elements.line.borderWidth = 2;
        defaults.elements.point.radius = 0;
        defaults.elements.point.hoverRadius = 5;
        defaults.elements.point.hitRadius = 8;
        defaults.elements.point.hoverBorderWidth = 2;
        defaults.elements.point.borderColor = surface;
        defaults.elements.bar.borderRadius = 4;
        defaults.elements.arc.borderColor = surface;
        defaults.elements.arc.borderWidth = 2;

        const { legend, tooltip } = defaults.plugins;

        legend.position = 'bottom';
        legend.rtl = rtl;

        Object.assign(legend.labels, {
            color: body,
            usePointStyle: true,
            pointStyle: 'circle',
            boxWidth: 8,
            boxHeight: 8,
            padding: 16
        });

        Object.assign(tooltip, {
            rtl,
            backgroundColor: surface,
            borderColor: cssVar('--bs-border-color', '#dee2e6'),
            borderWidth: 1,
            titleColor: cssVar('--bs-emphasis-color', '#000'),
            bodyColor: body,
            titleFont: { weight: '600' },
            padding: 10,
            cornerRadius: 6,
            caretSize: 5,
            boxPadding: 4,
            usePointStyle: true
        });

        ['line', 'bar'].forEach(type => {
            Chart.overrides[type].interaction = {
                mode: 'index',
                intersect: false
            };
        });
    };

    const forgetThemeColors = (options, stale) => {
        for (const [key, value] of Object.entries(options)) {
            if (value && typeof value === 'object' && !Array.isArray(value)) {
                forgetThemeColors(value, stale);
            } else if (/color$/i.test(key) && stale.includes(value)) {
                delete options[key];
            }
        }
    };

    const refreshCharts = () => {
        const stale = applied;

        applyChartTheme();

        Object.values(Chart.instances).forEach(chart => {
            forgetThemeColors(chart.config.options.scales || {}, stale);
            chart.update('none');
        });
    };

    applyChartTheme();

    new MutationObserver(refreshCharts).observe(root, {
        attributes: true,
        attributeFilter: ['data-bs-theme', 'dir']
    });

    if (document.fonts) {
        document.fonts.addEventListener('loadingdone', refreshCharts);
    }

    window.lteChartTheme = { cssVar, alpha, areaFill, refresh: refreshCharts };

    // ==========================================
    // Monthly Sales Chart
    // ==========================================

    const salesCanvas = document.querySelector('#sales-chart');

    if (salesCanvas) {
        new Chart(salesCanvas, {
            type: 'line',

            data: {
                labels: [
                    "Jan '23", "Feb '23", "Mar '23",
                    "Apr '23", "May '23", "Jun '23", "Jul '23"
                ],

                datasets: [
                    {
                        label: 'Digital Goods',
                        data: [28, 48, 40, 19, 86, 27, 90],
                        borderColor: '#0d6efd',
                        backgroundColor: areaFill('#0d6efd'),
                        pointBackgroundColor: '#0d6efd',
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Electronics',
                        data: [65, 59, 80, 81, 56, 55, 40],
                        borderColor: '#20c997',
                        backgroundColor: areaFill('#20c997'),
                        pointBackgroundColor: '#20c997',
                        fill: true,
                        tension: 0.4
                    }
                ]
            },

            options: {
                plugins: {
                    legend: {
                        display: false
                    },

                    tooltip: {
                        callbacks: {
                            title: ([item]) =>
                                `${new Date(2023, item.dataIndex).toLocaleString(
                                    'en',
                                    { month: 'long' }
                                )} 2023`
                        }
                    }
                },

                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // ==========================================
    // Table Sparkline Charts
    // ==========================================

    function createSparklineChart(selector, data) {
        const canvas = document.querySelector(selector);

        if (!canvas) {
            return;
        }

        const primary = cssVar('--bs-primary', '#0d6efd');

        new Chart(canvas, {
            type: 'line',

            data: {
                labels: data.map((value, index) => index + 1),

                datasets: [{
                    data,
                    borderColor: primary,
                    backgroundColor: primary,
                    pointHoverRadius: 3
                }]
            },

            options: {
                layout: {
                    padding: 3
                },

                plugins: {
                    legend: {
                        display: false
                    },

                    tooltip: {
                        displayColors: false,
                        padding: 6,

                        callbacks: {
                            title: () => ''
                        }
                    }
                },

                scales: {
                    x: { display: false },
                    y: { display: false }
                }
            }
        });
    }

    const sparklines = {
        '#table-sparkline-1': [25, 66, 41, 89, 63, 25, 44, 12, 36, 9, 54],
        '#table-sparkline-2': [12, 56, 21, 39, 73, 45, 64, 52, 36, 59, 44],
        '#table-sparkline-3': [15, 46, 21, 59, 33, 15, 34, 42, 56, 19, 64],
        '#table-sparkline-4': [30, 56, 31, 69, 43, 35, 24, 32, 46, 29, 64],
        '#table-sparkline-5': [20, 76, 51, 79, 53, 35, 54, 22, 36, 49, 64],
        '#table-sparkline-6': [5, 36, 11, 69, 23, 15, 14, 42, 26, 19, 44],
        '#table-sparkline-7': [12, 56, 21, 39, 73, 45, 64, 52, 36, 59, 74]
    };

    Object.entries(sparklines).forEach(([selector, data]) => {
        createSparklineChart(selector, data);
    });

    // ==========================================
    // Browser Usage Doughnut Chart
    // ==========================================

    const pieCanvas = document.querySelector('#pie-chart');

    if (pieCanvas) {
        new Chart(pieCanvas, {
            type: 'doughnut',

            data: {
                labels: [
                    'Chrome', 'Edge', 'FireFox',
                    'Safari', 'Opera', 'IE'
                ],

                datasets: [{
                    label: 'Users',
                    data: [700, 500, 400, 600, 300, 100],

                    backgroundColor: [
                        '#0d6efd',
                        '#20c997',
                        '#ffc107',
                        '#d63384',
                        '#6f42c1',
                        '#adb5bd'
                    ]
                }]
            },

            options: {
                cutout: '65%',

                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });
    }
});
