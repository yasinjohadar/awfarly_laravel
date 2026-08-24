<?php

namespace App\Http\Livewire\Advertisers;

use Auth;
use Hash;
use Exception;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Throwable;
use Carbon\Carbon;
use App\Helpers\Files;
use App\Helpers\Filter;
use App\Helpers\Settings;
use Livewire\WithFileUploads;
use App\Helpers\Admins\AdminLogs;
use App\Helpers\Advertisers\PackageQuotas;
use App\Models\Countries\Country;
use App\Models\Languages\Language;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\Rule;
use App\Models\Countries\Cities\City;
use App\Models\Countries\Governorates\Governorate;
use App\Models\Subscriptions\Packages\Package;
use Mediconesystems\LivewireDatatables\Column;
use App\Models\Users\Advertisers\AdvertiserUser;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\NumberColumn;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use App\Models\Users\Advertisers\BusinessTypes\AdvertiserBusinessType;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;

class AdvertisersInquiryComponent extends LivewireDatatable
{

    use WithFileUploads;
    use LivewireAlert;

    /**
     * set variables
     */
    public $exportable = true;
    public $hideable = 'select';
    public $beforeTableSlot = 'livewire.datatables.selected';
    public $afterTableSlot = 'modals.users.advertisers.edit';
    public string $afterTableSlot2 = 'modals.users.advertisers.assign-package';
    public string $afterTableSlot3 = 'modals.users.advertisers.categories';
    public $model = AdvertiserUser::class;
    public array $user;
    public Collection $languages;
    public Collection $countries;
    public Collection $governorates;
    public Collection $cities;
    public Collection $business_types;
    public Collection $packages;
    public Collection $viewed_categories;
    public bool $showDeleteModal = false;
    public bool $showEditModal = false;
    public bool $showAssignPackageModal = false;
    public bool $showStatusModal = false;
    public bool $showCategoriesModal = false;
    public array $deleteModalTexts;
    public array $editModalTexts;
    public array $assignPackageModalTexts;
    public array $statusModalTexts = [];
    public ?string $viewed_user_name = null;
    private string $country_column = '';
    public bool $has_delete = true;
    public ?string $country_code = null;
    public ?string $governorate_id = null;
    public ?string $business_type = null;
    public ?string $package_id = null;
    public ?string $user_package_id = null;
    public ?int $assign_advertiser_id = null;
    public ?string $assign_advertiser_name = null;
    public ?string $assign_package_id = null;
    public ?int $status_advertiser_id = null;
    public ?string $status_advertiser_name = null;
    public ?string $status_target = null;

    protected $listeners = [
        'setBusinessType' => 'setBusinessType',
        'setCountry',
        'setGovernorate',
    ];

    /**
     * AdvertisersInquiryComponent constructor.
     * @param null $id
     */
    public function __construct($id = null)
    {
        //get admin language
        $this->getAdminLanguage();

        $this->country_code = 'SA';
        $this->viewed_categories = new Collection();
        //set modal texts
        $this->setModalTexts();

        parent::__construct($id);
    }

    /**
     * @param null $model
     * @param array $include
     * @param array $exclude
     * @param array $hide
     * @param array $dates
     * @param array $times
     * @param array $searchable
     * @param null $sort
     * @param null $hideHeader
     * @param null $hidePagination
     * @param int $perPage
     * @param false $exportable
     * @param false $hideable
     * @param false $beforeTableSlot
     * @param false $afterTableSlot
     * @param array $params
     */
    public function mount($model = null, $include = [], $exclude = [], $hide = [], $dates = [], $times = [], $searchable = [], $sort = null, $hideHeader = null, $hidePagination = null, $perPage = 10, $exportable = false, $hideable = false, $beforeTableSlot = false, $afterTableSlot = false, $params = [])
    {
        //select all languages
        $languages = Language::select('name', 'code')
            ->get();

        $this->languages = $languages->mapWithKeys(function ($languages, $key) {
            return [$languages->code => $languages->name];
        });

        //get all countries
        $this->countries = Country::select(
            "$this->country_column",
            'code'
        )
            ->whereHas('governorates')
            ->get()
            ->keyBy(function ($value) {
                return $value->code;
            })
            ->map(function ($countries) {
                return [
                    'id' => $countries->code,
                    'value' => $countries[$this->country_column],
                ];
            });

        $this->governorates = new Collection();
        $this->cities = new Collection();

        //select all business types
        $this->business_types = AdvertiserBusinessType::select('id', "$this->country_column")
            ->get()
            ->map(function ($type) {
                return [
                    'id' => $type->id,
                    'name' => $type->{$this->country_column}
                ];
            });

        $this->packages = Package::where('is_active', true)
            ->get()
            ->map(function ($package) {
                return [
                    'id' => $package->id,
                    'name' => $package->{$this->country_column} . "(#{$package->id})",
                ];
            });

        parent::mount($model, $include, $exclude, $hide, $dates, $times, $searchable, $sort, $hideHeader, $hidePagination, $perPage, $exportable, $hideable, $beforeTableSlot, $afterTableSlot, $params);
    }


