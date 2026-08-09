<?php

namespace App\Models\Interests;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Interest extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $table = 'interests';

    protected $fillable = [
        'order',
        'parent_interest_id',
        'name_en',
        'name_ar',
        'description',
        'image',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * @return BelongsTo
     */
    public function parentInterest(): BelongsTo
    {
        return $this->belongsTo(__CLASS__, 'parent_interest_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function childInterests(): HasMany
    {
        return $this->hasMany(__CLASS__, 'parent_interest_id', 'id');
    }
}
