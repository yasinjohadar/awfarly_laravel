<?php

namespace App\Models\Requests;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class UsernameRequests extends Model
{
    use HasFactory;

    protected $table = 'username_requests';

    protected $fillable = [
        'user_type',
        'user_id',
        'old_username',
        'new_username',
        'reason',
        'status',
    ];

    /**
     * @return MorphTo
     */
    public function user(): MorphTo
    {
        return $this->morphTo('user');
    }

}
