<?php

namespace App\Mail\MarketingTools;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendMail extends Mailable
{
    use Queueable, SerializesModels;

    private array $data;

    /**
     * Create a new message instance.
     *
     * @param array $data
     * @param $recipients
     */
    public function __construct(array $data, $recipients)
    {
        $this->data = $data;
        $this->subject($data['subject'] ?? config('app.name'));
        $this->with($data['body']);
        $this->to($recipients);
        //Attachments
        if (isset($data['attachments'])) {
            foreach ($data['attachments'] as $attachment) {
                $this->attach($attachment->getRealPath(),
                    [
                        'as' => $attachment->getClientOriginalName(),
                        'mime' => $attachment->getClientMimeType(),
                    ]);
            }
        }
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): SendMail
    {
        return $this->markdown('mail.email')->with(['data' => $this->data['body']]);
    }
}
