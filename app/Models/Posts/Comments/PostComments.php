<?php

namespace App\Models\Posts\Comments;

use App\Models\Posts\Comments\Likes\PostsCommentLikes;
use App\Models\Posts\Post;
use App\Models\Reports\Report;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PostComments extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'posts_comments';

    protected $appends = [
        'data_user',
    ];
    protected $fillable = [
        'post_id',
        'user_type',
        'user_id',
        'comment_id',
        'comment',
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
    public function parent(): BelongsTo
    {
        return $this->belongsTo(PostComments::class, 'comment_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function replays(): HasMany
    {
        return $this->hasMany(PostComments::class, 'comment_id', 'id');
    }

    /**
     * @return MorphTo
     */
    public function getDataUserAttribute(): morphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function likes(): HasMany
    {
        return $this->hasMany(PostsCommentLikes::class, 'comment_id', 'id');
    }

    /**
     * @return MorphMany
     */
    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reported');
    }
}
