<?php

namespace App\Http\Livewire\Admins;

use App\Helpers\Admins\AdminLogs;
use App\Helpers\Files;
use App\Helpers\Filter;
use App\Models\Languages\Language;
use App\Models\Users\Admins\AdminUser;
use Carbon\Carbon;
use Exception;
use Hash;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\WithFileUploads;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Mediconesystems\LivewireDatatables\NumberColumn;
use Spatie\Permission\Models\Role;
use Throwable;

class AdminsInquiryComponent extends LivewireDatatable
{
    use WithFileUploads;
    use LivewireAlert;

    /**
     * set variables
     */
    public $exportable = true;
    public $hideable = 'select';
    public $beforeTableSlot = 'livewire.datatables.selected';
    public $afterTableSlot = 'modals.users.admins.edit';
    public $model = AdminUser::class;
    public array $user;
    public Collection $languages;
    public bool $showDeleteModal = false;
    public bool $showEditModal = false;
    public array $deleteModalTexts;
    public array $editModalTexts;
    public bool $has_delete = true;
    public array $admin_roles = [];
    public Collection $roles;

    /**
     * Set data inside mount.
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
        parent::mount($model, $include, $exclude, $hide, $dates, $times, $searchable, $sort, $hideHeader, $hidePagination, $perPage, $exportable, $hideable, $beforeTableSlot, $afterTableSlot, $params);

        //set modal texts
        $this->setModalTexts();

        //get all languages
        $languages = Language::select('name', 'code')
            ->get();

        //organize languages in one array with code as key and name as value
        $this->languages = $languages->mapWithKeys(function ($languages, $key) {
            return [$languages->code => $languages->name];
        });

        $this->roles = Role::all()
            ->map(function ($q) {
                return [
                    'id' => $q->id,
                    'name' => $q->name,
                ];
            });
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
                ->label(__('pages/admins/index.content.datatable.image')),
            Column::name('name')
                ->label(__('pages/admins/index.content.datatable.name'))
                ->filterable()
                ->searchable(),
            Column::name('email')
                ->label(__('pages/admins/index.content.datatable.email'))
                ->filterable()
                ->searchable(),
            Column::name('mobile')
                ->label(__('pages/admins/index.content.datatable.mobile'))
                ->filterable()
                ->searchable(),
            Column::callback('username', function ($username) {
                return $username ?: '-';
            })
                ->label(__('pages/admins/index.content.datatable.username'))
                ->filterable()
                ->searchable(),
            Column::name('languages.name')
                ->label(__('pages/admins/index.content.datatable.language'))
                ->filterable($this->all_languages)
                ->searchable(),
            Column::callback('last_login_at', function ($last_login_at) {
                return $last_login_at ? Carbon::make($last_login_at)->diffForHumans() : '-';
            })
                ->label(__('pages/admins/index.content.datatable.last_login_at'))
                ->searchable()
                ->hide(),
            Column::callback('status', function ($status) {
                if ($status === 'active') {
                    return '<div class="badge badge-success">' . __('pages/admins/index.content.datatable.status_type.active') . '</div>';
                } else if ($status === 'banned') {
                    return '<div class="badge badge-danger">' . __('pages/admins/index.content.datatable.status_type.banned') . '</div>';
                } else {
                    return '<div class="badge badge-info">' . __('pages/admins/index.content.datatable.status_type.closed') . '</div>';
                }
            })
                ->label(__('pages/admins/index.content.datatable.status'))
                ->filterable()
                ->searchable(),
            DateColumn::name('updated_at')
                ->format('Y-m-d h:i A')
                ->label(__('pages/admins/index.content.datatable.updated_at'))
                ->searchable()
                ->hide(),
            DateColumn::name('created_at')
                ->format('Y-m-d h:i A')
                ->label(__('pages/admins/index.content.datatable.created_at'))
                ->searchable()
                ->hide(),
            Column::callback(['id', 'name'], function ($id, $name) {
                return view('admin.pages.admins.table-actions', ['id' => $id, 'name' => $name]);
            })
                ->label(__('datatable.actions'))
                ->excludeFromExport()
                ->unsortable(),
        ];
    }

    /**
     * execute this query to render data
     * @return mixed
     */
    public function builder()
    {
        return AdminUser::leftJoin('languages', 'languages.code', 'admins_users.language_code');
    }

