<?php

namespace App\Models\Users\Customers;

use App\Models\Chats\ChatChannel;
use App\Models\Chats\Messages\ChatMessages;
use App\Models\Chats\Users\ChatUsers;
use App\Models\Countries\Cities\City;
use App\Models\Countries\Country;
use App\Models\Languages\Language;
use App\Models\Offers\Likes\OfferLikes;
use App\Models\Offers\Comments\Likes\OffersCommentLikes;
use App\Models\Offers\Comments\OffersComments;
use App\Models\Offers\Ratings\OfferRatings;
use App\Models\Offers\Viewed\ViewedOffers;
use App\Models\Posts\Comments\Likes\PostsCommentLikes;
use App\Models\Posts\Comments\PostComments;
use App\Models\Users\Advertisers\Hidden\HiddenAdvertiser;
use App\Models\Posts\Likes\PostLikes;
use App\Models\Posts\Post;
use App\Models\Posts\Saved\SavedPost;
use App\Models\Posts\Subscriptions\PostSubscriptions;
use App\Models\Posts\Viewed\ViewedPost;
use App\Models\Proposals\Proposal;
use App\Models\Requests\ContactForms;
use App\Models\Requests\UsernameRequests;
use App\Models\Users\Advertisers\Ratings\AdvertiserRatings;
use App\Models\Reports\Report;
use App\Models\Users\Customers\Categories\CustomerCategories;
use App\Models\Users\Shared\Blockings\BlockUser;
use App\Models\Users\Shared\Followings\UserFollowings;
use App\Models\Users\Shared\Social\SocialAccount;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\HasApiTokens;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\SpatialBuilder;

