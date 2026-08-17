<?php

namespace App\Http\Livewire\Advertisements\Side;

use App\Models\Advertisements\Side\SideAdvertisement;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Mediconesystems\LivewireDatatables\NumberColumn;
use Throwable;

class SideAdvertisementsInquiryComponent extends LivewireDatatable
{
    use LivewireAlert;

    /**
     * set variables
     */
    public $exportable = true;
    public $hideable = 'select';
    public $beforeTableSlot = 'livewire.datatables.selected';
    public $afterTableSlot = '';
    public $model = SideAdvertisement::class;
    public array $user;
    public bool $showDeleteModal = false;
    public array $deleteModalTexts;
    public bool $has_delete = true;
    public ?string $page_type = null;


    /**
     * @var array
     */
    public $listeners = ['rerenderDataTable' => 'changeType'];

    /**
     * @param $params
     */
    public function changeType($params)
    {
        $this->page_type = $params['page_type'];

        //reset to the first page so a stale page number from the previous
        //tab cannot land on an out-of-range page and show an empty table
        $this->resetPage();
    }


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

            Column::callback('advertisement_url', function ($advertiser_url) {
                return $advertiser_url ?: '-';
            })
                ->label(__('pages/advertisements/side/inquiry.content.datatable.url'))
                ->filterable()
                ->searchable()
                ->truncate(60)
                ->alignCenter(),
            Column::callback('side', function ($side) {
                return __("pages/advertisements/side/inquiry.content.datatable.side.$side");
            })
                ->label(__('pages/advertisements/side/inquiry.content.datatable.side.title'))
                ->filterable([
                    'right' => __('pages/advertisements/side/inquiry.content.datatable.side.right'),
                    'left' => __('pages/advertisements/side/inquiry.content.datatable.side.left'),
                ])
                ->searchable(),
            DateColumn::callback('starts_at', function ($starts_at) {
                return $starts_at ? Carbon::parse($starts_at)->format('Y-m-d h:i A') : '-';
            })
                ->label(__('pages/advertisements/side/inquiry.content.datatable.starts_at'))
                ->filterable()
                ->searchable()
                ->alignCenter(),
            DateColumn::callback('ends_at', function ($ends_at) {
                return $ends_at ? Carbon::parse($ends_at)->format('Y-m-d h:i A') : '-';
            })
                ->label(__('pages/advertisements/side/inquiry.content.datatable.ends_at'))
                ->filterable()
                ->searchable()
                ->alignCenter()
                ->hide(),
            Column::callback(['id'], function ($id) {
                return view('admin.pages.advertisements.side.table-actions', ['id' => $id]);
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
        if ($this->page_type === 'active') {
            return SideAdvertisement::where('ends_at', '>', now())
                ->orWhereNull('ends_at');
        } elseif ($this->page_type === 'expired') {
            return SideAdvertisement::where('ends_at', '<', now());
        }
        return SideAdvertisement::selectRaw('*');
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
            $advertisements = SideAdvertisement::whereIn('id', $this->selected)
                ->get();

            $advertisements->each->delete();

            //reset selected to empty
            $this->selected = [];

            //send toastr alert with success
            $this->alert('success', __('toastr.delete'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            //hide modal
            $this->showDeleteModal = false;

            $this->emitUp('recountCounters');
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
            'title' => __('pages/advertisements/side/inquiry.modal.delete.title'),
            'content' => __('pages/advertisements/side/inquiry.modal.delete.content'),
            'cancel' => __('pages/advertisements/side/inquiry.modal.delete.cancel'),
            'submit' => __('pages/advertisements/side/inquiry.modal.delete.submit'),
        ];
    }
}
