<?php

namespace App\Models\Users\Advertisers\Locations;

use App\Models\Countries\Cities\City;
use App\Models\Users\Advertisers\AdvertiserUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdvertiserPreferredCity extends Model
{
    use HasFactory;

    protected $table = 'advertiser_preferred_cities';

    protected $fillable = [
        'advertiser_id',
        'city_id',
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id', 'id');
    }

    public function advertiser(): BelongsTo
    {
        return $this->belongsTo(AdvertiserUser::class, 'advertiser_id', 'id');
    }
}
