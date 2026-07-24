<?php

namespace App\Models\Advertisements;

use App\Models\Posts\Post;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\HasMedia;

class Advertisement extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $table = 'advertisements';

    protected $fillable = [
        'type',
        'users',
        'advertiser_name',
        'advertiser_image',
        'advertiser_url',
        'content',
        'categories',
        'countries',
        'cities',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected $casts = [
        'categories' => 'json',
        'countries' => 'json',
        'cities' => 'json',
        'is_active' => 'boolean',
    ];

    /**
     *
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('advertisements')
            ->useDisk('local')
            ->registerMediaConversions(function (Media $media) {
                $this->addMediaConversion('thumb')
                    ->width(650)
                    ->height(473)
                    ->extractVideoFrameAtSecond(2)
                    ->performOnCollections('advertisements');
            });
    }

    /**
     * @return HasOne
     */
    public function post(): HasOne
    {
        return $this->hasOne(Post::class, 'advertisement_id', 'id');
    }
}
