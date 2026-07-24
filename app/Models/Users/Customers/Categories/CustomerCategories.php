<?php

namespace App\Models\Users\Customers\Categories;

use App\Models\Categories\Category;
use App\Models\Users\Customers\CustomerUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerCategories extends Model
{
    use HasFactory;

    protected $table = 'customer_categories';
    protected $fillable = [
        'category_id',
        'customer_id',
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
    public function customer(): BelongsTo
    {
        return $this->belongsTo(CustomerUser::class, 'customer_id', 'id');
    }
}
