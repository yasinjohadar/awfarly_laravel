<?php

namespace App\Http\Livewire\Subscriptions\Payments;

use App\Helpers\Admins\AdminLogs;
use App\Models\Subscriptions\Packages\Advertisers\AdvertiserPackages;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Throwable;

class PaymentsShowComponent extends Component
{
    use LivewireAlert;

    public int $payment_id;
    public bool $showEditModal = false;
    public array $payment = [];

    /**
     * @return Application|Factory|View
     */
    public function render()
    {
        //get payment
        $paymentData = AdvertiserPackages::withTrashed()
            ->where('id', $this->payment_id)
            ->first();

        return view('admin.pages.subscriptions.payments.show', [
            'paymentData' => $paymentData,
            'showEditModal' => $this->showEditModal,
        ]);
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

    public function closeEditModal()
    {
        //close the modal
        $this->showEditModal = false;


        //reset validation messages
        $this->resetValidation();
    }

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

}
