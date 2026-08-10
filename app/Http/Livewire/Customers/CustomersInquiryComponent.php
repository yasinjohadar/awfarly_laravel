<?php

namespace App\Http\Livewire\Customers;

use App\Helpers\Admins\AdminLogs;
use App\Helpers\Files;
use App\Helpers\Filter;
use App\Models\Countries\Cities\City;
use App\Models\Countries\Country;
use App\Models\Countries\Governorates\Governorate;
use App\Models\Languages\Language;
use App\Models\Users\Customers\CustomerUser;
use Carbon\Carbon;
use Exception;
use Hash;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\WithFileUploads;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Mediconesystems\LivewireDatatables\NumberColumn;
use Throwable;

class CustomersInquiryComponent extends LivewireDatatable
{
    use LivewireAlert;

    use WithFileUploads;

    public $exportable = true;
    public $hideable = 'select';
    public $beforeTableSlot = 'livewire.datatables.selected';
    public $afterTableSlot = 'modals.users.customers.edit';
    public string $afterTableSlot2 = 'modals.users.customers.categories';
    public $model = CustomerUser::class;
    public array $user;
    public Collection $languages;
    public Collection $countries;
    public Collection $governorates;
    public Collection $cities;
    public Collection $viewed_categories;
    public bool $showDeleteModal = false;
    public bool $showEditModal = false;
    public bool $showCategoriesModal = false;
    public array $deleteModalTexts;
    public array $editModalTexts;
    private string $country_column = '';
    public bool $has_delete = true;
    public ?string $country_code = null;
    public ?string $governorate_id = null;
    public ?string $viewed_user_name = null;

    protected $listeners = [
        'setCountry',
        'setGovernorate',
    ];

