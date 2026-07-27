<?php

namespace App\Models\Countries\Governorates;

use App\Models\Countries\Cities\City;
use App\Models\Countries\Country;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Governorate extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'governorates';

    /**
     * @var string[]
     */
    protected $fillable = [
        'order',
        'country_code',
        'name_ar',
        'name_en',
    ];

    /**
     * @return BelongsTo
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_code', 'code');
    }

    /**
     * @return HasMany
     */
    public function cities(): HasMany
    {
        return $this->hasMany(City::class, 'governorate_id', 'id');
    }
}
