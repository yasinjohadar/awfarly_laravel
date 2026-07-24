<?php

namespace App\Http\Livewire\System\Settings;

use App\Helpers\Admins\AdminLogs;
use App\Helpers\Filter;
use App\Models\Settings\Setting;
use App\Models\Users\Advertisers\AdvertiserUser;
use App\Models\Users\Customers\CustomerUser;
use Exception;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Laravel\Passport\Passport;
use Laravel\Passport\PassportUserProvider;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Mediconesystems\LivewireDatatables\NumberColumn;
use Str;

class SettingsInquiryComponent extends LivewireDatatable
{
    use LivewireAlert;

    public $exportable = true;
    public $hideable = 'select';
    public $afterTableSlot = 'modals.system.settings.edit';
    public $model = Setting::class;
    public array $setting;
    public bool $showEditModal = false;
    public array $editModalTexts;
    public bool $has_delete = false;
    public ?string $type = null;

    /**
     * CustomersInquiryComponent constructor.
     * @param null $id
     */
    public function __construct($id = null)
    {
        //set modal texts
        $this->setModalTexts();

        parent::__construct($id);
    }

    /**
     * set columns to render
     * @return array
     */
    public function columns(): array
    {
        return [
            NumberColumn::name('id')
                ->label('#')
                ->filterable()
                ->searchable(),
            Column::callback('name', function ($name) {
                return __("pages/settings/settings.names.$name");
            })
                ->label(__('pages/system/settings/settings.content.datatable.name'))
                ->filterable()
                ->searchable(),
            /*            Column::name('key')
                            ->label(__('pages/system/settings/settings.content.datatable.key'))
                            ->filterable()
                            ->searchable(),*/
            Column::callback(['value', 'value_type'], function ($value, $value_type) {
                if ($value_type === 'boolean') {
                    return $value ?
                        __('pages/system/settings/settings.content.datatable.active')
                        : __('pages/system/settings/settings.content.datatable.inactive');
                }
                return $value;
            })
                ->label(__('pages/system/settings/settings.content.datatable.value'))
                ->filterable()
                ->searchable(),
            /*Column::name('value_type')
                ->label(__('pages/system/settings/settings.content.datatable.value_type'))
                ->filterable($this->value_types)
                ->searchable(),
            Column::name("type")
                ->label(__('pages/system/settings/settings.content.datatable.type'))
                ->filterable()
                ->searchable(),*/
            Column::callback(['description', 'name'], function ($description, $name) {
                return Str::limit(__("pages/settings/settings.descriptions.$name"));
            })
                ->label(__('pages/system/settings/settings.content.datatable.description'))
                ->filterable()
                ->searchable(),
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
        if ($this->type) {
            return Setting::where('type', $this->type);
        }
        return Setting::selectRaw('*');
    }


    /**
     * @return mixed
     */
    public function getValueTypesProperty()
    {
        return Setting::distinct('value_type')
            ->pluck('value_type');
    }

    /**
     * show edit modal
     * @param $id
     */
    public function showEditModal($id)
    {
        //get setting with data
        $this->setting = Setting::where('id', $id)
            ->first()
            ->toArray();

        $name = $this->setting['name'];
        $this->setting['name'] = __("pages/settings/settings.names.$name");
        //show the modal
        $this->showEditModal = true;
    }

    public function closeEditModal()
    {
        //close the modal
        $this->showEditModal = false;

        //empty setting data
        $this->setting = [];

        //reset validation messages
        $this->resetValidation();
    }

    /**
     * @param $id
     * @return void|null
     */
    public function update($id)
    {
        if (!Auth::guard('admin')->user()->can('settings.edit')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }

        //validate data
        $this->validate([
            'setting.value' => ['nullable'],
        ]);
        //set data
        $data = $this->setting;
        $data['value'] = Filter::RemoveHtml($this->setting['value']);
        DB::beginTransaction();
        try {
            //get setting
            $setting = Setting::findOrFail($id);

            //add log
            AdminLogs::log('edit', 'customers', [
                'old' => $setting,
                'new' => $data,
            ], "Edit: customer #$id");

            //update setting
            $setting->update([
                'value' => $data['value']
            ]);

            if(($this->type == 'posts' && $setting->key == 'user.allowed.posts') || ($this->type == 'offers' && $setting->key == 'max.advertiser.active.offers') ){
                Artisan::call('check:advertisers-allowed-posts');
            }

            if ($setting->key === 'maintenance.mode' && $data['value']) {
                AdvertiserUser::all()
                    ->each(function ($user) {
                        $user->update(['fcm_token' => null, 'is_online' => false]);
                        $user->tokens()
                            ->each(function ($token) {
                                $token->revoke();
                            });
                    });

                CustomerUser::all()
                    ->each(function ($user) {
                        $user->update(['fcm_token' => null, 'is_online' => false]);
                        $user->tokens()
                            ->each(function ($token) {
                                $token->revoke();
                            });
                    });
            }
            //close modal
            $this->closeEditModal();

            //reset validation messages
            $this->resetValidation();

            //send toastr alert with success
            $this->alert('success', __('toastr.success'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

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
        $this->editModalTexts = [
            'title' => __('pages/system/settings/settings.modal.edit.title'),
            'cancel' => __('pages/system/settings/settings.modal.edit.cancel'),
            'submit' => __('pages/system/settings/settings.modal.edit.submit'),
        ];
    }
}