    /**
     * CustomersInquiryComponent constructor.
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
                ->label(__('pages/customers/index.content.datatable.image')),
            Column::name('name')
                ->label(__('pages/customers/index.content.datatable.name'))
                ->filterable()
                ->searchable(),
            Column::name('fcm_token')
                ->label(__('pages/advertisers/index.content.datatable.fcm_token'))
                ->filterable()
                ->searchable(),

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
                ->label(__('pages/customers/index.content.datatable.bio'))
                ->filterable()
                ->searchable()
                ->hide(),
            Column::callback('username', function ($username) {
                return $username ?: '-';
            })
                ->label(__('pages/customers/index.content.datatable.username'))
                ->filterable()
                ->searchable()
                ->hide(),
            Column::name("countries.$this->country_column")
                ->label(__('pages/customers/index.content.datatable.country'))
                ->filterable($this->all_countries)
                ->searchable(),
            Column::name("governorates.$this->country_column")
                ->label(__('pages/customers/index.content.datatable.governorate'))
                ->filterable($this->all_governorates)
                ->searchable()
                ->hide(),
            Column::name("cities.$this->country_column")
                ->label(__('pages/customers/index.content.datatable.city'))
                ->filterable($this->all_cities)
                ->searchable()
                ->hide(),
            Column::name('languages.name')
                ->label(__('pages/customers/index.content.datatable.language'))
                ->filterable($this->all_languages)
                ->searchable()
                ->hide(),
            Column::name('contact_number')
                ->label(__('pages/customers/index.content.datatable.contact_number'))
                ->filterable()
                ->searchable()
                ->hide(),
            Column::name('whatsapp_number')
                ->label(__('pages/customers/index.content.datatable.whatsapp_number'))
                ->filterable()
                ->searchable()
                ->hide(),
            Column::name('facebook_url')
                ->label(__('pages/customers/index.content.datatable.facebook_url'))
                ->filterable()
                ->searchable()
                ->hide(),
            Column::name('twitter_url')
                ->label(__('pages/customers/index.content.datatable.twitter_url'))
                ->filterable()
                ->searchable()
                ->hide(),
            Column::name('website_url')
                ->label(__('pages/customers/index.content.datatable.website_url'))
                ->filterable()
                ->searchable()
                ->hide(),
            BooleanColumn::name('is_accepted_send_notifications')
                ->label(__('pages/customers/index.content.datatable.accepted_send_notification'))
                ->filterable()
                ->searchable()
                ->hide(),
            Column::callback('last_login_at', function ($last_login_at) {
                return $last_login_at ? Carbon::make($last_login_at)->diffForHumans() : '-';
            })
                ->label(__('pages/customers/index.content.datatable.last_login_at'))
                ->searchable()
                ->hide(),
            DateColumn::callback('last_online_at', function ($online) {
                $humans = $online ? Carbon::make($online)->diffForHumans() : '-';
                $datetime = $online ? Carbon::make($online)->format("Y-m-d h:i A") : '-';
                return $online ? "<div title='{$datetime}'>{$humans}</div>" : '-';
            })
                ->label(__('pages/customers/index.content.datatable.last_online_at'))
                ->searchable()
                ->filterable(),
            Column::callback('status', function ($status) {
                if ($status === 'active') {
                    return '<div class="badge badge-success">' . __('pages/customers/index.content.datatable.status_type.active') . '</div>';
                } else if ($status === 'banned') {
                    return '<div class="badge badge-danger">' . __('pages/customers/index.content.datatable.status_type.banned') . '</div>';
                } else {
                    return '<div class="badge badge-info">' . __('pages/customers/index.content.datatable.status_type.closed') . '</div>';
                }
            })
                ->label(__('pages/customers/index.content.datatable.status'))
                ->filterable()
                ->searchable(),
            Column::callback('deleted_at', function ($deleted_at) {
                $humans = $deleted_at ? Carbon::make($deleted_at)->diffForHumans() : '-';

                if ($deleted_at == null) {
                    return '<div class="badge badge-success">' . __('pages/customers/index.modal.edit.inputs.boolean.no') . '</div>';
                } else {
                    return '<div class="badge badge-danger">' . __('pages/customers/index.modal.edit.inputs.boolean.yes') . ' ' . $humans . '</div>';
                }
            })
                ->label(__('pages/customers/index.content.datatable.deleted'))
                ->filterable()
                ->searchable(),

            Column::callback('is_accepted_send_notifications', function ($is_accepted_send_notifications) {
                if ($is_accepted_send_notifications == 1) {
                    return '<div class="badge badge-success">' . __('pages/customers/index.content.datatable.notifications_types.allowed') . '</div>';
                } else {
                    return '<div class="badge badge-danger">' . __('pages/customers/index.content.datatable.notifications_types.not-allowed') . '</div>';
                }
            })
                ->label(__('pages/customers/index.content.datatable.notifications')),
            DateColumn::name('updated_at')
                ->format('Y-m-d h:i A')
                ->label(__('pages/customers/index.content.datatable.updated_at'))
                ->searchable()
                ->hide(),
            DateColumn::name('created_at')
                ->format('Y-m-d h:i A')
                ->label(__('pages/customers/index.content.datatable.created_at'))
                ->searchable()
                ->hide(),
            Column::callback(['id', 'name'], function ($id, $name) {
                return view('admin.pages.customers.table-actions', ['id' => $id, 'name' => $name]);
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
        return CustomerUser::withTrashed()->leftJoin('languages', 'languages.code', 'customers_users.language_code')
            ->leftJoin('countries', 'countries.code', 'customers_users.country_code')
            ->leftJoin('governorates', 'governorates.id', 'customers_users.governorate_id')
            ->leftJoin('cities', 'cities.id', 'customers_users.city_id');
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

    /**
     * @return mixed
     */
    public function getAllGovernoratesProperty()
    {
        return Governorate::pluck($this->country_column);
    }

    public function getAllCitiesProperty()
    {
        return City::pluck($this->country_column);
    }

    /**
     * show delete modal
     */
    public function showDeleteModal()
    {
        $this->showDeleteModal = true;
    }

    /**
     * delete Selected data
     */
    public function customForceDelete($ids)
    {
        $this->model::whereIn('id', $ids)->forceDelete();
    }

