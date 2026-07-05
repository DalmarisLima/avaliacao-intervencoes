/**
 * Tema unificado para Chart.js — cores e tipografia alinhadas aos tokens.
 */
(function () {
    if (typeof Chart === 'undefined') {
        return;
    }

    const theme = {
        pre: getComputedStyle(document.documentElement).getPropertyValue('--chart-pre').trim() || '#94a3b8',
        pos: getComputedStyle(document.documentElement).getPropertyValue('--chart-pos').trim() || '#0f766e',
        grid: getComputedStyle(document.documentElement).getPropertyValue('--chart-grid').trim() || '#e2e8f0',
        text: getComputedStyle(document.documentElement).getPropertyValue('--chart-axis').trim() || '#64748b',
        font: getComputedStyle(document.documentElement).getPropertyValue('--font-sans').trim() || 'Inter, sans-serif',
    };

    Chart.defaults.font.family = theme.font;
    Chart.defaults.font.size = 12;
    Chart.defaults.color = theme.text;
    Chart.defaults.plugins.legend.labels.usePointStyle = true;
    Chart.defaults.plugins.legend.labels.boxWidth = 8;
    Chart.defaults.plugins.legend.labels.padding = 16;

    window.IntervencoesCharts = {
        colors: theme,
        datasetPre: {
            label: 'PRÉ-intervenção',
            backgroundColor: theme.pre,
            borderColor: theme.pre,
            borderWidth: 0,
            borderRadius: 6,
            borderSkipped: false,
        },
        datasetPos: {
            label: 'PÓS-intervenção',
            backgroundColor: theme.pos,
            borderColor: theme.pos,
            borderWidth: 0,
            borderRadius: 6,
            borderSkipped: false,
        },
        scaleOptions: function () {
            return {
                beginAtZero: true,
                grid: {
                    color: theme.grid,
                    drawBorder: false,
                },
                ticks: {
                    color: theme.text,
                    padding: 8,
                },
            };
        },
        baseOptions: function () {
            return {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        align: 'end',
                        labels: { color: theme.text },
                    },
                },
            };
        },
    };
})();
