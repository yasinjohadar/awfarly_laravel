<?php

namespace App\Http\Livewire\Admins\Roles;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Mediconesystems\LivewireDatatables\NumberColumn;
use Spatie\Permission\Models\Role;
use Throwable;

class AdminsRolesInquiryComponent extends LivewireDatatable
{
    use LivewireAlert;

    /**
     * set variables
     */
    public $exportable = true;
    public $hideable = 'select';
    public $beforeTableSlot = 'livewire.datatables.selected';
    public $afterTableSlot = '';
    public $model = Role::class;
    public array $user;
    public Collection $languages;
    public bool $showDeleteModal = false;
    public array $deleteModalTexts;
    public bool $has_delete = true;

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
            Column::name('name')
                ->label(__('pages/admins/roles/index.content.datatable.name'))
                ->filterable()
                ->searchable(),
            Column::name('guard_name')
                ->label(__('pages/admins/roles/index.content.datatable.guard'))
                ->filterable()
                ->searchable(),
            DateColumn::name('created_at')
                ->label(__('pages/admins/roles/index.content.datatable.created_at'))
                ->filterable()
                ->searchable(),

            Column::callback(['id', 'name'], function ($id) {
                return view('admin.pages.admins.roles.table-actions', ['id' => $id]);
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
        return Role::select('id', 'name', 'guard_name', 'created_at');
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
        DB::beginTransaction();
        try {

            //delete selected data
            Role::destroy($this->selected);

            //reset selected to empty
            $this->selected = [];

            //send toastr alert with success
            $this->alert('success', __('toastr.delete'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            //hide modal
            $this->showDeleteModal = false;
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
     * set Modal Texts
     */
    public function setModalTexts()
    {
        $this->deleteModalTexts = [
            'title' => __('pages/admins/roles/index.modal.delete.title'),
            'content' => __('pages/admins/roles/index.modal.delete.content'),
            'cancel' => __('pages/admins/roles/index.modal.delete.cancel'),
            'submit' => __('pages/admins/roles/index.modal.delete.submit'),
        ];
    }
}
