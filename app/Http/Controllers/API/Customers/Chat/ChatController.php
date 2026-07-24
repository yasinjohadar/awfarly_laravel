<?php

namespace App\Http\Controllers\API\Customers\Chat;

use App\Events\Chat\Messages\MessageSent;
use App\Helpers\FCM\FcmHelper;
use App\Helpers\Files;
use App\Helpers\Filter;
use App\Helpers\Notifications;
use App\Helpers\Settings;
use App\Http\Controllers\Controller;
use App\Http\Resources\Customers\Chats\ChatMessagesResource;
use App\Http\Resources\Customers\Chats\ChatsChannelsResource;
use App\Http\Resources\Customers\Chats\ChatsUsersResource;
use App\Http\Resources\Media\MediaResource;
use App\Models\Chats\ChatChannel;
use App\Models\Chats\Users\ChatUsers;
use App\Models\Users\Advertisers\AdvertiserUser;
use App\Models\Users\Customers\CustomerUser;
use Carbon\Carbon;
use Exception;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use FFMpeg\Filters\Video\ResizeFilter;
use FFMpeg\Format\Video\X264;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Spatie\Image\Image;

class ChatController extends Controller
{

    /**
     * @param Request $request
     * @param null $token
     * @return Application|ResponseFactory|Response
     */
    public function getChats(Request $request, $token = null)
    {
        //get chats
        $chats = Auth::guard('customer-api')->user()
            ->chats();

        if ($token) {
            //get chat
            $chat = $chats->where('token', $token)
                ->first();

            //if chat doesn't exist return error
            if (!$chat) {
                return $this->apiBadRequestResponse(__('api/customers/chat/chats.wrong-token'));
            }

            //return chat
            return $this->apiResponse(ChatsChannelsResource::make($chat));
        }
        //get limit
        $limit = ($request->has('limit') && $request->get('limit') > 0) ? $request->get('limit') : Settings::Get('chat.channels.pagination.limit', 10);

        //get chats
        $chats = $chats->orderBy('updated_at', 'desc')
            ->orderBy('last_message_at', 'desc')
            ->paginate($limit);

        //return
        return $this->apiPaginateResponse(ChatsChannelsResource::collection($chats));
    }

    /**
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function createChat(Request $request)
    {
        if (Auth::guard('customer-api')->user()->status === 'inactive') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-closed'));
        }

        //set data
        $data = $request->all();

        //validate the users
        $this->apiValidate($data, [
            'toUserId' => ['required'],
            'toUserType' => ['required', 'in:advertiser,customer']
        ]);

        //get user chats
        $user_chats = Auth::guard('customer-api')->user()
            ->chatsUsers()
            ->pluck('chat_id');

        //get user data
        if ($data['toUserType'] === 'advertiser') {
            //get user
            $user = AdvertiserUser::where('id', $data['toUserId'])
                ->first();

            //set type
            $type = AdvertiserUser::class;

        } else if ($data['toUserType'] === 'customer') {
            if ($data['toUserId'] == Auth::guard('customer-api')->id()) {
                return $this->apiBadRequestResponse(__('api/customers/chat/chats.self-chat'));
            }

            //get user
            $user = CustomerUser::where('id', $data['toUserId'])
                ->first();

            //get user type
            $type = CustomerUser::class;

        } else {
            $user = null;
            $type = null;
        }

        //if user doesn't exist return error
        if (!$user) {
            return $this->apiBadRequestResponse(__('api/customers/chat/chats.wrong-user'));
        }

        //check if chat exists
        $chat = ChatUsers::whereIn('chat_id', $user_chats)
            ->where('user_type', $type)
            ->where('user_id', $data['toUserId'])
            ->first();

        if (!$chat) {
            if ($user->status === 'banned') {
                return $this->apiBadRequestResponse(__('api/auth/auth.account-banned'));
            } else if ($user->status === 'inactive') {
                return $this->apiBadRequestResponse(__('api/auth/auth.account-inactive'));
            }
            $block = Auth::guard('customer-api')->user()
                ->block()
                ->where('blocked_type', $user->class)
                ->where('blocked_id', $user->id)
                ->exists();

            $blocked = Auth::guard('customer-api')->user()
                ->blocked()
                ->where('blocker_type', $user->class)
                ->where('blocker_id', $user->id)
                ->exists();

            if ($block || $blocked) {
                $message = $user->user_type === 'advertiser' ? __('api/customers/advertisers/advertisers.unavailable') : __('api/customers/customers/customers.unavailable');
                return $this->apiBadRequestResponse($message);
            }

            if ($user->chat_privacy === 'followers') {
                //check if user is followed
                $user_followed = Auth::guard('customer-api')->user()
                    ->followed()
                    ->where('followed_type', $type)
                    ->where('followed_id', $data['toUserId'])
                    ->where('status', 'approved')
                    ->first();
                if (!$user_followed) {
                    return $this->apiBadRequestResponse(__('api/customers/chat/chats.chat-private'));
                }
            } else if ($user->chat_privacy === 'disabled') {
                return $this->apiBadRequestResponse(__('api/customers/chat/chats.chat-disabled'));
            }
        }

        DB::beginTransaction();
        try {
            if (!$chat) {
                $chat = ChatChannel::create([
                    'token' => $this->generateToken(),
                ]);

                $user->chatsUsers()
                    ->create([
                        'chat_id' => $chat->id,
                    ]);

                Auth::guard('customer-api')->user()
                    ->chatsUsers()
                    ->create([
                        'chat_id' => $chat->id,
                    ]);
            } else {
                $chat = $chat->chat;
            }
        } catch (Exception $e) {
            DB::rollBack();
            return $this->apiExceptionResponse(__('api/customers/chat/chats.something-wrong'));
        }
        DB::commit();
        try {
            return $this->apiResponse([
                'message' => __('api/customers/chat/chats.chat-created'),
                'data' => ChatsChannelsResource::make($chat)
            ]);
        } finally {
            DB::beginTransaction();
            try {
                $chat->messages()
                    ->whereHasMorph('sender', '*', function ($q, $type) {
                        if ($type === CustomerUser::class) {
                            return $q->where('id', '!=', Auth::guard('customer-api')->id());
                        }
                        return $q;
                    })
                    ->update([
                        'is_read' => true,
                    ]);
            } catch (Exception $e) {
                DB::rollBack();
            }
            DB::commit();
        }

    }

    /**
     * Generate random md5
     * @return string
     */
    public function generateToken(): string
    {
        return md5(time() . Str::random(20));
    }

