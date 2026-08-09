<?php

namespace App\Models\Currencies;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Currency extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'currencies';

    protected $fillable = [
        'code',
        'name_en',
        'name_ar',
        'symbol',
        'exchange_rate',
        'is_base',
        'is_active',
        'is_visible',
        'order',
    ];

    protected $casts = [
        'exchange_rate' => 'float',
        'is_base' => 'boolean',
        'is_active' => 'boolean',
        'is_visible' => 'boolean',
    ];

    /**
     * Convert an amount from one currency code to another, using each
     * currency's exchange_rate relative to the base currency as the cross-rate.
     */
    public static function convert(float $amount, string $from, string $to): float
    {
        if ($from === $to) {
            return $amount;
        }

        $fromRate = static::where('code', $from)->value('exchange_rate') ?? 1;
        $toRate = static::where('code', $to)->value('exchange_rate') ?? 1;

        return ($amount / $fromRate) * $toRate;
    }
}
