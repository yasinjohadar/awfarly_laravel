<?php

namespace App\Models\Offers\Comments\Likes;

use App\Models\Offers\Comments\OffersComments;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class OffersCommentLikes extends Model
{
    use HasFactory;

    protected $table = 'offers_comments_likes';

    protected $fillable = [
        'comment_id',
        'user_type',
        'user_id',
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
    public function comment(): BelongsTo
    {
        return $this->belongsTo(OffersComments::class, 'comment_id', 'id');
    }
}
