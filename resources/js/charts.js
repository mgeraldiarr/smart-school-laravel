import {
    ArcElement,
    BarController,
    BarElement,
    CategoryScale,
    Chart,
    DoughnutController,
    Legend,
    LinearScale,
    Tooltip,
} from 'chart.js';

Chart.register(ArcElement, BarController, BarElement, CategoryScale, DoughnutController, Legend, LinearScale, Tooltip);

const css = getComputedStyle(document.documentElement);
const token = (name) => css.getPropertyValue(`--color-${name}`).trim();

Chart.defaults.font.family = "'Plus Jakarta Sans', system-ui, sans-serif";
Chart.defaults.font.size = 12;
Chart.defaults.color = token('redup');
Chart.defaults.borderColor = token('garis');
if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    Chart.defaults.animation = false;
} else {
    Chart.defaults.animation.duration = 400;
}
Chart.defaults.plugins.tooltip.backgroundColor = token('teks');
Chart.defaults.plugins.tooltip.padding = 10;
Chart.defaults.plugins.tooltip.cornerRadius = 6;
Chart.defaults.plugins.tooltip.boxPadding = 4;
Chart.defaults.plugins.legend.labels.boxWidth = 10;
Chart.defaults.plugins.legend.labels.boxHeight = 10;
Chart.defaults.plugins.legend.labels.useBorderRadius = true;
Chart.defaults.plugins.legend.labels.borderRadius = 2;

const number = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 1 });

/**
 * Spesifikasi grafik ditulis server di <script type="application/json">:
 * { kind: 'bar'|'doughnut', labels, datasets: [{ label, data, color }], stacked?, max?, suffix? }
 */
function build(canvas, spec) {
    const datasets = spec.datasets.map((set) => ({
        label: set.label,
        data: set.data,
        backgroundColor: token(set.color) || set.color,
        borderWidth: 0,
        borderRadius: spec.kind === 'bar' ? 3 : 0,
        maxBarThickness: spec.stacked ? 22 : 14,
    }));

    if (spec.kind === 'doughnut') {
        datasets[0].backgroundColor = spec.datasets[0].colors.map(token);
        datasets[0].borderColor = '#fff';
        datasets[0].borderWidth = 2;
    }

    const suffix = spec.suffix ?? '';
    const tooltipLabel = (ctx) => `${ctx.dataset.label ?? ctx.label}: ${ctx.raw === null ? '-' : number.format(ctx.raw) + suffix}`;

    // Untuk grafik horizontal, sumbu kategori ada di y dan sumbu nilai di x.
    const categoryAxis = spec.horizontal ? 'y' : 'x';
    const valueAxis = spec.horizontal ? 'x' : 'y';

    return new Chart(canvas, {
        type: spec.kind,
        data: { labels: spec.labels, datasets },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: spec.horizontal ? 'y' : 'x',
            cutout: spec.kind === 'doughnut' ? '72%' : undefined,
            interaction: { mode: spec.kind === 'bar' ? 'index' : 'nearest', intersect: false },
            plugins: {
                legend: { display: spec.legend ?? spec.kind === 'bar', position: 'bottom', align: 'start' },
                tooltip: {
                    callbacks: {
                        label: spec.kind === 'doughnut' ? (ctx) => `${ctx.label}: ${number.format(ctx.raw)} siswa` : tooltipLabel,
                    },
                },
            },
            scales: spec.kind !== 'bar' ? {} : {
                [categoryAxis]: {
                    stacked: !!spec.stacked,
                    grid: { display: false },
                    ticks: spec.horizontal ? { autoSkip: false } : { maxRotation: 0, autoSkipPadding: 12 },
                },
                [valueAxis]: {
                    stacked: !!spec.stacked,
                    beginAtZero: true,
                    max: spec.max,
                    border: { display: false },
                    ticks: { precision: 0, callback: (v) => number.format(v) + suffix },
                },
            },
        },
    });
}

export function mountCharts(root = document) {
    root.querySelectorAll('canvas[data-chart]').forEach((canvas) => {
        const source = document.getElementById(canvas.dataset.chart);
        if (source) build(canvas, JSON.parse(source.textContent));
    });
}
