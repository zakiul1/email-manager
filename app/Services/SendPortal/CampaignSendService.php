<?php

namespace App\Services\SendPortal;

use App\Mail\SendPortal\CampaignMessageMail;
use App\Models\SendPortal\Campaign;
use App\Models\SendPortal\CampaignMessage;
use App\Models\SendPortal\SmtpAccount;
use App\Support\SendPortal\ErrorMessageMapper;
use Exception;
use Illuminate\Mail\MailManager;
use Illuminate\Support\Facades\Mail;

class CampaignSendService
{
    public function __construct(
        protected CampaignTemplateRenderer $renderer,
        protected SmtpPoolSelectorService $poolSelector,
        protected SmtpAccountEncryption $encryption,
        protected SmtpAccountLimitService $limitService,
        protected SmtpHealthService $healthService,
        protected ActivityLogService $activityLogService,
        protected ErrorMessageMapper $errorMessageMapper,
    ) {
    }

    public function sendMessage(CampaignMessage $message): array
    {
        $message->loadMissing([
            'campaign.template',
            'campaign.smtpPool.accounts',
            'subscriber',
        ]);

        $campaign = $message->campaign;
        $subscriber = $message->subscriber;

        if (!$campaign || !$subscriber) {
            return $this->fail($message, 'Campaign or subscriber not found.');
        }

        if ($subscriber->is_suppressed) {
            return $this->fail($message, 'Subscriber is suppressed.');
        }

        if (blank($message->recipient_email)) {
            return $this->fail($message, 'Recipient email is missing.');
        }

        $payload = $this->renderer->render($campaign, $subscriber, $message);

        if (blank($payload['subject'] ?? null)) {
            return $this->fail($message, 'Campaign subject is empty.');
        }

        if (blank($payload['html'] ?? null) && blank($payload['text'] ?? null)) {
            return $this->fail($message, 'Campaign content is empty.');
        }

        $exclude = [];
        $lastReason = 'No active SMTP account available within limits.';

        for ($attempt = 0; $attempt < 3; $attempt++) {
            $smtpAccount = $this->poolSelector->select($campaign->smtpPool, $exclude);

            if (!$smtpAccount) {
                break;
            }

            try {
                $mailerName = 'sp_campaign_' . $smtpAccount->id . '_' . now()->timestamp . '_' . $attempt;

                $this->registerMailer($mailerName, $smtpAccount);

                $envelope = $this->resolveEnvelope($campaign, $smtpAccount);

                Mail::mailer($mailerName)
                    ->to($message->recipient_email)
                    ->send(new CampaignMessageMail(
                        subjectLine: (string) $payload['subject'],
                        htmlBody: (string) ($payload['html'] ?? ''),
                        textBody: filled($payload['text'] ?? null) ? (string) $payload['text'] : null,
                        fromAddress: $envelope['from_address'],
                        fromName: $envelope['from_name'],
                        replyToAddress: $envelope['reply_to_address'],
                        replyToName: $envelope['reply_to_name'],
                    ));

                $message->update([
                    'smtp_account_id' => $smtpAccount->id,
                    'status' => 'sent',
                    'attempt_count' => (int) $message->attempt_count + 1,
                    'subject' => (string) $payload['subject'],
                    'html_body' => (string) ($payload['html'] ?? ''),
                    'text_body' => filled($payload['text'] ?? null) ? (string) $payload['text'] : null,
                    'queued_at' => $message->queued_at ?: now(),
                    'sent_at' => now(),
                    'delivered_at' => now(),
                    'failed_at' => null,
                    'retry_at' => null,
                    'failure_reason' => null,
                ]);

                $campaign->increment('sent_count');

                $this->limitService->recordSend($smtpAccount);
                $this->healthService->markSuccess($smtpAccount);

                $this->activityLogService->log('campaign_message.sent', $message, [
                    'campaign_id' => $campaign->id,
                    'smtp_account_id' => $smtpAccount->id,
                    'recipient_email' => $message->recipient_email,
                ]);

                return [
                    'ok' => true,
                    'message' => 'Message sent successfully.',
                ];
            } catch (Exception $exception) {
                $lastReason = $exception->getMessage();
                $exclude[] = $smtpAccount->id;

                $this->healthService->markFailure($smtpAccount, $lastReason);
            }
        }

        return $this->fail($message, $lastReason);
    }

    protected function registerMailer(string $mailerName, SmtpAccount $account): void
    {
        config()->set("mail.mailers.{$mailerName}", [
            'transport' => 'smtp',
            'host' => $account->host,
            'port' => $account->port,
            'username' => $account->username,
            'password' => $this->encryption->decrypt($account->encrypted_password),
            'encryption' => $account->encryption,
            'timeout' => 30,
        ]);

        $mailManager = app(MailManager::class);

        if (method_exists($mailManager, 'forgetMailers')) {
            $mailManager->forgetMailers();
        }

        $mailManager->mailer($mailerName);
    }

    protected function resolveEnvelope(Campaign $campaign, SmtpAccount $account): array
    {
        $defaultFromAddress = config('mail.from.address');
        $defaultFromName = config('mail.from.name');

        $fromAddress = $campaign->from_email
            ?: $account->from_email
            ?: $defaultFromAddress;

        $fromName = $campaign->from_name
            ?: $account->from_name
            ?: $defaultFromName;

        $replyToAddress = $campaign->reply_to_email
            ?: $account->reply_to_email
            ?: null;

        $replyToName = $campaign->reply_to_name
            ?: $account->reply_to_name
            ?: null;

        return [
            'from_address' => $fromAddress,
            'from_name' => $fromName,
            'reply_to_address' => $replyToAddress,
            'reply_to_name' => $replyToName,
        ];
    }

    protected function fail(CampaignMessage $message, string $reason): array
    {
        $message->update([
            'status' => 'failed',
            'attempt_count' => (int) $message->attempt_count + 1,
            'queued_at' => $message->queued_at ?: now(),
            'failed_at' => now(),
            'retry_at' => now()->addMinutes(15),
            'failure_reason' => $reason,
        ]);

        if ($message->campaign) {
            $message->campaign->increment('failed_count');
        }

        $this->activityLogService->log('campaign_message.failed', $message, [
            'campaign_id' => $message->campaign_id,
            'reason' => $reason,
            'recipient_email' => $message->recipient_email,
        ]);

        return [
            'ok' => false,
            'message' => $this->errorMessageMapper->map($reason),
        ];
    }
}