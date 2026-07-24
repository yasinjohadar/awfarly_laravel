<?php

namespace App\Models\Offers\Viewed;

use App\Models\Offers\Offer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ViewedOffers extends Model
{
    use HasFactory;

    protected $table = 'viewed_offers';

    protected $fillable = [
        'offer_id',
        'user_type',
        'user_id',
    ];

    /**
     * @return MorphTo
     */
    public function user(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo
     */
    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class, 'offer_id', 'id');
    }
}
