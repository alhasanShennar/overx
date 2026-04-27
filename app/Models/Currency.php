<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Currency extends Model
{
    protected $fillable = [
        'code',
        'name',
        'symbol',
        'rate',
        'is_default',
        'is_active',
        'order',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'rate' => 'decimal:8',
    ];

    public function cashoutDetails(): HasMany
    {
        return $this->hasMany(CashoutDetail::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public static function getDefault(): ?self
    {
        return self::where('is_default', true)->first();
    }
}
