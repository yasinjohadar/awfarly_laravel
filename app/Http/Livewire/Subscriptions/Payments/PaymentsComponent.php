<?php

namespace App\Http\Livewire\Subscriptions\Payments;

use App\Models\Subscriptions\Packages\Advertisers\AdvertiserPackages;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class PaymentsComponent extends Component
{
    use LivewireAlert;

    public string $page_type = 'all';
    private ?int $payment_id = null;
    protected $listeners = ['setPaymentId', 'recountCounters'];
    public ?int $all_payments_count = null;
    public ?int $active_payments_count = null;
    public ?int $expired_payments_count = null;
    public ?int $deleted_payments_count = null;

    public function render()
    {
        $this->all_payments_count = AdvertiserPackages::withTrashed()
            ->count();

        $this->active_payments_count = AdvertiserPackages::where('is_active', true)
            ->where('is_ended', false)
            ->where('ends_at', '>', now())
            ->orWhereNull('ends_at')
            ->count();

        $this->expired_payments_count = AdvertiserPackages::where('ends_at', '<', now())
            ->orWhere('is_ended', true)
            ->count();

        $this->deleted_payments_count = AdvertiserPackages::onlyTrashed()
            ->count();


        return view('livewire.pages.subscriptions.payments.inquiry', [
            'payment_id' => $this->payment_id,
        ]);
    }

    /**
     * @param $active
     */
    public function changeActiveTab($active)
    {
        $this->page_type = $active;

        $this->emit('rerenderDataTable', ['page_type' => $active]);
    }

    /**
     * @param $id
     */
    public function setPaymentId($id = null)
    {
        $this->payment_id = $id;

        if (!$id) {
            $this->emit('rerenderDataTable', ['page_type' => $this->page_type]);
        }
    }

    public function recountCounters()
    {
        $this->all_payments_count = AdvertiserPackages::withTrashed()
            ->count();

        $this->active_payments_count = AdvertiserPackages::where('is_active', true)
            ->where('is_ended', false)
            ->where('ends_at', '>', now())
            ->orWhereNull('ends_at')
            ->count();

        $this->expired_payments_count = AdvertiserPackages::where('ends_at', '<', now())
            ->orWhere('is_ended', true)
            ->count();

        $this->deleted_payments_count = AdvertiserPackages::onlyTrashed()
            ->count();
    }
}
