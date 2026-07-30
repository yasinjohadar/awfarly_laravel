<?php

namespace App\Http\Livewire\Customers;

use App\Helpers\Filter;
use App\Models\Countries\Country;
use App\Models\Languages\Language;
use App\Models\Users\Customers\CustomerUser;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

class CustomersCreateComponent extends Component
{
    use LivewireAlert;

    use WithFileUploads;

    public ?string $name = null;
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
    public string $status = 'active';
    public int $is_accepted_send_notification = 0;
    private string $country_column = '';

    public function __construct($id = null)
    {
        $this->getAdminLanguage();
        parent::__construct($id);
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'unique:admins_users,name', 'unique:advertisers_users,name', 'unique:customers_users,name'],
            'email' => ['nullable', 'email:rfc,dns', 'unique:admins_users,email', 'unique:advertisers_users,email', 'unique:customers_users,email'],
            'mobile' => ['required', 'unique:admins_users,mobile', 'unique:advertisers_users,mobile', 'unique:customers_users,mobile', 'regex:^\+\d+$^'],
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
            'contact_number' => ['nullable', 'regex:^\+\d+$^'],
            'whatsapp_number' => ['nullable', 'regex:^\+\d+$^'],
            'facebook_url' => ['nullable', 'url'],
            'twitter_url' => ['nullable', 'url'],
            'website_url' => ['nullable', 'url'],
            'status' => ['required', 'in:active,inactive,banned'],
            'is_accepted_send_notification' => ['required', 'boolean'],
        ];
    }

    public function render()
    {
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
        return view('livewire.pages.customers.create', [
            'languages' => $languages,
            'countries' => $countries,
        ]);
    }

    /**
     * create new user
     * @return null|void
     */
    public function store()
    {
        if (!Auth::guard('admin')->user()->can('customers.add')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }
        $this->validate();
        DB::beginTransaction();
        try {
            if ($this->image != null) {
                $url = $this->image->store('uploads/avatars', 'local');
            } else {
                $url = null;
            }
            if ($this->password) {
                $password = Hash::make($this->password);
            } else {
                $password = null;
            }

            //set data
            $data = [
                'name' => Filter::RemoveHtml($this->name),
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
                'status' => $this->status,
                'is_accepted_send_notification' => $this->is_accepted_send_notification,
            ];

            CustomerUser::create($data);

            $this->resetValidation();
            $this->alert('success', __('toastr.success'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            $this->reset([
                'name',
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
                'status',
                'is_accepted_send_notification',
            ]);

            $this->dispatchBrowserEvent('clearFileInput');
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
