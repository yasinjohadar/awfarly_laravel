<?php

namespace App\Http\Livewire\Frontend\Home;

use App\Helpers\Files;
use App\Helpers\Settings;
use App\Http\Resources\Media\MediaResource;
use App\Models\Advertisements\Advertisement;
use App\Models\Categories\Category;
use App\Models\Posts\Post;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;


class HomeComponent extends Component
{
    use WithPagination;

    public Collection $posts;
    public string $name_column = 'name_ar';
    public bool $has_more = false;
    public array $advertisements_ids = [];
    public ?int $category_id = null;

    protected $listeners = [
        'setCategoryId'
    ];

    public function mount()
    {
        //get language column to show
        $this->name_column = App::currentLocale() === 'ar' ? 'name_ar' : 'name_en';

        $limit = Settings::Get('posts.pagination.limit', 10);
        $this->posts = Post::select('posts.*')
            ->join('advertisers_users', function ($q) {
                return $q->on('advertisers_users.id', 'posts.user_id')
                    ->where('advertisers_users.profile_privacy', 'public')
                    ->where('advertisers_users.status', 'active');
            })
            ->whereNull('posts.advertisement_id')
            ->orderBy('advertisers_users.is_elite', 'desc')
            ->orderBy('posts.created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($post) {
                //Get image if exists
                if (!is_null(optional($post->user)->image) && !empty(optional($post->user)->image) && optional($post->user)->image != null) {
                    $storagePath = route('files.image.get', optional($post->user)->image);
                } else {
                    //Set default image
                    $storagePath = Settings::Logo('assets/images/frontend/logo.png');
                }
                //get user rate
                $rate = optional($post->user)->rate ?? null;
                return [
                    'id' => $post->id,
                    'content' => $post->content,
                    'websiteUrl' => route('post.index', ['id' => $post->id]),
                    'media' => MediaResource::collection($post->getMedia('posts'))->resolve(),
                    'statistics' => [
                        'views' => $post->views_count,
                        'likes' => $post->likes_count,
                        'comments' => $post->comments_count,
                    ],
                    'owner' => [
                        'id' => optional($post->user)->id,
                        'username' => optional($post->user)->username,
                        'bio' => optional($post->user)->bio ?? null,
                        'name' => optional($post->user)->name,
                        'businessTypeName' => optional($post->user)->business_type ? optional($post->user)->business->{$this->name_column} : null,
                        'imageUrl' => $storagePath,
                        'type' => optional($post->user)->user_type,
//                        'country' => optional($post->user)->country->{$this->name_column},
//                        'city' => optional($post->user)->city->{$this->name_column},
                        'rate' => $rate,
                        'isElite' => (bool)optional($post->user)->is_elite,
                        'chatStatus' => optional($post->user)->chats_privacy,
                        'profilePrivacy' => optional($post->user)->profile_privacy,
                        'isSelf' => false,
                        'isOnline' => optional($post->user)->is_online,
                    ],
                    'createdAt' => $post->created_at ? Carbon::make($post->created_at)->diffForHumans() : null,
                ];
            });

        if ($this->posts->count() < $limit && $this->posts->count() > 0) {
            $this->addAdvertisement();
        }

