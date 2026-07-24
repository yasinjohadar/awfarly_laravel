<?php

namespace App\Http\Resources\Notifications;

use App\Helpers\Files;
use App\Models\Posts\Post;
use App\Models\Users\Advertisers\AdvertiserUser;
use App\Models\Users\Customers\CustomerUser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

class NotificationsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        $data = [
            'type' => $this->data['type'] ?? null,
            'action' => $this->data['action'] ?? null,
        ];

        if ($this->data['customProperties']) {
            if (isset($this->data['customProperties']['userType']) && isset($this->data['customProperties']['userId'])) {
                if ($this->data['customProperties']['userType'] === 'advertiser') {
                    $user = AdvertiserUser::where('id', $this->data['customProperties']['userId'])
                        ->first();
                } else {
                    $user = CustomerUser::where('id', $this->data['customProperties']['userId'])
                        ->first();
                }
            }
            if (isset($this->data['customProperties']['title'])) {
                $title = (isset($user) && $user->notify_language == 'ar') || ($this->notifiable && $this->notifiable->notify_language == 'ar') ? ($this->data['customProperties']['title'] ?? null) : ($this->data['customProperties']['title_en'] ?? null);
            } elseif ($data['type'] === 'chats') {
                $title = isset($user) ? __('api/advertisers/chat/chats.fcm.title', ['name' => Str::limit($user->name, 20)]) : null;
            } else {
                $title = __("api/notifications/notifications.{$this->data['type']}.title");
            }

            if (isset($this->data['customProperties']['notify_link'])) {
                $notify_link = $this->data['customProperties']['notify_link'] ?? null;
            }

            if (isset($this->data['customProperties']['chatToken'])) {
                $data['chatToken'] = $this->data['customProperties']['chatToken'];
            }
            if (isset($this->data['customProperties']['postId'])) {
                $data['postId'] = $this->data['customProperties']['postId'];
            }
            if (isset($this->data['customProperties']['commentId'])) {
                $data['commentId'] = $this->data['customProperties']['commentId'];
            }
            if (isset($this->data['customProperties']['offerId'])) {
                $data['offerId'] = $this->data['customProperties']['offerId'];
            }
            if (isset($this->data['customProperties']['offerId'])) {
                $data['offerId'] = $this->data['customProperties']['offerId'];
            }
            if (isset($this->data['customProperties']['status'])) {
                $data['status'] = $this->data['customProperties']['status'];
            }
            if (isset($this->data['customProperties']['proposalId'])) {
                $data['proposalId'] = $this->data['customProperties']['proposalId'];
            }
            if (isset($this->data['customProperties']['followId'])) {
                $data['followId'] = $this->data['customProperties']['followId'];
            }
        }

        if ($this->data['type'] === 'admin.notification') {
            $content = (isset($user) && $user->notify_language == 'ar') || ($this->notifiable && $this->notifiable->notify_language == 'ar') ? $this->data['message'] : ($this->data['customProperties']['body_en'] ?? $this->data['message']);
        } else if (isset($user) && $user) {
            if ($this->data['message'] === 'posts.comment_add_subscription' && isset($data['postId'])) {
                $post = Post::where('id', $data['postId'])
                    ->first();

                $content = __("api/notifications/notifications.{$this->data['message']}", ['name' => $user->name, 'owner' => $post ? $post->user->name : ""]);
            } elseif (isset($this->data['customProperties']['chatToken'])) {
                if (isset($this->data['customProperties']['message_type']) && $this->data['customProperties']['message_type'] === 'video') {
                    $content = __('api/advertisers/chat/chats.fcm.video');
                } else if (isset($this->data['customProperties']['message_type']) && $this->data['customProperties']['message_type'] === 'image') {
                    $content = __('api/advertisers/chat/chats.fcm.image');
                } else if (isset($this->data['customProperties']['message_type']) && $this->data['customProperties']['message_type'] === 'audio') {
                    $content = __('api/advertisers/chat/chats.fcm.audio');
                } else if (isset($this->data['customProperties']['message_type']) && $this->data['customProperties']['message_type'] === 'file') {
                    $content = __('api/advertisers/chat/chats.fcm.file');
                } else {
                    $content = $this->data['customProperties']['body'];
                }
            } else {
                $content = $this->data['message'];
            }
        } else {
            $content = $this->data['message'];
        }

        return [
            'id'                => $this->id,
            'title'             => $title ?? null,
            'content'           => $content,
            'notify_link'       => $notify_link ?? null,
            'data'              => $data,
            'isRead'            => (bool)$this->read_at,
            'createdAt'         => $this->created_at ? Carbon::make($this->created_at)->diffForHumans() : null,
        ];
    }
}
