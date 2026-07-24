<?php

namespace App\Http\Livewire\Customers\Reports;

use App\Helpers\Admins\AdminLogs;
use App\Http\Resources\Media\MediaResource;
use App\Models\Posts\Post;
use App\Models\Reports\Report;
use App\Models\Users\Customers\CustomerUser;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Throwable;

class ReportedCustomerInquiryComponent extends Component
{
    use LivewireAlert;

    public int $customer_id;
    public bool $showSolveModal = false;
    public bool $showDeleteModal = false;
    public string $active;
    public array $solveModalTexts;
    public array $deleteModalTexts;

    public function __construct($id = null)
    {
        $this->setModalTexts();

        parent::__construct($id);
    }

    public function render()
    {
        $customer = CustomerUser::query()
            ->where('id', $this->customer_id)
            ->first();

        $customer['created_at'] = isset($customer['created_at']) ? Carbon::make($customer['created_at'])->format('Y-m-d h:i A') : null;

        //get report status
        $report_status = $customer->report()
            ->first()
            ->status;

        return view('livewire.pages.customers.reports.show', ['customer' => $customer, 'status' => $report_status]);
    }

    /**
     * show edit modal
     * @param $id
     */
    public function showSolveModal($id)
    {
        //show the modal
        $this->showSolveModal = true;
    }

    public function closeSolveModal()
    {
        //close the modal
        $this->showSolveModal = false;
    }

    /**
     * show edit modal
     * @param $id
     */
    public function showDeleteModal($id)
    {
        //show the modal
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal()
    {
        //close the modal
        $this->showDeleteModal = false;
    }

    public function delete()
    {
        //check if user is allowed to do this action or not
        if (!Auth::guard('admin')->user()->can('customers.edit')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }
        $customer = CustomerUser::query()
            ->where('id', $this->customer_id)
            ->first();

        DB::beginTransaction();
        try {
            $customer->update([
                'status' => 'banned'
            ]);

            //send toastr alert with success
            $this->alert('success', __('toastr.delete'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            //hide modal
            $this->showDeleteModal = false;

            //add log
            AdminLogs::log('edit', 'customers', [
                'customer' => $customer
            ], "Ban: customer #$this->customer_id");

            Report::query()
                ->where('reported_type', CustomerUser::class)
                ->where('reported_id', $this->customer_id)
                ->update([
                    'status' => 'solved'
                ]);
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


    public function solve()
    {
        //check if user is allowed to do this action or not
        if (!Auth::guard('admin')->user()->can('customers.inquiry')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }
        $customer = CustomerUser::query()
            ->where('id', $this->customer_id)
            ->first();

        DB::beginTransaction();
        try {
            //send toastr alert with success
            $this->alert('success', __('toastr.delete'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            //hide modal
            $this->showSolveModal = false;

            //add log
            AdminLogs::log('decline', 'reports', [
                'customer' => $customer
            ], "Solve: customer #$this->customer_id");

            Report::where('reported_type', CustomerUser::class)
                ->where('reported_id', $this->customer_id)
                ->update([
                    'status' => 'solved'
                ]);
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
     * set modal texts
     */
    public function setModalTexts()
    {
        $this->solveModalTexts = [
            'title' => __('pages/customers/reports/show.modal.solve.title'),
            'content' => __('pages/customers/reports/show.modal.solve.content'),
            'cancel' => __('pages/customers/reports/show.modal.solve.cancel'),
            'submit' => __('pages/customers/reports/show.modal.solve.submit'),
        ];

        $this->deleteModalTexts = [
            'title' => __('pages/customers/reports/show.modal.delete.title'),
            'content' => __('pages/customers/reports/show.modal.delete.content'),
            'cancel' => __('pages/customers/reports/show.modal.delete.cancel'),
            'submit' => __('pages/customers/reports/show.modal.delete.submit'),
        ];
    }
}
