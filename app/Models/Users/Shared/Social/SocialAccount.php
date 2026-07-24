<?php

namespace App\Models\Users\Shared\Social;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SocialAccount extends Model
{
    use HasFactory;
    /**
     * The database table used by the model. -- this is auto generated
     *
     * @var string
     */
    protected $table = 'social_accounts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_type',
        'user_id',
        'provider',
        'provider_id',
    ];

    /**
     * @return morphTo
     */
    public function user(): morphTo
    {
        return $this->morphTo();
    }
}
