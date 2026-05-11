<div class="p-4 md:p-6 space-y-6">

    {{-- ── HEADER ─────────────────────────────────────────────────── --}}
    <div>
        <h1 class="text-xl font-bold text-gray-900 dark:text-white">₿ BTC Pool</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Your stored BTC compared to the total pool</p>
    </div>

    {{-- ── FILTERS ──────────────────────────────────────────────────── --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
        <div class="flex flex-wrap items-end gap-3">

            <div class="flex-1 min-w-[140px] space-y-1">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Date From</label>
                <input type="date" wire:model.live="dateFrom"
                    class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700
                           text-gray-900 dark:text-gray-100 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-400" />
            </div>

            <div class="flex-1 min-w-[140px] space-y-1">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Date To</label>
                <input type="date" wire:model.live="dateTo"
                    class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700
                           text-gray-900 dark:text-gray-100 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-400" />
            </div>

            <div class="flex-1 min-w-[180px] space-y-1">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Earning Period</label>
                <select wire:model.live="periodId"
                    class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700
                           text-gray-900 dark:text-gray-100 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-400">
                    <option value="">All Periods</option>
                    @foreach ($periods as $period)
                        <option value="{{ $period->id }}">
                            {{ $period->start_date->format('M d, Y') }}
                            @if ($period->end_date) – {{ $period->end_date->format('M d, Y') }} @endif
                        </option>
                    @endforeach
                </select>
            </div>

            <button wire:click="resetFilters"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
                Reset
            </button>

        </div>
    </div>

    {{-- ── SUMMARY TABLE ────────────────────────────────────────────── --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-700/60 border-b border-gray-200 dark:border-gray-600 text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    <th class="px-5 py-3 text-left font-semibold">Bitcoin Unit (Mine)</th>
                    <th class="px-5 py-3 text-left font-semibold">Bitcoin Value (Mine)</th>
                    <th class="px-5 py-3 text-left font-semibold border-l border-gray-200 dark:border-gray-600">Bitcoin Unit (Total Pool)</th>
                    <th class="px-5 py-3 text-left font-semibold">Bitcoin Value (Total Pool)</th>
                </tr>
            </thead>
            <tbody>
                <tr class="border-t border-gray-100 dark:border-gray-700">
                    <td class="px-5 py-4">
                        <span class="text-xl font-bold text-amber-600 dark:text-amber-400">{{ number_format($myBtc, 2) }}</span>
                    </td>
                    <td class="px-5 py-4">
                        <span class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{ number_format($myValue, 2) }}</span>
                    </td>
                    <td class="px-5 py-4 border-l border-gray-100 dark:border-gray-700">
                        <span class="text-lg font-semibold text-gray-700 dark:text-gray-300">{{ number_format($poolBtc, 2) }}</span>
                    </td>
                    <td class="px-5 py-4">
                        <span class="text-lg font-semibold text-gray-700 dark:text-gray-300">{{ number_format($poolValue, 2) }}</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- ── CHARTS ──────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6" wire:ignore>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <h2 class="font-semibold text-gray-900 dark:text-white">Bitcoin Unit</h2>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5 mb-4">Mine vs Total Pool</p>
            <div class="relative mx-auto" style="width:220px;height:220px;">
                <canvas id="chartUnits" data-my="{{ $myBtc }}" data-pool="{{ $poolBtc }}"></canvas>
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                    <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">BTC Units</span>
                    <span class="text-lg font-bold text-gray-900 dark:text-white mt-0.5">{{ number_format($poolBtc, 2) }}</span>
                </div>
            </div>
            <div class="flex items-center justify-center gap-5 mt-4 text-xs text-gray-500">
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-amber-400 inline-block"></span> Mine</span>
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-violet-500 inline-block"></span> Others</span>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <h2 class="font-semibold text-gray-900 dark:text-white">Bitcoin Value</h2>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5 mb-4">Mine vs Total Pool</p>
            <div class="relative mx-auto" style="width:220px;height:220px;">
                <canvas id="chartValue" data-my="{{ $myValue }}" data-pool="{{ $poolValue }}"></canvas>
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                    <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Total Value</span>
                    <span class="text-lg font-bold text-gray-900 dark:text-white mt-0.5">${{ number_format($poolValue, 0) }}</span>
                </div>
            </div>
            <div class="flex items-center justify-center gap-5 mt-4 text-xs text-gray-500">
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-blue-500 inline-block"></span> Mine</span>
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span> Others</span>
            </div>
        </div>

    </div>

    {{-- ── PER-CLIENT CHARTS ────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6" wire:ignore
        id="clientChartsWrapper"
        data-clients="{{ $chartPerClientJson }}">

        {{-- Bar: Total BTC per client --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <h2 class="font-semibold text-gray-800 dark:text-gray-200 mb-1">Total BTC Earned per Client</h2>
            <p class="text-xs text-gray-400 dark:text-gray-500 mb-4">Stored BTC amount by client</p>
            <div style="position:relative;height:280px;">
                <canvas id="chartClientBtc"></canvas>
            </div>
        </div>

        {{-- Bar: Total Revenue per client --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <h2 class="font-semibold text-gray-800 dark:text-gray-200 mb-1">Total Revenue per Client</h2>
            <p class="text-xs text-gray-400 dark:text-gray-500 mb-4">USD revenue by client</p>
            <div style="position:relative;height:280px;">
                <canvas id="chartClientRevenue"></canvas>
            </div>
        </div>

        {{-- Pie: Revenue Distribution --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <h2 class="font-semibold text-gray-800 dark:text-gray-200 mb-1">Revenue Distribution per Pool</h2>
            <p class="text-xs text-gray-400 dark:text-gray-500 mb-4">Each client's share of total revenue</p>
            <div style="position:relative;height:280px;">
                <canvas id="chartRevenuePie"></canvas>
            </div>
        </div>

        {{-- Line: Avg BTC Price per client --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <h2 class="font-semibold text-gray-800 dark:text-gray-200 mb-1">Average BTC Price per Client</h2>
            <p class="text-xs text-gray-400 dark:text-gray-500 mb-4">Implied avg price (Revenue ÷ BTC)</p>
            <div style="position:relative;height:280px;">
                <canvas id="chartAvgPrice"></canvas>
            </div>
        </div>

    </div>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
(function () {

    /* ── Mine vs Pool pie charts ── */
    function makePie(id, myVal, poolVal, myColor, othersColor) {
        var el = document.getElementById(id);
        if (!el) return;
        el.width  = el.parentElement.offsetWidth;
        el.height = el.parentElement.offsetHeight;
        var rest = Math.max(0, poolVal - myVal);
        new Chart(el, {
            type: 'doughnut',
            data: {
                labels: ['Mine', 'Others'],
                datasets: [{ data: [myVal || 0.0001, rest || 0.0001], backgroundColor: [myColor, othersColor], borderColor: '#fff', borderWidth: 3, hoverOffset: 6 }]
            },
            options: {
                responsive: false,
                cutout: '68%',
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: function(ctx) {
                        var t = ctx.dataset.data.reduce(function(a,b){return a+b;},0);
                        var p = t > 0 ? ((ctx.parsed/t)*100).toFixed(2) : '0';
                        return ' ' + ctx.label + ': ' + p + '%';
                    }}}
                },
                animation: { animateRotate: true, duration: 800 }
            }
        });
    }

    /* ── Palette for multi-client charts ── */
    var PALETTE = [
        '#6366f1','#f59e0b','#10b981','#3b82f6','#ec4899',
        '#14b8a6','#f97316','#8b5cf6','#84cc16','#06b6d4'
    ];

    function initClientCharts(clients) {
        var names    = clients.map(function(c){ return c.name; });
        var btcs     = clients.map(function(c){ return c.total_btc; });
        var revenues = clients.map(function(c){ return c.total_revenue; });
        var prices   = clients.map(function(c){ return c.avg_price; });
        var colors   = clients.map(function(_,i){ return PALETTE[i % PALETTE.length]; });

        var gridColor = 'rgba(156,163,175,0.15)';
        var fontColor = '#6b7280';

        var sharedScales = {
            x: { ticks: { color: fontColor, font: { size: 11 }, maxRotation: 35 }, grid: { display: false } },
            y: { ticks: { color: fontColor, font: { size: 11 } }, grid: { color: gridColor } }
        };

        /* Bar – BTC */
        new Chart(document.getElementById('chartClientBtc'), {
            type: 'bar',
            data: { labels: names, datasets: [{ label: 'BTC Stored', data: btcs, backgroundColor: colors, borderRadius: 6, borderSkipped: false }] },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: sharedScales
            }
        });

        /* Bar – Revenue */
        new Chart(document.getElementById('chartClientRevenue'), {
            type: 'bar',
            data: { labels: names, datasets: [{ label: 'Revenue ($)', data: revenues, backgroundColor: colors, borderRadius: 6, borderSkipped: false }] },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: sharedScales
            }
        });

        /* Pie – Revenue distribution */
        new Chart(document.getElementById('chartRevenuePie'), {
            type: 'pie',
            data: { labels: names, datasets: [{ data: revenues, backgroundColor: colors, borderWidth: 2, borderColor: '#fff' }] },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: true, position: 'right', labels: { font: { size: 11 }, padding: 10 } },
                    tooltip: { callbacks: { label: function(ctx) {
                        var t = ctx.dataset.data.reduce(function(a,b){return a+b;},0);
                        var p = t > 0 ? ((ctx.parsed/t)*100).toFixed(1) : '0';
                        return ' ' + ctx.label + ': ' + p + '%';
                    }}}
                }
            }
        });

        /* Line – Avg price */
        new Chart(document.getElementById('chartAvgPrice'), {
            type: 'line',
            data: {
                labels: names,
                datasets: [{
                    label: 'Avg BTC Price ($)',
                    data: prices,
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99,102,241,0.08)',
                    pointBackgroundColor: colors,
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    tension: 0.4,
                    fill: true,
                    borderWidth: 2.5
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: sharedScales
            }
        });
    }

    function init() {
        /* Mine vs Pool charts */
        var u = document.getElementById('chartUnits');
        var v = document.getElementById('chartValue');
        if (u) makePie('chartUnits',  parseFloat(u.dataset.my), parseFloat(u.dataset.pool), '#f59e0b', '#8b5cf6');
        if (v) makePie('chartValue',  parseFloat(v.dataset.my), parseFloat(v.dataset.pool), '#3b82f6', '#10b981');

        /* Per-client charts */
        var wrapper = document.getElementById('clientChartsWrapper');
        if (wrapper) {
            try {
                var clients = JSON.parse(wrapper.dataset.clients || '[]');
                if (clients.length) initClientCharts(clients);
            } catch(e) {}
        }
    }

    document.readyState === 'loading'
        ? document.addEventListener('DOMContentLoaded', init)
        : init();
})();
</script>
@endpush