    /**
     * set columns to render
     * @return array
     */
    public function columns(): array
    {
        return [
            Column::checkbox(),
            NumberColumn::name('id')
                ->label('#')
                ->filterable()
                ->searchable(),
            Column::callback(['image', 'name'], function ($image, $name) {
                return '<a href=' . route('users.profile.image', $image) . ' target="_blank"><img class="rounded-circle" width="34" height="34" src=' . route('users.profile.image', $image) . ' alt="' . $name . '"/></a>';
            })
                ->label(__('pages/advertisers/index.content.datatable.image')),
            Column::name("business.$this->country_column")
                ->label(__('pages/advertisers/index.content.datatable.business_type'))
                ->filterable()
                ->searchable(),
            Column::name('name')
                ->label(__('pages/advertisers/index.content.datatable.name'))
                ->filterable()
                ->searchable(),
            Column::name('discount_percentage')
                ->label(__('pages/advertisements/inquiry.content.datatable.discount_percentage'))
                ->filterable()
                ->searchable()
                ->alignCenter()
                ->round(2),
            Column::callback('email', function ($email) {
                return $email ? "<a dir='ltr' class='ltr' href='mailto:{$email}'>{$email}</a>" : '-';
            })
                ->label(__('pages/advertisers/index.content.datatable.email'))
                ->filterable()
                ->searchable()
                ->hide(),
            Column::callback('mobile', function ($mobile) {
                return "<a dir='ltr' class='ltr' href='tel:{$mobile}'>{$mobile}</a>";
            })
                ->label(__('pages/advertisers/index.content.datatable.mobile'))
                ->filterable()
                ->searchable(),
            Column::name('bio')
                ->label(__('pages/advertisers/index.content.datatable.bio'))
                ->filterable()
                ->searchable()
                ->hide(),
            Column::callback('username', function ($username) {
                return $username ?: '-';
            })
                ->label(__('pages/advertisers/index.content.datatable.username'))
                ->filterable()
                ->searchable()
                ->hide(),
            Column::name("governorates.$this->country_column")
                ->label(__('pages/advertisers/index.content.datatable.governorate'))
                ->filterable($this->all_governorates)
                ->searchable()
                ->hide(),
            Column::name("cities.$this->country_column")
                ->label(__('pages/advertisers/index.content.datatable.city'))
                ->filterable($this->all_cities)
                ->searchable()
                ->hide(),
            Column::name('languages.name')
                ->label(__('pages/advertisers/index.content.datatable.language'))
                ->filterable($this->all_languages)
                ->searchable()
                ->hide(),
            Column::callback('rate', function ($rate) {
                return $rate ?: '-';
            })
                ->label(__('pages/advertisers/index.content.datatable.rate'))
                ->filterable()
                ->searchable()
                ->hide(),
            Column::callback(['id', 'username'], function ($id, $username) {
                $advertiser = AdvertiserUser::withTrashed()->where('id', $id)
                    ->first();

                $package = $advertiser->packages()
                    ->where('is_current', true)
                    ->where('is_active', true)
                    ->where('is_ended', false)
                    ->where('ends_at', '>', now())
                    ->with('package')
                    ->first();

                if (!$package || !$package->package) {
                    return '<span class="badge badge-secondary">' . e(__('pages/advertisers/index.content.datatable.no_package')) . '</span>';
                }

                $name = $package->package->{$this->country_column}
                    ?: ($package->package->name_ar ?: $package->package->name_en);

                return '<span class="badge badge-primary">' . e($name) . '</span>';
            })
                ->label(__('pages/advertisers/index.content.datatable.package'))
                ->unsortable(),
            Column::name('contact_number')
                ->label(__('pages/advertisers/index.content.datatable.contact_number'))
                ->filterable()
                ->searchable()
                ->hide(),
            Column::name('whatsapp_number')
                ->label(__('pages/advertisers/index.content.datatable.whatsapp_number'))
                ->filterable()
                ->searchable()
                ->hide(),
            Column::name('facebook_url')
                ->label(__('pages/advertisers/index.content.datatable.facebook_url'))
                ->filterable()
                ->searchable()
                ->hide(),
            Column::name('twitter_url')
                ->label(__('pages/advertisers/index.content.datatable.twitter_url'))
                ->filterable()
                ->searchable()
                ->hide(),
            Column::name('website_url')
                ->label(__('pages/advertisers/index.content.datatable.website_url'))
                ->filterable()
                ->searchable()
                ->hide(),
            BooleanColumn::name('is_elite')
                ->label(__('pages/advertisers/index.content.datatable.is_elite'))
                ->filterable()
                ->searchable(),
            BooleanColumn::name('is_accepted_send_notifications')
                ->label(__('pages/advertisers/index.content.datatable.accepted_send_notification'))
                ->filterable()
                ->searchable()
                ->hide(),
            Column::callback('last_login_at', function ($last_login_at) {
                return $last_login_at ? Carbon::make($last_login_at)->diffForHumans() : '-';
            })
                ->label(__('pages/advertisers/index.content.datatable.last_login_at'))
                ->searchable()
                ->hide(),
            Column::callback('is_accepted_send_notifications', function ($is_accepted_send_notifications) {
                if ($is_accepted_send_notifications) {
                    return '<div class="badge badge-success">' . __('pages/customers/index.content.datatable.notifications_types.allowed') . '</div>';
                } else {
                    return '<div class="badge badge-danger">' . __('pages/customers/index.content.datatable.notifications_types.not-allowed') . '</div>';
                }
            })
                ->label(__('pages/customers/index.content.datatable.notifications')),
            DateColumn::name('updated_at')
                ->format('Y-m-d h:i A')
                ->label(__('pages/advertisers/index.content.datatable.updated_at'))
                ->searchable()
                ->hide(),
            DateColumn::name('created_at')
                ->format('Y-m-d h:i A')
                ->label(__('pages/advertisers/index.content.datatable.created_at'))
                ->searchable()
                ->hide(),
            Column::callback(['id', 'name', 'status', 'deleted_at'], function ($id, $name, $status, $deleted_at) {
                return view('admin.pages.advertisers.table-actions', [
                    'id' => $id,
                    'name' => $name,
                    'status' => $status,
                    'deleted_at' => $deleted_at,
                ]);
            })
                ->label(__('datatable.actions'))
                ->excludeFromExport()
                ->unsortable(),
        ];
    }

