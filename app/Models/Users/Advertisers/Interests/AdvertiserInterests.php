<?php

namespace App\Models\Users\Advertisers\Interests;

use App\Models\Interests\Interest;
use App\Models\Users\Advertisers\AdvertiserUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdvertiserInterests extends Model
{
    use HasFactory;

    protected $table = 'advertiser_interests';
    protected $fillable = [
        'interest_id',
        'advertiser_id',
    ];

    /**
     * @return BelongsTo
     */
    public function interest(): BelongsTo
    {
        return $this->belongsTo(Interest::class, 'interest_id', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function advertiser(): BelongsTo
    {
        return $this->belongsTo(AdvertiserUser::class, 'advertiser_id', 'id');
    }
}
