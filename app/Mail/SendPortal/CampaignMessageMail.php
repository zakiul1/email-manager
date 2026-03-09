<?php

namespace App\Mail\SendPortal;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CampaignMessageMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $subjectLine,
        public string $htmlBody,
        public ?string $textBody = null
    ) {
    }

    public function build(): static
    {
        $mail = $this->subject($this->subjectLine)
            ->html($this->htmlBody);

        if ($this->textBody) {
            $mail->text('emails.sendportal.campaign-text');
        }

        return $mail->with([
            'textBody' => $this->textBody,
        ]);
    }
}