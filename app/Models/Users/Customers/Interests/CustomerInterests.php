<?php

namespace App\Models\Users\Customers\Interests;

use App\Models\Interests\Interest;
use App\Models\Users\Customers\CustomerUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerInterests extends Model
{
    use HasFactory;

    protected $table = 'customer_interests';
    protected $fillable = [
        'interest_id',
        'customer_id',
    ];

    /**
     * @return BelongsTo
     */
    public function interest(): BelongsTo
    {
        return $this->belongsTo(Interest::class, 'interest_id', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(CustomerUser::class, 'customer_id', 'id');
    }
}