        $this->has_more = ($this->getPostsCount() > $this->posts->count());

    }

    public function render()
    {
        return view('livewire.frontend.home.home');
    }

    public function addAdvertisement()
    {
        $advertisement = Advertisement::inRandomOrder()
            ->whereNotIn('id', $this->advertisements_ids)
            ->whereIn('type', ['any', 'website'])
            ->where(function ($q) {
                return $q->where('ends_at', '>', now())
                    ->orWhereNull('ends_at');
            })
            ->where('is_active', true)
            ->first();
        if ($advertisement) {
            $this->advertisements_ids[] = $advertisement->id;
            //Get image if exists
            if (!is_null($advertisement->advertiser_image) && !empty($advertisement->advertiser_image) && $advertisement->advertiser_image != null) {
                $storagePath = route('files.image.get', $advertisement->advertiser_image);
            } else {
                //Set default image
                $storagePath = Settings::Logo('assets/images/frontend/logo.png');
            }

            $advertisement = [
                'id' => $advertisement->id,
                'type' => $advertisement->type,
                'users' => $advertisement->users,
                'advertiserName' => $advertisement->advertiser_name ?? Settings::Get('site.name'),
                'advertiserUrl' => $advertisement->advertiser_url ?? null,
                'advertiserImage' => $storagePath,
                'content' => $advertisement->content ?? null,
                'media' => MediaResource::collection($advertisement->getMedia('advertisements'))->resolve(),
                'startsAt' => $advertisement->starts_at ? Carbon::parse($advertisement->starts_at)->format('Y-m-d h:i A') : null,
                'endsAt' => $advertisement->ends_at ? Carbon::parse($advertisement->ends_at)->format('Y-m-d h:i A') : null,
                'isAdvertisement' => true,
            ];

            $this->posts->push($advertisement);
        }
    }

    public function loadMore()
    {
        $limit = Settings::Get('posts.pagination.limit', 10);
        $posts_ids = $this->posts
            ->pluck('id');

        $this->addAdvertisement();

        if ($this->category_id) {
            $posts = Post::select('posts.*')
                ->whereNotIn('posts.id', $posts_ids)
                ->where('posts.category_id', $this->category_id)
                ->whereNull('posts.advertisement_id')
                ->join('advertisers_users', function ($q) {
                    return $q->on('advertisers_users.id', 'posts.user_id')
                        ->where('advertisers_users.profile_privacy', 'public')
                        ->where('advertisers_users.status', 'active');
                })
                ->orderBy('advertisers_users.is_elite', 'desc')
                ->orderBy('posts.created_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(function ($post) {
                    //get user rate
                    $rate = optional($post->user)->rate ?? null;
                    //Get image if exists
                    if (!is_null(optional($post->user)->image) && !empty(optional($post->user)->image) && optional($post->user)->image != null) {
                        $storagePath = route('files.image.get', optional($post->user)->image);
                    } else {
                        //Set default image
                        $storagePath = Settings::Logo('assets/images/frontend/logo.png');
                    }
                    return [
                        'id' => $post->id,
                        'content' => $post->content,
                        'websiteUrl' => route('post.index', ['id' => $post->id]),
                        'media' => MediaResource::collection($post->getMedia('posts'))->resolve(),
                        'statistics' => [
                            'views' => $post->views_count,
                            'likes' => $post->likes_count,
                            'comments' => $post->comments_count,
                        ],
                        'owner' => [
                            'id' => optional($post->user)->id,
                            'username' => optional($post->user)->username,
                            'bio' => optional($post->user)->bio ?? null,
                            'name' => optional($post->user)->name,
                            'businessTypeName' => optional($post->user)->business_type ? optional($post->user)->business->{$this->name_column} : null,
                            'imageUrl' => $storagePath,
                            'type' => optional($post->user)->user_type,
//                            'country' => optional($post->user)->country->{$this->name_column},
//                            'city' => optional($post->user)->city->{$this->name_column},
                            'rate' => $rate,
                            'isElite' => (bool)optional($post->user)->is_elite,
                            'chatStatus' => optional($post->user)->chats_privacy,
                            'profilePrivacy' => optional($post->user)->profile_privacy,
                            'isSelf' => false,
                            'isOnline' => optional($post->user)->is_online,
                        ],
                        'createdAt' => $post->created_at ? Carbon::make($post->created_at)->diffForHumans() : null,
                    ];
                });
        } else {
            $posts = Post::select('posts.*')
                ->whereNotIn('posts.id', $posts_ids)
                ->whereNull('posts.advertisement_id')
                ->join('advertisers_users', function ($q) {
                    return $q->on('advertisers_users.id', 'posts.user_id')
                        ->where('advertisers_users.profile_privacy', 'public')
                        ->where('advertisers_users.status', 'active');
                })
                ->orderBy('advertisers_users.is_elite', 'desc')
                ->orderBy('posts.created_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(function ($post) {
                    //get user rate
                    $rate = optional($post->user)->rate ?? null;
                    //Get image if exists
                    if (!is_null(optional($post->user)->image) && !empty(optional($post->user)->image) && optional($post->user)->image != null) {
                        $storagePath = route('files.image.get', optional($post->user)->image);
                    } else {
                        //Set default image
                        $storagePath = Settings::Logo('assets/images/frontend/logo.png');
                    }
                    return [
                        'id' => $post->id,
                        'content' => $post->content,
                        'websiteUrl' => route('post.index', ['id' => $post->id]),
                        'media' => MediaResource::collection($post->getMedia('posts'))->resolve(),
                        'statistics' => [
                            'views' => $post->views_count,
                            'likes' => $post->likes_count,
                            'comments' => $post->comments_count,
                        ],
                        'owner' => [
                            'id' => optional($post->user)->id,
                            'username' => optional($post->user)->username,
                            'bio' => optional($post->user)->bio ?? null,
                            'name' => optional($post->user)->name,
                            'businessTypeName' => optional($post->user)->business_type ? optional($post->user)->business->{$this->name_column} : null,
                            'imageUrl' => $storagePath,
                            'type' => optional($post->user)->user_type,
//                            'country' => optional($post->user)->country->{$this->name_column},
//                            'city' => optional($post->user)->city->{$this->name_column},
                            'rate' => $rate,
                            'isElite' => (bool)optional($post->user)->is_elite,
                            'chatStatus' => optional($post->user)->chats_privacy,
                            'profilePrivacy' => optional($post->user)->profile_privacy,
                            'isSelf' => false,
                            'isOnline' => optional($post->user)->is_online,
                        ],
                        'createdAt' => $post->created_at ? Carbon::make($post->created_at)->diffForHumans() : null,
                    ];
                });
        }


        foreach ($posts as $post) {
            $this->posts->push($post);
        }
        if ($this->category_id) {
            $this->has_more = (Post::where('category_id', $this->category_id)->count() > $this->posts->count());
        } else {
            $this->has_more = ($this->getPostsCount() > $this->posts->count());
        }
        $this->dispatchBrowserEvent('resetLightGallery');
    }

    public function setCategoryId($id)
    {
        if ($this->category_id != $id) {
            $this->category_id = $id;

            $limit = Settings::Get('posts.pagination.limit', 10);
            if ($this->category_id) {
                $category = Category::where('id', $id)
                    ->first();

                if ($category->childCategories()->count() == 0) {
                    $this->posts = Post::select('posts.*')
                        ->where('category_id', $this->category_id)
                        ->whereNull('posts.advertisement_id')
                        ->join('advertisers_users', function ($q) {
                            return $q->on('advertisers_users.id', 'posts.user_id')
                                ->where('advertisers_users.profile_privacy', 'public')
                                ->where('advertisers_users.status', 'active');
                        })
                        ->orderBy('advertisers_users.is_elite', 'desc')
                        ->orderBy('posts.created_at', 'desc')
                        ->limit($limit)
                        ->get()
                        ->map(function ($post) {
                            //get user rate
                            $rate = optional($post->user)->rate ?? null;
                            //Get image if exists
                            if (!is_null(optional($post->user)->image) && !empty(optional($post->user)->image) && optional($post->user)->image != null) {
                                $storagePath = route('files.image.get', optional($post->user)->image);
                            } else {
                                //Set default image
                                $storagePath = Settings::Logo('assets/images/frontend/logo.png');
                            }
                            return [
                                'id' => $post->id,
                                'content' => $post->content,
                                'websiteUrl' => route('post.index', ['id' => $post->id]),
                                'media' => MediaResource::collection($post->getMedia('posts'))->resolve(),
                                'statistics' => [
                                    'views' => $post->views_count,
                                    'likes' => $post->likes_count,
                                    'comments' => $post->comments_count,
                                ],
                                'owner' => [
                                    'id' => optional($post->user)->id,
                                    'username' => optional($post->user)->username,
                                    'bio' => optional($post->user)->bio ?? null,
                                    'name' => optional($post->user)->name,
                                    'businessTypeName' => optional($post->user)->business_type ? optional($post->user)->business->{$this->name_column} : null,
                                    'imageUrl' => $storagePath,
                                    'type' => optional($post->user)->user_type,
//                                    'country' => optional($post->user)->country->{$this->name_column},
//                                    'city' => optional($post->user)->city->{$this->name_column},
                                    'rate' => $rate,
                                    'isElite' => (bool)optional($post->user)->is_elite,
                                    'chatStatus' => optional($post->user)->chats_privacy,
                                    'profilePrivacy' => optional($post->user)->profile_privacy,
                                    'isSelf' => false,
                                    'isOnline' => optional($post->user)->is_online,
                                ],
                                'createdAt' => $post->created_at ? Carbon::make($post->created_at)->diffForHumans() : null,
                            ];
                        });

                    if ($this->posts->count() < $limit && $this->posts->count() > 0) {
                        $this->addAdvertisement();
                    }
                }
            } else {
                $this->posts = Post::select('posts.*')
                    ->join('advertisers_users', function ($q) {
                        return $q->on('advertisers_users.id', 'posts.user_id')
                            ->where('advertisers_users.profile_privacy', 'public')
                            ->where('advertisers_users.status', 'active');
                    })
                    ->whereNull('posts.advertisement_id')
                    ->orderBy('advertisers_users.is_elite', 'desc')
                    ->orderBy('posts.created_at', 'desc')
                    ->limit($limit)
                    ->get()
                    ->map(function ($post) {
                        //get user rate
                        $rate = optional($post->user)->rate ?? null;
                        //Get image if exists
                        if (!is_null(optional($post->user)->image) && !empty(optional($post->user)->image) && optional($post->user)->image != null) {
                            $storagePath = route('files.image.get', optional($post->user)->image);
                        } else {
                            //Set default image
                            $storagePath = Settings::Logo('assets/images/frontend/logo.png');
                        }
                        return [
                            'id' => $post->id,
                            'content' => $post->content,
                            'websiteUrl' => route('post.index', ['id' => $post->id]),
                            'media' => MediaResource::collection($post->getMedia('posts'))->resolve(),
                            'statistics' => [
                                'views' => $post->views_count,
                                'likes' => $post->likes_count,
                                'comments' => $post->comments_count,
                            ],
                            'owner' => [
                                'id' => optional($post->user)->id,
                                'username' => optional($post->user)->username,
                                'bio' => optional($post->user)->bio ?? null,
                                'name' => optional($post->user)->name,
                                'businessTypeName' => optional($post->user)->business_type ? optional($post->user)->business->{$this->name_column} : null,
                                'imageUrl' => $storagePath,
                                'type' => optional($post->user)->user_type,
//                                'country' => optional($post->user)->country->{$this->name_column},
//                                'city' => optional($post->user)->city->{$this->name_column},
                                'rate' => $rate,
                                'isElite' => (bool)optional($post->user)->is_elite,
                                'chatStatus' => optional($post->user)->chats_privacy,
                                'profilePrivacy' => optional($post->user)->profile_privacy,
                                'isSelf' => false,
                                'isOnline' => optional($post->user)->is_online,
                            ],
                            'createdAt' => $post->created_at ? Carbon::make($post->created_at)->diffForHumans() : null,
                        ];
                    });

                if ($this->posts->count() < $limit && $this->posts->count() > 0) {
                    $this->addAdvertisement();
                }
            }

            if ($this->category_id) {
                $this->has_more = (Post::where('category_id', $this->category_id)->count() > $this->posts->count());
            } else {
                $this->has_more = ($this->getPostsCount() > $this->posts->count());
            }

            $this->dispatchBrowserEvent('resetLightGallery');
        }
    }

    /**
     * @return mixed
     */
    public function getPostsCount()
    {
        return Post::select('posts.*')
            ->join('advertisers_users', function ($q) {
                return $q->on('advertisers_users.id', 'posts.user_id')
                    ->where('advertisers_users.profile_privacy', 'public')
                    ->where('advertisers_users.status', 'active');
            })
            ->whereNull('posts.advertisement_id')
            ->count();
    }
}
