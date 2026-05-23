@php
    $btcU   = (float) $btcUnit;
    $ethU   = (float) $ethUnit;
    $usdtV  = (float) $usdtValue;

    $btcPrice = (float) $btcMarketPrice;
    $ethPrice = (float) $ethMarketPrice;

    $btcV = $btcU * $btcPrice;
    $ethV = $ethU * $ethPrice;

    $totalValue = $btcV + $ethV + $usdtV;

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

        <div class="flex flex-wrap items-center gap-3">
            {{-- Market price indicator --}}
            @if($pricesLoaded)
            <div class="flex items-center gap-3 px-3 py-1.5 rounded-lg bg-green-50 dark:bg-green-900/20
                        border border-green-200 dark:border-green-700 text-xs">
                <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                <span class="font-mono text-amber-600 dark:text-amber-400 font-semibold">₿ ${{ number_format($btcMarketPrice, 0) }}</span>
                <span class="text-gray-300 dark:text-gray-600">|</span>
                <span class="font-mono text-violet-600 dark:text-violet-400 font-semibold">Ξ ${{ number_format($ethMarketPrice, 0) }}</span>
                <button wire:click="refreshPrices" title="Refresh prices"
                        class="ml-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none"
                         viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/>
                    </svg>
                </button>
            </div>
            @else
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-yellow-50 dark:bg-yellow-900/20
                        border border-yellow-200 dark:border-yellow-700 text-xs text-yellow-700 dark:text-yellow-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none"
                     viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                </svg>
                Prices unavailable
                <button wire:click="refreshPrices" class="underline hover:no-underline">Retry</button>
            </div>
            @endif

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
                            @if($pricesLoaded) ${{ number_format($btcPrice, 0) }} @else <span class="text-gray-400">—</span> @endif
                        </td>
                        <td class="px-6 py-5 text-right">
                            <span class="text-lg font-bold text-gray-900 dark:text-white">
                                @if($pricesLoaded) ${{ number_format($btcV, 2) }} @else <span class="text-gray-400 text-sm">N/A</span> @endif
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
                            @if($pricesLoaded) ${{ number_format($ethPrice, 2) }} @else <span class="text-gray-400">—</span> @endif
                        </td>
                        <td class="px-6 py-5 text-right">
                            <span class="text-lg font-bold text-gray-900 dark:text-white">
                                @if($pricesLoaded) ${{ number_format($ethV, 2) }} @else <span class="text-gray-400 text-sm">N/A</span> @endif
                            </span>
                        </td>
                    </tr>

                    {{-- USDT --}}
                    <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-700/30 transition">
                        <td class="px-6 py-5">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-green-100 dark:bg-green-900/40
                                            flex items-center justify-center shrink-0">
                                    <span class="text-green-600 dark:text-green-400 font-bold text-sm">₮</span>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900 dark:text-white">Tether</p>
                                    <p class="text-xs text-gray-400">USDT</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-5 text-right font-mono text-gray-700 dark:text-gray-300">
                            {{ number_format($usdtV, 2) }}
                        </td>
                        <td class="px-6 py-5 text-right text-green-500 font-medium">
                            $1.00
                        </td>
                        <td class="px-6 py-5 text-right">
                            <span class="text-lg font-bold text-gray-900 dark:text-white">
                                ${{ number_format($usdtV, 2) }}
                            </span>
                        </td>
                    </tr>

                </tbody>
                <tfoot>
                    <tr class="bg-gray-50 dark:bg-gray-700/40 border-t border-gray-200 dark:border-gray-600">
                        <td class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Total</td>
                        <td class="px-6 py-4 text-right font-mono font-semibold text-gray-500 dark:text-gray-400 text-xs">
                            3 Assets
                        </td>
                        <td></td>
                        <td class="px-6 py-4 text-right text-blue-600 dark:text-blue-400 font-bold text-lg">
                            @if($pricesLoaded) ${{ number_format($totalValue, 2) }}
                            @else <span class="text-sm">${{ number_format($usdtV, 2) }} + BTC/ETH</span>
                            @endif
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

        {{-- API prices note --}}
        @if($pricesLoaded)
        <div class="px-6 pt-4 flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
            <span class="flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-green-400"></span>
                Live prices from CoinGecko:
            </span>
            <span class="font-mono text-amber-600 dark:text-amber-400">BTC = ${{ number_format($btcMarketPrice, 0) }}</span>
            <span class="font-mono text-violet-600 dark:text-violet-400">ETH = ${{ number_format($ethMarketPrice, 0) }}</span>
            <span class="text-gray-400 dark:text-gray-500 italic">(USD values auto-calculated)</span>
        </div>
        @else
        <div class="px-6 pt-4 text-xs text-yellow-600 dark:text-yellow-400">
            ⚠ Market prices unavailable — USD values will be recalculated when prices load.
        </div>
        @endif

        <div class="p-6 grid grid-cols-1 sm:grid-cols-3 gap-5">

            <div class="space-y-2">
                <label class="block text-xs font-semibold text-amber-600 uppercase tracking-wider">₿ Bitcoin Units</label>
                <input type="number" step="any" min="0" wire:model.defer="btcUnit" placeholder="0.00000000"
                    class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700
                           text-gray-900 dark:text-gray-100 text-sm px-4 py-3 font-mono
                           focus:outline-none focus:ring-2 focus:ring-amber-400 transition" />
                @error('btcUnit')<span class="text-xs text-red-500">{{ $message }}</span>@enderror
            </div>

            <div class="space-y-2">
                <label class="block text-xs font-semibold text-violet-600 uppercase tracking-wider">Ξ Ethereum Units</label>
                <input type="number" step="any" min="0" wire:model.defer="ethUnit" placeholder="0.00000000"
                    class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700
                           text-gray-900 dark:text-gray-100 text-sm px-4 py-3 font-mono
                           focus:outline-none focus:ring-2 focus:ring-violet-400 transition" />
                @error('ethUnit')<span class="text-xs text-red-500">{{ $message }}</span>@enderror
            </div>

            <div class="space-y-2">
                <label class="block text-xs font-semibold text-green-600 uppercase tracking-wider">₮ USDT Amount (USD)</label>
                <input type="number" step="any" min="0" wire:model.defer="usdtValue" placeholder="0.00"
                    class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700
                           text-gray-900 dark:text-gray-100 text-sm px-4 py-3 font-mono
                           focus:outline-none focus:ring-2 focus:ring-green-400 transition" />
                @error('usdtValue')<span class="text-xs text-red-500">{{ $message }}</span>@enderror
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

        {{-- Units (BTC vs ETH) --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <h2 class="font-semibold text-gray-900 dark:text-white">BTC vs ETH Units</h2>
            <p class="text-xs text-gray-400 mt-0.5 mb-4">Distribution of raw tokens held</p>

            @php $tokenTotal = $btcU + $ethU; @endphp
            <div class="relative mx-auto" style="width:220px;height:220px;">
                <canvas id="chartHoldingUnits"
                    data-btc="{{ $btcU }}" data-eth="{{ $ethU }}"></canvas>
                <div class="absolute inset-0 flex flex-col items-center justify-center text-center pointer-events-none">
                    <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest leading-none">BTC + ETH</span>
                    <span class="text-xl font-bold text-gray-900 dark:text-white mt-1 leading-none">
                        {{ number_format($tokenTotal, 4) }}
                    </span>
                </div>
            </div>

            <div class="flex items-center justify-center gap-5 mt-5 text-xs text-gray-500">
                <span class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-400 inline-block"></span>
                    BTC ({{ $tokenTotal > 0 ? number_format($btcU/$tokenTotal*100,1) : 0 }}%)
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-violet-500 inline-block"></span>
                    ETH ({{ $tokenTotal > 0 ? number_format($ethU/$tokenTotal*100,1) : 0 }}%)
                </span>
            </div>
        </div>

        {{-- Value (BTC + ETH + USDT) --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <h2 class="font-semibold text-gray-900 dark:text-white">Portfolio Value Split</h2>
            <p class="text-xs text-gray-400 mt-0.5 mb-4">BTC · ETH · USDT proportional valuation</p>

            <div class="relative mx-auto" style="width:220px;height:220px;">
                <canvas id="chartHoldingValue"
                    data-btc="{{ $btcV }}" data-eth="{{ $ethV }}" data-usdt="{{ $usdtV }}"></canvas>
                <div class="absolute inset-0 flex flex-col items-center justify-center text-center pointer-events-none">
                    <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest leading-none">Total USD</span>
                    <span class="text-xl font-bold text-gray-900 dark:text-white mt-1 leading-none">
                        {{ fmtCompact($totalValue) }}
                    </span>
                </div>
            </div>

            <div class="flex items-center justify-center gap-4 mt-5 text-xs text-gray-500 flex-wrap">
                <span class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-400 inline-block"></span>
                    BTC ({{ $totalValue > 0 ? number_format($btcV/$totalValue*100,1) : 0 }}%)
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-violet-400 inline-block"></span>
                    ETH ({{ $totalValue > 0 ? number_format($ethV/$totalValue*100,1) : 0 }}%)
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-green-400 inline-block"></span>
                    USDT ({{ $totalValue > 0 ? number_format($usdtV/$totalValue*100,1) : 0 }}%)
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

    function makeDoughnut(id, segments) {
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
                    data:            segments.map(function(s) { return s.value || 0.0001; }),
                    backgroundColor: segments.map(function(s) { return s.color; }),
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
                                return ' ' + segments[ctx.dataIndex].label + ': ' + p + '%';
                            }
                        }
                    }
                },
                animation: { animateRotate: true, duration: 900 }
            }
        });
    }

    function drawCharts(btcU, ethU, btcV, ethV, usdtV) {
        makeDoughnut('chartHoldingUnits', [
            { label: 'BTC', value: btcU,  color: '#f59e0b' },
            { label: 'ETH', value: ethU,  color: '#8b5cf6' }
        ]);
        makeDoughnut('chartHoldingValue', [
            { label: 'BTC',  value: btcV,  color: '#f59e0b' },
            { label: 'ETH',  value: ethV,  color: '#8b5cf6' },
            { label: 'USDT', value: usdtV, color: '#22c55e' }
        ]);
    }

    function init() {
        var u = document.getElementById('chartHoldingUnits');
        var v = document.getElementById('chartHoldingValue');
        if (u && v) {
            drawCharts(
                parseFloat(u.dataset.btc  || 0), parseFloat(u.dataset.eth || 0),
                parseFloat(v.dataset.btc  || 0), parseFloat(v.dataset.eth || 0),
                parseFloat(v.dataset.usdt || 0)
            );
        }
    }

    document.readyState === 'loading'
        ? document.addEventListener('DOMContentLoaded', init)
        : init();
})();
</script>
@endpush
