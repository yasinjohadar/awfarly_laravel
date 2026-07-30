<?php

namespace App\Models\Users\Customers\Locations;

use App\Models\Countries\Governorates\Governorate;
use App\Models\Users\Customers\CustomerUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerPreferredGovernorate extends Model
{
    use HasFactory;

    protected $table = 'customer_preferred_governorates';

    protected $fillable = [
        'customer_id',
        'governorate_id',
    ];

    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class, 'governorate_id', 'id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(CustomerUser::class, 'customer_id', 'id');
    }
}
