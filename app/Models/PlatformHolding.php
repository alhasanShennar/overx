<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformHolding extends Model
{
    protected $fillable = [
        'btc_unit',
        'btc_value',
        'eth_unit',
        'eth_value',
        'usdt_value',
    ];

    protected $casts = [
        'btc_unit'   => 'decimal:8',
        'btc_value'  => 'decimal:2',
        'eth_unit'   => 'decimal:8',
        'eth_value'  => 'decimal:2',
        'usdt_value' => 'decimal:2',
    ];

    /**
     * Always return the single platform record, creating it if needed.
     */
    public static function instance(): self
    {
        return self::firstOrCreate(
            ['id' => 1],
            ['btc_unit' => 0, 'btc_value' => 0, 'eth_unit' => 0, 'eth_value' => 0, 'usdt_value' => 0]
        );
    }
}