class CustomerUser extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'customers_users';

    /**
     * Set user type
     * @var string
     */
    protected string $user_type = 'customer';

    /**
     * Appendable attributes.
     *
     * @var array
     */
    protected $appends = [
        'user_type',
        //'is_mobile_approved',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'birth_date',
        'gender',
        'mobile',
        'bio',
        'notify_language',
        'image',
        'country_code',
        'city_id',
        'language_code',
        'contact_number',
        'whatsapp_number',
        'facebook_url',
        'twitter_url',
        'website_url',
        'email_verified_at',
        'mobile_verified_at',
        'password',
        'status',
        'chats_privacy',
        'profile_privacy',
        'fcm_token',
        'is_follow_allowed',
        'is_accepted_send_notifications',
        'address_latitude',
        'address_longitude',
        'last_login_at',
        'last_online_at',
        'is_online',
        'location',
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
        'mobile_verified_at' => 'datetime',
        'is_mobile_approved' => 'boolean',
        'is_accept_send_notifications' => 'boolean',
        'last_login_at' => 'datetime',
        'last_online_at' => 'datetime',
        'is_online' => 'boolean',
        'birth_date' => 'date',
        'location' => Point::class,
    ];

    /**
     * @return void|null
     */
    function delete()
    {
        $this->socialAccounts()->delete();
        $this->posts()->delete();
        $this->postsLikes()->delete();
        $this->commentsLikes()->delete();
        $this->postsComments()->delete();
        $this->savedPosts()->delete();
        $this->viewedPosts()->delete();
        $this->subscribedPosts()->delete();
        $this->hiddenAdvertisers()->delete();
        $this->reports()->delete();
        $this->report()->delete();
        $this->block()->delete();
        $this->blocked()->delete();
        $this->advertisersRated()->delete();
        $this->followed()->delete();
        $this->followers()->delete();
        $this->offersRated()->delete();
        $this->offersLikes()->delete();
        $this->viewedOffers()->delete();
        $this->offersCommentsLikes()->delete();
        $this->offersComments()->delete();
        $this->sentProposals()->delete();
        $this->messages()->delete();
        $this->chatsUsers()->delete();
        $this->usernameRequests()->delete();

        parent::delete();
    }

    public function setBirthDateAttribute($value)
    {
        $this->attributes['birth_date'] =  Carbon::parse($value);
    }

    public function newEloquentBuilder($query): SpatialBuilder
    {
        return new SpatialBuilder($query);
    }
    /**
     * @return HasOne
     */
    public function language(): HasOne
    {
        return $this->hasOne(Language::class, 'code', 'language_code');
    }

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
     * Set is mobile approved
     * @return bool
     */
    public function getIsMobileApprovedAttribute()
    {
        return !!DB::table($this->table)
            ->find($this->id)
            ->mobile_verified_at;
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
    public function socialAccounts(): MorphMany
    {
        return $this->morphMany(SocialAccount::class, 'user');
    }

    /**
     * @return HasOne
     */
    public function country(): HasOne
    {
        return $this->hasOne(Country::class, 'code', 'country_code');
    }

    /**
     * @return HasOne
     */
    public function city(): HasOne
    {
        return $this->hasOne(City::class, 'id', 'city_id');
    }

    /**
     * @return MorphMany
     */
    public function posts(): MorphMany
    {
        return $this->morphMany(Post::class, 'user');
    }

    /**
     * @return MorphMany
     */
    public function postsLikes(): MorphMany
    {
        return $this->morphMany(PostLikes::class, 'user');
    }

    /**
     * @return MorphMany
     */
    public function commentsLikes(): MorphMany
    {
        return $this->morphMany(PostsCommentLikes::class, 'user');
    }

    /**
     * @return MorphMany
     */
    public function postsComments(): MorphMany
    {
        return $this->morphMany(PostComments::class, 'user');
    }

    /**
     * @return MorphMany
     */
    public function savedPosts(): MorphMany
    {
        return $this->morphMany(SavedPost::class, 'user');
    }

    /**
     * @return MorphMany
     */
    public function viewedPosts(): MorphMany
    {
        return $this->morphMany(ViewedPost::class, 'user');
    }

    /**
     * @return MorphMany
     */
    public function subscribedPosts(): MorphMany
    {
        return $this->morphMany(PostSubscriptions::class, 'user');
    }

    /**
     * @return MorphMany
     */
    public function hiddenAdvertisers(): MorphMany
    {
        return $this->morphMany(HiddenAdvertiser::class, 'user');
    }

    /**
     * @return MorphMany
     */
    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'user');
    }

    /**
     * @return MorphMany
     */
    public function report(): MorphMany
    {
        return $this->morphMany(Report::class, 'reported');
    }

    /**
     * @return MorphMany
     */
    public function advertisersRated(): MorphMany
    {
        return $this->morphMany(AdvertiserRatings::class, 'user');
    }


    /**
     * @return MorphMany
     */
    public function followed(): MorphMany
    {
        return $this->morphMany(UserFollowings::class, 'follower');
    }

    /**
     * @return MorphMany
     */
    public function followers(): MorphMany
    {
        return $this->morphMany(UserFollowings::class, 'followed');
    }


    /**
     * @return MorphMany
     */
    public function offersRated(): MorphMany
    {
        return $this->morphMany(OfferRatings::class, 'user');
    }

    /**
     * @return MorphMany
     */
    public function offersLikes(): MorphMany
    {
        return $this->morphMany(OfferLikes::class, 'user');
    }

    /**
     * @return MorphMany
     */
    public function viewedOffers(): MorphMany
    {
        return $this->morphMany(ViewedOffers::class, 'user');
    }


    /**
     * @return MorphMany
     */
    public function offersCommentsLikes(): MorphMany
    {
        return $this->morphMany(OffersCommentLikes::class, 'user');
    }

    /**
     * @return MorphMany
     */
    public function offersComments(): MorphMany
    {
        return $this->morphMany(OffersComments::class, 'user');
    }

    /**
     * @return MorphMany
     */
    public function sentProposals(): MorphMany
    {
        return $this->morphMany(Proposal::class, 'user');
    }

    /**
     * @return MorphMany
     */
    public function messages(): MorphMany
    {
        return $this->morphMany(ChatMessages::class, 'sender');
    }

    /**
     * @return HasManyThrough
     */
    public function chats(): HasManyThrough
    {
        return $this->hasManyThrough(
            ChatChannel::class,
            ChatUsers::class,
            'user_id',
            'id',
            'id',
            'chat_id'
        )->where('chat_users.user_type', __CLASS__);
    }

    /**
     * @return morphMany
     */
    public function chatsUsers(): morphMany
    {
        return $this->morphMany(ChatUsers::class, 'user');
    }


    /**
     * @return MorphMany
     */
    public function usernameRequests(): MorphMany
    {
        return $this->morphMany(UsernameRequests::class, 'user');
    }

    /**
     * @return HasMany
     */
    public function categories(): HasMany
    {
        return $this->hasMany(CustomerCategories::class, 'customer_id', 'id');
    }

    /**
     * @return MorphMany
     */
    public function block(): MorphMany
    {
        return $this->morphMany(BlockUser::class, 'blocker');
    }

    /**
     * @return MorphMany
     */
    public function blocked(): MorphMany
    {
        return $this->morphMany(BlockUser::class, 'blocked');
    }
}
