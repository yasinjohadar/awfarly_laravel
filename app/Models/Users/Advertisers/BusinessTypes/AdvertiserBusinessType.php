<?php

namespace App\Models\Users\Advertisers\BusinessTypes;

use App\Models\Users\Advertisers\AdvertiserUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdvertiserBusinessType extends Model
{
    use HasFactory;

    protected $table = 'advertisers_business_types';

    protected $fillable = [
        'order',
        'name_ar',
        'name_en',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * @return HasMany
     */
    public function advertisers(): HasMany
    {
        return $this->hasMany(AdvertiserUser::class, 'id', 'business_type');
    }
}
