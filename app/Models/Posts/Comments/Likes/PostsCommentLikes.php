<?php

namespace App\Models\Posts\Comments\Likes;

use App\Models\Posts\Comments\PostComments;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PostsCommentLikes extends Model
{
    use HasFactory;

    protected $table = 'posts_comments_likes';

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
        return $this->belongsTo(PostComments::class, 'comment_id', 'id');
    }
}
