<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-lg font-semibold">Dashboard</h1>

        <div class="flex items-center gap-2">
            <select wire:model="days" class="rounded-md border px-3 py-2 text-sm">
                <option value="7">Last 7 days</option>
                <option value="30">Last 30 days</option>
                <option value="90">Last 90 days</option>
            </select>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <flux:card>
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-muted-foreground">Emails added</div>
                    <div class="text-lg font-semibold">Last {{ $days }} days</div>
                </div>

                <button class="rounded-md border px-3 py-2 text-sm"
                        type="button"
                        onclick="downloadSvgFromChart('chartAdded', 'emails-added.svg')">
                    Download SVG
                </button>
            </div>

            <div class="mt-4">
                <canvas id="chartAdded" height="120"></canvas>
            </div>
        </flux:card>

        <flux:card>
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-muted-foreground">Top domains</div>
                    <div class="text-lg font-semibold">Top 10</div>
                </div>

                <button class="rounded-md border px-3 py-2 text-sm"
                        type="button"
                        onclick="downloadSvgFromChart('chartDomains', 'top-domains.svg')">
                    Download SVG
                </button>
            </div>

            <div class="mt-4">
                <canvas id="chartDomains" height="120"></canvas>
            </div>
        </flux:card>
    </div>

    <flux:card>
        <div class="flex items-center justify-between">
            <div>
                <div class="text-sm text-muted-foreground">Import outcomes</div>
                <div class="text-lg font-semibold">Last {{ $days }} days</div>
            </div>

            <button class="rounded-md border px-3 py-2 text-sm"
                    type="button"
                    onclick="downloadSvgFromChart('chartImports', 'import-outcomes.svg')">
                Download SVG
            </button>
        </div>

        <div class="mt-4">
            <canvas id="chartImports" height="110"></canvas>
        </div>
    </flux:card>

    <script>
        // Chart.js + SVG download helper
        let chartAddedInstance = null;
        let chartDomainsInstance = null;
        let chartImportsInstance = null;

        function renderCharts(chartData) {
            const { labels, addedCounts, topDomains, importSummary } = chartData;

            const ctx1 = document.getElementById('chartAdded');
            const ctx2 = document.getElementById('chartDomains');
            const ctx3 = document.getElementById('chartImports');

            if (chartAddedInstance) chartAddedInstance.destroy();
            if (chartDomainsInstance) chartDomainsInstance.destroy();
            if (chartImportsInstance) chartImportsInstance.destroy();

            chartAddedInstance = new Chart(ctx1, {
                type: 'line',
                data: {
                    labels,
                    datasets: [{
                        label: 'Emails added',
                        data: addedCounts,
                        tension: 0.25,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: true } },
                    scales: { x: { display: false } }
                }
            });

            chartDomainsInstance = new Chart(ctx2, {
                type: 'bar',
                data: {
                    labels: topDomains.labels,
                    datasets: [{
                        label: 'Count',
                        data: topDomains.counts,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } }
                }
            });

            chartImportsInstance = new Chart(ctx3, {
                type: 'bar',
                data: {
                    labels: ['Inserted', 'Invalid', 'Duplicate', 'Suppressed'],
                    datasets: [{
                        label: 'Rows',
                        data: [
                            importSummary.inserted,
                            importSummary.invalid,
                            importSummary.duplicate,
                            importSummary.suppressed
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } }
                }
            });
        }

        // Download SVG from Canvas by converting to SVG wrapper with embedded PNG.
        // This is accepted as SVG file by most tools. (True vector requires different lib.)
        function downloadSvgFromChart(canvasId, filename) {
            const canvas = document.getElementById(canvasId);
            const pngDataUrl = canvas.toDataURL('image/png');

            const svg = `
<svg xmlns="http://www.w3.org/2000/svg" width="${canvas.width}" height="${canvas.height}">
  <image href="${pngDataUrl}" width="100%" height="100%"/>
</svg>`.trim();

            const blob = new Blob([svg], { type: 'image/svg+xml;charset=utf-8' });
            const url = URL.createObjectURL(blob);

            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            a.remove();

            URL.revokeObjectURL(url);
        }

        // Livewire hook to re-render charts after DOM updates
        document.addEventListener('livewire:navigated', () => {
            renderCharts(@json($chart));
        });

        // initial render
        document.addEventListener('DOMContentLoaded', () => {
            renderCharts(@json($chart));
        });
    </script>
</div>