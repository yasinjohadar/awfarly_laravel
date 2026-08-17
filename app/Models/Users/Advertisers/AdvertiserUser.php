<?php

namespace App\Models\Users\Advertisers;

use App\Models\Chats\ChatChannel;
use App\Models\Chats\Messages\ChatMessages;
use App\Models\Chats\Users\ChatUsers;
use App\Models\Countries\Cities\City;
use App\Models\Countries\Country;
use App\Models\Countries\Governorates\Governorate;
use App\Models\Languages\Language;
use App\Models\Offers\Likes\OfferLikes;
use App\Models\Offers\Comments\Likes\OffersCommentLikes;
use App\Models\Offers\Comments\OffersComments;
use App\Models\Offers\Offer;
use App\Models\Offers\Ratings\OfferRatings;
use App\Models\Offers\Viewed\ViewedOffers;
use App\Models\Posts\Comments\Likes\PostsCommentLikes;
use App\Models\Posts\Comments\PostComments;
use App\Models\Subscriptions\Packages\Advertisers\AdvertiserPackages;
use App\Models\Users\Advertisers\Categories\AdvertiserCategories;
use App\Models\Users\Advertisers\Categories\AdvertiserInterests;
use App\Models\Users\Advertisers\Locations\AdvertiserPreferredCity;
use App\Models\Users\Advertisers\Locations\AdvertiserPreferredGovernorate;
use App\Models\Users\Advertisers\Hidden\HiddenAdvertiser;
use App\Models\Posts\Likes\PostLikes;
use App\Models\Posts\Post;
use App\Models\Posts\Saved\SavedPost;
use App\Models\Posts\Subscriptions\PostSubscriptions;
use App\Models\Posts\Viewed\ViewedPost;
use App\Models\Proposals\Proposal;
use App\Models\Requests\ContactForms;
use App\Models\Requests\UsernameRequests;
use App\Models\Users\Advertisers\BusinessTypes\AdvertiserBusinessType;
use App\Models\Users\Advertisers\Ratings\AdvertiserRatings;
use App\Models\Reports\Report;
use App\Models\Users\Shared\Blockings\BlockUser;
use App\Models\Users\Shared\Followings\UserFollowings;
use App\Models\Users\Shared\Social\SocialAccount;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Traits\HasWalletFloat;
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

class AdvertiserUser extends Authenticatable implements Wallet
{
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes;
    use HasWalletFloat;


    /**
     * @var string
     */
    protected $table = 'advertisers_users';

    /**
     * Set user type
     * @var string
     */
    protected string $user_type = 'advertiser';

    /**
     * Appendable attributes.
     *
     * @var array
     */
    protected $appends = [
        'user_type',
        'is_profile_completed',
        //'is_mobile_approved',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'business_type',
        'username',
        'birth_date',
        'gender',
        'email',
        'mobile',
        'bio',
        'notify_language',
        'image',
        'country_code',
        'governorate_id',
        'city_id',
        'language_code',
        'contact_number',
        'whatsapp_number',
        'facebook_url',
        'twitter_url',
        'website_url',
        'allowed_posts_count',
        'allowed_offers_count',
        'maximum_monthly_offers',
        'email_verified_at',
        'mobile_verified_at',
        'rate',
        'password',
        'fcm_token',
        'status',
        'is_elite',
        'is_follow_allowed',
        'is_accepted_send_notifications',
        'address_latitude',
        'address_longitude',
        'chats_privacy',
        'profile_privacy',
        'last_login_at',
        'last_online_at',
        'is_online',
        'location',
        'discount_percentage',
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
        'is_follow_allowed' => 'boolean',
        'last_login_at' => 'datetime',
        'last_online_at' => 'datetime',
        'is_online' => 'boolean',
        'birth_date' => 'date',
        'location' => Point::class,
    ];

    public function newEloquentBuilder($query): SpatialBuilder
    {
        return new SpatialBuilder($query);
    }
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

    /**
     * set class
     * @return string
     */
    public function getClassAttribute(): string
    {
        return __CLASS__;
    }

    /**
     * @return HasOne
     */
    public function language(): HasOne
    {
        return $this->hasOne(Language::class, 'code', 'language_code');
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
     * Set account type
     * @return bool
     */
    public function getIsProfileCompletedAttribute(): bool
    {
        $sub_category = $this->categories()
            ->latest()
            ->first();

        return (bool)($this->username && $sub_category);
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
     * @return HasOne
     */
    public function business(): HasOne
    {
        return $this->hasOne(AdvertiserBusinessType::class, 'id', 'business_type');
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
    public function governorate(): HasOne
    {
        return $this->hasOne(Governorate::class, 'id', 'governorate_id');
    }

    /**
     * @return HasOne
     */
    public function city(): HasOne
    {
        return $this->hasOne(City::class, 'id', 'city_id');
    }

    /**
     * Set is mobile approved
     * @return bool
     */
    public function getIsMobileApprovedAttribute(): bool
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
     * @return MorphMany
     */
    public function posts(): MorphMany
    {
        return $this->MorphMany(Post::class, 'user');
    }

    /**
     * @return HasMany
     */
    public function advertiserPosts(): HasMany
    {
        return $this->HasMany(Post::class, 'user_id', 'id');
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
     * @return hasMany
     */
    public function rating(): hasMany
    {
        return $this->hasMany(AdvertiserRatings::class, 'advertiser_id', 'id');
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
     * @return HasMany
     */
    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class, 'advertiser_id', 'id');
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
     * @return HasMany
     */
    public function receivedProposals(): HasMany
    {
        return $this->hasMany(Proposal::class, 'advertiser_id', 'id');
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
    public function packages(): HasMany
    {
        return $this->hasMany(AdvertiserPackages::class, 'advertiser_id', 'id');
    }

    /**
     * The advertiser's OWN business categories: what they publish under.
     * Drives the default category of a new post/offer and the advertiser-level
     * feed match. NOT the same as interests().
     *
     * @return HasMany
     */
    public function categories(): HasMany
    {
        return $this->hasMany(AdvertiserCategories::class, 'advertiser_id', 'id');
    }

    /**
     * The categories this advertiser FOLLOWS, deciding what appears in their
     * own feed. Editable independently of categories().
     *
     * @return HasMany
     */
    public function interests(): HasMany
    {
        return $this->hasMany(AdvertiserInterests::class, 'advertiser_id', 'id');
    }


    /**
     * @return HasMany
     */
    public function preferredGovernorates(): HasMany
    {
        return $this->hasMany(AdvertiserPreferredGovernorate::class, 'advertiser_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function preferredCities(): HasMany
    {
        return $this->hasMany(AdvertiserPreferredCity::class, 'advertiser_id', 'id');
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
