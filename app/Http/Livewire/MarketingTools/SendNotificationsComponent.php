<?php

namespace App\Http\Livewire\MarketingTools;

use App\Helpers\Categories\CategoriesFilter;
use App\Helpers\Geography\LocationTree;
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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Throwable;

class SendNotificationsComponent extends Component
{
    use LivewireAlert;

    public const RECIPIENT_TYPES = ['all_users', 'all_advertisers', 'all_customers', 'specific_advertisers', 'specific_customers', 'interested'];
    public const AUDIENCES = ['both', 'advertisers', 'customers'];

    public string $recipients_type = 'all_users';
    // who an "interested" send reaches: advertisers, customers or both
    public string $audience = 'both';
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

    public function updatedRecipientsType(): void
    {
        // a list of advertiser ids must never be read as customer ids (or vice versa)
        $this->recipients = null;
        $this->resetValidation('recipients');
    }

    public function render()
    {
        $name_column = App::currentLocale() === 'ar' ? 'name_ar' : 'name_en';

        // Every active account is selectable, not only those with a registered device:
        // the in-app notification still reaches them. The label says who gets a push.
        $all_advertisers = $this->recipients_type === 'specific_advertisers'
            ? $this->selectableUsers(AdvertiserUser::query())
            : collect();
        $all_customers = $this->recipients_type === 'specific_customers'
            ? $this->selectableUsers(CustomerUser::query())
            : collect();

        // NOTE: named "all_categories"/"all_governorates", NOT "categories"/"governorates" —
        // those names are already taken by this component's own public properties (the
        // admin's *selected* ids), and Livewire auto-shares public properties into the view,
        // silently overwriting any same-named view data with the (usually null) property value.
        $all_categories = collect();
        $all_governorates = collect();
        if ($this->recipients_type === 'interested') {
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
        }

        return view('livewire.pages.marketing-tools.send-notifications-component', [
            'all_advertisers' => $all_advertisers,
            'all_customers' => $all_customers,
            'all_categories' => $all_categories,
            'all_governorates' => $all_governorates,
            'preview' => $this->preview(),
        ]);
    }

    /**
     * Who the current selection reaches, shown live above the send button so the admin
     * sees the exact audience before sending.
     */
    private function preview(): array
    {
        $resolved = $this->resolveRecipients();

        $count = fn (?Builder $q) => $q ? (clone $q)->count() : 0;
        $pushable = fn (?Builder $q) => $q
            ? (clone $q)->whereNotNull('fcm_token')->where('is_accepted_send_notifications', true)->count()
            : 0;

        return [
            'advertisers' => $count($resolved['advertisers']),
            'customers' => $count($resolved['customers']),
            'push' => $pushable($resolved['advertisers']) + $pushable($resolved['customers']),
            'needs_selection' => $resolved['needs_selection'],
        ];
    }

