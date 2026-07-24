<?php

namespace App\Models\Users\Shared\Blockings;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class BlockUser extends Model
{
    use HasFactory;

    protected $table = 'block_users';

    protected $fillable = [
        'blocker_id',
        'blocker_type',
        'blocked_id',
        'blocked_type',
    ];

    /**
     * @return MorphTo
     */
    public function blocker(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return MorphTo
     */
    public function blocked(): MorphTo
    {
        return $this->morphTo();
    }
}
