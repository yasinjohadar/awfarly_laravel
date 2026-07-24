<?php

namespace App\Models\Posts;

use Spatie\Image\Image;
use App\Models\Reports\Report;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\HasMedia;
use App\Models\Categories\Category;
use App\Models\Posts\Likes\PostLikes;
use App\Models\Offers\Likes\OfferLikes;
use App\Models\Posts\Viewed\ViewedPost;
use Illuminate\Database\Eloquent\Model;
use App\Models\Posts\Comments\PostComments;
use Spatie\MediaLibrary\InteractsWithMedia;
use App\Models\Advertisements\Advertisement;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Users\Advertisers\AdvertiserUser;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Post extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes;

    protected $table = 'posts';

    protected $appends = [
        'is_trashed'
    ];

    protected $fillable = [
        'user_type',
        'user_id',
        'advertisement_id',
        'category_id',
        'content',
        'views_count',
        'likes_count',
        'comments_count',
        'shares_count',
        'status',
    ];

    /**
     * Set account type
     * @return string
     */
    public function getIsTrashedAttribute(): string
    {
        return !!($this->deleted_at);
    }

    /**
     * @return morphTo
     */
    public function user(): morphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo
     */
    public function advertiser(): BelongsTo
    {
        return $this->belongsTo(AdvertiserUser::class, 'user_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function views(): HasMany
    {
        return $this->hasMany(ViewedPost::class);
    }

    /**
     * @return HasMany
     */
    public function likes_users(): HasMany
    {
        return $this->hasMany(PostLikes::class)->with('user');
    }

    /**
     * @return HasMany
     */
    public function users_comments(): HasMany
    {
        return $this->hasMany(PostComments::class)->with('user')->where('comment_id', null);
    }

    /**
     * @return HasMany
     */
    public function comments(): HasMany
    {
        return $this->hasMany(PostComments::class);
    }

    /**
     *
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('posts')
            ->useDisk('local')
            ->registerMediaConversions(function (Media $media) {
                $this->addMediaConversion('thumb')
                    ->fit(Manipulations::FIT_CONTAIN, 640, 480)
                    ->extractVideoFrameAtSecond(2)
                    ->performOnCollections('posts');
            });
    }

    /**
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
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
    public function advertisement(): BelongsTo
    {
        return $this->belongsTo(Advertisement::class, 'advertisement_id', 'id');
    }
}
