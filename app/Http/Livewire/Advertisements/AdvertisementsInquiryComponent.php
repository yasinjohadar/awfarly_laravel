<?php

namespace App\Http\Livewire\Advertisements;

use App\Helpers\Files;
use App\Models\Advertisements\Advertisement;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Mediconesystems\LivewireDatatables\NumberColumn;
use Str;
use Throwable;

class AdvertisementsInquiryComponent extends LivewireDatatable
{
    use LivewireAlert;

    /**
     * set variables
     */
    public $exportable = true;
    public $hideable = 'select';
    public $beforeTableSlot = 'livewire.datatables.selected';
    public $afterTableSlot = '';
    public $model = Advertisement::class;
    public array $user;
    public Collection $languages;
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
            Column::callback('type', function ($type) {
                return __("pages/advertisements/inquiry.content.datatable.type_values.$type");
            })
                ->label(__('pages/advertisements/inquiry.content.datatable.type'))
                ->filterable([
                    'any' => __('pages/advertisements/inquiry.content.datatable.type_values.any'),
                    'website' => __('pages/advertisements/inquiry.content.datatable.type_values.website'),
                    'mobile' => __('pages/advertisements/inquiry.content.datatable.type_values.mobile'),
                ])
                ->searchable(),
            Column::callback('users', function ($users) {
                return __("pages/advertisements/inquiry.content.datatable.users_values.$users");
            })
                ->label(__('pages/advertisements/inquiry.content.datatable.users'))
                ->filterable([
                    'any' => __('pages/advertisements/inquiry.content.datatable.users_values.any'),
                    'advertisers' => __('pages/advertisements/inquiry.content.datatable.users_values.advertisers'),
                    'customers' => __('pages/advertisements/inquiry.content.datatable.users_values.customers'),
                ])
                ->searchable(),
            Column::callback('advertiser_name', function ($advertiser_name) {
                return $advertiser_name ?: '-';
            })
                ->label(__('pages/advertisements/inquiry.content.datatable.name'))
                ->filterable()
                ->searchable()
                ->alignCenter(),
            Column::callback(['advertiser_image', 'advertiser_name'], function ($image, $name) {
                return '<img class="rounded-circle" width="34" height="34" src=' . route('users.profile.image', $image) . ' alt="' . $name . '"/>';
            })
                ->label(__('pages/advertisements/inquiry.content.datatable.image'))
                ->filterable()
                ->searchable()
                ->hide(),
            Column::callback('advertiser_url', function ($advertiser_url) {
                return $advertiser_url ?: '-';
            })
                ->label(__('pages/advertisements/inquiry.content.datatable.url'))
                ->filterable()
                ->searchable()
                ->alignCenter(),
            Column::callback('content', function ($content) {
                return $content ? Str::limit($content, 40) : '-';
            })
                ->label(__('pages/advertisements/inquiry.content.datatable.content'))
                ->filterable()
                ->searchable(),
            Column::callback('categories', function ($categories) {
                return $categories ? Str::limit($categories, 40) : '-';
            })
                ->label(__('pages/advertisements/inquiry.content.datatable.categories'))
                ->filterable()
                ->searchable()
                ->hide(),
            Column::callback('countries', function ($countries) {
                return $countries ? Str::limit($countries, 40) : '-';
            })
                ->label(__('pages/advertisements/inquiry.content.datatable.countries'))
                ->filterable()
                ->searchable()
                ->hide(),
            Column::callback('cities', function ($cities) {
                return $cities ? Str::limit($cities, 40) : '-';
            })
                ->label(__('pages/advertisements/inquiry.content.datatable.cities'))
                ->filterable()
                ->searchable()
                ->hide(),
            DateColumn::callback('starts_at', function ($starts_at) {
                return $starts_at ? Carbon::parse($starts_at)->format('Y-m-d h:i A') : '-';
            })
                ->label(__('pages/advertisements/inquiry.content.datatable.starts_at'))
                ->filterable()
                ->searchable()
                ->alignCenter(),
            DateColumn::callback('ends_at', function ($ends_at) {
                return $ends_at ? Carbon::parse($ends_at)->format('Y-m-d h:i A') : '-';
            })
                ->label(__('pages/advertisements/inquiry.content.datatable.ends_at'))
                ->filterable()
                ->searchable()
                ->alignCenter()
                ->hide(),
            BooleanColumn::callback(['ends_at', 'is_active'], function ($ends_at, $is_active) {
                if ($is_active && (!($ends_at != null) || !Carbon::make($ends_at)->isPast())) {
                    $value = true;
                } else {
                    $value = false;
                }
                return view('datatables::boolean', ['value' => $value]);
            })
                ->label(__('pages/advertisements/inquiry.content.datatable.is_active'))
                ->searchable(),
            Column::callback(['id'], function ($id) {
                return view('admin.pages.advertisements.table-actions', ['id' => $id]);
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
            return Advertisement::where(function ($q) {
                return $q->where('ends_at', '>', now())
                    ->orWhereNull('ends_at');
            })
                ->where('is_active', true);
        } elseif ($this->page_type === 'expired') {
            return Advertisement::where('ends_at', '<', now());
        }
        return Advertisement::selectRaw('*');
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
            $advertisements = Advertisement::whereIn('id', $this->selected)
                ->get();

            foreach ($advertisements as $advertisement) {
                if ($advertisement->advertiser_image) {
                    Files::deleteS3File($advertisement->advertiser_image);
                }
                if ($advertisement->getMedia('advertisements')->count() > 0) {
                    foreach ($advertisement->getMedia('advertisements') as $media) {
                        $media->delete();
                    }
                }
            }

            parent::delete($this->selected);
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
            'title' => __('pages/admins/roles/index.modal.delete.title'),
            'content' => __('pages/admins/roles/index.modal.delete.content'),
            'cancel' => __('pages/admins/roles/index.modal.delete.cancel'),
            'submit' => __('pages/admins/roles/index.modal.delete.submit'),
        ];
    }
}
