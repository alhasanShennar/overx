<?php

namespace App\Livewire;

use App\Models\PlatformHolding;
use Livewire\Component;

class PlatformHoldings extends Component
{
    public bool $isEditing = false;

    public string $btcUnit      = '0';
    public string $btcValue     = '0';
    public string $ethUnit      = '0';
    public string $ethValue     = '0';
    public string $lastUpdated  = 'Never';

    public function mount(): void
    {
        $record = PlatformHolding::instance();
        $this->fillFromRecord($record);
    }

    public function edit(): void
    {
        $this->isEditing = true;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'btcUnit'  => 'required|numeric|min:0',
            'btcValue' => 'required|numeric|min:0',
            'ethUnit'  => 'required|numeric|min:0',
            'ethValue' => 'required|numeric|min:0',
        ]);

        $record = PlatformHolding::instance();
        $record->update([
            'btc_unit'  => $validated['btcUnit'],
            'btc_value' => $validated['btcValue'],
            'eth_unit'  => $validated['ethUnit'],
            'eth_value' => $validated['ethValue'],
        ]);

        $this->fillFromRecord($record->fresh());
        $this->isEditing = false;

        $this->dispatch('holdings-updated', [
            'btcUnit'  => (float) $this->btcUnit,
            'btcValue' => (float) $this->btcValue,
            'ethUnit'  => (float) $this->ethUnit,
            'ethValue' => (float) $this->ethValue,
        ]);
    }

    public function cancel(): void
    {
        $record = PlatformHolding::instance();
        $this->fillFromRecord($record);
        $this->isEditing = false;
    }

    private function fillFromRecord(PlatformHolding $record): void
    {
        $this->btcUnit     = (string) $record->btc_unit;
        $this->btcValue    = (string) $record->btc_value;
        $this->ethUnit     = (string) $record->eth_unit;
        $this->ethValue    = (string) $record->eth_value;
        $this->lastUpdated = $record->updated_at
            ? $record->updated_at->diffForHumans()
            : 'Never';
    }

    public function render()
    {
        return view('livewire.platform-holdings');
    }
}