    /**
     * @param Request $request
     * @param $token
     * @return Application|ResponseFactory|Response
     */
    public function sendMessage(Request $request, $token)
    {
        if (Auth::guard('customer-api')->user()->status === 'inactive') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-closed'));
        }

        //get data
        $data = $request->all();

        //validate data
        $this->apiValidate($data, [
            'message' => 'nullable|min:1|max:500',
            'media' => ['nullable', 'array'],
            'media.file' => ['nullable', 'mimes:jpg,jpeg,png,bmp,gif,mp4,mov,ogg,qt,avi,wmv,flv,ts,3gp', 'max:100000'],
            'media.startAt' => ['nullable', 'integer'],
            'media.endAt' => ['nullable', 'integer', 'gt:media.startAt'],
            'isVoiceNote' => ['nullable', 'boolean']
        ]);

        //get user
        $user = Auth::guard('customer-api')->user();

        //get chat
        $chat = $user->chats()
            ->where('token', $token)
            ->first();

        //return error if chat not found
        if (!$chat) {
            return $this->apiBadRequestResponse(__('api/customers/chat/chats.wrong-token'));
        }

        $other_user = $chat->users()
            ->where(function ($q) {
                return $q->where(function ($q) {
                    return $q->where('user_id', '!=', Auth::guard('customer-api')->id());
                })
                    ->orWhere(function ($q) {
                        return $q->where('user_type', '!=', CustomerUser::class);
                    });
            })
            ->first()
            ->user;

