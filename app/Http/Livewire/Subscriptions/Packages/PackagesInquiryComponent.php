<?php

namespace App\Http\Livewire\Subscriptions\Packages;

use App\Helpers\Admins\AdminLogs;
use App\Helpers\Advertisers\PackageQuotas;
use App\Models\Subscriptions\Packages\Package;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Mediconesystems\LivewireDatatables\NumberColumn;
use Throwable;

class PackagesInquiryComponent extends LivewireDatatable
{
    use LivewireAlert;

    /**
     * set variables
     */
    public $exportable = true;
    public $hideable = 'select';
    public $beforeTableSlot = 'livewire.datatables.selected';
    public $model = Package::class;
    public bool $showDeleteModal = false;
    public bool $showRestoreModal = false;
    public array $deleteModalTexts;
    public string $page_type = 'all';
    public bool $has_delete = true;
    public bool $has_restore = false;
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
     * AdvertisersInquiryComponent constructor.
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
            Column::name('product_id')
                ->label(__('pages/subscriptions/packages/inquiry.datatable.product_id'))
                ->filterable()
                ->searchable()
                ->hide(),
            Column::name('name_en')
                ->label(__('pages/subscriptions/packages/inquiry.datatable.name_en'))
                ->filterable()
                ->searchable(),
            Column::name('name_ar')
                ->label(__('pages/subscriptions/packages/inquiry.datatable.name_ar'))
                ->filterable()
                ->searchable(),
            NumberColumn::name('maximum_posts')
                ->label(__('pages/subscriptions/packages/inquiry.datatable.maximum_posts'))
                ->filterable()
                ->searchable(),
            NumberColumn::name('maximum_offers')
                ->label(__('pages/subscriptions/packages/inquiry.datatable.maximum_offers'))
                ->filterable()
                ->searchable(),
            NumberColumn::name('maximum_monthly_offers')
                ->label(__('pages/subscriptions/packages/inquiry.datatable.maximum_monthly_offers'))
                ->filterable()
                ->searchable(),
            Column::callback('description_en', function ($description) {
                return Str::limit($description, 30);
            })
                ->label(__('pages/subscriptions/packages/inquiry.datatable.description_en'))
                ->filterable()
                ->searchable()
                ->hide(),
            Column::callback('description_ar', function ($description) {
                return Str::limit($description, 30);
            })
                ->label(__('pages/subscriptions/packages/inquiry.datatable.description_ar'))
                ->filterable()
                ->searchable()
                ->hide(),
            Column::callback('specifications_en', function ($specifications) {
                return nl2br(Str::limit($specifications, 30));
            })
                ->label(__('pages/subscriptions/packages/inquiry.datatable.specifications_en'))
                ->filterable()
                ->searchable()
                ->hide(),
            Column::callback('specifications_ar', function ($specifications) {
                return nl2br(Str::limit($specifications, 30));
            })
                ->label(__('pages/subscriptions/packages/inquiry.datatable.specifications_ar'))
                ->filterable()
                ->searchable()
                ->hide(),
            NumberColumn::callback(['price', 'subscription_type', 'currency'], function ($price, $subscription_type, $currency) {
                return "$price $currency / " . __("pages/subscriptions/packages/inquiry.datatable.duration_types.$subscription_type");
            })
                ->label(__('pages/subscriptions/packages/inquiry.datatable.price'))
                ->filterable()
                ->searchable()
                ->alignCenter(),
            NumberColumn::callback(['old_price', 'subscription_type', 'currency'], function ($old_price, $subscription_type, $currency) {
                return "$old_price $currency / " . __("pages/subscriptions/packages/inquiry.datatable.duration_types.$subscription_type");
            })
                ->label(__('pages/subscriptions/packages/inquiry.datatable.old_price'))
                ->filterable()
                ->searchable()
                ->alignCenter()
                ->hide(),
            NumberColumn::callback(['id', 'price'], function ($id, $price) {
                $package = Package::where('id', $id)
                    ->first();
                return $package->advertisers()
                    ->where('is_active', true)
                    ->where('is_current', true)
                    ->where('is_ended', false)
                    ->distinct('advertiser_id')
                    ->count();
            })
                ->label(__('pages/subscriptions/packages/inquiry.datatable.purchase_count'))
                ->filterable()
                ->searchable()
                ->alignCenter(),
            Column::name('duration')
                ->label(__('pages/subscriptions/packages/inquiry.datatable.duration'))
                ->searchable()
                ->hide(),
            BooleanColumn::name('is_visible')
                ->label(__('pages/subscriptions/packages/inquiry.datatable.is_visible'))
                ->filterable()
                ->searchable()
                ->hide(),
            BooleanColumn::name('is_active')
                ->label(__('pages/subscriptions/packages/inquiry.datatable.is_active'))
                ->filterable()
                ->searchable()
                ->hide(),
            BooleanColumn::name('is_trial')
                ->label(__('pages/subscriptions/packages/inquiry.datatable.is_trial'))
                ->filterable()
                ->searchable()
                ->hide(),
            Column::callback(['id', 'is_elite'], function ($id, $isElite) {
                return view('admin.pages.subscriptions.packages.elite-toggle', [
                    'id' => $id,
                    'isElite' => (bool) $isElite,
                ]);
            })
                ->label(__('pages/subscriptions/packages/inquiry.datatable.is_elite'))
                ->alignCenter()
                ->excludeFromExport()
                ->unsortable(),
            DateColumn::name('created_at')
                ->label(__('datatable.created_at'))
                ->filterable()
                ->searchable()
                ->hide(),
            Column::callback(['id'], function ($id) {
                return view('admin.pages.subscriptions.packages.table-actions', ['id' => $id,]);
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
        if ($this->page_type === 'active') {
            $query = Package::where('is_active', true);
        } else if ($this->page_type === 'inactive') {
            $query = Package::where('is_active', false);
        } else {
            $query = Package::selectRaw('*');
        }
        return $query;
    }

    /**
     * Instantly flip a package's "elite" flag from the datatable switch.
     * @param int $id
     */
    public function toggleElite($id)
    {
        if (!Auth::guard('admin')->user()->can('packages.edit')) {
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }

        $package = Package::findOrFail($id);
        $package->update(['is_elite' => !$package->is_elite]);

        //without this, subscribers already on this package would only pick up the
        //new elite flag at their next renewal/expiry — make it take effect now
        PackageQuotas::resyncSubscribersOfPackage($package);

        AdminLogs::log('edit', 'packages', ['package' => $package], "Toggle elite: package #{$id}");

        $this->alert('success', __('toastr.success'), [
            'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
        ]);
    }

    /**
     * show delete modal for selected rows, or a single package by id
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

        $this->setDeleteModalTextsForSelection();
        $this->showDeleteModal = true;
    }

    /**
     * Set delete modal title/content based on selection count and package name
     */
    protected function setDeleteModalTextsForSelection(): void
    {
        $base = 'pages/subscriptions/packages/inquiry.modal.delete';

        if (count($this->selected) === 1) {
            $package = Package::find($this->selected[0]);
            $name = '';
            if ($package) {
                $name = App::currentLocale() === 'ar'
                    ? ($package->name_ar ?: $package->name_en)
                    : ($package->name_en ?: $package->name_ar);
            }

            $this->deleteModalTexts = [
                'title' => __("$base.title"),
                'content' => __("$base.content", ['name' => $name]),
                'cancel' => __("$base.cancel"),
                'submit' => __("$base.submit"),
            ];

            return;
        }

        $this->deleteModalTexts = [
            'title' => __("$base.title_multiple"),
            'content' => __("$base.content_multiple"),
            'cancel' => __("$base.cancel"),
            'submit' => __("$base.submit"),
        ];
    }

    /**
     * show delete modal
     */
    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
    }

