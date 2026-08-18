<?php

namespace App\Http\Livewire\Advertisers;

use App\Helpers\Advertisers\PackageQuotas;
use App\Helpers\Filter;
use App\Helpers\Settings;
use App\Models\Countries\Country;
use App\Models\Languages\Language;
use App\Models\Subscriptions\Packages\Package;
use App\Models\Users\Advertisers\AdvertiserUser;
use App\Models\Users\Advertisers\BusinessTypes\AdvertiserBusinessType;
use Hash;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

class AdvertisersCreateComponent extends Component
{
    use WithFileUploads;
    use LivewireAlert;

    /**
     * set variables
     */
    public ?string $name = null;
    public ?string $business_type = null;
    public ?string $email = null;
    public ?string $mobile = null;
    public ?string $username = null;
    public $image;
    public ?string $password = null;
    public ?string $country_code = 'none';
    public ?string $governorate_id = 'none';
    public ?string $city_id = 'none';
    public ?string $language_code = 'ar';
    public ?string $contact_number = null;
    public ?string $whatsapp_number = null;
    public ?string $facebook_url = null;
    public ?string $twitter_url = null;
    public ?string $website_url = null;
    public ?string $allowed_posts_count = null;
    public ?string $allowed_offers_count = null;
    public ?string $maximum_monthly_offers = null;
    public ?string $discount_percentage = null;
    public ?string $package_id = null;
    public string $status = 'active';
    public int $is_elite = 0;
    public int $is_accepted_send_notification = 0;
    private string $country_column = '';
    public $packages = [];

    protected $listeners = [
        'setBusinessType' => 'setBusinessType',
    ];

    /**
     * AdvertisersCreateComponent constructor.
     * @param null $id
     */
    public function __construct($id = null)
    {
        //get admin country to specify which language it will use
        $this->getAdminLanguage();
        parent::__construct($id);
    }

