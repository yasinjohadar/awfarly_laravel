<?php

namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    /**
     * @var string
     */
    protected $table = 'settings';

    /**
     * @var string[]
     */
    protected $fillable = [
        'name',
        'key',
        'value',
        'value_type',
        'type',
        'created_at',
        'updated_at'
    ];
}