    private function selectableUsers(Builder $query): Collection
    {
        return $query->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'username', 'mobile', 'fcm_token', 'is_accepted_send_notifications'])
            ->map(fn ($user) => [
                'id' => $user->id,
                'name' => trim($user->name . ($user->username ? ' @' . $user->username : '') . ($user->mobile ? ' — ' . $user->mobile : '')),
                'push' => $user->fcm_token && $user->is_accepted_send_notifications,
            ]);
    }

    /**
     * Single source of truth for the audience, used by both the live preview and the send.
     *
     * @return array{advertisers: ?Builder, customers: ?Builder, needs_selection: bool}
     */
    private function resolveRecipients(): array
    {
        $advertisers = AdvertiserUser::query()->where('status', 'active');
        $customers = CustomerUser::query()->where('status', 'active');
        $ids = array_filter((array) $this->recipients);

        switch ($this->recipients_type) {
            case 'all_users':
                return ['advertisers' => $advertisers, 'customers' => $customers, 'needs_selection' => false];

            case 'all_advertisers':
                return ['advertisers' => $advertisers, 'customers' => null, 'needs_selection' => false];

            case 'all_customers':
                return ['advertisers' => null, 'customers' => $customers, 'needs_selection' => false];

            case 'specific_advertisers':
                return ['advertisers' => $advertisers->whereIn('id', $ids), 'customers' => null, 'needs_selection' => empty($ids)];

            case 'specific_customers':
                return ['advertisers' => null, 'customers' => $customers->whereIn('id', $ids), 'needs_selection' => empty($ids)];

            case 'interested':
                if (empty($this->categories) && empty($this->governorates) && empty($this->cities)) {
                    return ['advertisers' => null, 'customers' => null, 'needs_selection' => true];
                }

                if (!empty($this->categories)) {
                    $categoryIds = CategoriesFilter::expandCategoryIds($this->categories);
                    $advertisers->whereIn('id', AdvertiserInterests::whereIn('category_id', $categoryIds)->select('advertiser_id'));
                    $customers->whereIn('id', CustomerCategories::whereIn('category_id', $categoryIds)->select('customer_id'));
                }

                $this->applyLocationFilter($advertisers, 'advertisers_users', AdvertiserPreferredGovernorate::class, AdvertiserPreferredCity::class, 'advertiser_id');
                $this->applyLocationFilter($customers, 'customers_users', CustomerPreferredGovernorate::class, CustomerPreferredCity::class, 'customer_id');

                return [
                    'advertisers' => $this->audience === 'customers' ? null : $advertisers,
                    'customers' => $this->audience === 'advertisers' ? null : $customers,
                    'needs_selection' => false,
                ];
        }

        return ['advertisers' => null, 'customers' => null, 'needs_selection' => true];
    }

    /**
     * Strict location targeting: a user is reached only when one of their preferred
     * governorates/cities matches the selection, or — if they saved no preference at all —
     * when the governorate/city on their own profile does. Users with no location are left
     * out, unlike the feed's matcher (Geography::candidatesInterestedInLocations), which
     * treats "no preference" as "everywhere" and made a city-targeted send reach everyone.
     *
     * The hierarchy is walked both ways: picking a governorate reaches followers of its
     * cities, picking a city reaches followers of its governorate.
     */
    private function applyLocationFilter(Builder $query, string $table, string $governorateModel, string $cityModel, string $ownerColumn): void
    {
        $governorateIds = array_map('intval', array_filter((array) $this->governorates));
        $cityIds = array_map('intval', array_filter((array) $this->cities));
        if (empty($governorateIds) && empty($cityIds)) {
            return;
        }

        $matchGovernorateIds = array_values(array_unique(array_merge($governorateIds, LocationTree::governorateIdsOfCities($cityIds))));
        $matchCityIds = array_values(array_unique(array_merge($cityIds, LocationTree::cityIdsOfGovernorates($governorateIds))));

        $query->where(function (Builder $q) use ($table, $governorateModel, $cityModel, $ownerColumn, $matchGovernorateIds, $matchCityIds, $governorateIds, $cityIds) {
            // 1) saved location preferences
            $q->whereIn("$table.id", $governorateModel::whereIn('governorate_id', $matchGovernorateIds ?: [0])->select($ownerColumn))
                ->orWhereIn("$table.id", $cityModel::whereIn('city_id', $matchCityIds ?: [0])->select($ownerColumn))
                // 2) no preferences saved: fall back to the profile's own location, matched
                //    exactly (living in a sibling city of the chosen one does not count)
                ->orWhere(function (Builder $q) use ($table, $governorateModel, $cityModel, $ownerColumn, $governorateIds, $cityIds) {
                    $q->whereNotIn("$table.id", $governorateModel::select($ownerColumn))
                        ->whereNotIn("$table.id", $cityModel::select($ownerColumn))
                        ->where(function (Builder $q) use ($table, $governorateIds, $cityIds) {
                            if (!empty($cityIds)) {
                                $q->orWhereIn("$table.city_id", $cityIds);
                            }
                            if (!empty($governorateIds)) {
                                $q->orWhereIn("$table.governorate_id", $governorateIds);
                            }
                        });
                });
        });
    }

    /**
     * Send Notification
     * @return void|null
     */
    public function sendNotification()
    {
        $this->validate([
            'recipients_type'   => ['required', 'in:' . implode(',', self::RECIPIENT_TYPES)],
            'audience'          => ['required', 'in:' . implode(',', self::AUDIENCES)],
            'recipients'        => ['nullable', 'array', 'required_if:recipients_type,specific_advertisers,specific_customers'],
            'categories'        => ['nullable', 'array'],
            'governorates'      => ['nullable', 'array'],
            'cities'            => ['nullable', 'array'],
            'subject'           => ['required', 'string'],
            'subject_en'        => ['required', 'string'],
            'body'              => ['required', 'string'],
            'body_en'           => ['required', 'string'],
            'notify_link'       => ['nullable', 'string', 'url'],
            'image'             => ['nullable', 'string', 'url'],
        ], [
            'recipients.required_if' => $this->recipients_type === 'specific_customers'
                ? __('pages/marketing-tools/notifications.errors.no-customers')
                : __('pages/marketing-tools/notifications.errors.no-advertisers'),
        ]);

        $resolved = $this->resolveRecipients();
        if ($resolved['needs_selection']) {
            $this->alert('error', __('toastr.error'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                'text' => __("pages/marketing-tools/notifications.errors.{$this->recipients_type}"),
            ]);

            return null;
        }

        $sentCount = 0;
        $pushAttempted = 0;
        $pushDelivered = 0;
        $pushErrorSample = null;

        $customProperties = [
            'title'         => $this->subject,
            'title_en'      => $this->subject_en,
            'body_en'       => $this->body_en,
            'notify_link'   => $this->notify_link,
            'image'         => $this->image,
        ];

        DB::beginTransaction();
        try {
            foreach (['advertisers', 'customers'] as $group) {
                if (!$resolved[$group]) {
                    continue;
                }

                $users = $resolved[$group]->get();
                if ($users->isEmpty()) {
                    continue;
                }

                $result = Notifications::sendFromAdmin($users, 'admin.notification', $this->body, 'add', $customProperties, true);
                $sentCount += $result['notified'];
                $pushAttempted += $result['push_attempted'];
                $pushDelivered += $result['push_delivered'];
                $pushErrorSample = $pushErrorSample ?? $result['push_error_sample'];
            }

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
            //send toastr alert with error
            $this->alert('error', __('toastr.error'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                'text' => __("pages/marketing-tools/notifications.errors.{$this->recipients_type}"),
            ]);

            return null;
        }

        // A device push failing silently used to be invisible — the in-app notification
        // always succeeded, making the whole send "look" fine even when every push failed.
        // Surface it explicitly whenever we tried to push to at least one device and most
        // (or all) of those attempts didn't actually deliver.
        if ($pushAttempted > 0 && $pushDelivered < $pushAttempted) {
            $warningText = __('pages/marketing-tools/notifications.warnings.push_partial_failure', [
                'notified' => $sentCount,
                'attempted' => $pushAttempted,
                'delivered' => $pushDelivered,
            ]);
            if ($pushErrorSample) {
                $warningText .= ' — ' . __('pages/marketing-tools/notifications.warnings.push_error_sample', [
                    'error' => $pushErrorSample,
                ]);
            }
            $this->alert('warning', __('pages/marketing-tools/notifications.content.title'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                'text' => $warningText,
            ]);
        } else {
            //send toastr alert with success
            $this->alert('success', __('toastr.sent', ['type' => __('pages/marketing-tools/notifications.name')]), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                'text' => __('pages/marketing-tools/notifications.content.sent_summary', [
                    'notified' => $sentCount,
                    'push' => $pushDelivered,
                ]),
            ]);
        }

        $this->reset([
            'recipients_type',
            'audience',
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
