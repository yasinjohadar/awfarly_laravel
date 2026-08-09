<?php

namespace App\Http\Livewire\Currencies;

use App\Helpers\Admins\AdminLogs;
use App\Helpers\Filter;
use App\Models\Currencies\Currency;
use App\Models\Subscriptions\Packages\Package;
use Exception;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Mediconesystems\LivewireDatatables\NumberColumn;
use Throwable;

class CurrenciesInquiryComponent extends LivewireDatatable
{
    use LivewireAlert;

    public $exportable = true;
    public $hideable = 'select';
    public $beforeTableSlot = 'livewire.datatables.selected';
    public $afterTableSlot = 'modals.currencies.edit';
    public $model = Currency::class;
    public array $currency;
    public bool $showDeleteModal = false;
    public bool $showEditModal = false;
    public array $deleteModalTexts;
    public array $editModalTexts;
    public bool $has_delete = true;

    /**
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
            Column::name('code')
                ->label(__('pages/currencies/index.content.datatable.code'))
                ->filterable()
                ->searchable(),
            Column::name('name_en')
                ->label(__('pages/currencies/index.content.datatable.name_en'))
                ->filterable()
                ->searchable(),
            Column::name('name_ar')
                ->label(__('pages/currencies/index.content.datatable.name_ar'))
                ->filterable()
                ->searchable(),
            Column::name('symbol')
                ->label(__('pages/currencies/index.content.datatable.symbol')),
            Column::name('exchange_rate')
                ->label(__('pages/currencies/index.content.datatable.exchange_rate')),
            BooleanColumn::name('is_base')
                ->label(__('pages/currencies/index.content.datatable.is_base'))
                ->filterable()
                ->searchable(),
            BooleanColumn::name('is_active')
                ->label(__('pages/currencies/index.content.datatable.active'))
                ->filterable()
                ->searchable(),
            BooleanColumn::name('is_visible')
                ->label(__('pages/currencies/index.content.datatable.visible'))
                ->filterable()
                ->searchable(),
            Column::callback(['id'], function ($id) {
                return view('admin.pages.currencies.table-actions', ['id' => $id]);
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
        return Currency::orderBy('order');
    }

    /**
     * show delete modal for selected rows, or a single currency by id
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
     * Set delete modal title/content based on selection count and currency name
     */
    protected function setDeleteModalTextsForSelection(): void
    {
        $base = 'pages/currencies/index.modal.delete';

        if (count($this->selected) === 1) {
            $currency = Currency::find($this->selected[0]);
            $name = '';
            if ($currency) {
                $name = App::currentLocale() === 'ar'
                    ? ($currency->name_ar ?: $currency->name_en)
                    : ($currency->name_en ?: $currency->name_ar);
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
     * delete Selected data
     */
    public function deleteSelected()
    {
        if (!Auth::guard('admin')->user()->can('currencies.delete')) {
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
            //get currencies
            $currencies = Currency::whereIn('id', $this->selected)->get();

            //block delete if a currency is the base currency
            $hasBase = $currencies->contains(fn ($currency) => $currency->is_base);

            //block delete if a currency is currently used by any package
            $codes = $currencies->pluck('code');
            $hasUsage = $hasBase ? true : Package::whereIn('currency', $codes)->exists();

            if ($hasBase || $hasUsage) {
                DB::rollBack();
                $this->alert('error', __('pages/currencies/index.modal.delete.in_use'), [
                    'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                ]);
                $this->showDeleteModal = false;
                return null;
            }

            //delete data
            parent::delete($this->selected);

            //set selected data to null
            $this->selected = [];

            //send toastr alert with success
            $this->alert('success', __('toastr.delete'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            //add log
            AdminLogs::log('delete', 'currencies', [
                'currencies' => $currencies
            ], "Delete: currencies");

            //close modal
            $this->showDeleteModal = false;
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
     * show edit modal
     * @param $id
     */
    public function showEditModal($id)
    {
        $this->currency = Currency::where('id', $id)
            ->first()
            ->toArray();

        $this->currency['is_base'] = $this->currency['is_base'] ? 1 : 0;
        $this->currency['is_active'] = $this->currency['is_active'] ? 1 : 0;
        $this->currency['is_visible'] = $this->currency['is_visible'] ? 1 : 0;

        //show the modal
        $this->showEditModal = true;
    }

    public function closeEditModal()
    {
        //close the modal
        $this->showEditModal = false;

        //empty currency data
        $this->currency = [];

        //reset validation messages
        $this->resetValidation();
    }

    /**
     * @param $id
     * @return void|null
     */
    public function update($id)
    {
        if (!Auth::guard('admin')->user()->can('currencies.edit')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }

        //validate data
        $this->validate([
            'currency.code' => ['required', "unique:currencies,code,$id"],
            'currency.name_en' => ['required'],
            'currency.name_ar' => ['required'],
            'currency.symbol' => ['nullable'],
            'currency.exchange_rate' => ['required', 'numeric', 'min:0.000001'],
            'currency.is_base' => ['boolean'],
            'currency.is_active' => ['boolean'],
            'currency.is_visible' => ['boolean'],
        ]);

        $currency = Currency::findOrFail($id);

        //base currency cannot be deactivated or hidden
        if ($currency->is_base && (!$this->currency['is_active'] || !$this->currency['is_visible'])) {
            $this->alert('error', __('pages/currencies/index.modal.edit.base_must_stay_active'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }

        //set data
        $data = [
            'code' => strtoupper(Filter::RemoveHtml($this->currency['code'])),
            'name_en' => Filter::RemoveHtml($this->currency['name_en']),
            'name_ar' => Filter::RemoveHtml($this->currency['name_ar']),
            'symbol' => isset($this->currency['symbol']) ? Filter::RemoveHtml($this->currency['symbol']) : null,
            'exchange_rate' => $this->currency['exchange_rate'],
            'is_active' => $this->currency['is_active'],
            'is_visible' => $this->currency['is_visible'],
        ];

        $becomingBase = !$currency->is_base && $this->currency['is_base'];

        DB::beginTransaction();
        try {
            if ($becomingBase) {
                //unset the previous base currency
                Currency::where('is_base', true)->update(['is_base' => false]);
                $data['is_base'] = true;
                $data['exchange_rate'] = 1;
                $data['is_active'] = true;
                $data['is_visible'] = true;
            } elseif ($currency->is_base) {
                //base currency's rate always stays 1
                $data['is_base'] = true;
                $data['exchange_rate'] = 1;
            } else {
                $data['is_base'] = false;
            }

            //add log
            AdminLogs::log('edit', 'currencies', [
                'old' => $currency,
                'new' => $data,
            ], "Edit: currency #$id");

            //update currency
            $currency->update($data);

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
            'title' => __('pages/currencies/index.modal.delete.title'),
            'content' => __('pages/currencies/index.modal.delete.content'),
            'cancel' => __('pages/currencies/index.modal.delete.cancel'),
            'submit' => __('pages/currencies/index.modal.delete.submit'),
        ];
        $this->editModalTexts = [
            'title' => __('pages/currencies/index.modal.edit.title'),
            'cancel' => __('pages/currencies/index.modal.edit.cancel'),
            'submit' => __('pages/currencies/index.modal.edit.submit'),
        ];
    }
}
