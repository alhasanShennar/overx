<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TradingPeriod extends Model
{
    const STATUS_PENDING = 'pending';

    const STATUS_COMPLETED = 'completed';

    const STATUS_REQUEST_PENDING = 'request_pending';

    const STATUS_STORED = 'stored';

    const STATUS_CASHED_OUT = 'cashed_out';

    const STATUS_REJECTED = 'rejected';

    const DECISION_STORE = 'store';

    const DECISION_CASHOUT = 'cashout';

    protected $fillable = [
        'client_id',
        'trading_contract_id',
        'year',
        'month',
        'start_date',
        'end_date',
        'total_earning',
        'status',
        'client_decision',
        'requested_at',
        'processed_at',
        'is_locked',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_earning' => 'decimal:2',
        'requested_at' => 'datetime',
        'processed_at' => 'datetime',
        'is_locked' => 'boolean',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function tradingContract(): BelongsTo
    {
        return $this->belongsTo(TradingContract::class);
    }

    public function tradingEarnings(): HasMany
    {
        return $this->hasMany(TradingEarning::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(TradingTransaction::class);
    }

    public function transaction(): HasOne
    {
        return $this->hasOne(TradingTransaction::class)->latestOfMany();
    }

    public function tradingCashout(): HasOne
    {
        return $this->hasOne(TradingCashout::class);
    }

    public function tradingStoredEarning(): HasOne
    {
        return $this->hasOne(TradingStoredEarning::class);
    }

    public function getPeriodLabelAttribute(): string
    {
        return Carbon::create($this->year, $this->month, 1)->format('F Y');
    }

    public function isEligibleForDecision(): bool
    {
        return $this->status === self::STATUS_COMPLETED
            && ! $this->is_locked
            && (float) $this->total_earning != 0.0;
    }

    public function recalculateTotals(): void
    {
        $total = $this->tradingEarnings()->sum('amount');

        $this->update(['total_earning' => $total]);
    }

    public function maybeMarkCompleted(): void
    {
        if ($this->status !== self::STATUS_PENDING) {
            return;
        }

        if (today()->greaterThan($this->end_date)) {
            $this->update(['status' => self::STATUS_COMPLETED]);
        }
    }
}
