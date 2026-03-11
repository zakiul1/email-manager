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
        public ?string $textBody = null,
        public ?string $fromAddress = null,
        public ?string $fromName = null,
        public ?string $replyToAddress = null,
        public ?string $replyToName = null,
    ) {
    }

    public function build(): static
    {
        $mail = $this->subject($this->subjectLine);

        if ($this->fromAddress) {
            $mail->from($this->fromAddress, $this->fromName);
        }

        if ($this->replyToAddress) {
            $mail->replyTo($this->replyToAddress, $this->replyToName);
        }

        $mail->html($this->htmlBody);

        if (filled($this->textBody)) {
            $mail->text('emails.sendportal.campaign-text');
        }

        return $mail->with([
            'textBody' => $this->textBody,
        ]);
    }
}