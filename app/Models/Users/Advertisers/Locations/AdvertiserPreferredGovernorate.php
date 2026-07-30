<?php

namespace App\Models\Users\Advertisers\Locations;

use App\Models\Countries\Governorates\Governorate;
use App\Models\Users\Advertisers\AdvertiserUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdvertiserPreferredGovernorate extends Model
{
    use HasFactory;

    protected $table = 'advertiser_preferred_governorates';

    protected $fillable = [
        'advertiser_id',
        'governorate_id',
    ];

    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class, 'governorate_id', 'id');
    }

    public function advertiser(): BelongsTo
    {
        return $this->belongsTo(AdvertiserUser::class, 'advertiser_id', 'id');
    }
}
