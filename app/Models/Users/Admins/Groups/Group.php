<?php

namespace App\Models\Users\Admins\Groups;

use App\Models\Users\Admins\Groups\Permissions\GroupPermission;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    /**
     * @var string
     */
    protected $table = 'permissions_groups';


    /**
     * @var string[]
     */
    protected $fillable = [
        'name',
        'is_active',
        'is_allowed',
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'is_allowed' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * @return HasMany
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(GroupPermission::class, 'group_id', 'id');
    }
}