    /**
     * set query to render data
     * @return mixed
     */
    public function builder()
    {
        return AdvertiserUser::withTrashed()->leftJoin('languages', 'languages.code', 'advertisers_users.language_code')
            ->leftJoin('countries', 'countries.code', 'advertisers_users.country_code')
            ->leftJoin('governorates', 'governorates.id', 'advertisers_users.governorate_id')
            ->leftJoin('cities', 'cities.id', 'advertisers_users.city_id');
    }

    /**
     * get admin language
     */
    public function getAdminLanguage()
    {
        $country_column = Auth::guard('admin')->user()->language_code;
        if ($country_column === 'ar') {
            $this->country_column = 'name_ar';
        } else {
            $this->country_column = 'name_en';
        }
    }

    /**
     * @return mixed
     */
    public function getAllLanguagesProperty()
    {
        return Language::pluck('name');
    }

    /**
     * @return mixed
     */
    public function getAllCountriesProperty()
    {
        return Country::pluck($this->country_column);
    }

    public function getAllGovernoratesProperty()
    {
        return Governorate::pluck($this->country_column);
    }

    /**
     * @return mixed
     */
    public function getAllCitiesProperty()
    {
        return City::pluck($this->country_column);
    }

    /**
     * show delete modal for selected rows, or a single advertiser by id
     * @param int|null $id
     */
    public function showDeleteModal($id = null)
    {
        if ($id !== null) {
            $this->selected = [(string) $id];
        }

        if (empty($this->selected)) {
            return;
        }

        $this->setDeleteModalTextsForSelection();
        $this->showDeleteModal = true;
    }

