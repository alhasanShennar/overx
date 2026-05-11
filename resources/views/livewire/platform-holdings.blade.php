@php
    $btcU   = (float) $btcUnit;
    $btcV   = (float) $btcValue;
    $ethU   = (float) $ethUnit;
    $ethV   = (float) $ethValue;

    $btcPrice   = $btcU > 0 ? $btcV / $btcU : 0;
    $ethPrice   = $ethU > 0 ? $ethV / $ethU : 0;
    $totalUnits = $btcU + $ethU;
    $totalValue = $btcV + $ethV;

    function fmtCompact($n): string {
        if ($n >= 1_000_000) return '$' . number_format($n / 1_000_000, 1) . 'M';
        if ($n >= 1_000)     return '$' . number_format($n / 1_000, 1)     . 'K';
        return '$' . number_format($n, 2);
    }
@endphp

<div class="p-4 md:p-6 space-y-6">

    {{-- ── HEADER ──────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Platform Holdings Overview</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Real-time consolidated asset performance and allocation.
            </p>
        </div>

        @if(! $isEditing)
        <button wire:click="edit"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold
                   bg-primary-600 hover:bg-primary-500 text-white shadow-sm transition-all active:scale-95">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                 viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M16.862 3.487a2.25 2.25 0 1 1 3.182 3.182L7.5 19.213l-4.5 1.317 1.317-4.5L16.862 3.487z"/>
            </svg>
            Edit Holdings
        </button>
        @endif
    </div>

    {{-- ── TABLE (view mode) ───────────────────────────────────────── --}}
    @if(! $isEditing)
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">

        {{-- card header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
            <span class="font-semibold text-gray-800 dark:text-gray-200">Core Asset Allocation</span>
            <span class="text-xs text-gray-400 uppercase tracking-widest">
                Last Updated: {{ $lastUpdated }}
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs text-gray-400 uppercase tracking-wider border-b border-gray-100 dark:border-gray-700">
                        <th class="px-6 py-3 text-left font-semibold">Asset</th>
                        <th class="px-6 py-3 text-right font-semibold">Units</th>
                        <th class="px-6 py-3 text-right font-semibold">Market Price</th>
                        <th class="px-6 py-3 text-right font-semibold">Total Value (USD)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">

                    {{-- BTC --}}
                    <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-700/30 transition">
                        <td class="px-6 py-5">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-amber-100 dark:bg-amber-900/40
                                            flex items-center justify-center shrink-0">
                                    <span class="text-amber-600 dark:text-amber-400 font-bold">₿</span>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900 dark:text-white">Bitcoin</p>
                                    <p class="text-xs text-gray-400">BTC</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-5 text-right font-mono text-gray-700 dark:text-gray-300">
                            {{ number_format($btcU, 8) }}
                        </td>
                        <td class="px-6 py-5 text-right text-amber-500 font-medium">
                            ${{ number_format($btcPrice, 2) }}
                        </td>
                        <td class="px-6 py-5 text-right">
                            <span class="text-lg font-bold text-gray-900 dark:text-white">
                                ${{ number_format($btcV, 2) }}
                            </span>
                        </td>
                    </tr>

                    {{-- ETH --}}
                    <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-700/30 transition">
                        <td class="px-6 py-5">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-violet-100 dark:bg-violet-900/40
                                            flex items-center justify-center shrink-0">
                                    <span class="text-violet-600 dark:text-violet-400 font-bold">Ξ</span>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900 dark:text-white">Ethereum</p>
                                    <p class="text-xs text-gray-400">ETH</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-5 text-right font-mono text-gray-700 dark:text-gray-300">
                            {{ number_format($ethU, 8) }}
                        </td>
                        <td class="px-6 py-5 text-right text-violet-500 font-medium">
                            ${{ number_format($ethPrice, 2) }}
                        </td>
                        <td class="px-6 py-5 text-right">
                            <span class="text-lg font-bold text-gray-900 dark:text-white">
                                ${{ number_format($ethV, 2) }}
                            </span>
                        </td>
                    </tr>

                </tbody>
                <tfoot>
                    <tr class="bg-gray-50 dark:bg-gray-700/40 border-t border-gray-200 dark:border-gray-600">
                        <td class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Total</td>
                        <td class="px-6 py-4 text-right font-mono font-semibold text-gray-700 dark:text-gray-300">
                            {{ number_format($totalUnits, 4) }}
                        </td>
                        <td></td>
                        <td class="px-6 py-4 text-right text-blue-600 dark:text-blue-400 font-bold text-lg">
                            ${{ number_format($totalValue, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @endif

    {{-- ── EDIT FORM ─────────────────────────────────────────────────── --}}
    @if($isEditing)
    <form wire:submit.prevent="save"
          class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">

        <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100 dark:border-gray-700">
            <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-blue-600 dark:text-blue-400"
                     fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M16.862 3.487a2.25 2.25 0 1 1 3.182 3.182L7.5 19.213l-4.5 1.317 1.317-4.5L16.862 3.487z"/>
                </svg>
            </div>
            <div>
                <p class="font-semibold text-gray-800 dark:text-gray-200">Edit Platform Holdings</p>
                <p class="text-xs text-gray-400 mt-0.5">Update the current crypto holdings for the platform</p>
            </div>
        </div>

        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">

            <div class="space-y-2">
                <label class="block text-xs font-semibold text-amber-600 uppercase tracking-wider">₿ Bitcoin Unit</label>
                <input type="number" step="any" min="0" wire:model.defer="btcUnit" placeholder="0.00000000"
                    class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700
                           text-gray-900 dark:text-gray-100 text-sm px-4 py-3 font-mono
                           focus:outline-none focus:ring-2 focus:ring-amber-400 transition" />
                @error('btcUnit')<span class="text-xs text-red-500">{{ $message }}</span>@enderror
            </div>

            <div class="space-y-2">
                <label class="block text-xs font-semibold text-amber-600 uppercase tracking-wider">$ Bitcoin Value (USD)</label>
                <input type="number" step="any" min="0" wire:model.defer="btcValue" placeholder="0.00"
                    class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700
                           text-gray-900 dark:text-gray-100 text-sm px-4 py-3 font-mono
                           focus:outline-none focus:ring-2 focus:ring-amber-400 transition" />
                @error('btcValue')<span class="text-xs text-red-500">{{ $message }}</span>@enderror
            </div>

            <div class="space-y-2">
                <label class="block text-xs font-semibold text-violet-600 uppercase tracking-wider">Ξ Ethereum Unit</label>
                <input type="number" step="any" min="0" wire:model.defer="ethUnit" placeholder="0.00000000"
                    class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700
                           text-gray-900 dark:text-gray-100 text-sm px-4 py-3 font-mono
                           focus:outline-none focus:ring-2 focus:ring-violet-400 transition" />
                @error('ethUnit')<span class="text-xs text-red-500">{{ $message }}</span>@enderror
            </div>

            <div class="space-y-2">
                <label class="block text-xs font-semibold text-violet-600 uppercase tracking-wider">$ Ethereum Value (USD)</label>
                <input type="number" step="any" min="0" wire:model.defer="ethValue" placeholder="0.00"
                    class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700
                           text-gray-900 dark:text-gray-100 text-sm px-4 py-3 font-mono
                           focus:outline-none focus:ring-2 focus:ring-violet-400 transition" />
                @error('ethValue')<span class="text-xs text-red-500">{{ $message }}</span>@enderror
            </div>

        </div>

        <div class="flex gap-3 px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/40">
            <button type="submit"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium
                       bg-primary-600 hover:bg-primary-500 text-white transition active:scale-95">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                     viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                Save Changes
            </button>
            <button type="button" wire:click="cancel"
                class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium
                       border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700
                       hover:bg-gray-50 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 transition active:scale-95">
                Cancel
            </button>
        </div>

    </form>
    @endif

    {{-- ── CHARTS ──────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6" wire:ignore>

        {{-- Units --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <h2 class="font-semibold text-gray-900 dark:text-white">Bitcoin vs Ethereum Unit</h2>
            <p class="text-xs text-gray-400 mt-0.5 mb-4">Distribution of raw tokens held</p>

            <div class="relative mx-auto" style="width:220px;height:220px;">
                <canvas id="chartHoldingUnits"
                    data-btc="{{ $btcU }}" data-eth="{{ $ethU }}"></canvas>
                <div class="absolute inset-0 flex flex-col items-center justify-center text-center pointer-events-none">
                    <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest leading-none">Total Assets</span>
                    <span class="text-xl font-bold text-gray-900 dark:text-white mt-1 leading-none">
                        {{ number_format($totalUnits, 2) }}
                    </span>
                </div>
            </div>

            <div class="flex items-center justify-center gap-5 mt-5 text-xs text-gray-500">
                <span class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-400 inline-block"></span>
                    BTC ({{ $totalUnits > 0 ? number_format($btcU/$totalUnits*100,1) : 0 }}%)
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-violet-500 inline-block"></span>
                    ETH ({{ $totalUnits > 0 ? number_format($ethU/$totalUnits*100,1) : 0 }}%)
                </span>
            </div>
        </div>

        {{-- Value --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <h2 class="font-semibold text-gray-900 dark:text-white">Bitcoin vs Ethereum Value</h2>
            <p class="text-xs text-gray-400 mt-0.5 mb-4">Proportional USD valuation</p>

            <div class="relative mx-auto" style="width:220px;height:220px;">
                <canvas id="chartHoldingValue"
                    data-btc="{{ $btcV }}" data-eth="{{ $ethV }}"></canvas>
                <div class="absolute inset-0 flex flex-col items-center justify-center text-center pointer-events-none">
                    <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest leading-none">Total USD</span>
                    <span class="text-xl font-bold text-gray-900 dark:text-white mt-1 leading-none">
                        {{ fmtCompact($totalValue) }}
                    </span>
                </div>
            </div>

            <div class="flex items-center justify-center gap-5 mt-5 text-xs text-gray-500">
                <span class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-500 inline-block"></span>
                    BTC ({{ $totalValue > 0 ? number_format($btcV/$totalValue*100,1) : 0 }}%)
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-violet-400 inline-block"></span>
                    ETH ({{ $totalValue > 0 ? number_format($ethV/$totalValue*100,1) : 0 }}%)
                </span>
            </div>
        </div>

    </div>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
(function () {
    var chartInstances = {};

    function makeDoughnut(id, val1, val2, color1, color2) {
        // Destroy existing instance if any
        if (chartInstances[id]) {
            chartInstances[id].destroy();
            delete chartInstances[id];
        }
        var el = document.getElementById(id);
        if (!el) return;
        el.width  = el.parentElement.offsetWidth;
        el.height = el.parentElement.offsetHeight;
        chartInstances[id] = new Chart(el, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [val1 || 0.0001, val2 || 0.0001],
                    backgroundColor: [color1, color2],
                    borderWidth: 4,
                    borderColor: '#fff',
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: false,
                cutout: '70%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                var t = ctx.dataset.data.reduce(function(a,b){return a+b;},0);
                                var p = t > 0 ? ((ctx.parsed / t) * 100).toFixed(1) : '0';
                                return ' ' + (ctx.dataIndex === 0 ? 'BTC' : 'ETH') + ': ' + p + '%';
                            }
                        }
                    }
                },
                animation: { animateRotate: true, duration: 900 }
            }
        });
    }

    function drawCharts(btcU, ethU, btcV, ethV) {
        makeDoughnut('chartHoldingUnits', btcU, ethU, '#f59e0b', '#8b5cf6');
        makeDoughnut('chartHoldingValue', btcV, ethV, '#3b82f6', '#a78bfa');
    }

    function init() {
        var u = document.getElementById('chartHoldingUnits');
        var v = document.getElementById('chartHoldingValue');
        if (u && v) {
            drawCharts(
                parseFloat(u.dataset.btc), parseFloat(u.dataset.eth),
                parseFloat(v.dataset.btc), parseFloat(v.dataset.eth)
            );
        }
    }

    document.readyState === 'loading'
        ? document.addEventListener('DOMContentLoaded', init)
        : init();

    // Listen for Livewire event after save
    document.addEventListener('holdings-updated', function (e) {
        var d = e.detail[0] || e.detail;
        drawCharts(
            parseFloat(d.btcUnit),  parseFloat(d.ethUnit),
            parseFloat(d.btcValue), parseFloat(d.ethValue)
        );

        // Update center labels
        var totalUnits = parseFloat(d.btcUnit) + parseFloat(d.ethUnit);
        var totalValue = parseFloat(d.btcValue) + parseFloat(d.ethValue);

        var centerU = document.querySelector('#chartHoldingUnits')
            ?.closest('.relative')?.querySelector('span:last-child');
        var centerV = document.querySelector('#chartHoldingValue')
            ?.closest('.relative')?.querySelector('span:last-child');

        if (centerU) centerU.textContent = totalUnits.toLocaleString(undefined, {maximumFractionDigits:2});
        if (centerV) {
            var fmt = totalValue >= 1000000
                ? '$' + (totalValue/1000000).toFixed(1) + 'M'
                : totalValue >= 1000
                    ? '$' + (totalValue/1000).toFixed(1) + 'K'
                    : '$' + totalValue.toFixed(2);
            centerV.textContent = fmt;
        }
    });
})();
</script>
@endpush
