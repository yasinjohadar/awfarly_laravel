<?php

namespace App\Http\Livewire\Subscriptions\Packages;

use App\Helpers\Admins\AdminLogs;
use App\Helpers\Filter;
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
    public $afterTableSlot = 'modals.subscriptions.packages.edit';
    public $model = Package::class;
    public array $package;
    public bool $showDeleteModal = false;
    public bool $showEditModal = false;
    public bool $showRestoreModal = false;
    public array $deleteModalTexts;
    public string $page_type = 'all';
    public bool $has_delete = true;
    public bool $has_restore = false;
    public ?string $product_id = null;
    public ?string $name_en = null;
    public ?string $name_ar = null;
    public ?int $maximum_posts = null;
    public ?int $maximum_offers = null;
    public ?int $maximum_monthly_offers = null;
    public ?string $description_en = null;
    public ?string $description_ar = null;
    public ?string $specifications_en = null;
    public ?string $specifications_ar = null;
    public ?float $price = null;
    public ?float $old_price = null;
    public string $subscription_type = 'monthly';
    public string $currency = 'SAR';
    public $is_visible = true;
    public $is_active = true;
    public $is_trial = false;
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
     * show edit modal
     * @param $id
     */
    public function showEditModal($id)
    {
        //get package
        $package = Package::where('id', $id)
            ->first();

        $this->product_id = $package->product_id;
        $this->name_en = $package->name_en;
        $this->name_ar = $package->name_ar;
        $this->description_en = $package->description_en;
        $this->description_ar = $package->description_ar;
        $this->specifications_en = implode("\n", $package->specifications_en);
        $this->specifications_ar = implode("\n", $package->specifications_ar);
        $this->maximum_posts = $package->maximum_posts;
        $this->maximum_offers = $package->maximum_offers;
        $this->maximum_monthly_offers = $package->maximum_monthly_offers;
        $this->price = $package->price;
        $this->old_price = $package->old_price;
        $this->subscription_type = $package->subscription_type;
        $this->currency = $package->currency;
        $this->is_visible = $package->is_visible ? 1 : 0;
        $this->is_active = $package->is_active ? 1 : 0;
        $this->is_trial = $package->is_trial ? 1 : 0;
        $this->package = $package->toArray();
        //show the modal
        $this->showEditModal = true;
    }

    public function closeEditModal()
    {
        //close the modal
        $this->showEditModal = false;


        //reset validation messages
        $this->resetValidation();
    }

    public function update($id)
    {
        if (!Auth::guard('admin')->user()->can('packages.edit')) {
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }

        $this->validate([
            'product_id' => ['nullable', 'string', "unique:packages,product_id,{$id}"],
            'name_en' => ['required', 'string', "unique:packages,name_en,{$id}"],
            'name_ar' => ['required', 'string', "unique:packages,name_ar,{$id}"],
            'maximum_posts' => ['required', 'integer'],
            'maximum_offers' => ['required', 'integer'],
            'maximum_monthly_offers' => ['required', 'integer', 'min:0'],
            'description_en' => ['nullable', 'string'],
            'description_ar' => ['nullable', 'string'],
            'specifications_en' => ['required', 'string'],
            'specifications_ar' => ['required', 'string'],
            'price' => ['required', 'numeric'],
            'old_price' => ['required', 'numeric'],
            'subscription_type' => ['required', 'in:daily,weekly,monthly,two_months,three_months,six_months,yearly'],
            'currency' => ['required', 'in:SAR'], //usd
            'is_visible' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'is_trial' => ['required', 'boolean'],
        ]);

        $package = Package::where('id', $id)
            ->first();

        DB::beginTransaction();
        try {

            //close modal
            $this->closeEditModal();

            $description_en = $this->description_en ? Filter::RemoveHtml($this->description_en) : null;
            if ($description_en) {
                $description_en = preg_replace("/[\r\n]+/", "\n", $description_en);
            }

            $description_ar = $this->description_ar ? Filter::RemoveHtml($this->description_ar) : null;
            if ($description_ar) {
                $description_ar = preg_replace("/[\r\n]+/", "\n", $description_ar);
            }

            $specifications_en = Filter::RemoveHtml($this->specifications_en);
            $specifications_en = preg_replace("/[\r\n]+/", "\n", $specifications_en);
            $specifications_en = explode("\n", $specifications_en);

            $specifications_ar = Filter::RemoveHtml($this->specifications_ar);
            $specifications_ar = preg_replace("/[\r\n]+/", "\n", $specifications_ar);
            $specifications_ar = explode("\n", $specifications_ar);

            if ($this->subscription_type === 'monthly') {
                $duration = 1;
            } else if ($this->subscription_type === 'two_months') {
                $duration = 2;
            } else if ($this->subscription_type === 'three_months') {
                $duration = 3;
            } else if ($this->subscription_type === 'six_months') {
                $duration = 6;
            } else if ($this->subscription_type === 'yearly') {
                $duration = 1;
            } else {
                $duration = 1;
            }
            $package->update([
                'product_id' => $this->product_id ? Filter::RemoveHtml($this->product_id) : null,
                'name_en' => Filter::RemoveHtml($this->name_en),
                'name_ar' => Filter::RemoveHtml($this->name_ar),
                'maximum_posts' => $this->maximum_posts,
                'maximum_offers' => $this->maximum_offers,
                'maximum_monthly_offers' => $this->maximum_monthly_offers,
                'description_en' => $description_en,
                'description_ar' => $description_ar,
                'specifications_en' => $specifications_en,
                'specifications_ar' => $specifications_ar,
                'price' => $this->price,
                'old_price' => $this->old_price,
                'duration' => $duration,
                'subscription_type' => $this->subscription_type,
                'currency' => $this->currency,
                'is_visible' => $this->is_visible,
                'is_active' => $this->is_active,
                'is_trial' => $this->is_trial,
            ]);

            //reset validation messages
            $this->resetValidation();

            //send toastr alert with success
            $this->alert('success', __('toastr.success'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
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
        DB::commit();
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
