<?php

namespace App\Http\Livewire\Account;

use App\Helpers\Admins\AdminLogs;
use App\Helpers\Files;
use App\Helpers\Filter;
use App\Models\Languages\Language;
use Auth;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

class AccountComponent extends Component
{
    use WithFileUploads;
    use LivewireAlert;


    public ?string $name = null;
    public ?string $email = null;
    public ?string $mobile = null;
    public ?string $username = null;
    public $new_image;
    public ?string $language_code = null;
    public ?string $password = null;
    public ?string $password_confirmation = null;
    public ?string $old_password = null;

    public function __construct($id = null)
    {
        $user = Auth::guard('admin')->user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->mobile = $user->mobile;
        $this->username = $user->username;
        $this->language_code = $user->language_code;

        parent::__construct($id);
    }

    public function render()
    {
        $languages = Language::select('name', 'code')
            ->get();

        $languages = $languages->mapWithKeys(function ($languages, $key) {
            return [$languages->code => $languages->name];
        });

        return view('livewire.pages.account.account-component', ['languages' => $languages]);
    }


    public function update()
    {
        $admin = Auth::guard('admin')->user();
        $this->validate([
            'name' => ['required', "unique:admins_users,name,{$admin->id}"],
            'email' => ['required', 'string', 'email:rfc,dns', 'max:255', "unique:admins_users,email,{$admin->id}", "unique:customers_users,email", "unique:advertisers_users,email"],
            'mobile' => ['nullable', 'string', 'min:4', 'max:20', "unique:admins_users,mobile,{$admin->id}", 'unique:advertisers_users,mobile', 'unique:customers_users,mobile'],
            'username' => ['nullable', "unique:admins_users,username,{$admin->id}", 'unique:advertisers_users,username', 'unique:customers_users,username'],
            'new_image' => ['nullable', 'image'],
            'language_code' => ['required', "exists:languages,code"],
            'password' => ['nullable', "string", 'confirmed'],
            'password_confirmation' => ['required_with:password'],
            'old_password' => ['required', "string"],
        ]);

        $data = [
            'name' => Filter::removeHTML($this->name),
            'email' => Filter::removeHTML($this->email),
            'mobile' => Filter::removeHTML($this->mobile),
            'username' => Filter::removeHTML($this->username),
            'language_code' => $this->language_code,
        ];

        //Check old password
        if (!Hash::check($this->old_password, $admin->password)) {
            $this->alert('error', __('toastr.error'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                'text' => __('pages/account/edit.content.invalidOldPassword'),
            ]);
            return null;
        }

        //Change password
        if (isset($this->password) && !is_null(isset($this->password))) {
            $data['password'] = Hash::make($this->password);
        }


        //check if admin chosen image or not then upload it
        if (isset($this->new_image) && $this->new_image != null) {
            //check if user had a previous image
            if ($admin->image) {
                Files::deleteS3File($admin->image);
            }
            $data['image'] = $this->new_image->store('uploads/avatars', 'local');
        }

        //Set logged admin (old)
        $logged_admin = [
            'name' => $admin->name,
            'email' => $admin->email,
            'mobile' => $admin->mobile,
            'image' => $admin->image,
            'country_code' => $admin->country_code,
        ];
        //Begin Transaction
        DB::beginTransaction();
        try {
            $admin->update($data);

            //Log Action
            AdminLogs::log('edit', 'account', [
                'old' => $logged_admin,
                'new' => $data,
                'is_password_updated' => (isset($data['password'])),
            ], "Update: Account data");

            $this->resetValidation();
            $this->reset([
                'name',
                'email',
                'mobile',
                'username',
                'new_image',
                'language_code',
                'password',
                'password_confirmation',
                'old_password',
            ]);
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

        //send toastr alert with success
        $this->alert('success', __('toastr.success'), [
            'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
        ]);
    }
}
