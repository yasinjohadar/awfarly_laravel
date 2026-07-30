<?php

namespace App\Http\Livewire\Subscriptions\Payments;

use App\Helpers\Admins\AdminLogs;
use App\Models\Subscriptions\Packages\Advertisers\AdvertiserPackages;
use App\Models\Subscriptions\Packages\Package;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Mediconesystems\LivewireDatatables\NumberColumn;
use Throwable;

class PaymentsInquiryComponent extends LivewireDatatable
{
    use LivewireAlert;

    /**
     * set variables
     */
    public $exportable = true;
    public $hideable = 'select';
    public $beforeTableSlot = 'livewire.datatables.selected-soft-delete';
    public $afterTableSlot = 'modals.subscriptions.payments.edit';
    public string $afterTableSlot2 = 'modals.subscriptions.payments.restore';
    public $model = AdvertiserPackages::class;
    public array $payment;
    public bool $showDeleteModal = false;
    public bool $showEditModal = false;
    public bool $showRestoreModal = false;
    public array $restoreModalTexts;
    public array $deleteModalTexts;
    public array $editModalTexts;
    public string $page_type = 'all';
    public string $delete_type = 'soft';
    public bool $has_delete = true;
    public bool $has_restore = true;
    public ?int $restore = null;

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

        //get language column to show
        $name_column = App::currentLocale() === 'ar' ? 'name_ar' : 'name_en';

