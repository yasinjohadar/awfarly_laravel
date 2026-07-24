<?php

namespace App\Models\Advertisements\Slider;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class SliderAdvertisement extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $table = 'slider_advertisements';

    protected $fillable = [
        'advertisement_url',
        'starts_at',
        'ends_at',
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
}
