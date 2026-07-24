<?php

namespace App\Models\Offers;

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
