<?php

namespace App\Http\Livewire\MarketingTools;

use App\Helpers\Categories\CategoriesFilter;
use App\Helpers\Geography\Geography;
use App\Helpers\Notifications;
use App\Models\Categories\Category;
use App\Models\Countries\Governorates\Governorate;
use App\Models\Users\Advertisers\AdvertiserUser;
use App\Models\Users\Advertisers\Categories\AdvertiserInterests;
use App\Models\Users\Advertisers\Locations\AdvertiserPreferredCity;
use App\Models\Users\Advertisers\Locations\AdvertiserPreferredGovernorate;
use App\Models\Users\Customers\Categories\CustomerCategories;
use App\Models\Users\Customers\CustomerUser;
use App\Models\Users\Customers\Locations\CustomerPreferredCity;
use App\Models\Users\Customers\Locations\CustomerPreferredGovernorate;
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
    public ?array $categories = null;
    public ?array $governorates = null;
    public ?array $cities = null;
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

        $name_column = App::currentLocale() === 'ar' ? 'name_ar' : 'name_en';

        // NOTE: named "all_categories"/"all_governorates", NOT "categories"/"governorates" —
        // those names are already taken by this component's own public properties (the
        // admin's *selected* ids), and Livewire auto-shares public properties into the view,
        // silently overwriting any same-named view data with the (usually null) property value.
        $all_categories = Category::whereNull('parent_category_id')
            ->orderBy('order')
            ->with('childCategories')
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->{$name_column},
                'children' => $c->childCategories->map(fn ($ch) => [
                    'id' => $ch->id,
                    'name' => $ch->{$name_column},
                ]),
            ]);

        $all_governorates = Governorate::with('cities')
            ->orderBy('order')
            ->get()
            ->map(fn ($g) => [
                'id' => $g->id,
                'name' => $g->{$name_column},
                'cities' => $g->cities->map(fn ($c) => [
                    'id' => $c->id,
                    'name' => $c->{$name_column},
                ]),
            ]);

        return view('livewire.pages.marketing-tools.send-notifications-component', [
            'all_advertisers' => $all_advertisers,
            'all_customers' => $all_customers,
            'all_categories' => $all_categories,
            'all_governorates' => $all_governorates,
        ]);
    }


    /**
     * Send Notification
     * @return void|null
     */
    public function sendNotification()
    {
        $this->validate([
            'recipients_type'   => ['required', 'in:all_users,all_advertisers,all_customers,specific_advertisers,specific_customers,interested'],
            'recipients'        => ['nullable',],
            'categories'        => ['nullable',],
            'governorates'      => ['nullable',],
            'cities'            => ['nullable',],
            'subject'           => ['required', 'string'],
            'subject_en'        => ['required', 'string'],
            'body'              => ['required', 'string'],
            'body_en'           => ['required', 'string'],
            'notify_link'       => ['nullable', 'string', 'url'],
            'image'             => ['nullable', 'string', 'url'],
        ]);

        $sentCount = 0;
        $pushAttempted = 0;
        $pushDelivered = 0;
        $error = '';

        DB::beginTransaction();
        try {
            if ($this->recipients_type === 'all_users') {
                $tokens = null;

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
                    ->whereIn('id', $this->recipients ?? [])
                    ->where('is_accepted_send_notifications', true)
                    ->pluck('fcm_token')
                    ->toArray();
                $users = AdvertiserUser::where('status', 'active')
                    ->whereIn('id', $this->recipients ?? [])
                    ->get();
            } elseif ($this->recipients_type === 'specific_customers') {
                if (!$this->recipients) {
                    $error = __('pages/marketing-tools/notifications.errors.no-customers');
                }

                $tokens = CustomerUser::whereNotNull('fcm_token')
                    ->whereIn('id', $this->recipients ?? [])
                    ->where('is_accepted_send_notifications', true)
                    ->pluck('fcm_token')
                    ->toArray();

                $users = CustomerUser::where('status', 'active')
                    ->whereIn('id', $this->recipients ?? [])
                    ->get();
            } elseif ($this->recipients_type === 'interested') {
                if (empty($this->categories) && empty($this->governorates) && empty($this->cities)) {
                    $error = __('pages/marketing-tools/notifications.errors.interested');
                }

                $categoryIds = !empty($this->categories) ? CategoriesFilter::expandCategoryIds($this->categories) : null;

                $governorateIds = $this->governorates ?? [];
                $cityIds = array_unique(array_merge(
                    $this->cities ?? [],
                    Geography::expandGovernorateIdsToCities($governorateIds)
                ));

                $customerQuery = CustomerUser::where('status', 'active');
                $advertiserQuery = AdvertiserUser::where('status', 'active');

                if ($categoryIds !== null) {
                    $customerQuery->whereIn('id', CustomerCategories::whereIn('category_id', $categoryIds)->pluck('customer_id'));
                    $advertiserQuery->whereIn('id', AdvertiserInterests::whereIn('category_id', $categoryIds)->pluck('advertiser_id'));
                }

                $all_customers = Geography::candidatesInterestedInLocations(
                    $customerQuery->pluck('id'), CustomerPreferredGovernorate::class, CustomerPreferredCity::class, 'customer_id', $governorateIds, $cityIds
                );
                $all_advertisers = Geography::candidatesInterestedInLocations(
                    $advertiserQuery->pluck('id'), AdvertiserPreferredGovernorate::class, AdvertiserPreferredCity::class, 'advertiser_id', $governorateIds, $cityIds
                );

                $all_customers = CustomerUser::whereIn('id', $all_customers)->get();
                $all_advertisers = AdvertiserUser::whereIn('id', $all_advertisers)->get();

                $tokens = null;
            } else {
                $tokens = null;
                $users = null;
            }

            $customProperties = [
                'title'         => $this->subject,
                'title_en'      => $this->subject_en,
                'body_en'       => $this->body_en,
                'notify_link'   => $this->notify_link,
                'image'         => $this->image,
            ];

            if (isset($all_advertisers) && $all_advertisers->count() > 0) {
                $result = Notifications::sendFromAdmin($all_advertisers, 'admin.notification', $this->body, 'add', $customProperties);
                $sentCount += $result['notified'];
                $pushAttempted += $result['push_attempted'];
                $pushDelivered += $result['push_delivered'];
            }
            if (isset($all_customers) && $all_customers->count() > 0) {
                $result = Notifications::sendFromAdmin($all_customers, 'admin.notification', $this->body, 'add', $customProperties);
                $sentCount += $result['notified'];
                $pushAttempted += $result['push_attempted'];
                $pushDelivered += $result['push_delivered'];
            }
            if (isset($users) && $users->count() > 0) {
                $result = Notifications::sendFromAdmin($users, 'admin.notification', $this->body, 'add', $customProperties);
                $sentCount += $result['notified'];
                $pushAttempted += $result['push_attempted'];
                $pushDelivered += $result['push_delivered'];
            }

            $this->tokens = $tokens;

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();

            //send toastr alert with error
            $this->alert('error', __('toastr.error'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                'text' => $e->getMessage(),
            ]);

            return null;
        }

        if ($sentCount === 0) {
            if (empty($error)) {
                $error = __("pages/marketing-tools/notifications.errors.{$this->recipients_type}");
            }
            //send toastr alert with error
            $this->alert('error', __('toastr.error'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                'text' => $error,
            ]);

            return null;
        }

        // A device push failing silently used to be invisible — the in-app notification
        // always succeeded, making the whole send "look" fine even when every push failed.
        // Surface it explicitly whenever we tried to push to at least one device and most
        // (or all) of those attempts didn't actually deliver.
        if ($pushAttempted > 0 && $pushDelivered < $pushAttempted) {
            $this->alert('warning', __('pages/marketing-tools/notifications.content.title'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                'text' => __('pages/marketing-tools/notifications.warnings.push_partial_failure', [
                    'notified' => $sentCount,
                    'attempted' => $pushAttempted,
                    'delivered' => $pushDelivered,
                ]),
            ]);
        } else {
            //send toastr alert with success
            $this->alert('success', __('toastr.sent', ['type' => __('pages/marketing-tools/notifications.name')]), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
        }

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
        $this->categories = null;
        $this->governorates = null;
        $this->cities = null;
        $this->dispatchBrowserEvent('clear-select');
    }
}