        if ($other_user->status === 'banned') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-banned'));
        } else if ($other_user->status === 'inactive') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-inactive'));
        }
        $block = Auth::guard('customer-api')->user()
            ->block()
            ->where('blocked_type', $other_user->class)
            ->where('blocked_id', $other_user->id)
            ->exists();

        $blocked = Auth::guard('customer-api')->user()
            ->blocked()
            ->where('blocker_type', $other_user->class)
            ->where('blocker_id', $other_user->id)
            ->exists();

        if ($block || $blocked) {
            $message = $other_user->user_type === 'advertiser' ? __('api/customers/advertisers/advertisers.unavailable') : __('api/customers/customers/customers.unavailable');
            return $this->apiBadRequestResponse($message);
        }
        if ($other_user->chats_privacy === 'followers') {
            //check if user is followed
            $user_followed = Auth::guard('advertiser-api')->user()
                ->followed()
                ->where('followed_type', $other_user->class)
                ->where('followed_id', $other_user->id)
                ->where('status', 'approved')
                ->first();
            if (!$user_followed) {
                return $this->apiBadRequestResponse(__('api/customers/chat/chats.chat-private'));
            }
        } else if ($other_user->chats_privacy === 'disabled') {
            return $this->apiBadRequestResponse(__('api/customers/chat/chats.chat-disabled'));
        }

        DB::beginTransaction();
        try {
            //set message
            if ($request->has('message')) {
                $data['message'] = Filter::RemoveXSS($data['message']);
            } else {
                $data['message'] = null;
            }

            //create message
            $message = $user->messages()
                ->create([
                    'chat_id' => $chat->id,
                    'message' => $data['message'] ?? null,
                    'is_read' => false,
                ]);

            if ($request->hasFile('media.file')) {
                $is_voice_note = $request->has('isVoiceNote') ? $data['isVoiceNote'] : false;
                $media = $request->media;
                $mime_type = $media['file']->getMimeType();
                $file = $media['file'];
                if (strstr($mime_type, "video/")) {
                    $filename = pathinfo($file->hashName(), PATHINFO_FILENAME) . ".mp4";
                    $ffmpeg = FFMpeg::create();
                    $video = $ffmpeg->open($file);
                    $video_stream = $video->getStreams()
                        ->videos()
                        ->first()
                        ->getDimensions();
                    $file_width = $video_stream->getWidth();
                    $file_height = $video_stream->getHeight();
                    if ((isset($media['startAt']) && $media['startAt'] != null) && (isset($media['endAt']) && $media['endAt'] != null)) {
                        $start_at = $media['startAt'];
                        $duration = $media['endAt'] - $start_at;
                        $video->filters()->clip(TimeCode::fromSeconds($start_at), TimeCode::fromSeconds($duration));
                        $video->save(new X264(), storage_path("app/uploads/$filename"));
                        $file = storage_path("app/uploads/$filename");
                    } elseif ($file_height > 480 && $file_height < $file_width) {
                        $video->filters()->resize(new Dimension(640, 480), ResizeFilter::RESIZEMODE_SCALE_WIDTH);
                        $video->save(new X264(), storage_path("app/uploads/$filename"));
                        $video_dimensions = $video->getFFProbe()
                            ->streams(storage_path("app/uploads/$filename"))
                            ->videos()
                            ->first()
                            ->getDimensions();
                        $file_width = $video_dimensions->getWidth();
                        $file_height = $video_dimensions->getHeight();
                        $file = storage_path("app/uploads/$filename");
                    } elseif ($file_width > 480 && $file_height > $file_width) {
                        $video->filters()->resize(new Dimension(480, 640), ResizeFilter::RESIZEMODE_SCALE_HEIGHT);
                        $video->save(new X264(), storage_path("app/uploads/$filename"));
                        $video_dimensions = $video->getFFProbe()
                            ->streams(storage_path("app/uploads/$filename"))
                            ->videos()
                            ->first()
                            ->getDimensions();
                        $file_width = $video_dimensions->getWidth();
                        $file_height = $video_dimensions->getHeight();
                        $file = storage_path("app/uploads/$filename");
                    } else {
                        $video->save(new X264(), storage_path("app/uploads/$filename"));
                        $video_dimensions = $video->getFFProbe()
                            ->streams(storage_path("app/uploads/$filename"))
                            ->videos()
                            ->first()
                            ->getDimensions();
                        $file_width = $video_dimensions->getWidth();
                        $file_height = $video_dimensions->getHeight();
                        $file = storage_path("app/uploads/$filename");
                    }
                } else if (strstr($mime_type, 'image/')) {
                    $file_width = Image::load($file)->getWidth();
                    $file_height = Image::load($file)->getHeight();
                    $temp_image = Files::uploadTempImage($request, 'uploads/media', "media.file");
                    $file = storage_path("app/$temp_image");
                } else {
                    $file_width = null;
                    $file_height = null;
                }
                $message->addMedia($file)
                    ->withCustomProperties(['isVoiceNote' => $is_voice_note, 'width' => $file_width, 'height' => $file_height])
                    ->toMediaCollection('messages');
            }

            $is_owner = ($message->sender->user_type === $user->user_type && $message->sender->id === $user->id);
            $media = $message->getMedia('messages')->first();
            $data = [
                'id' => $message->id,
                'owner' => ChatsUsersResource::make($message->sender)->resolve(),
                'channel' => [
                    'id' => $message->chat->id,
                    'token' => $message->chat->token,
                ],
                'message' => $message->message,
                'media' => $media ? MediaResource::make($media)->resolve() : null,
                'isOwner' => $is_owner,
                'isElite' => false,
                'createdAt' => Carbon::make($message->created_at)->diffForHumans(),
            ];

            //get message resource
            $message_resource = ChatMessagesResource::make($message);

            $chat->update([
                'last_message_at' => Carbon::now(),
            ]);


            if ($request->hasFile('media')) {
                //get file mime type
                $mime_type = $media->mime_type;

                if (strstr($mime_type, "video/")) {
                    $message = __('api/customers/chat/chats.fcm.video');
                    $type = 'video';
                } else if (strstr($mime_type, "image/")) {
                    $message = __('api/customers/chat/chats.fcm.image');
                    $type = 'image';
                } else if (strstr($mime_type, "audio/")) {
                    $message = __('api/customers/chat/chats.fcm.audio');
                    $type = 'audio';
                } else {
                    $message = __('api/customers/chat/chats.fcm.file');
                    $type = 'file';
                }
            } else {
                $message = $data['message'] ? Str::limit($data['message'], 30) : null;
                $type = 'text';
            }

            $chat->messages()
                ->whereHasMorph('sender', '*', function ($q, $type) {
                    if ($type === CustomerUser::class) {
                        return $q->where('id', '!=', Auth::guard('customer-api')->id());
                    }
                    return $q;
                })
                ->update([
                    'is_read' => true,
                ]);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->apiExceptionResponse(__('api/customers/chat/chats.something-wrong'));
        }
        DB::commit();

        try {
            //return with message
            return $this->apiResponse([
                'message' => __('api/customers/chat/chats.message-sent'),
                'data' => $message_resource,
            ]);
        } finally {
            $other_user->notifications()
                ->whereJsonContains('data->customProperties->chatToken', $chat->token)
                ->delete();

            $customProperties = [
                'chatToken' => $chat->token,
                'userId' => Auth::guard('customer-api')->id(),
                'userType' => 'customer',
                'body' => $message,
                'message_type' => $type,
            ];

            Notifications::sendForCommunity($other_user, 'chats', 'chats.message_received', 'add', $customProperties);
            if ($other_user->fcm_token && !$other_user->is_online) {
                FcmHelper::sendFcmNotification([
                    'title' => __('api/customers/chat/chats.fcm.title', ['name' => Str::limit(Auth::guard('customer-api')->user()->name, 20)]),
                    'body' => $message,
                ], [$other_user->fcm_token]);
            }

            try {
                $firebase = Firebase::firestore()
                    ->database();

                $firebase->collection('chats')
                    ->document($chat->token)
                    ->set($data);

            } catch (Exception $e) {

            }
        }
    }

    /**
     * @param $id
     * @return Application|ResponseFactory|Response
     */
    public function deleteMessage($id)
    {
        if (Auth::guard('customer-api')->user()->status === 'inactive') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-closed'));
        }

        $message = Auth::guard('customer-api')->user()
            ->messages()
            ->where('id', $id)
            ->first();

        if (!$message) {
            return $this->apiBadRequestResponse(__('api/customers/chat/chats.wrong-message-id'));
        }
        DB::beginTransaction();
        try {
            //set data
            $data = ChatMessagesResource::make($message)->resolve();
            $data['responseType'] = 'delete';

            //delete message
            $message->delete();
            //broadcast to others
            MessageSent::broadcast($data)->toOthers();
        } catch (Exception $e) {
            DB::rollBack();
            return $this->apiExceptionResponse(__('api/customers/chat/chats.something-wrong'));
        }
        DB::commit();
        return $this->apiResponse([
            'message' => __('api/customers/chat/chats.message-deleted'),
        ]);
    }

    /**
     * @param $token
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function getChatMessages($token, Request $request)
    {
        //get chats
        $chat = Auth::guard('customer-api')->user()
            ->chats()
            ->where('token', $token)
            ->first();

        //if chat doesn't exist return error
        if (!$chat) {
            return $this->apiBadRequestResponse(__('api/customers/chat/chats.wrong-token'));
        }

        //get limit
        $limit = ($request->has('limit') && $request->get('limit') > 0) ? $request->get('limit') : Settings::Get('chat.messages.pagination.limit', 20);

        //get messages
        $messages = $chat->messages()
            ->orderBy('created_at', 'desc')
            ->paginate($limit);

        $chat->messages()
            ->whereHasMorph('sender', '*', function ($q, $type) {
                if ($type === CustomerUser::class) {
                    return $q->where('id', '!=', Auth::guard('customer-api')->id());
                }
                return $q;
            })
            ->update([
                'is_read' => true,
            ]);

        try {
            //return chat
            return $this->apiPaginateResponse(ChatMessagesResource::collection($messages));
        } finally {
            Auth::guard('customer-api')->user()
                ->unreadNotifications()
                ->whereJsonContains('data->customProperties->chatToken', $token)
                ->get()
                ->markAsRead();

            $count = Auth::guard('customer-api')->user()
                ->unreadNotifications()
                ->count();

            Notifications::setNotificationsCount(Auth::guard('customer-api')->user(), $count);
        }
    }
}
