<?php

namespace App\Models\Users\Admins\Groups\Permissions;

use App\Models\Users\Admins\Groups\Group;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupPermission extends Model
{
    public $timestamps = false;
    /**
     * @var string
     */
    protected $table = 'permissions_groups_data';

    /**
     * @var string[]
     */
    protected $fillable = [
        'group_id',
        'name',
        'key',
        'is_allowed',
        'is_active',
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'is_allowed' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * @return BelongsTo
     */
    public function group()
    {
        return $this->belongsTo(Group::class, 'id', 'group_id');
    }
}
