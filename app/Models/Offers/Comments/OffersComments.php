<?php

namespace App\Models\Offers\Comments;

use App\Models\Offers\Offer;
use App\Models\Reports\Report;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Models\Posts\Comments\Likes\PostsCommentLikes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Offers\Comments\Likes\OffersCommentLikes;

class OffersComments extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'offers_comments';

    protected $fillable = [
        'offer_id',
        'user_type',
        'user_id',
        'comment',
        'comment_id',
        'likes_count',
    ];

    /**
     * @return MorphTo
     */
    public function user(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo
     */
    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class, 'offer_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function likes(): HasMany
    {
        return $this->hasMany(OffersCommentLikes::class, 'comment_id', 'id');
    }


    /**
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(OffersComments::class, 'comment_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function replays(): HasMany
    {
        return $this->hasMany(OffersComments::class, 'comment_id', 'id');
    }

    /**
     * @return MorphMany
     */
    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reported');
    }
}
