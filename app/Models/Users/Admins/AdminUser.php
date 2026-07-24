<?php

namespace App\Models\Users\Admins;

use App\Models\Chats\Messages\ChatMessages;
use App\Models\Languages\Language;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class AdminUser extends Authenticatable
{
    use HasApiTokens, HasRoles, Notifiable;

    /**
     * @var string
     */
    protected $table = 'admins_users';

    /**
     * Set user type
     * @var string
     */
    protected string $user_type = 'admin';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'mobile',
        'username',
        'language_code',
        'image',
        'email_verified_at',
        'mobile_verified_at',
        'password',
        'last_login_at',
        'is_super_administrator',
        'is_protected',
        'status',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'is_super_administrator' => 'boolean',
        'is_protected' => 'boolean',
    ];


    /**
     * set class
     * @return string
     */
    public function getClassAttribute(): string
    {
        return __CLASS__;
    }

    /**
     * Set account type
     * @return string
     */
    public function getUserTypeAttribute(): string
    {
        return $this->user_type;
    }

    /**
     * Set Receives Broadcast Notifications On
     * @return string
     */
    public function receivesBroadcastNotificationsOn(): string
    {
        return "{$this->user_type}.{$this->id}";
    }

    /**
     * @return string
     */
    public function getShortNameAttribute(): string
    {
        $var = explode(' ', $this->name);
        $name = '';
        foreach ($var as $iValue) {
            $value = str_split($iValue, 1);
            $name .= $value[0];
        }
        return $name;
    }

    /**
     * set first name
     * @return mixed|string
     */
    public function getFirstNameAttribute()
    {
        //Set user name
        $user_name = explode(' ', $this->name);
        return $user_name[0];
    }

    /**
     * set last name
     * @return string
     */
    public function getLastNameAttribute(): string
    {
        //Set user name
        $user_name = explode(' ', $this->name);
        return implode(' ', array_slice($user_name, 1));
    }

    /**
     * @return MorphMany
     */
    public function messages(): MorphMany
    {
        return $this->morphMany(ChatMessages::class, 'sender');
    }

    /**
     * @return BelongsTo
     */
    public function user_language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'language_code', 'code');
    }
}
