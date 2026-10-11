// Tampilkan nama berkas yang dipilih pada input file kustom.
document.addEventListener('change', (event) => {
    const input = event.target.closest('[data-file-input]');
    if (!input) return;

    const label = input.closest('label')?.querySelector('[data-file-name]');
    if (label) label.textContent = input.files[0]?.name ?? label.dataset.fileName;
});

// Filter yang langsung diterapkan saat pilihannya berubah.
document.addEventListener('change', (event) => {
    if (event.target.matches('[data-auto-submit]')) event.target.form.requestSubmit();
});

// Minta konfirmasi sebelum mengirim form yang berdampak permanen.
document.addEventListener('submit', (event) => {
    const message = event.target.dataset.confirm;
    if (message && !window.confirm(message)) event.preventDefault();
});

// Buka-tutup menu samping di layar kecil.
document.addEventListener('click', (event) => {
    const toggle = event.target.closest('[data-sidebar-toggle]');
    if (!toggle) return;

    const open = document.body.toggleAttribute('data-sidebar-open');
    document.querySelectorAll('[data-sidebar-toggle]').forEach((el) => el.setAttribute('aria-expanded', String(open)));
});

// Grafik hanya dimuat di halaman yang memilikinya.
if (document.querySelector('canvas[data-chart]')) {
    import('./charts.js').then(({ mountCharts }) => mountCharts());
}
