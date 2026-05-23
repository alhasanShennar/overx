<?php

namespace App\Livewire;

use App\Models\PlatformHolding;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Component;

class PlatformHoldings extends Component
{
    public bool $isEditing = false;

    public string $btcUnit     = '0';
    public string $ethUnit     = '0';
    public string $usdtValue   = '0';
    public string $lastUpdated = 'Never';

    public float $btcMarketPrice = 0;
    public float $ethMarketPrice = 0;
    public bool  $pricesLoaded  = false;

    public function mount(): void
    {
        $record = PlatformHolding::instance();
        $this->fillFromRecord($record);
        $this->fetchMarketPrices();
    }

    public function refreshPrices(): void
    {
        Cache::forget('coingecko_prices');
        $this->fetchMarketPrices();
    }

    private function fetchMarketPrices(): void
    {
        try {
            $prices = Cache::remember('coingecko_prices', 300, function () {
                return Http::timeout(6)
                    ->get('https://api.coingecko.com/api/v3/simple/price', [
                        'ids'           => 'bitcoin,ethereum',
                        'vs_currencies' => 'usd',
                    ])->json();
            });

            $this->btcMarketPrice = (float) ($prices['bitcoin']['usd'] ?? 0);
            $this->ethMarketPrice = (float) ($prices['ethereum']['usd'] ?? 0);
            $this->pricesLoaded   = true;
        } catch (\Throwable) {
            $this->pricesLoaded = false;
        }
    }

    public function edit(): void
    {
        $this->isEditing = true;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'btcUnit'   => 'required|numeric|min:0',
            'ethUnit'   => 'required|numeric|min:0',
            'usdtValue' => 'required|numeric|min:0',
        ]);

        $record = PlatformHolding::instance();
        $record->update([
            'btc_unit'   => $validated['btcUnit'],
            'btc_value'  => (float) $validated['btcUnit'] * $this->btcMarketPrice,
            'eth_unit'   => $validated['ethUnit'],
            'eth_value'  => (float) $validated['ethUnit'] * $this->ethMarketPrice,
            'usdt_value' => $validated['usdtValue'],
        ]);

        $this->fillFromRecord($record->fresh());
        $this->isEditing = false;
    }

    public function cancel(): void
    {
        $this->fillFromRecord(PlatformHolding::instance());
        $this->isEditing = false;
    }

    private function fillFromRecord(PlatformHolding $record): void
    {
        $this->btcUnit     = (string) $record->btc_unit;
        $this->ethUnit     = (string) $record->eth_unit;
        $this->usdtValue   = (string) $record->usdt_value;
        $this->lastUpdated = $record->updated_at?->diffForHumans() ?? 'Never';
    }

    public function render()
    {
        return view('livewire.platform-holdings');
    }
}
