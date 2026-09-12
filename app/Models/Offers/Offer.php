<?php

namespace App\Models\Offers;

use App\Helpers\Settings;
use App\Models\Reports\Report;
use Spatie\MediaLibrary\HasMedia;
use App\Models\Categories\Category;
use App\Models\Offers\Likes\OfferLikes;
use Illuminate\Database\Eloquent\Model;
use App\Models\Offers\Viewed\ViewedOffers;
use App\Models\Offers\Ratings\OfferRatings;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Offers\Comments\OffersComments;
use App\Models\Users\Advertisers\AdvertiserUser;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Offer extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes;

    protected $table = 'offers';

    protected $fillable = [
        'category_id',
        'advertiser_id',
        'content',
        'sale_percentage',
        'advertisement_url',
        'rate',
        'expires_at',
        'expires_in',
        'status',
        'views_count',
        'likes_count',
        'comments_count',
        'amount',
        'currency',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * @return BelongsTo
     */
    public function advertiser(): BelongsTo
    {
        return $this->belongsTo(AdvertiserUser::class, 'advertiser_id', 'id');
    }

    /**
     * Keep only the offers a viewer is allowed to see: the newest N per
     * advertiser, N being that advertiser's own active-offer ceiling.
     *
     * Expressed as "this offer has fewer than N newer siblings from the same
     * advertiser", which is a plain WHERE predicate — so it stays correct under
     * pagination, unlike filtering a fetched page in PHP. A correlated subquery
     * rather than ROW_NUMBER() because the ceiling differs per advertiser and
     * MySQL 8 cannot be assumed (nothing else in this codebase uses window
     * functions).
     *
     * The ceiling is read from advertisers_users.allowed_offers_count, which
     * PackageQuotas keeps in sync with the package / global setting.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int|null $exceptAdvertiserId Advertiser whose own offers stay fully
     *   visible to themselves — they need to see every one to pick which to delete.
     */
    public function scopeWithinAdvertiserActiveLimit($query, ?int $exceptAdvertiserId = null)
    {
        $default = (int) Settings::Get('max.advertiser.active.offers', 20);

        $condition = '(select count(*) from offers as newer
                where newer.advertiser_id = offers.advertiser_id
                  and newer.deleted_at is null
                  and newer.status = ?
                  and newer.expires_at > ?
                  and (newer.created_at > offers.created_at
                       or (newer.created_at = offers.created_at and newer.id > offers.id)))
             < (select coalesce(a.allowed_offers_count, ?)
                  from advertisers_users as a where a.id = offers.advertiser_id)';

        $bindings = ['approved', now(), $default];

        if ($exceptAdvertiserId) {
            return $query->where(function ($q) use ($condition, $bindings, $exceptAdvertiserId) {
                $q->whereRaw($condition, $bindings)
                    ->orWhere('offers.advertiser_id', $exceptAdvertiserId);
            });
        }

        return $query->whereRaw($condition, $bindings);
    }

    /**
     * @return HasMany
     */
    public function rating(): HasMany
    {
        return $this->hasMany(OfferRatings::class, 'offer_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function likes_users(): HasMany
    {
        return $this->hasMany(OfferLikes::class)->with('user');
    }

    /**
     * @return HasMany
     */
    public function views(): HasMany
    {
        return $this->hasMany(ViewedOffers::class);
    }

    /**
     * @return HasMany
     */
    public function users_comments(): HasMany
    {
        return $this->hasMany(OffersComments::class)->with('user')->where('comment_id', null);
    }

    /**
     * @return HasMany
     */
    public function comments(): HasMany
    {
        return $this->hasMany(OffersComments::class);
    }

    /**
     * Media data
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('offers')
            ->useDisk('local')
            ->registerMediaConversions(function (Media $media) {
                $this->addMediaConversion('thumb')
                    ->width(650)
                    ->height(473)
                    ->extractVideoFrameAtSecond(2)
                    ->performOnCollections('offers');
            });
    }

    /**
     * @return MorphMany
     */
    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reported');
    }

    /**
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }
}
