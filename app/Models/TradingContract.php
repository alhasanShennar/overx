<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TradingContract extends Model
{
    protected $fillable = [
        'client_id',
        'amount',
        'earning',
        'file',
        'start_date',
        'end_date',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'amount' => 'decimal:2',
        'earning' => 'decimal:2',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function tradingEarnings(): HasMany
    {
        return $this->hasMany(TradingEarning::class);
    }

    public function tradingPeriods(): HasMany
    {
        return $this->hasMany(TradingPeriod::class);
    }

    public function isActive(): bool
    {
        return $this->end_date === null || $this->end_date->greaterThanOrEqualTo(today());
    }

    public function getStatusAttribute(): string
    {
        return $this->isActive() ? 'active' : 'expired';
    }

    public function getRoiPercentAttribute(): ?float
    {
        $amount = (float) $this->amount;

        if ($amount <= 0) {
            return null;
        }

        return round(((float) $this->earning / $amount) * 100, 2);
    }

    public function getPeriodLabelAttribute(): string
    {
        $start = $this->start_date?->format('M d, Y') ?? '—';
        $end = $this->end_date?->format('M d, Y') ?? 'Open';

        return "{$start} → {$end}";
    }

    public function recalculateEarning(): void
    {
        $this->update([
            'earning' => $this->tradingEarnings()->sum('amount'),
        ]);
    }
}
