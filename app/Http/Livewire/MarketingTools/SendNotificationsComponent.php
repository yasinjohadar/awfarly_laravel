<?php

namespace App\Http\Livewire\MarketingTools;

use App\Helpers\FCM\FcmHelper;
use App\Helpers\Notifications;
use App\Models\Users\Advertisers\AdvertiserUser;
use App\Models\Users\Customers\CustomerUser;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Throwable;

class SendNotificationsComponent extends Component
{
    use LivewireAlert;


    public string $recipients_type = 'all_users';
    public ?array $recipients = null;
    public ?string $subject = null;
    public ?string $subject_en = null;
    public ?string $notify_link = null;
    public ?string $body = null;
    public ?string $body_en = null;
    public ?string $image = null;
    public ?array $tokens = null;

    public function render()
    {
        $all_advertisers = AdvertiserUser::whereNotNull('fcm_token')
            ->where('is_accepted_send_notifications', true)
            ->get()
            ->map(function ($advertiser) {
                return [
                    'id' => $advertiser->id,
                    'name' => $advertiser->name,
                ];
            });

        $all_customers = CustomerUser::whereNotNull('fcm_token')
            ->where('is_accepted_send_notifications', true)
            ->get()
            ->map(function ($customers) {
                return [
                    'id' => $customers->id,
                    'name' => $customers->name,
                ];
            });

        return view('livewire.pages.marketing-tools.send-notifications-component', [
            'all_advertisers' => $all_advertisers,
            'all_customers' => $all_customers,
        ]);
    }


    /**
     * Send Notification
     * @return void|null
     */
    public function sendNotification()
    {
        $this->validate([
            'recipients_type'   => ['required', 'in:all_users,all_advertisers,all_customers,specific_advertisers,specific_customers'],
            'recipients'        => ['nullable',],
            'subject'           => ['required', 'string'],
            'subject_en'        => ['required', 'string'],
            'body'              => ['required', 'string'],
            'body_en'           => ['required', 'string'],
            'notify_link'       => ['nullable', 'string', 'url'],
            'image'             => ['nullable', 'string', 'url'],
        ]);
        DB::beginTransaction();
        try {
            $is_notifications_sent = false;
            $error = '';
            if ($this->recipients_type === 'all_users') {
                $customers = CustomerUser::whereNotNull('fcm_token')->where('status', 'active')->where('is_accepted_send_notifications', true)->pluck('fcm_token')->toArray();

                $advertisers = AdvertiserUser::whereNotNull('fcm_token')->where('status', 'active')->where('is_accepted_send_notifications', true)->pluck('fcm_token')->toArray();

                $tokens = array_merge($customers, $advertisers);

                $all_advertisers = AdvertiserUser::select('id')
                    ->where('advertisers_users.status', 'active')
                    ->get();

                $all_customers = CustomerUser::select('id')
                    ->where('status', 'active')
                    ->get();
            } elseif ($this->recipients_type === 'all_advertisers') {
                $tokens = AdvertiserUser::whereNotNull('fcm_token')
                    ->where('is_accepted_send_notifications', true)
                    ->pluck('fcm_token')
                    ->toArray();

                $users = AdvertiserUser::where('status', 'active')
                    ->get();
            } elseif ($this->recipients_type === 'all_customers') {
                $tokens = CustomerUser::whereNotNull('fcm_token')
                    ->where('is_accepted_send_notifications', true)
                    ->pluck('fcm_token')
                    ->toArray();

                $users = CustomerUser::where('status', 'active')
                    ->get();
            } elseif ($this->recipients_type === 'specific_advertisers') {
                if (!$this->recipients) {
                    $error = __('pages/marketing-tools/notifications.errors.no-advertisers');
                }

                $tokens = AdvertiserUser::whereNotNull('fcm_token')
                    ->whereIn('id', $this->recipients)
                    ->where('is_accepted_send_notifications', true)
                    ->pluck('fcm_token')
                    ->toArray();
                $users = AdvertiserUser::where('status', 'active')
                    ->whereIn('id', $this->recipients)
                    ->get();
            } elseif ($this->recipients_type === 'specific_customers') {
                if (!$this->recipients) {
                    $error = __('pages/marketing-tools/notifications.errors.no-customers');
                }

                $tokens = CustomerUser::whereNotNull('fcm_token')
                    ->whereIn('id', $this->recipients)
                    ->where('is_accepted_send_notifications', true)
                    ->pluck('fcm_token')
                    ->toArray();

                $users = CustomerUser::where('status', 'active')
                    ->whereIn('id', $this->recipients)
                    ->get();
            } else {
                $tokens = null;
                $users = null;
            }

            $customProperties = [
                'title'         => $this->subject,
                'title_en'         => $this->subject_en,
                'body_en'         => $this->body_en,
                'notify_link'   => $this->notify_link,
            ];

            if (isset($all_advertisers) && $all_advertisers->count() > 0 && isset($all_customers) && $all_customers->count() > 0) {
                Notifications::sendFromAdmin($all_advertisers, 'admin.notification', $this->body, 'add', $customProperties);
                Notifications::sendFromAdmin($all_customers, 'admin.notification', $this->body, 'add', $customProperties);
            } else if (isset($users) && sizeof($users) > 0) {
                Notifications::sendFromAdmin($users, 'admin.notification', $this->body, 'add', $customProperties);
            }
            $this->tokens = $tokens;
        } catch (Throwable $e) {
            DB::rollBack();
            dd($e);
            //send toastr alert with error
            $this->alert('error', __('toastr.error'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                'text' => $e->getMessage(),
            ]);

            return null;
        } finally {
            DB::commit();
            if ($this->tokens && count($this->tokens) > 0) {
                foreach($this->tokens as $token){

                $is_notifications_sent = FcmHelper::sendFcmNotification([
                    'title' => $this->subject,
                    'title_en' => $this->subject_en,
                    'body' => $this->body,
                    'body_en' => $this->body_en,
                    'image' => $this->image,
                ], [$token]);
                }
            }

            if (!$is_notifications_sent) {
                if (empty($error)) {
                    $error = __("pages/marketing-tools/notifications.errors.{$this->recipients_type}");
                }
                //send toastr alert with error
                $this->alert('error', __('toastr.error'), [
                    'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                    'text' => $error,
                ]);

                return null;
            } else {
                //send toastr alert with success
                $this->alert('success', __('toastr.sent', ['type' => __('pages/marketing-tools/notifications.name')]), [
                    'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                ]);

                $this->reset([
                    'recipients_type',
                    'subject',
                    'subject_en',
                    'body',
                    'body_en',
                    'image',
                    'notify_link',
                    'tokens',
                ]);
                $this->resetValidation();
                $this->recipients = null;
                $this->dispatchBrowserEvent('clear-select');
            }
        }
    }
}