    /**
     * @return mixed
     */
    public function getAllLanguagesProperty()
    {
        return Language::pluck('name');
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
    public function deleteSelected()
    {
        if (!Auth::guard('admin')->user()->can('admins.delete')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }
        DB::beginTransaction();
        try {
            //get Admins
            $admins = AdminUser::whereIn('id', $this->selected)
                ->get();


            foreach ($admins as $index => $admin) {
                if ($admin->is_protected) {
                    unset($admins[$index]);
                } else {
                    $admin->delete();
                }
            }

            //reset selected to empty
            $this->selected = [];

            //send toastr alert with success
            $this->alert('success', __('toastr.delete'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            //hide modal
            $this->showDeleteModal = false;

            //add log
            AdminLogs::log('delete', 'admins', [
                'admins' => $admins
            ], "Delete: admins");

        } catch (Throwable $e) {
            //rollback changes
            DB::rollBack();

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

    /**
     * show edit modal with data
     * @param $id
     */
    public function showEditModal($id)
    {
        //get user data
        $user = AdminUser::where('id', $id)
            ->first();

        $this->admin_roles = $user->roles()
            ->pluck('id')
            ->toArray();

        $this->user = $user->toArray();
        $this->user['password'] = '';
        $this->user['is_protected'] = $this->user['is_protected'] ? 1 : 0;

        $this->dispatchBrowserEvent('setSelect2', $this->admin_roles);

        //set show edit modal to true
        $this->showEditModal = true;

    }

    /**
     * Close edit modal
     */
    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->user = [];
        $this->reset('admin_roles');
        //reset validation messages
        $this->resetValidation();
    }

    /**
     * Update user data
     * @param $id
     * @return void|null
     */
    public function update($id)
    {
        if (!Auth::guard('admin')->user()->can('admins.edit')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }
        //validate data
        $this->validate([
            'user.name' => ['required', "unique:admins_users,name,$id"],
            'user.email' => [
                'required',
                "unique:admins_users,email,$id",
                "unique:advertisers_users,email",
                "unique:customers_users,email"
            ],
            'user.mobile' => [
                'nullable',
                "unique:admins_users,mobile,$id",
                "unique:advertisers_users,mobile",
                "unique:customers_users,mobile",
                'regex:^\+\d+$^',
            ],
            'user.username' => [
                'nullable',
                "unique:admins_users,username,$id",
                "unique:advertisers_users,username",
                "unique:customers_users,username"
            ],
            'user.language' => ['nullable', 'exists:languages,code'],
            'user.password' => ['nullable'],
            'user.status' => ['required', 'in:active,inactive,banned'],
            'user.new_image' => ['nullable'],
            'admin_roles' => ['nullable', 'exists:roles,id']
        ]);

        //set user array
        $data = $this->user;

        $data['name'] = Filter::RemoveHtml($this->user['name']);
        $data['email'] = Filter::RemoveHtml($this->user['email']);
        $data['mobile'] = Filter::RemoveHtml($this->user['mobile']);
        $data['username'] = Filter::RemoveHtml($this->user['username']);
        $data['status'] = $this->user['status'];

        //unset the ID
        unset($data['id']);

        //check if admin edited the password or not
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        if ($this->user['is_protected']) {
            unset($data['status']);
        }

        DB::beginTransaction();
        try {
            //get user
            $user = AdminUser::findOrFail($id);

            //check if admin chosen image or not then upload it
            if (isset($this->user['new_image']) && $this->user['new_image'] != null) {
                //check if category had a previous image
                if ($user->image) {
                    Files::deleteS3File($user->image);
                }
                $data['image'] = $this->user['new_image']->store('uploads/avatars', 'local');
            }

            //add log
            AdminLogs::log('edit', 'admins', [
                'old' => $user,
                'new' => $data,
            ], "Edit: admin #$id");

            $roles = $user->getRoleNames();
            foreach ($roles as $role) {
                $user->removeRole($role);
            }

            if ($user->is_super_administrator) {
                $user->assignRole('Super Administrator');
            } else {
                foreach ($this->admin_roles as $role) {
                    $role_name = Role::findById($role);
                    $user->assignRole($role_name);
                }
            }
            //update user data
            $user->update($data);

            //close the modal
            $this->closeEditModal();

            //rest validation
            $this->resetValidation();

            //send toastr alert with success
            $this->alert('success', __('toastr.success'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

        } catch (Exception $e) {
            //roll back
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
     * set Modal Texts
     */
    public function setModalTexts()
    {
        $this->deleteModalTexts = [
            'title' => __('pages/admins/index.modal.delete.title'),
            'content' => __('pages/admins/index.modal.delete.content'),
            'cancel' => __('pages/admins/index.modal.delete.cancel'),
            'submit' => __('pages/admins/index.modal.delete.submit'),
        ];
        $this->editModalTexts = [
            'title' => __('pages/admins/index.modal.edit.title'),
            'cancel' => __('pages/admins/index.modal.edit.cancel'),
            'submit' => __('pages/admins/index.modal.edit.submit'),
        ];
    }
}
