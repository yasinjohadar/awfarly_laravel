<?php

namespace App\Models\Pages;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    /**
     * The database table used by the model. -- this is auto generated
     *
     * @var string
     */
    protected $table = 'pages';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'slug',
        'title_en',
        'title_ar',
        'contents_en',
        'contents_ar',
        'is_protected',
        'is_active',
    ];
}
