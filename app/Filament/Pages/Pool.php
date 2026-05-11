<?php

namespace App\Filament\Pages;

use App\Models\EarningPeriod;
use App\Models\PlatformHolding;
use App\Models\StoredEarning;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class Pool extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-banknotes';
    protected static string  $view            = 'livewire.pool';
    protected static ?string $navigationLabel = 'Pool';

    public float $myBtc   = 0;
    public float $myValue = 0;
    public float $poolBtc   = 0;
    public float $poolValue = 0;

    public string $dateFrom = '';
    public string $dateTo   = '';
    public string $periodId = '';

    public string $chartPerClientJson = '[]';

    public static function canAccess(): bool
    {
        return auth()->check();
    }

    public function mount(): void
    {
        $this->loadFromDb();
    }

    public function updated($property): void
    {
        if (in_array($property, ['dateFrom', 'dateTo', 'periodId'])) {
            $this->loadFromDb();
        }
    }

    public function resetFilters(): void
    {
        $this->dateFrom = '';
        $this->dateTo   = '';
        $this->periodId = '';
        $this->loadFromDb();
    }

    private function loadFromDb(): void
    {
        $holding       = PlatformHolding::instance();
        $this->myBtc   = (float) $holding->btc_unit;
        $this->myValue = (float) $holding->btc_value;

        $query = StoredEarning::query();
        if ($this->dateFrom !== '') $query->whereDate('stored_at', '>=', $this->dateFrom);
        if ($this->dateTo   !== '') $query->whereDate('stored_at', '<=', $this->dateTo);
        if ($this->periodId !== '') $query->where('earning_period_id', $this->periodId);

        $this->poolBtc   = (float) (clone $query)->sum('btc_amount');
        $this->poolValue = (float) (clone $query)->sum('revenue_amount');

        $rows = (clone $query)
            ->select(
                'client_id',
                DB::raw('SUM(btc_amount) as total_btc'),
                DB::raw('SUM(revenue_amount) as total_revenue'),
                DB::raw('COUNT(*) as entries')
            )
            ->groupBy('client_id')
            ->with('client.user')
            ->get()
            ->map(function ($row) {
                $avgPrice = $row->total_btc > 0
                    ? round((float) $row->total_revenue / (float) $row->total_btc, 2)
                    : 0;
                return [
                    'name'          => optional(optional($row->client)->user)->name ?? 'Unknown',
                    'total_btc'     => round((float) $row->total_btc, 8),
                    'total_revenue' => round((float) $row->total_revenue, 2),
                    'avg_price'     => $avgPrice,
                    'entries'       => (int) $row->entries,
                ];
            });

        $this->chartPerClientJson = $rows->toJson();
    }

    protected function getViewData(): array
    {
        return [
            'periods' => EarningPeriod::orderByDesc('start_date')->get(['id', 'start_date', 'end_date']),
        ];
    }
}

