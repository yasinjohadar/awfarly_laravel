<?php

namespace App\Models\Modals;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modal extends Model
{
    use HasFactory;

    protected $fillable = [
        'title_ar',
        'title_en',
        'body_ar',
        'body_en',
        'link',
        'start_at',
        'end_at',
        'recipients_type',
    ];

    protected $casts = [
        // 'start_at' => 'datetime',
        // 'end_at' => 'datetime',
    ];
}
