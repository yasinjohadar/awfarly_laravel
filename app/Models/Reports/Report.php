<?php

namespace App\Models\Reports;

use App\Models\Users\Admins\AdminUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Report extends Model
{
    use HasFactory;

    protected $table = 'reports';

    protected $appends = [
        'reports_count'
    ];

    protected $fillable = [
        'type',
        'user_type',
        'user_id',
        'reported_type',
        'reported_id',
        'reason',
        'status',
        'resolution',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    /**
     * @return mixed
     */
    public function getReportsCountAttribute()
    {
        return Report::where('reported_type', $this->reported_type)
            ->where('reported_id', $this->reported_id)
            ->count();
    }

    /**
     * @return MorphTo
     */
    public function user(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return MorphTo
     */
    public function reported(): MorphTo
    {
        return $this->morphTo()->withTrashed();
    }

    /**
     * The admin who marked this report as solved
     * @return BelongsTo
     */
    public function resolvedByAdmin(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'resolved_by');
    }
}
