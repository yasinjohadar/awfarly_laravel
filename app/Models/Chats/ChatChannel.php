<?php

namespace App\Models\Chats;

use App\Models\Chats\Messages\ChatMessages;
use App\Models\Chats\Users\ChatUsers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatChannel extends Model
{
    use HasFactory;

    protected $table = 'chat_channels';

    protected $fillable = [
        'token',
        'last_message_at'
    ];

    protected $casts = [
        'last_message_at' => 'datetime'
    ];


    /**
     * @return HasMany
     */
    public function users(): HasMany
    {
        return $this->hasMany(ChatUsers::class, 'chat_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessages::class, 'chat_id', 'id');
    }
}
