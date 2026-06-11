<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TradingEarning extends Model
{
    protected $fillable = [
        'client_id',
        'trading_contract_id',
        'trading_period_id',
        'date',
        'amount',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (TradingEarning $earning) {
            if (! $earning->trading_contract_id || ! $earning->date) {
                return;
            }

            $contract = $earning->tradingContract ?? TradingContract::find($earning->trading_contract_id);

            if (! $contract) {
                return;
            }

            $period = app(\App\Services\TradingPeriodService::class)
                ->getOrCreateMonthlyPeriod($contract, $earning->date);

            $earning->trading_period_id = $period->id;
        });

        $afterChange = function (TradingEarning $earning): void {
            $earning->tradingPeriod?->recalculateTotals();
            $earning->tradingPeriod?->maybeMarkCompleted();
            $earning->tradingContract?->recalculateEarning();
        };

        static::saved($afterChange);
        static::deleted($afterChange);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function tradingContract(): BelongsTo
    {
        return $this->belongsTo(TradingContract::class);
    }

    public function tradingPeriod(): BelongsTo
    {
        return $this->belongsTo(TradingPeriod::class);
    }
}
