<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Earning extends Model
{
    protected $fillable = [
        'client_id',
        'earning_period_id',
        'date',
        'btc_earned',
        'btc_price',
        'revenue',
        'additional_notes',
    ];

    protected $casts = [
        'date' => 'date',
        'btc_earned' => 'decimal:8',
        'btc_price' => 'decimal:2',
        'revenue' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (Earning $earning) {
            // Auto-calculate revenue
            $earning->revenue = $earning->btc_earned * $earning->btc_price;
        });

        static::saved(function (Earning $earning) {
            $earning->earningPeriod->recalculateTotals();
        });

        static::deleted(function (Earning $earning) {
            $earning->earningPeriod->recalculateTotals();
        });
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function earningPeriod(): BelongsTo
    {
        return $this->belongsTo(EarningPeriod::class);
    }
}
