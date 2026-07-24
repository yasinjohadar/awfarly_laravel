<?php

namespace App\Models\Users\Shared\Followings;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class UserFollowings extends Model
{
    use HasFactory;

    protected $table = 'users_followings';

    protected $fillable = [
        'followed_type',
        'followed_id',
        'follower_type',
        'follower_id',
        'status',
    ];

    /**
     * @return MorphTo
     */
    public function followed(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return MorphTo
     */
    public function follower(): MorphTo
    {
        return $this->morphTo();
    }

}
