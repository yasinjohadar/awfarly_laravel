<?php

namespace App\Models\Offers\Ratings;

use App\Models\Offers\Offer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class OfferRatings extends Model
{
    use HasFactory;

    protected $table = 'offers_ratings';

    protected $fillable = [
        'offer_id',
        'user_type',
        'user_id',
        'comment',
        'rate',
        'status',
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
