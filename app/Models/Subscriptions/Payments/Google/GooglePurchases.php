<?php

namespace App\Models\Subscriptions\Payments\Google;

use App\Models\Subscriptions\Packages\Advertisers\AdvertiserPackages;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GooglePurchases extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'google_purchases';

    /**
     * @var string[]
     */
    protected $fillable = [
        'subscription',
        'unique_identifier',
        'product_id',
        'expiration_date'
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'subscription' => 'json',
    ];

    /**
     * @return BelongsTo
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(AdvertiserPackages::class, 'unique_identifier', 'unique_identifier');
    }
}