    protected function setDeleteModalTextsForSelection(): void
    {
        $base = 'pages/advertisers/index.modal.delete';

        if (count($this->selected) === 1) {
            $advertiser = AdvertiserUser::withTrashed()->find($this->selected[0]);
            $name = $advertiser->name ?? '';

            $this->deleteModalTexts = [
                'title' => __("$base.title"),
                'content' => __("$base.content", ['name' => $name]),
                'cancel' => __("$base.cancel"),
                'submit' => __("$base.submit"),
            ];

            return;
        }

        $this->deleteModalTexts = [
            'title' => __("$base.title_multiple"),
            'content' => __("$base.content_multiple"),
            'cancel' => __("$base.cancel"),
            'submit' => __("$base.submit"),
        ];
    }

    /**
     * Soft-delete selected advertisers
     */
    public function deleteSelected()
    {
        if (!Auth::guard('admin')->user()->can('advertisers.delete')) {
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }

        if (empty($this->selected)) {
            $this->showDeleteModal = false;
            return null;
        }

        DB::beginTransaction();
        try {
            $advertisers = AdvertiserUser::withTrashed()->whereIn('id', $this->selected)->get();

            AdvertiserUser::whereIn('id', $this->selected)->delete();

            $this->selected = [];

            $this->alert('success', __('toastr.delete'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            $this->showDeleteModal = false;

            AdminLogs::log('delete', 'advertisers', [
                'advertisers' => $advertisers
            ], "Delete: advertisers");
        } catch (Throwable $e) {
            DB::rollBack();
            $this->alert('error', __('toastr.error'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                'text' => $e->getMessage(),
            ]);
            return null;
        }
        DB::commit();
    }

    public function showStatusModal($id, string $status): void
    {
        if (!in_array($status, ['inactive', 'banned'], true)) {
            return;
        }

        $advertiser = AdvertiserUser::withTrashed()->findOrFail($id);
        $this->status_advertiser_id = $advertiser->id;
        $this->status_advertiser_name = $advertiser->name;
        $this->status_target = $status;

        $key = $status === 'banned' ? 'freeze' : 'stop';
        $this->statusModalTexts = [
            'title' => __("pages/advertisers/index.modal.{$key}.title"),
            'content' => __("pages/advertisers/index.modal.{$key}.content", ['name' => $advertiser->name]),
            'cancel' => __("pages/advertisers/index.modal.{$key}.cancel"),
            'submit' => __("pages/advertisers/index.modal.{$key}.submit"),
        ];

        $this->showStatusModal = true;
    }

    public function closeStatusModal(): void
    {
        $this->showStatusModal = false;
        $this->status_advertiser_id = null;
        $this->status_advertiser_name = null;
        $this->status_target = null;
        $this->statusModalTexts = [];
    }

    public function confirmStatusChange()
    {
        if (!$this->status_advertiser_id || !$this->status_target) {
            $this->closeStatusModal();
            return null;
        }

        return $this->changeStatus($this->status_advertiser_id, $this->status_target, true);
    }

    public function changeStatus($id, string $status, bool $fromModal = false)
    {
        if (!Auth::guard('admin')->user()->can('advertisers.edit')) {
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }

        if (!in_array($status, ['active', 'inactive', 'banned'], true)) {
            return null;
        }

        DB::beginTransaction();
        try {
            $advertiser = AdvertiserUser::withTrashed()->findOrFail($id);
            $old = $advertiser->status;
            $advertiser->update(['status' => $status]);

            AdminLogs::log('edit', 'advertisers', [
                'advertiser_id' => $advertiser->id,
                'old_status' => $old,
                'new_status' => $status,
            ], "Change advertiser #{$advertiser->id} status to {$status}");

            if ($fromModal) {
                $this->closeStatusModal();
            }

            $this->alert('success', __('toastr.success'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
        } catch (Throwable $e) {
            DB::rollBack();
            $this->alert('error', __('toastr.error'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                'text' => $e->getMessage(),
            ]);
            return null;
        }
        DB::commit();
    }

    public function restoreAdvertiser($id)
    {
        if (!Auth::guard('admin')->user()->can('advertisers.edit')) {
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }

        DB::beginTransaction();
        try {
            $advertiser = AdvertiserUser::withTrashed()->findOrFail($id);
            $advertiser->restore();

            if ($advertiser->status !== 'active') {
                $advertiser->update(['status' => 'active']);
            }

            AdminLogs::log('edit', 'advertisers', [
                'advertiser_id' => $advertiser->id,
            ], "Restore advertiser #{$advertiser->id}");

            $this->alert('success', __('toastr.success'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
        } catch (Throwable $e) {
            DB::rollBack();
            $this->alert('error', __('toastr.error'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                'text' => $e->getMessage(),
            ]);
            return null;
        }
        DB::commit();
    }

    /**
     * show edit modal
     * @param $id
     */
    public function showEditModal($id)
    {
        $user = AdvertiserUser::withTrashed()->where('id', $id)
            ->first();

        //get user package
        $package = $user->packages()
            ->where('is_current', true)
            ->where('is_active', true)
            ->where('is_ended', false)
            ->where('ends_at', '>', now())
            ->first();

        if ($package) {
            $this->package_id = $package->package_id;
            $this->user_package_id = $package->package_id;
        } else {
            $this->package_id = null;
            $this->user_package_id = null;
        }

        //get user with data
        $this->user = $user->toArray();

        $this->user['password'] = '';

        //set dates to format (Y-m-d\TH:i) to render in the inputs
        $this->user['email_verified_at'] = $this->user['email_verified_at'] ? 1 : 0;
        $this->user['mobile_verified_at'] = $this->user['mobile_verified_at'] ? 1 : 0;

        //strip the decimal column's trailing zeros (e.g. "70.00" -> "70", "12.50" -> "12.5")
        if ($this->user['discount_percentage'] !== null) {
            $this->user['discount_percentage'] = rtrim(rtrim(number_format((float) $this->user['discount_percentage'], 2, '.', ''), '0'), '.');
        }

        $this->country_code = $this->user['country_code'];
        $this->governorate_id = $this->user['governorate_id'] ?? null;

        $governorates = Governorate::select("$this->country_column", 'id')
            ->where('country_code', $this->user['country_code'])
            ->get();

        $this->governorates = $governorates->mapWithKeys(function ($governorate) {
            return [$governorate->id => $governorate[$this->country_column]];
        });

        $cities = City::select("$this->country_column", 'id')
            ->where('governorate_id', $this->user['governorate_id'] ?? 0)
            ->get();

        $this->cities = $cities->mapWithKeys(function ($city) {
            return [$city->id => $city[$this->country_column]];
        });

        $this->business_type = $this->user['business_type'];
        $this->dispatchBrowserEvent('change-business', $this->business_type);
        $this->dispatchBrowserEvent('change-package-id', $this->user_package_id);
        //show the modal
        $this->showEditModal = true;
        //dispatch event to refresh select2
        $this->dispatchBrowserEvent('refreshSelect2Create');
        $this->dispatchBrowserEvent('change-country', [
            'country_code' => $this->user['country_code'],
            'governorate_id' => $this->user['governorate_id'] ?? null,
            'city_id' => $this->user['city_id'],
        ]);
    }

    /**
     * close the modal
     */
    public function closeEditModal()
    {
        //close the modal
        $this->showEditModal = false;

        //empty user data
        $this->user = [];

        //reset validation messages
        $this->resetValidation();
    }

    /**
     * update user data
     * @param $id
     * @return void|null
     */
    public function update($id)
    {
        if (!Auth::guard('admin')->user()->can('advertisers.edit')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }
        //validate data
        $this->validate([
            'user.name' => ['required', "unique:advertisers_users,name,$id"],
            'user.business_type' => ['nullable'],
            'user.email' => [
                'nullable',
                "unique:admins_users,email",
                "unique:advertisers_users,email,$id",
                "unique:customers_users,email"
            ],
            'user.mobile' => [
                'required',
                "unique:admins_users,mobile",
                "unique:advertisers_users,mobile,$id",
                "unique:customers_users,mobile",
                'regex:/^\+?\d+$/',
            ],
            'user.username' => [
                'nullable',
                "unique:admins_users,username",
                "unique:advertisers_users,username,$id",
                "unique:customers_users,username"
            ],
            'user.password' => ['nullable'],
            'user.bio' => ['nullable'],
            'user.country_code' => ['required', 'exists:countries,code'],
            'user.governorate_id' => ['required', 'exists:governorates,id'],
            'user.city_id' => [
                'required',
                Rule::exists('cities', 'id')->where(function ($query) {
                    return $query->where('governorate_id', $this->user['governorate_id'] ?? null);
                }),
            ],
            'user.language_code' => ['nullable', 'exists:languages,code'],
            'user.contact_number' => ['nullable', 'regex:/^\+?\d+$/'],
            'user.whatsapp_number' => ['nullable', 'regex:/^\+?\d+$/'],
            'user.facebook_url' => ['nullable', 'url'],
            'user.twitter_url' => ['nullable', 'url'],
            'user.website_url' => ['nullable', 'url'],
            'user.allowed_posts_count' => ['nullable', 'numeric', 'min:0'],
            'user.allowed_offers_count' => ['nullable', 'numeric', 'min:0'],
            'user.maximum_monthly_offers' => ['nullable', 'numeric', 'min:0'],
            'user.discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'user.rate' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'user.status' => ['nullable', 'in:active,inactive,banned'],
            'user.email_verified_at' => ['nullable', 'boolean'],
            'user.mobile_verified_at' => ['nullable', 'boolean'],
            'user.new_image' => ['nullable'],
            'package_id' => ['nullable', 'exists:packages,id']
        ]);

        $this->user['country_code'] = $this->country_code;
        $this->user['governorate_id'] = $this->governorate_id ?? $this->user['governorate_id'];

        //set data
        $data = $this->user;

        $data['name'] = !empty($this->user['name']) ? Filter::RemoveHtml($this->user['name']) : null;
        $data['email'] = !empty($this->user['email']) ? Filter::RemoveHtml($this->user['email']) : null;
        $data['mobile'] = !empty($this->user['mobile']) ? Filter::RemoveHtml($this->user['mobile']) : null;
        $data['username'] = !empty($this->user['username']) ? Filter::RemoveHtml($this->user['username']) : null;
        $data['bio'] = !empty($this->user['bio']) ? Filter::RemoveHtml($this->user['bio']) : null;
        $data['contact_number'] = !empty($this->user['contact_number']) ? Filter::RemoveHtml($this->user['contact_number']) : null;
        $data['whatsapp_number'] = !empty($this->user['whatsapp_number']) ? Filter::RemoveHtml($this->user['whatsapp_number']) : null;
        $data['facebook_url'] = !empty($this->user['facebook_url']) ? Filter::RemoveHtml($this->user['facebook_url']) : null;
        $data['twitter_url'] = !empty($this->user['twitter_url']) ? Filter::RemoveHtml($this->user['twitter_url']) : null;
        $data['website_url'] = !empty($this->user['website_url']) ? Filter::RemoveHtml($this->user['website_url']) : null;
        $data['allowed_posts_count'] = !empty($this->user['allowed_posts_count']) ? $this->user['allowed_posts_count'] : null;
        $data['allowed_offers_count'] = !empty($this->user['allowed_offers_count']) ? $this->user['allowed_offers_count'] : null;
        $data['maximum_monthly_offers'] = isset($this->user['maximum_monthly_offers']) && $this->user['maximum_monthly_offers'] !== ''
            ? $this->user['maximum_monthly_offers']
            : null;
        $data['discount_percentage'] = isset($this->user['discount_percentage']) && $this->user['discount_percentage'] !== ''
            ? $this->user['discount_percentage']
            : 0;
        $data['rate'] = !empty($this->user['rate']) ? $this->user['rate'] : null;

        //unset the user id
        unset($data['id']);

        //check whether admin changed password or not
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }


        DB::beginTransaction();
        try {

            //get user
            $user = AdvertiserUser::withTrashed()->findOrFail($id);
            if ($this->package_id) {
                if ($this->user_package_id) {
                    if ($this->user_package_id != $this->package_id) {
                        $subscription_package = Package::findOrFail($this->package_id);
                        PackageQuotas::assignPackage($user, $subscription_package);
                        $data['is_elite'] = (bool) $subscription_package->is_elite;
                        $data['allowed_posts_count'] = $subscription_package->maximum_posts;
                        $data['allowed_offers_count'] = $subscription_package->maximum_offers;
                        $data['maximum_monthly_offers'] = $subscription_package->maximum_monthly_offers;
                    }
                } else {
                    $subscription_package = Package::findOrFail($this->package_id);
                    PackageQuotas::assignPackage($user, $subscription_package);
                    $data['is_elite'] = (bool) $subscription_package->is_elite;
                    $data['allowed_posts_count'] = $subscription_package->maximum_posts;
                    $data['allowed_offers_count'] = $subscription_package->maximum_offers;
                    $data['maximum_monthly_offers'] = $subscription_package->maximum_monthly_offers;
                }
            } else {
                PackageQuotas::assignPackage($user, null);
                $data['is_elite'] = false;
                $data['allowed_posts_count'] = Settings::Get('user.allowed.posts', 10);
                $data['allowed_offers_count'] = Settings::Get('max.advertiser.active.offers', 20);
                $data['maximum_monthly_offers'] = Settings::Get('max.advertiser.monthly.offers', 30);
            }
            //check if admin chosen image or not then upload it
            if (isset($this->user['new_image']) && $this->user['new_image'] != null) {
                //check if category had a previous image
                if ($user->image) {
                    Files::deleteS3File($user->image);
                }
                $data['image'] = $this->user['new_image']->store('uploads/avatars', 'local');
            }

            $data['email_verified_at'] = $data['email_verified_at'] ? $user['email_verified_at'] ?? now() : null;
            $data['mobile_verified_at'] = $data['mobile_verified_at'] ? $user['mobile_verified_at'] ?? now() : null;

            // Filter html data
            $data['bio'] = Filter::RemoveHtml($data['bio']);

            //add log
            AdminLogs::log('edit', 'advertisers', [
                'old' => $user,
                'new' => $data,
            ], "Edit: advertiser #$id");

            //update user
            $user->update($data);

            //close modal
            $this->closeEditModal();

            //reset validation messages
            $this->resetValidation();
            //send toastr alert with success
            $this->alert('success', __('toastr.success'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            $this->reset([
                'package_id',
                'user_package_id',
            ]);

            //dispatch event to refresh file input
            $this->dispatchBrowserEvent('clearFileInput');
        } catch (Exception $e) {
            //rollback
            DB::rollBack();

            //send toastr alert with error
            $this->alert('error', __('toastr.error'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                'text' => $e->getMessage(),
            ]);
            return null;
        }
        //commit
        DB::commit();
    }

    /**
     * set modal texts
     */
    public function setModalTexts()
    {
        $this->deleteModalTexts = [
            'title' => __('pages/advertisers/index.modal.delete.title'),
            'content' => __('pages/advertisers/index.modal.delete.content', ['name' => '']),
            'cancel' => __('pages/advertisers/index.modal.delete.cancel'),
            'submit' => __('pages/advertisers/index.modal.delete.submit'),
        ];
        $this->editModalTexts = [
            'title' => __('pages/advertisers/index.modal.edit.title'),
            'cancel' => __('pages/advertisers/index.modal.edit.cancel'),
            'submit' => __('pages/advertisers/index.modal.edit.submit'),
        ];
        $this->assignPackageModalTexts = [
            'title' => __('pages/advertisers/index.modal.assign_package.title'),
            'cancel' => __('pages/advertisers/index.modal.assign_package.cancel'),
            'submit' => __('pages/advertisers/index.modal.assign_package.submit'),
        ];
    }

    public function showAssignPackageModal($id): void
    {
        $advertiser = AdvertiserUser::withTrashed()->findOrFail($id);

        $current = $advertiser->packages()
            ->where('is_current', true)
            ->where('is_active', true)
            ->where('is_ended', false)
            ->where('ends_at', '>', now())
            ->first();

        $this->assign_advertiser_id = $advertiser->id;
        $this->assign_advertiser_name = $advertiser->name;
        $this->assign_package_id = $current ? (string) $current->package_id : null;
        $this->showAssignPackageModal = true;
        $this->dispatchBrowserEvent('change-assign-package-id', $this->assign_package_id);
    }

    public function closeAssignPackageModal(): void
    {
        $this->showAssignPackageModal = false;
        $this->assign_advertiser_id = null;
        $this->assign_advertiser_name = null;
        $this->assign_package_id = null;
        $this->resetValidation();
    }

    public function assignPackage()
    {
        if (!Auth::guard('admin')->user()->can('advertisers.edit')) {
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }

        $this->validate([
            'assign_advertiser_id' => ['required', 'exists:advertisers_users,id'],
            'assign_package_id' => ['nullable', 'exists:packages,id'],
        ]);

        DB::beginTransaction();
        try {
            $advertiser = AdvertiserUser::withTrashed()->findOrFail($this->assign_advertiser_id);
            $package = $this->assign_package_id
                ? Package::findOrFail($this->assign_package_id)
                : null;

            PackageQuotas::assignPackage($advertiser, $package);

            AdminLogs::log('edit', 'advertisers', [
                'advertiser_id' => $advertiser->id,
                'package_id' => $this->assign_package_id,
            ], "Assign package to advertiser #{$advertiser->id}");

            $this->closeAssignPackageModal();

            $this->alert('success', __('toastr.success'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
        } catch (Throwable $e) {
            DB::rollBack();
            $this->alert('error', __('toastr.error'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                'text' => $e->getMessage(),
            ]);
            return null;
        }

        DB::commit();
    }

    /**
     * show a read-only modal listing the advertiser's categories
     * @param $id
     */
    public function showCategoriesModal($id)
    {
        $advertiser = AdvertiserUser::withTrashed()->findOrFail($id);

        $this->viewed_user_name = $advertiser->name;
        $this->viewed_categories = $advertiser->categories()
            ->with('category')
            ->get()
            ->pluck('category')
            ->filter();

        $this->showCategoriesModal = true;
    }

    public function closeCategoriesModal()
    {
        $this->showCategoriesModal = false;
        $this->viewed_user_name = null;
        $this->viewed_categories = new Collection();
    }

    /**
     * @param $id
     */
    public function setBusinessType($id)
    {
        $this->user['business_type'] = $id;

        //dispatch event to refresh select2
        $this->dispatchBrowserEvent('refreshSelect2Create');
    }


    public function updating()
    {
        //dispatch event to refresh select2
        $this->dispatchBrowserEvent('refreshSelect2Create');
    }

    /**
     * @param $code
     */
    public function setCountry($code)
    {
        $governorates = Governorate::select("$this->country_column", 'id')
            ->where('country_code', $code)
            ->get();

        $this->governorates = $governorates->mapWithKeys(function ($governorate) {
            return [$governorate->id => $governorate[$this->country_column]];
        });

        $this->cities = new Collection();
        $this->governorate_id = null;
        $this->user['governorate_id'] = 'none';
        $this->user['city_id'] = 'none';
    }

    public function setGovernorate($id)
    {
        $cities = City::select("$this->country_column", 'id')
            ->where('governorate_id', $id)
            ->get();

        $this->cities = $cities->mapWithKeys(function ($city) {
            return [$city->id => $city[$this->country_column]];
        });

        $this->governorate_id = $id;
        $this->user['city_id'] = 'none';
    }
}
