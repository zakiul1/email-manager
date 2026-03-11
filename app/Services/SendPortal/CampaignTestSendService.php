<?php

namespace App\Services\SendPortal;

use App\Mail\SendPortal\CampaignMessageMail;
use App\Models\SendPortal\Campaign;
use App\Models\SendPortal\SmtpAccount;
use App\Models\SendPortal\Subscriber;
use App\Support\SendPortal\ErrorMessageMapper;
use Exception;
use Illuminate\Mail\MailManager;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CampaignTestSendService
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

    public function sendTest(Campaign $campaign, string $recipientEmail): array
    {
        $campaign->loadMissing([
            'template',
            'smtpPool.accounts',
        ]);

        $recipientEmail = trim($recipientEmail);

        if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Please provide a valid test recipient email address.');
        }

        $smtpAccount = $this->poolSelector->select($campaign->smtpPool);

        if (!$smtpAccount) {
            return [
                'ok' => false,
                'message' => 'No active SMTP account available for this campaign.',
            ];
        }

        $payload = $this->buildTestPayload($campaign, $recipientEmail);

        if (blank($payload['subject'])) {
            return [
                'ok' => false,
                'message' => 'Campaign subject is empty.',
            ];
        }

        if (blank($payload['html']) && blank($payload['text'])) {
            return [
                'ok' => false,
                'message' => 'Campaign content is empty.',
            ];
        }

        try {
            $mailerName = 'sp_campaign_test_' . $smtpAccount->id . '_' . now()->timestamp . '_' . Str::random(6);

            $this->registerMailer($mailerName, $smtpAccount);

            $envelope = $this->resolveEnvelope($campaign, $smtpAccount);

            Mail::mailer($mailerName)
                ->to($recipientEmail)
                ->send(new CampaignMessageMail(
                    subjectLine: (string) $payload['subject'],
                    htmlBody: (string) ($payload['html'] ?? ''),
                    textBody: filled($payload['text'] ?? null) ? (string) $payload['text'] : null,
                    fromAddress: $envelope['from_address'],
                    fromName: $envelope['from_name'],
                    replyToAddress: $envelope['reply_to_address'],
                    replyToName: $envelope['reply_to_name'],
                ));

            $this->limitService->recordSend($smtpAccount);
            $this->healthService->markSuccess($smtpAccount);

            $this->activityLogService->log('campaign.test_mail_sent', $campaign, [
                'campaign_id' => $campaign->id,
                'smtp_account_id' => $smtpAccount->id,
                'recipient_email' => $recipientEmail,
            ]);

            return [
                'ok' => true,
                'message' => 'Test email sent successfully.',
            ];
        } catch (Exception $exception) {
            $reason = $exception->getMessage();

            $this->healthService->markFailure($smtpAccount, $reason);

            $this->activityLogService->log('campaign.test_mail_failed', $campaign, [
                'campaign_id' => $campaign->id,
                'smtp_account_id' => $smtpAccount->id,
                'recipient_email' => $recipientEmail,
                'reason' => $reason,
            ]);

            return [
                'ok' => false,
                'message' => $this->errorMessageMapper->map($reason),
            ];
        }
    }

    protected function buildTestPayload(Campaign $campaign, string $recipientEmail): array
    {
        $subscriber = $this->makeTestSubscriber($recipientEmail);

        $rendered = $this->renderer->render($campaign, $subscriber, null);

        return [
            'subject' => (string) ($rendered['subject'] ?? $campaign->subject ?? ''),
            'html' => (string) ($rendered['html'] ?? ''),
            'text' => (string) ($rendered['text'] ?? ''),
        ];
    }

    protected function makeTestSubscriber(string $recipientEmail): Subscriber
    {
        $subscriber = new Subscriber();

        $subscriber->email = $recipientEmail;
        $subscriber->first_name = 'Test';
        $subscriber->last_name = 'Recipient';
        $subscriber->is_suppressed = false;

        return $subscriber;
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
}