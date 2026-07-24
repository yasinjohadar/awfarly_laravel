<?php

namespace App\Models\Users\Admins\Logs;

use App\Models\Users\Admins\AdminUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminActionLogs extends Model
{
    use HasFactory;
    /**
     * The database table used by the model. -- this is auto generated
     *
     * @var string
     */
    protected $table = 'admins_actions_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'admin_id',
        'summary',
        'data',
        'type',
        'action',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Get admin data
     * @return BelongsTo
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class,'admin_id','id');
    }
}
