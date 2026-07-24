<?php

namespace App\Models\Chats\Messages;

use App\Models\Chats\ChatChannel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ChatMessages extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes;

    protected $table = 'chat_messages';

    protected $fillable = [
        'chat_id',
        'sender_type',
        'sender_id',
        'message',
        'is_read',
    ];

    protected $casts = [
        'data' => 'json',
        'is_read' => 'boolean',
    ];

    /**
     * @return MorphTo
     */
    public function sender(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo
     */
    public function chat(): BelongsTo
    {
        return $this->belongsTo(ChatChannel::class, 'chat_id', 'id');
    }

    /**
     * media data
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('messages')
            ->useDisk('local')
            ->registerMediaConversions(function (Media $media) {
                $this->addMediaConversion('thumb')
                    ->width(650)
                    ->height(473)
                    ->extractVideoFrameAtSecond(2)
                    ->performOnCollections('messages');
            });
    }
}
