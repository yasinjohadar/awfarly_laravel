<?php

namespace App\Http\Livewire\MarketingTools;

use App\Helpers\Admins\AdminLogs;
use App\Mail\MarketingTools\SendMail;
use App\Models\Users\Advertisers\AdvertiserUser;
use App\Models\Users\Customers\CustomerUser;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

class SendEmailsComponent extends Component
{
    use LivewireAlert;

    use WithFileUploads;

    public string $recipients_type = 'all_users';
    public ?array $advertisers = null;
    public ?array $customers = null;
    public ?array $emails = null;
    public ?string $subject = null;
    public ?string $body = null;
    public ?string $image = null;
    public $attachments = null;

    public function render()
    {
        $all_advertisers = AdvertiserUser::get()
            ->map(function ($advertiser) {
                return [
                    'id' => $advertiser->id,
                    'name' => "$advertiser->name ($advertiser->email)",
                ];
            });

        $all_customers = CustomerUser::get()
            ->map(function ($customers) {
                return [
                    'id' => $customers->id,
                    'name' => "$customers->name ($customers->email)",
                ];
            });

        return view('livewire.pages.marketing-tools.send-emails-component', [
            'all_advertisers' => $all_advertisers,
            'all_customers' => $all_customers,
        ]);
    }


    /**
     * Send Email
     */
    public function sendEmail()
    {

        $this->validate([
            'recipients_type' => ['required', 'in:all_users,all_advertisers,all_customers,specific_advertisers,specific_customers,specific_emails'],
            'advertisers' => ['nullable', 'array'],
            'advertisers.*' => ['nullable', 'exists:advertisers_users,id'],
            'customers' => ['nullable', 'array'],
            'customers.*' => ['nullable', 'exists:customers_users,id'],
            'emails' => ['nullable', 'array'],
            'emails.*' => ['email'],
            'subject' => ['required', 'string'],
            'body' => ['required', 'string'],
            'attachments.*' => ['nullable', 'file'],
        ]);
        $data = [
            'subject' => $this->subject,
            'body' => $this->body,
            'attachments' => $this->attachments,
        ];

        $recipients = [];
        try {
            $error = '';
            if ($this->recipients_type === 'all_users') {
                $customers = CustomerUser::pluck('email');

                $recipients = AdvertiserUser::pluck('email')
                    ->union($customers)
                    ->toArray();

            } elseif ($this->recipients_type === 'all_advertisers') {
                $recipients = AdvertiserUser::pluck('email')
                    ->all();
            } elseif ($this->recipients_type === 'all_customers') {
                $recipients = CustomerUser::pluck('email')
                    ->all();
            } elseif ($this->recipients_type === 'specific_advertisers') {
                if (!$this->advertisers) {
                    $error = __('pages/marketing-tools/emails.errors.no-advertisers');
                }

                $recipients = AdvertiserUser::whereIn('id', $this->advertisers)
                    ->pluck('email')
                    ->all();
            } elseif ($this->recipients_type === 'specific_customers') {
                if (!$this->customers) {
                    $error = __('pages/marketing-tools/emails.errors.no-customers');
                }

                $recipients = CustomerUser::whereIn('id', $this->customers)
                    ->pluck('email')
                    ->all();

            } elseif ($this->recipients_type === 'specific_emails') {
                if (!$this->emails) {
                    $error = __('pages/marketing-tools/emails.errors.no-emails');
                }

                $recipients = $this->emails;
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

            //Declare data
            $recipients = array_unique($recipients);
            $recipients_raw = $recipients;
            $recipients = [];

            //check and filter recipients
            foreach ($recipients_raw as $recipient) {
                //Filter
                $recipient = str_replace('\n', '', $recipient);
                $recipient = str_replace('\r', '', $recipient);
                $recipient = trim($recipient);

                //If email
                if (filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                    $recipients[] = $recipient;
                }
            }
            //Remove html
            /*$data['body'] = Filter::HtmlFilter($data['body']);*/

            //Send mail
            try {

                Mail::send(new SendMail($data, $recipients));


            } catch (\Exception $e) {
                //send toastr alert with error
                $this->alert('error', __('toastr.error'), [
                    'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                    'text' => $e->getMessage(),
                ]);

                return null;
            }
            //Log Action
            AdminLogs::log('send', 'emails', [
                'data' => $data
            ], "Inquiry: Send email");


        } catch (Throwable $e) {
            //send toastr alert with error
            $this->alert('error', __('toastr.error'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                'text' => $e->getMessage(),
            ]);

            return null;
        }
        //send toastr alert with success
        $this->alert('success', __('toastr.sent', ['type' => __('pages/marketing-tools/emails.name')]), [
            'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
        ]);

        $this->resetValidation();
        $this->reset([
            'recipients_type',
            'advertisers',
            'customers',
            'emails',
            'subject',
            'body',
            'image',
        ]);

        $this->dispatchBrowserEvent('clearData');
        $this->dispatchBrowserEvent('reset-recipients', 'all_users');
    }
}