    public function deleteSelected()
    {
        if (!Auth::guard('admin')->user()->can('customers.delete')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }
        DB::beginTransaction();
        try {
            //get customers
            $customers = CustomerUser::withTrashed()->whereIn('id', $this->selected)
                ->get();

            //delete data
            parent::delete($this->selected);

            // delete record
            $this->customForceDelete($this->selected);

            //set selected data to null
            $this->selected = [];

            //send toastr alert with success
            $this->alert('success', __('toastr.delete'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            //add log
            AdminLogs::log('delete', 'customers', [
                'customers' => $customers
            ], "Delete: customers");

            //close modal
            $this->showDeleteModal = false;
        } catch (Throwable $e) {
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
     * show edit modal
     * @param $id
     */
    public function showEditModal($id)
    {
        //get user with data
        $this->user = CustomerUser::withTrashed()->where('id', $id)
            ->first()
            ->toArray();
        $this->user['password'] = '';

        //set dates to format (Y-m-d\TH:i) to render in the inputs
        $this->user['email_verified_at'] = $this->user['email_verified_at'] ? 1 : 0;
        $this->user['mobile_verified_at'] = $this->user['mobile_verified_at'] ? 1 : 0;

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

        //show the modal
        $this->showEditModal = true;
        $this->dispatchBrowserEvent('change-country', [
            'country_code' => $this->user['country_code'],
            'governorate_id' => $this->user['governorate_id'] ?? null,
            'city_id' => $this->user['city_id'],
        ]);
    }

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
     * @param $id
     * @return void|null
     */
    public function update($id)
    {
        if (!Auth::guard('admin')->user()->can('customers.edit')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }

        //validate data
        $this->validate([
            'user.name' => ['required', "unique:customers_users,name,$id"],
            'user.email' => [
                'nullable',
                "unique:admins_users,email",
                "unique:advertisers_users,email",
                "unique:customers_users,email,$id"
            ],
            'user.mobile' => [
                'required',
                "unique:admins_users,mobile",
                "unique:advertisers_users,mobile",
                "unique:customers_users,mobile,$id",
                'regex:^\+\d+$^',
            ],
            'user.username' => [
                'nullable',
                "unique:admins_users,username",
                "unique:advertisers_users,username",
                "unique:customers_users,username,$id"
            ],
            'user.password' => ['nullable'],
            'user.bio' => ['nullable'],
            'country_code' => ['required', 'exists:countries,code'],
            'user.governorate_id' => ['required', 'exists:governorates,id'],
            'user.city_id' => [
                'required',
                Rule::exists('cities', 'id')->where(function ($query) {
                    return $query->where('governorate_id', $this->user['governorate_id'] ?? null);
                }),
            ],
            'user.language_code' => ['nullable', 'exists:languages,code'],
            'user.contact_number' => ['nullable', 'regex:^\+\d+$^'],
            'user.whatsapp_number' => ['nullable', 'regex:^\+\d+$^'],
            'user.facebook_url' => ['nullable', 'url'],
            'user.twitter_url' => ['nullable', 'url'],
            'user.website_url' => ['nullable', 'url'],
            'user.status' => ['nullable', 'in:active,inactive,banned'],
            'user.email_verified_at' => ['nullable', 'boolean'],
            'user.mobile_verified_at' => ['nullable', 'boolean'],
            'user.new_image' => ['nullable'],
        ]);

        $this->user['country_code'] = $this->country_code;
        $this->user['governorate_id'] = $this->governorate_id ?? $this->user['governorate_id'];

        //set data
        $data = $this->user;
        $data['name'] = Filter::RemoveHtml($this->user['name']);
        $data['email'] = Filter::RemoveHtml($this->user['email']);
        $data['mobile'] = Filter::RemoveHtml($this->user['mobile']);
        $data['username'] = Filter::RemoveHtml($this->user['username']);
        $data['bio'] = Filter::RemoveHtml($this->user['bio']);
        $data['contact_number'] = Filter::RemoveHtml($this->user['contact_number']);
        $data['whatsapp_number'] = Filter::RemoveHtml($this->user['whatsapp_number']);
        $data['facebook_url'] = Filter::RemoveHtml($this->user['facebook_url']);
        $data['twitter_url'] = Filter::RemoveHtml($this->user['twitter_url']);
        $data['website_url'] = Filter::RemoveHtml($this->user['website_url']);

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
            $user = CustomerUser::withTrashed()->findOrFail($id);

            $data['email_verified_at'] = $data['email_verified_at'] ? $user['email_verified_at'] ?? now() : null;
            $data['mobile_verified_at'] = $data['mobile_verified_at'] ? $user['mobile_verified_at'] ?? now() : null;

            //check if admin chosen image or not then upload it
            if (isset($this->user['new_image']) && $this->user['new_image'] != null) {
                //check if category had a previous image
                if ($user->image) {
                    Files::deleteS3File($user->image);
                }
                $data['image'] = $this->user['new_image']->store('uploads/avatars', 'local');
            }

            //add log
            AdminLogs::log('edit', 'customers', [
                'old' => $user,
                'new' => $data,
            ], "Edit: customer #$id");

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
            'title' => __('pages/customers/index.modal.delete.title'),
            'content' => __('pages/customers/index.modal.delete.content'),
            'cancel' => __('pages/customers/index.modal.delete.cancel'),
            'submit' => __('pages/customers/index.modal.delete.submit'),
        ];
        $this->editModalTexts = [
            'title' => __('pages/customers/index.modal.edit.title'),
            'cancel' => __('pages/customers/index.modal.edit.cancel'),
            'submit' => __('pages/customers/index.modal.edit.submit'),
        ];
    }

    /**
     * show a read-only modal listing the customer's categories
     * @param $id
     */
    public function showCategoriesModal($id)
    {
        $customer = CustomerUser::withTrashed()->findOrFail($id);

        $this->viewed_user_name = $customer->name;
        $this->viewed_categories = $customer->categories()
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
