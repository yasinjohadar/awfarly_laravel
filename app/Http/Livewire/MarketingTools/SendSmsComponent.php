<?php

namespace App\Http\Livewire\MarketingTools;

use App\Models\Users\Advertisers\AdvertiserUser;
use App\Models\Users\Customers\CustomerUser;
use Illuminate\Support\Facades\App;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Throwable;

class SendSmsComponent extends Component
{
    use LivewireAlert;

    public string $recipients_type = 'all_users';
    public ?array $advertisers = null;
    public ?array $customers = null;
    public ?array $numbers = null;
    public ?string $subject = null;
    public ?string $body = null;
    public ?string $image = null;

    public function render()
    {
        $all_advertisers = AdvertiserUser::whereNotNull('mobile')
        ->get()
            ->map(function ($advertiser) {
                return [
                    'id' => $advertiser->id,
                    'name' => "$advertiser->name ($advertiser->mobile)",
                ];
            });

        $all_customers = CustomerUser::whereNotNull('mobile')
        ->get()
            ->map(function ($customers) {
                return [
                    'id' => $customers->id,
                    'name' => "$customers->name ($customers->mobile)",
                ];
            });

        return view('livewire.pages.marketing-tools.send-sms-component', [
            'all_advertisers' => $all_advertisers,
            'all_customers' => $all_customers,
        ]);
    }


    /**
     * Send Notification
     */
    public function sendSMS()
    {
        $this->validate([
            'recipients_type' => ['required', 'in:all_users,all_advertisers,all_customers,specific_advertisers,specific_customers,specific_numbers'],
            'advertisers' => ['nullable', 'array'],
            'advertisers.*' => ['nullable', 'exists:advertisers_users,id'],
            'customers' => ['nullable', 'array'],
            'customers.*' => ['nullable', 'exists:customers_users,id'],
            'numbers' => ['nullable', 'array'],
            'numbers.*' => ['nullable', 'string'],
            'subject' => ['required', 'string'],
            'body' => ['required', 'string','max:160'],
            'image' => ['nullable', 'string', 'url'],
        ]);

        try {
            $recipients = [];
            $error = '';
            if ($this->recipients_type === 'all_users') {
                $customers = CustomerUser::pluck('mobile');

                $recipients = AdvertiserUser::pluck('mobile')
                    ->union($customers);

                if (count($recipients) == 0) {
                    $error = __('pages/marketing-tools/sms.errors.all_users');
                }
            } elseif ($this->recipients_type === 'all_advertisers') {
                $recipients = AdvertiserUser::pluck('mobile')
                    ->all();

                if (count($recipients) == 0) {
                    $error = __('pages/marketing-tools/sms.errors.all_advertisers');
                }
            } elseif ($this->recipients_type === 'all_customers') {
                $recipients = CustomerUser::pluck('mobile')
                    ->all();

                if (count($recipients) == 0) {
                    $error = __('pages/marketing-tools/sms.errors.all_customers');
                }
            } elseif ($this->recipients_type === 'specific_advertisers') {
                if (!$this->advertisers) {
                    $error = __('pages/marketing-tools/sms.errors.no-advertisers');
                }

                $recipients = AdvertiserUser::whereIn('id', $this->advertisers)
                    ->pluck('mobile')
                    ->all();

            } elseif ($this->recipients_type === 'specific_customers') {
                if (!$this->customers) {
                    $error = __('pages/marketing-tools/sms.errors.no-customers');
                }

                $recipients = CustomerUser::whereIn('id', $this->customers)
                    ->pluck('mobile')
                    ->all();

            } elseif ($this->recipients_type === 'specific_numbers') {
                if (!$this->customers) {
                    $error = __('pages/marketing-tools/sms.errors.no-numbers');
                }

                $recipients = $this->numbers;
            } else {
                $error = '';
            }
            if (sizeof($recipients) == 0) {
                //send toastr alert with error
                $this->alert('error', __('toastr.error'), [
                    'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                    'text' => $error,
                ]);
                return null;
            }

            //TODO: add send sms function


        } catch (Throwable $e) {
            //send toastr alert with error
            $this->alert('error', __('toastr.error'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                 'text' => $e->getMessage(),
            ]);

            return null;
        }
        //send toastr alert with success
        $this->alert('success', __('toastr.sent', ['type' => __('pages/marketing-tools/sms.name')]), [
            'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
        ]);

        $this->resetValidation();
        $this->reset([
            'recipients_type',
            'advertisers',
            'customers',
            'numbers',
            'subject',
            'body',
        ]);

        $this->dispatchBrowserEvent('clearData');
        $this->dispatchBrowserEvent('reset-recipients', 'all_users');
    }
}