        return [
            Column::checkbox(),
            NumberColumn::name('id')
                ->label('#')
                ->filterable()
                ->searchable(),
            NumberColumn::name('package_id')
                ->label(__('pages/subscriptions/payments/inquiry.datatable.package_id'))
                ->filterable()
                ->searchable()
                ->linkTo('admin/subscriptions/packages')
                ->hide(),
            Column::name("package.{$name_column}")
                ->label(__('pages/subscriptions/payments/inquiry.datatable.package_name'))
                ->filterable()
                ->searchable(),
            NumberColumn::name('advertiser_id')
                ->label(__('pages/subscriptions/payments/inquiry.datatable.advertiser_id'))
                ->filterable()
                ->searchable()
                ->linkTo('admin/advertisers')
                ->hide(),
            Column::name('advertisers_users.name')
                ->label(__('pages/subscriptions/payments/inquiry.datatable.advertiser_name'))
                ->filterable()
                ->searchable(),
            DateColumn::name('starts_at')
                ->label(__('pages/subscriptions/payments/inquiry.datatable.starts_at'))
                ->filterable()
                ->searchable()
                ->hide(),
            DateColumn::name('ends_at')
                ->label(__('pages/subscriptions/payments/inquiry.datatable.ends_at'))
                ->filterable()
                ->searchable(),
            NumberColumn::name('purchase_count')
                ->label(__('pages/subscriptions/payments/inquiry.datatable.purchase_count'))
                ->filterable()
                ->searchable(),
            NumberColumn::callback(['purchase_count', 'package_id'], function ($count, $id) {
                $package = Package::query()
                    ->where('id', $id)
                    ->first();

                return $package ? ((int)$package->price * $count) : '-';
            })
                ->label(__('pages/subscriptions/payments/inquiry.datatable.total_price'))
                ->exportCallback(function ($id) {
                    $payment = AdvertiserPackages::query()
                        ->where('id', $id)
                        ->first();
                    if (!$payment || !$payment->package) {
                        return '-';
                    }
                    return ((int)$payment->package->price * $payment->purchase_count);
                }),
            BooleanColumn::name('is_active')
                ->label(__('pages/subscriptions/payments/inquiry.datatable.is_active'))
                ->filterable()
                ->searchable(),
            BooleanColumn::name('is_ended')
                ->label(__('pages/subscriptions/payments/inquiry.datatable.is_ended'))
                ->filterable()
                ->searchable(),
            BooleanColumn::name('is_current')
                ->label(__('pages/subscriptions/payments/inquiry.datatable.is_current'))
                ->filterable()
                ->searchable(),
            DateColumn::name('created_at')
                ->label(__('datatable.created_at'))
                ->filterable()
                ->searchable()
                ->hide(),
            Column::callback(['id', 'deleted_at'], function ($id, $deleted_at) {
                return view('admin.pages.subscriptions.payments.table-actions', ['id' => $id, 'deleted_at' => $deleted_at]);
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
            $package = AdvertiserPackages::where('advertiser_packages.is_active', true)
                ->where('advertiser_packages.is_ended', false)
                ->where('advertiser_packages.ends_at', '>', now());
        } else if ($this->page_type === 'expired') {
            $package = AdvertiserPackages::where('advertiser_packages.is_ended', true)
                ->orWhere('advertiser_packages.ends_at', '<', now());
        } else if ($this->page_type === 'deleted') {
            $package = AdvertiserPackages::onlyTrashed();
        } else {
            $package = AdvertiserPackages::withTrashed();
        }
        return $package->leftJoin('packages', 'packages.id', 'advertiser_packages.package_id')
            ->leftJoin('advertisers_users', 'advertisers_users.id', 'advertiser_packages.advertiser_id');
    }

    /**
     * show delete modal for selected rows, or a single payment by id
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
     * Set delete modal title/content based on selection
     */
    protected function setDeleteModalTextsForSelection(): void
    {
        $base = 'pages/subscriptions/payments/inquiry.modal.delete';

        if (count($this->selected) === 1) {
            $payment = AdvertiserPackages::withTrashed()
                ->with(['package', 'advertiser'])
                ->find($this->selected[0]);

            $packageName = '-';
            $advertiserName = '-';
            if ($payment) {
                if ($payment->package) {
                    $packageName = App::currentLocale() === 'ar'
                        ? ($payment->package->name_ar ?: $payment->package->name_en)
                        : ($payment->package->name_en ?: $payment->package->name_ar);
                }
                $advertiserName = $payment->advertiser->name ?? '-';
            }

            $this->deleteModalTexts = [
                'title' => __("$base.title"),
                'select-option' => __("$base.select-option"),
                'soft-delete' => __("$base.soft-delete"),
                'permanent-delete' => __("$base.permanent-delete"),
                'content' => __("$base.content", [
                    'name' => $packageName,
                    'advertiser' => $advertiserName,
                ]),
                'cancel' => __("$base.cancel"),
                'submit' => __("$base.submit"),
            ];

            return;
        }

        $this->deleteModalTexts = [
            'title' => __("$base.title_multiple"),
            'select-option' => __("$base.select-option"),
            'soft-delete' => __("$base.soft-delete"),
            'permanent-delete' => __("$base.permanent-delete"),
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
        if (!Auth::guard('admin')->user()->can('payments.delete')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }
        DB::beginTransaction();
        try {
            //get advertisers
            $payments = AdvertiserPackages::whereIn('id', $this->selected)
                ->get();

            if ($this->delete_type === 'soft') {
                //delete data
                AdvertiserPackages::withTrashed()
                    ->whereIn('id', $this->selected)
                    ->delete();
            } else {
                AdvertiserPackages::withTrashed()
                    ->whereIn('id', $this->selected)
                    ->forceDelete();
            }

            //set selected data to null
            $this->selected = [];

            //send toastr alert with success
            $this->alert('success', __('toastr.delete'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            //close modal
            $this->showDeleteModal = false;

            //add log
            AdminLogs::log('delete', 'payments', [
                'payments' => $payments
            ], "Delete: payments");


            $this->reset('delete_type');
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
     * show edit modal
     * @param $id
     */
    public function showEditModal($id)
    {
        //get user with data
        $this->payment = AdvertiserPackages::withTrashed()
            ->where('id', $id)
            ->first()
            ->toArray();

        $this->payment['starts_at'] = $this->payment['starts_at'] ? Carbon::make($this->payment['starts_at'])->format('Y-m-d\TH:m') : null;
        $this->payment['ends_at'] = $this->payment['ends_at'] ? Carbon::make($this->payment['ends_at'])->format('Y-m-d\TH:m') : null;
        $this->payment['is_active'] = $this->payment['is_active'] ? 1 : 0;
        $this->payment['is_ended'] = $this->payment['is_ended'] ? 1 : 0;
        $this->payment['is_current'] = $this->payment['is_current'] ? 1 : 0;
        //show the modal
        $this->showEditModal = true;
    }

    /**
     * show edit modal
     * @param $id
     */
    public function showRestoreModal($id)
    {
        //set restore id
        $this->restore = $id;
        //show the modal
        $this->showRestoreModal = true;
    }

    /**
     * close the modal
     */
    public function closeEditModal()
    {
        //close the modal
        $this->showEditModal = false;

        //empty user data
        $this->payment = [];

        //reset validation messages
        $this->resetValidation();
    }

    /**
     * update user data
     * @param $id
     * @return void|null
     */
    public function update($id)
    {
        if (!Auth::guard('admin')->user()->can('payments.edit')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }

        //validate data
        $this->validate([
            'payment.package_id' => ['required', "exists:packages,id"],
            'payment.advertiser_id' => ['required', "exists:advertisers_users,id"],
            'payment.starts_at' => ['required', 'date_format:Y-m-d\TH:m'],
            'payment.ends_at' => ['nullable', 'date_format:Y-m-d\TH:m'],
            'payment.is_ended' => ['required', 'boolean'],
            'payment.is_current' => ['required', 'boolean'],
            'payment.is_active' => ['required', 'boolean'],
        ]);

        //set data
        $data = $this->payment;

        //unset the user id
        unset($data['id']);

        DB::beginTransaction();
        try {
            $data['starts_at'] = Carbon::make($data['starts_at']);
            $data['ends_at'] = $data['ends_at'] ? Carbon::make($data['ends_at']) : null;

            if ($data['is_current']) {
                AdvertiserPackages::withTrashed()
                    ->where('advertiser_id', $data['advertiser_id'])
                    ->where('id', '!=', $this->payment['id'])
                    ->update([
                        'is_current' => false,
                    ]);
            }
            if ($data['is_ended']) {
                $data['ends_at'] = now();
            }

            //get user
            $payment = AdvertiserPackages::withTrashed()
                ->findOrFail($id);

            //add log
            AdminLogs::log('edit', 'payments', [
                'old' => $payment,
                'new' => $data,
            ], "Edit: payment #$id");

            //update user
            $payment->update($data);

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

        $this->emitUp('recountCounters');
    }

    /**
     * set modal texts
     */
    public function setModalTexts()
    {
        $this->deleteModalTexts = [
            'title' => __('pages/subscriptions/payments/inquiry.modal.delete.title'),
            'select-option' => __('pages/subscriptions/payments/inquiry.modal.delete.select-option'),
            'soft-delete' => __('pages/subscriptions/payments/inquiry.modal.delete.soft-delete'),
            'permanent-delete' => __('pages/subscriptions/payments/inquiry.modal.delete.permanent-delete'),
            'content' => __('pages/subscriptions/payments/inquiry.modal.delete.content', [
                'name' => '',
                'advertiser' => '',
            ]),
            'cancel' => __('pages/subscriptions/payments/inquiry.modal.delete.cancel'),
            'submit' => __('pages/subscriptions/payments/inquiry.modal.delete.submit'),
        ];
        $this->editModalTexts = [
            'title' => __('pages/subscriptions/payments/inquiry.modal.edit.title'),
            'cancel' => __('pages/subscriptions/payments/inquiry.modal.edit.cancel'),
            'submit' => __('pages/subscriptions/payments/inquiry.modal.edit.submit'),
        ];

        $this->restoreModalTexts = [
            'title' => __('pages/subscriptions/payments/inquiry.modal.restore.title'),
            'content' => __('pages/subscriptions/payments/inquiry.modal.restore.content'),
            'cancel' => __('pages/subscriptions/payments/inquiry.modal.restore.cancel'),
            'submit' => __('pages/subscriptions/payments/inquiry.modal.restore.submit'),
        ];
    }

    /**
     * @param $id
     * @return void|null
     */
    public function restore($id)
    {
        if (!Auth::guard('admin')->user()->can('payments.edit')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }

        DB::beginTransaction();
        try {

            //restore post
            $post = AdvertiserPackages::withTrashed()
                ->find($id)
                ->restore();

            //send toastr alert with success
            $this->alert('success', __('toastr.restored'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            //add log
            AdminLogs::log('edit', 'payments', [
                'old' => $post,
            ], "Restore: post #$id");

            $this->reset('restore');
            $this->showRestoreModal = false;
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
        $this->emitUp('recountCounters');
    }
}
