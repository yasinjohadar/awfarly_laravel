<?php

namespace App\Http\Livewire\Frontend\Community\Posts;

use App\Helpers\Files;
use App\Helpers\Settings;
use App\Http\Resources\Media\MediaResource;
use App\Models\Posts\Post;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class CommunityPostComponent extends Component
{
    public int $post_id;
    public string $name_column;

    public function render()
    {
        //get language column to show
        $this->name_column = App::currentLocale() === 'ar' ? 'name_ar' : 'name_en';

        $post = Post::where('id', $this->post_id)
            ->first();
        //Get image if exists
        if ($post->user && !is_null($post->user->image) && !empty($post->user->image) && $post->user->image != null) {
            $storagePath = route('files.image.get', $post->user->image);
        } elseif ($post->advertisement_id && !is_null($post->advertisement->advertiser_image) && !empty($post->advertisement->advertiser_image) && $post->advertisement->advertiser_image != null) {
            $storagePath = route('users.profile.image', ['image' => $post->advertisement->advertiser_image]);
        } else {
            //Set default image
            $storagePath = asset('assets/images/user-default.png');
        }


        if ($post->user) {
            //get user rate
            $rate = $post->user->rate ?? null;
            $post = [
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
                    'id' => $post->user->id,
                    'username' => $post->user->username,
                    'bio' => $post->user->bio ?? null,
                    'name' => $post->user->name,
                    'businessTypeName' => $post->user->business_type ? $post->user->business->{$this->name_column} : null,
                    'imageUrl' => $storagePath,
                    'type' => $post->user->user_type,
                    'country' => $post->user->country->{$this->name_column},
                    'city' => $post->user->city->{$this->name_column},
                    'rate' => $rate,
                    'isElite' => (bool)$post->user->is_elite,
                    'chatStatus' => $post->user->chats_privacy,
                    'profilePrivacy' => $post->user->profile_privacy,
                    'isSelf' => false,
                    'isOnline' => $post->user->is_online,
                ],
                'createdAt' => $post->created_at ? Carbon::make($post->created_at)->diffForHumans() : null,
            ];
        } else {
            $post = [
                'id' => $post->id,
                'content' => $post->advertisement->content,
                'websiteUrl' => route('post.index', ['id' => $post->id]),
                'media' => MediaResource::collection($post->advertisement->getMedia('advertisements'))->resolve(),
                'statistics' => [
                    'views' => $post->views_count,
                    'likes' => $post->likes_count,
                    'comments' => $post->comments_count,
                ],
                'owner' => [
                    'name' => $post->advertisement->advertiser_name,
                    'businessTypeName' => null,
                    'imageUrl' => $storagePath,
                    'isElite' => true,
                    'country' => null,
                    'city' => null,
                ],
                'createdAt' => $post->created_at ? Carbon::make($post->created_at)->diffForHumans() : null,
            ];
        }
        return view('livewire.frontend.community.posts.post', ['post' => $post]);
    }
}
