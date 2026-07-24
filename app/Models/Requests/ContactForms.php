<?php

namespace App\Models\Requests;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactForms extends Model
{
    use HasFactory;

    protected $table = 'contact_forms';

    protected $fillable = [
        'type',
        'name',
        'mobile',
        'whatsappMobile',
        'email',
        'message',
        'status',
    ];
}