    /**
     * set validation rules
     * @return array
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'unique:admins_users,name', 'unique:advertisers_users,name', 'unique:customers_users,name'],
            'business_type' => ['required', 'exists:advertisers_business_types,id'],
            'email' => ['nullable', 'email:rfc,dns', 'unique:admins_users,email', 'unique:advertisers_users,email', 'unique:customers_users,email'],
            'mobile' => ['required', 'unique:admins_users,mobile', 'unique:advertisers_users,mobile', 'unique:customers_users,mobile', 'regex:/^\+?\d+$/'],
            'username' => ['nullable', 'unique:admins_users,username', 'unique:advertisers_users,username', 'unique:customers_users,username'],
            'country_code' => ['required', 'exists:countries,code'],
            'governorate_id' => ['required', 'exists:governorates,id'],
            'city_id' => [
                'required',
                Rule::exists('cities', 'id')->where(function ($query) {
                    return $query->where('governorate_id', $this->governorate_id);
                }),
            ],
            'language_code' => ['required', 'exists:languages,code'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif'],
            'password' => ['required'],
            'contact_number' => ['nullable', 'regex:/^\+?\d+$/'],
            'whatsapp_number' => ['nullable', 'regex:/^\+?\d+$/'],
            'facebook_url' => ['nullable', 'url'],
            'twitter_url' => ['nullable', 'url'],
            'website_url' => ['nullable', 'url'],
            'allowed_posts_count' => ['nullable', 'numeric', 'min:0'],
            'allowed_offers_count' => ['nullable', 'numeric', 'min:0'],
            'maximum_monthly_offers' => ['nullable', 'numeric', 'min:0'],
            'discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'package_id' => ['nullable', 'exists:packages,id'],
            'status' => ['required', 'in:active,inactive,banned'],
            'is_elite' => ['required', 'boolean'],
            'is_accepted_send_notification' => ['required', 'boolean'],
        ];
    }

    /**
     * set rendering view with data
     * @return Application|Factory|View
     */
    public function render()
    {
        //get existed languages
        $languages = Language::select('name', 'code')
            ->get();

        $languages = $languages->mapWithKeys(function ($languages, $key) {
            return [$languages->code => $languages->name];
        });

        //get all countries
        $countries = Country::select(
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
        //get all cities
        $business_types = AdvertiserBusinessType::select(
            "$this->country_column",
            'id'
        )
            ->get();

        $business_types = $business_types->mapWithKeys(function ($cities, $key) {
            return [$cities->id => $cities[$this->country_column]];
        });

        $packages = Package::where('is_active', true)
            ->get()
            ->map(function ($package) {
                return [
                    'id' => $package->id,
                    'name' => $package->{$this->country_column} . " (#{$package->id})",
                ];
            });

        //render data.
        return view('livewire.pages.advertisers.create', [
            'languages' => $languages,
            'countries' => $countries,
            'business_types' => $business_types,
            'packages' => $packages,
        ]);
    }

    /**
     * @param $id
     */
    public function setBusinessType($id)
    {
        $this->business_type = $id;

        //dispatch event to refresh select2
        $this->dispatchBrowserEvent('refreshSelect2Create');
    }

    public function updating()
    {
        //dispatch event to refresh select2
        $this->dispatchBrowserEvent('refreshSelect2Create');
    }

    /**
     * create new user
     * @return null|void
     */
    public function store()
    {
        if (!Auth::guard('admin')->user()->can('advertisers.add')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }

        //validate data
        $this->validate();

        DB::beginTransaction();
        try {

            //check if admin chosen image or not then upload it
            if ($this->image != null) {
                $url = $this->image->store('uploads/avatars', 'local');
            } else {
                $url = null;
            }
            if ($this->username === '') {
                $this->username = null;
            }

            if ($this->password) {
                $password = Hash::make($this->password);
            } else {
                $password = null;
            }

            if ($this->allowed_posts_count === '') {
                $this->allowed_posts_count = null;
            }

            if ($this->allowed_offers_count === '') {
                $this->allowed_offers_count = null;
            }

            if ($this->maximum_monthly_offers === '') {
                $this->maximum_monthly_offers = null;
            }

            if ($this->discount_percentage === '' || $this->discount_percentage === null) {
                $this->discount_percentage = '0';
            }
            //set data
            $data = [
                'name' => Filter::RemoveHtml($this->name),
                'business_type' => $this->business_type,
                'email' => Filter::RemoveHtml($this->email),
                'mobile' => Filter::RemoveHtml($this->mobile),
                'username' => Filter::RemoveHtml($this->username),
                'country_code' => $this->country_code,
                'governorate_id' => $this->governorate_id,
                'city_id' => $this->city_id,
                'language_code' => $this->language_code,
                'image' => $url,
                'password' => $password,
                'contact_number' => Filter::RemoveHtml($this->contact_number),
                'whatsapp_number' => Filter::RemoveHtml($this->whatsapp_number),
                'facebook_url' => Filter::RemoveHtml($this->facebook_url),
                'twitter_url' => Filter::RemoveHtml($this->twitter_url),
                'website_url' => Filter::RemoveHtml($this->website_url),
                'allowed_posts_count' => $this->allowed_posts_count,
                'allowed_offers_count' => $this->allowed_offers_count,
                'maximum_monthly_offers' => $this->maximum_monthly_offers,
                'discount_percentage' => $this->discount_percentage,
                'status' => $this->status,
                'is_elite' => $this->is_elite,
                'is_accepted_send_notification' => $this->is_accepted_send_notification,
            ];

            //create user
            $advertiser = AdvertiserUser::create($data);

            if ($this->package_id) {
                PackageQuotas::assignPackage($advertiser, Package::findOrFail($this->package_id));
            } else {
                //no package explicitly chosen: fall back to the configured default, if any
                $defaultPackageId = Settings::Get('advertisers.default_package_id');
                if ($defaultPackageId) {
                    $defaultPackage = Package::where('id', $defaultPackageId)
                        ->where('is_active', true)
                        ->where('is_visible', true)
                        ->first();

                    if ($defaultPackage) {
                        PackageQuotas::assignPackage($advertiser, $defaultPackage);
                    }
                }
            }

            //reset validation messages
            $this->resetValidation();

            //send toastr alert with success
            $this->alert('success', __('toastr.success'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            //reset variables to its default values
            $this->reset([
                'name',
                'business_type',
                'email',
                'mobile',
                'username',
                'country_code',
                'governorate_id',
                'city_id',
                'language_code',
                'image',
                'password',
                'contact_number',
                'whatsapp_number',
                'facebook_url',
                'twitter_url',
                'website_url',
                'allowed_posts_count',
                'allowed_offers_count',
                'maximum_monthly_offers',
                'discount_percentage',
                'package_id',
                'status',
                'is_elite',
                'is_accepted_send_notification',
            ]);

            //dispatch event to refresh file input
            $this->dispatchBrowserEvent('clearFileInput');
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
     * get user language to select which language it will display data with
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
}
