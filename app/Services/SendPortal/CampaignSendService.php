<?php

namespace App\Services\SendPortal;

use App\Mail\SendPortal\CampaignMessageMail;
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
        $message->loadMissing(['campaign.template', 'campaign.smtpPool', 'subscriber']);

        $campaign = $message->campaign;
        $subscriber = $message->subscriber;

        if (! $campaign || ! $subscriber) {
            return $this->fail($message, 'Campaign or subscriber not found.');
        }

        if ($subscriber->is_suppressed) {
            return $this->fail($message, 'Subscriber is suppressed.');
        }

        $exclude = [];
        $lastReason = 'No active SMTP account available within limits.';

        for ($attempt = 0; $attempt < 3; $attempt++) {
            $smtpAccount = $this->poolSelector->select($campaign->smtpPool, $exclude);

            if (! $smtpAccount) {
                break;
            }

            $payload = $this->renderer->render($campaign, $subscriber, $message);

            try {
                $mailerName = 'sp_campaign_'.$smtpAccount->id.'_'.time().'_'.$attempt;

                $this->registerMailer($mailerName, $smtpAccount);

                Mail::mailer($mailerName)
                    ->to($message->recipient_email)
                    ->send(new CampaignMessageMail(
                        subjectLine: $payload['subject'],
                        htmlBody: $payload['html'],
                        textBody: $payload['text'] !== '' ? $payload['text'] : null,
                    ));

                $message->update([
                    'smtp_account_id' => $smtpAccount->id,
                    'status' => 'sent',
                    'attempt_count' => (int) $message->attempt_count + 1,
                    'subject' => $payload['subject'],
                    'html_body' => $payload['html'],
                    'text_body' => $payload['text'],
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

        app(MailManager::class)->mailer($mailerName);
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
        ]);

        return [
            'ok' => false,
            'message' => $this->errorMessageMapper->map($reason),
        ];
    }
}