<?php

namespace App\Models\Users\Customers\Locations;

use App\Models\Countries\Cities\City;
use App\Models\Users\Customers\CustomerUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerPreferredCity extends Model
{
    use HasFactory;

    protected $table = 'customer_preferred_cities';

    protected $fillable = [
        'customer_id',
        'city_id',
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id', 'id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(CustomerUser::class, 'customer_id', 'id');
    }
}
