<?php

namespace App\Models\Users\Advertisers\Ratings;

use App\Models\Users\Advertisers\AdvertiserUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AdvertiserRatings extends Model
{
    use HasFactory;

    protected $table = 'advertisers_ratings';

    protected $fillable = [
        'advertiser_id',
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
    public function advertiser(): BelongsTo
    {
        return $this->belongsTo(AdvertiserUser::class, 'advertiser_id', 'id');
    }
}
