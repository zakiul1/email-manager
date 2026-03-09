<?php

namespace App\Services\SendPortal;

use App\Models\SendPortal\Template;
use App\Models\SendPortal\TemplateTest;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class TemplateTestSendService
{
    public function __construct(
        protected TemplatePlaceholderService $placeholderService
    ) {
    }

    public function send(Template $template, string $recipientEmail): array
    {
        $test = TemplateTest::query()->create([
            'template_id' => $template->id,
            'actor_id' => Auth::id(),
            'recipient_email' => $recipientEmail,
            'status' => 'queued',
        ]);

        try {
            $subject = $this->placeholderService->render((string) $template->subject);
            $html = $this->placeholderService->render((string) $template->html_content);
            $text = $this->placeholderService->render((string) ($template->text_content ?? ''));

            Mail::html($html, function ($message) use ($recipientEmail, $subject) {
                $message->to($recipientEmail)->subject($subject);
            });

            if ($text !== '') {
                // keep text available for future multipart implementation
            }

            $test->update([
                'status' => 'sent',
                'sent_at' => now(),
                'error_message' => null,
            ]);

            $template->update([
                'last_test_sent_at' => now(),
            ]);

            return [
                'ok' => true,
                'message' => 'Template test email sent successfully.',
            ];
        } catch (Exception $exception) {
            $test->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
            ]);

            return [
                'ok' => false,
                'message' => 'Template test send failed: '.$exception->getMessage(),
            ];
        }
    }
}