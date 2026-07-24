<?php

namespace App\Models\Advertisements\Side;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class SideAdvertisement extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $table = 'side_advertisements';

    protected $fillable = [
        'advertisement_url',
        'side',
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
