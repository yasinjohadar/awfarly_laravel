<?php

namespace App\Models\Users\Advertisers\Categories;

use App\Models\Categories\Category;
use App\Models\Users\Advertisers\AdvertiserUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The categories an advertiser FOLLOWS, which decide what appears in their
 * feed. Distinct from AdvertiserCategories, which is what the advertiser
 * publishes under. Both draw from the same `categories` taxonomy.
 */
class AdvertiserInterests extends Model
{
    use HasFactory;

    protected $table = 'advertiser_interests';
    protected $fillable = [
        'category_id',
        'advertiser_id',
    ];

    /**
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function advertiser(): BelongsTo
    {
        return $this->belongsTo(AdvertiserUser::class, 'advertiser_id', 'id');
    }
}
