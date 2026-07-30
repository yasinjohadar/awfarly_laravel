<?php

namespace App\Http\Livewire\Advertisers\BusinessTypes;

use App\Helpers\Admins\AdminLogs;
use App\Helpers\Filter;
use App\Models\Users\Advertisers\AdvertiserUser;
use App\Models\Users\Advertisers\BusinessTypes\AdvertiserBusinessType;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\WithFileUploads;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Mediconesystems\LivewireDatatables\NumberColumn;
use Throwable;

class BusinessTypesInquiryComponent extends LivewireDatatable
{
    use LivewireAlert;

    use WithFileUploads;

    public $exportable = true;
    public $hideable = 'select';
    public $beforeTableSlot = 'livewire.datatables.selected';
    public $afterTableSlot = 'modals.users.advertisers.business-types.edit';
    public $model = AdvertiserBusinessType::class;
    public array $business_type;
    public bool $showDeleteModal = false;
    public bool $showEditModal = false;
    public array $deleteModalTexts;
    public array $editModalTexts;
    private string $name_column = '';
    public bool $has_delete = true;


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
            Column::checkbox(),
            NumberColumn::name('id')
                ->label('#')
                ->filterable()
                ->searchable(),
            Column::name('name_en')
                ->label(__('pages/advertisers/business-types/index.content.datatable.name_en'))
                ->filterable()
                ->searchable(),
            Column::name('name_ar')
                ->label(__('pages/advertisers/business-types/index.content.datatable.name_ar'))
                ->filterable()
                ->searchable(),
            BooleanColumn::name('is_active')
                ->label(__('pages/advertisers/business-types/index.content.datatable.is_active'))
                ->filterable()
                ->searchable(),
            Column::callback(['id'], function ($id) {
                return view('admin.pages.advertisers.business-types.table-actions', ['id' => $id]);
            })
                ->label(__('datatable.actions'))
                ->excludeFromExport()
                ->unsortable(),
        ];
    }

    /**
     * set query to render data
     * @return Builder
     */
    public function builder(): Builder
    {
        return AdvertiserBusinessType::selectRaw('*');
    }


    /**
     * show delete modal for selected rows, or a single business type by id
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

        $this->deleteModalTexts = [
            'title' => __('pages/advertisers/business-types/index.modal.delete.title'),
            'content' => count($this->selected) === 1
                ? __('pages/advertisers/business-types/index.modal.delete.content_single')
                : __('pages/advertisers/business-types/index.modal.delete.content'),
            'cancel' => __('pages/advertisers/business-types/index.modal.delete.cancel'),
            'submit' => __('pages/advertisers/business-types/index.modal.delete.submit'),
        ];
        $this->showDeleteModal = true;
    }

    /**
     * delete Selected data
     */
    public function deleteSelected()
    {
        if (!Auth::guard('admin')->user()->can('business.types.delete')) {
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
            $inUse = AdvertiserUser::whereIn('business_type', $this->selected)->exists();
            if ($inUse) {
                DB::rollBack();
                $this->alert('error', __('pages/advertisers/business-types/index.modal.delete.in_use'), [
                    'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                ]);
                $this->showDeleteModal = false;
                return null;
            }

            $business_types = AdvertiserBusinessType::whereIn('id', $this->selected)->get();

            AdvertiserBusinessType::whereIn('id', $this->selected)->delete();

            $this->selected = [];

            $this->alert('success', __('toastr.delete'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            AdminLogs::log('delete', 'business_types', [
                'business_type' => $business_types,
            ], 'Delete: business_type');

            $this->showDeleteModal = false;
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
        //get user with data
        $this->business_type = AdvertiserBusinessType::where('id', $id)
            ->first()
            ->toArray();

        $this->business_type['is_active'] = (int)$this->business_type['is_active'];

        //show the modal
        $this->showEditModal = true;
    }

    public function closeEditModal()
    {
        //close the modal
        $this->showEditModal = false;

        //empty user data
        $this->business_type = [];

        //reset validation messages
        $this->resetValidation();
    }

    /**
     * @param $id
     * @return void|null
     */
    public function update($id)
    {
        if (!Auth::guard('admin')->user()->can('business.types.edit')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }
        //validate data
        $this->validate([
            'business_type.name_en' => ['required', "unique:advertisers_business_types,name_en,$id"],
            'business_type.name_ar' => ['required', "unique:advertisers_business_types,name_ar,$id"],
            'business_type.is_active' => ['required', 'boolean'],
        ]);


        //set data
        $data = $this->business_type;
        $data['name_en'] = Filter::RemoveHtml($this->business_type['name_en']);
        $data['name_ar'] = Filter::RemoveHtml($this->business_type['name_ar']);

        //unset the user id
        unset($data['id']);

        DB::beginTransaction();
        try {
            //get user
            $business_type = AdvertiserBusinessType::findOrFail($id);

            //add log
            AdminLogs::log('edit', 'business_types', [
                'old' => $business_type,
                'new' => $data,
            ], "Edit: business_type #$id");

            //update user
            $business_type->update($data);

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
        $this->deleteModalTexts = [
            'title' => __('pages/advertisers/business-types/index.modal.delete.title'),
            'content' => __('pages/advertisers/business-types/index.modal.delete.content'),
            'cancel' => __('pages/advertisers/business-types/index.modal.delete.cancel'),
            'submit' => __('pages/advertisers/business-types/index.modal.delete.submit'),
        ];
        $this->editModalTexts = [
            'title' => __('pages/advertisers/business-types/index.modal.edit.title'),
            'cancel' => __('pages/advertisers/business-types/index.modal.edit.cancel'),
            'submit' => __('pages/advertisers/business-types/index.modal.edit.submit'),
        ];
    }
}
