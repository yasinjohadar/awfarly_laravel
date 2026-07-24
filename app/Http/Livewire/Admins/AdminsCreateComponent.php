<?php

namespace App\Http\Livewire\Admins;

use App\Helpers\Filter;
use App\Models\Languages\Language;
use App\Models\Users\Admins\AdminUser;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\Permission\Models\Role;
use Throwable;

class AdminsCreateComponent extends Component
{
    use WithFileUploads;
    use LivewireAlert;


    /**
     * set variables
     */
    public string $name = '';
    public string $email = '';
    public string $mobile = '';
    public string $username = '';
    public string $language_code = 'ar';
    public $image;
    public string $password = '';
    public array $admin_roles = [];
    public string $status = 'active';

    /**
     * set the validating rules
     * @var array|string[][]
     */
    protected array $rules = [
        'name' => ['required', 'unique:admins_users,name', 'unique:advertisers_users,name', 'unique:customers_users,name'],
        'email' => ['required', 'email:rfc,dns', 'unique:admins_users,email', 'unique:advertisers_users,email', 'unique:customers_users,email'],
        'mobile' => ['nullable', 'unique:admins_users,mobile', 'unique:advertisers_users,mobile', 'unique:customers_users,mobile', 'regex:^\+\d+$^'],
        'username' => ['required', 'unique:admins_users,username', 'unique:advertisers_users,username', 'unique:customers_users,username'],
        'language_code' => ['required', 'exists:languages,code'],
        'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif'],
        'password' => ['required'],
        'status' => ['required', 'in:active,inactive,banned'],
    ];

    /**
     * set rendering view.
     * @return Application|Factory|View
     */
    public function render()
    {
        $languages = Language::select('name', 'code')
            ->get();

        $languages = $languages->mapWithKeys(function ($languages, $key) {
            return [$languages->code => $languages->name];
        });

        $roles = Role::all()
            ->map(function ($q) {
                return [
                    'id' => $q->id,
                    'name' => $q->name,
                ];
            });

        return view('livewire.pages.admins.create', ['languages' => $languages, 'roles' => $roles]);
    }

    /**
     * create Admin User
     */
    public function store()
    {
        if (!Auth::guard('admin')->user()->can('admins.add')) {
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
            //check whether the user has chosen image or not
            if ($this->image != null) {
                //upload the image
                $url = $this->image->store('uploads/avatars', 'local');
            }

            $data = [
                'name' => Filter::RemoveHtml($this->name),
                'email' => Filter::RemoveHtml($this->email),
                'mobile' => Filter::RemoveHtml($this->mobile),
                'username' => Filter::RemoveHtml($this->username),
                'language_code' => $this->language_code,
                'image' => $url ?? null,
                'password' => bcrypt($this->password),
                'status' => $this->status,
            ];
            //create admin user
            $admin = AdminUser::create($data);

            if ($this->admin_roles) {
                foreach ($this->admin_roles as $role) {
                    $role_name = Role::findById($role);
                    $admin->assignRole($role_name);
                }
            }
            //reset validation messages
            $this->resetValidation();

            //send toastr alert with success
            $this->alert('success', __('toastr.success'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            //reset variables to default values
            $this->reset([
                'name',
                'email',
                'mobile',
                'username',
                'password',
                'language_code',
                'image',
                'status',
            ]);

            $this->admin_roles = [];
            //dispatch event to refresh file input
            $this->dispatchBrowserEvent('clearFileInput');
        } catch (Throwable $e) {
            //rollback changes
            DB::rollBack();
            dd($e);
            //send toastr alert with error
            $this->alert('error', __('toastr.error'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                'text' => $e->getMessage(),
            ]);
            return null;
        }
        //commit changes
        DB::commit();
    }
}
