<?php

namespace App\Models\Subscriptions\Packages\Advertisers;

use App\Models\Subscriptions\Packages\Package;
use App\Models\Users\Advertisers\AdvertiserUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdvertiserPackages extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'advertiser_packages';

    /**
     * @var string[]
     */
    protected $fillable = [
        'unique_identifier',
        'receipt_data',
        'package_id',
        'advertiser_id',
        'starts_at',
        'ends_at',
        'purchase_count',
        'is_ended',
        'is_current',
        'is_active',
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_ended' => 'boolean',
        'is_current' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * @return BelongsTo
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'package_id', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function advertiser(): BelongsTo
    {
        return $this->belongsTo(AdvertiserUser::class, 'advertiser_id', 'id');
    }
}
