<?php

namespace App\Http\Livewire\System\Logs;

use App\Helpers\Admins\AdminLogs;
use App\Models\Users\Admins\Logs\AdminActionLogs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Mediconesystems\LivewireDatatables\NumberColumn;
use Mediconesystems\LivewireDatatables\TimeColumn;
use Throwable;

class SystemLogsComponent extends LivewireDatatable
{
    use LivewireAlert;

    /**
     * set variables
     */
    public $exportable = true;
    public $hideable = 'select';
    public $beforeTableSlot = 'livewire.datatables.selected';
    public $afterTableSlot = 'modals.system.logs.show';
    public $model = AdminActionLogs::class;
    public array $log;
    public bool $showDeleteModal = false;
    public bool $showMoreModal = false;
    public array $deleteModalTexts;
    public array $showMoreModalTexts;
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
            Column::name('summary')
                ->label(__('pages/system/logs/index.content.datatable.summary'))
                ->filterable()
                ->searchable(),
            NumberColumn::name('admin.id')
                ->label(__('pages/system/logs/index.content.datatable.admin_id'))
                ->filterable()
                ->searchable()
                ->hide(),
            Column::name('admin.name')
                ->label(__('pages/system/logs/index.content.datatable.admin_name'))
                ->filterable()
                ->searchable(),
            Column::name('type')
                ->label(__('pages/system/logs/index.content.datatable.type'))
                ->filterable()
                ->searchable(),
            Column::name("action")
                ->label(__('pages/system/logs/index.content.datatable.log_action'))
                ->filterable($this->actions)
                ->searchable(),
            DateColumn::name('created_at')
                ->label(__('pages/system/logs/index.content.datatable.date'))
                ->filterable()
                ->searchable(),
            TimeColumn::name('updated_at')
                ->label(__('pages/system/logs/index.content.datatable.time'))
                ->filterable()
                ->searchable(),
            Column::callback(['id'], function ($id) {
                return view('admin.pages.system.logs.table-actions', ['id' => $id]);
            })
                ->label(__('datatable.actions'))
                ->excludeFromExport()
                ->unsortable(),
        ];
    }

    /**
     * execute this query to render data
     * @return Builder
     */
    public function builder(): Builder
    {
        return AdminActionLogs::with('admin');
    }

    /**
     * @return array
     */
    public function getActionsProperty(): array
    {
        return [
            'add',
            'edit',
            'delete',
            'inquiry',
            'login',
            'send',
            'approve',
            'decline',
            'refund',
            'cancel',
            'join',
            'call',
            'restart',
            'reset',
            'import',
            'export'
        ];
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
            //get Logs
            $logs = AdminActionLogs::whereIn('id', $this->selected)
                ->get();

            //delete selected data
            parent::delete($this->selected);

            //reset selected to empty
            $this->selected = [];

            //send toastr alert with success
            $this->alert('success', __('toastr.delete'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            //hide modal
            $this->showDeleteModal = false;

            //add log
            AdminLogs::log('delete', 'logs', [
                'logs' => $logs
            ], "Delete: logs #$selected_data");

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
    public function showMoreModal($id)
    {
        //get user data
        $this->log = AdminActionLogs::where('id', $id)
            ->with('admin')
            ->first()
            ->toArray();

        //set show edit modal to true
        $this->showMoreModal = true;
    }

    /**
     * Close edit modal
     */
    public function closeShowMoreModal()
    {
        $this->showMoreModal = false;
        $this->log = [];
        //reset validation messages
        $this->resetValidation();
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
        $this->showMoreModalTexts = [
            'title' => __('pages/system/logs/index.modal.show.title'),
            'close' => __('pages/system/logs/index.modal.show.close'),
        ];
    }
}
