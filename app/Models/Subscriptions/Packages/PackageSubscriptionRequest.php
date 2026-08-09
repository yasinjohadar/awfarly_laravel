<?php

namespace App\Models\Subscriptions\Packages;

use App\Models\Users\Advertisers\AdvertiserUser;
use App\Models\Users\Admins\AdminUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PackageSubscriptionRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'package_subscription_requests';

    protected $fillable = [
        'advertiser_id',
        'package_id',
        'status',
        'notes',
        'receipt',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function advertiser(): BelongsTo
    {
        return $this->belongsTo(AdvertiserUser::class, 'advertiser_id', 'id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'package_id', 'id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'reviewed_by', 'id');
    }
}