    /**
     * delete Selected data
     */
    public function deleteSelected()
    {
        if (!Auth::guard('admin')->user()->can('packages.delete')) {
            //send toastr alert with error
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
            $packages = Package::whereIn('id', $this->selected)
                ->withCount('advertisers')
                ->get();

            $inUse = $packages->first(function ($package) {
                return $package->advertisers_count > 0;
            });

            if ($inUse) {
                DB::rollBack();
                $this->alert('error', __('pages/subscriptions/packages/inquiry.modal.delete.in_use'), [
                    'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                ]);
                $this->showDeleteModal = false;
                return null;
            }

            parent::delete($this->selected);

            //set selected data to null
            $this->selected = [];

            //send toastr alert with success
            $this->alert('success', __('toastr.delete'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            //close modal
            $this->showDeleteModal = false;

            //add log
            AdminLogs::log('delete', 'packages', [
                'packages' => $packages
            ], "Delete: packages");

            $this->emitUp('recountCounters');
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
    }

    /**
     * set modal texts
     */
    public function setModalTexts()
    {
        $this->deleteModalTexts = [
            'title' => __('pages/subscriptions/packages/inquiry.modal.delete.title'),
            'content' => __('pages/subscriptions/packages/inquiry.modal.delete.content', ['name' => '']),
            'cancel' => __('pages/subscriptions/packages/inquiry.modal.delete.cancel'),
            'submit' => __('pages/subscriptions/packages/inquiry.modal.delete.submit'),
        ];
    }
}
