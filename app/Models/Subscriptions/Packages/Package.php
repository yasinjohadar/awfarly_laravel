<?php

namespace App\Models\Subscriptions\Packages;

use App\Models\Subscriptions\Packages\Advertisers\AdvertiserPackages;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    use HasFactory;

    protected $table = 'packages';

    protected $fillable = [
        'product_id',
        'name_en',
        'name_ar',
        'maximum_posts',
        'description_en',
        'description_ar',
        'specifications_en',
        'specifications_ar',
        'price',
        'old_price',
        'subscription_type',
        'duration',
        'currency',
        'is_visible',
        'is_active',
        'is_trial',
        'maximum_offers',
        'maximum_monthly_offers',
        'maximum_points',
    ];

    protected $casts = [
        'specifications_en' => 'json',
        'specifications_ar' => 'json',
        'is_visible' => 'boolean',
        'is_active' => 'boolean',
        'is_trial' => 'boolean',
    ];

    /**
     * @return HasMany
     */
    public function advertisers(): HasMany
    {
        return $this->hasMany(AdvertiserPackages::class, 'package_id', 'id');
    }
}
