<?php

namespace App\Services\SendPortal;

use App\Contracts\SendPortal\ChecksSuppressedEmails;
use App\Models\SendPortal\CampaignMessage;

class CampaignWebhookService
{
    public function __construct(
        protected ChecksSuppressedEmails $suppressionChecker,
        protected ActivityLogService $activityLogService
    ) {
    }

    public function process(array $payload): array
    {
        $message = $this->resolveMessage($payload);

        if (! $message) {
            return [
                'ok' => false,
                'message' => 'Campaign message not found.',
            ];
        }

        $event = $this->normalizeEvent($payload);

        $update = [
            'provider_event' => $event,
            'provider_payload' => $payload,
        ];

        switch ($event) {
            case 'delivered':
                $update['delivered_at'] = $message->delivered_at ?: now();
                if ($message->status !== 'sent') {
                    $update['status'] = 'sent';
                }
                break;

            case 'open':
                $update['opened_at'] = $message->opened_at ?: now();
                $update['open_count'] = (int) $message->open_count + 1;
                break;

            case 'click':
                $update['clicked_at'] = $message->clicked_at ?: now();
                $update['click_count'] = (int) $message->click_count + 1;
                break;

            case 'bounce':
                $update['status'] = 'failed';
                $update['failed_at'] = $message->failed_at ?: now();
                $update['bounced_at'] = now();
                $update['failure_reason'] = $this->extractReason($payload) ?: 'Provider reported bounce';
                $this->suppressRecipient($message);
                break;

            case 'complaint':
                $update['status'] = 'failed';
                $update['failed_at'] = $message->failed_at ?: now();
                $update['complained_at'] = now();
                $update['failure_reason'] = $this->extractReason($payload) ?: 'Provider reported complaint';
                $this->suppressRecipient($message);
                break;

            case 'unsubscribe':
                $update['unsubscribed_at'] = now();
                $this->suppressRecipient($message);
                break;
        }

        $message->update($update);

        $this->activityLogService->log('campaign_message.webhook_processed', $message, [
            'event' => $event,
        ]);

        return [
            'ok' => true,
            'message' => 'Webhook processed.',
        ];
    }

    protected function resolveMessage(array $payload): ?CampaignMessage
    {
        $providerMessageId = $payload['provider_message_id']
            ?? $payload['message_id']
            ?? data_get($payload, 'data.message_id')
            ?? data_get($payload, 'event-data.message.headers.message-id');

        if ($providerMessageId) {
            $message = CampaignMessage::query()
                ->where('provider_message_id', $providerMessageId)
                ->first();

            if ($message) {
                return $message;
            }
        }

        $trackingToken = $payload['tracking_token']
            ?? data_get($payload, 'tracking_token')
            ?? data_get($payload, 'metadata.tracking_token');

        if ($trackingToken) {
            return CampaignMessage::query()
                ->where('tracking_token', $trackingToken)
                ->first();
        }

        return null;
    }

    protected function normalizeEvent(array $payload): string
    {
        $raw = strtolower((string) (
            $payload['event']
            ?? $payload['type']
            ?? data_get($payload, 'event')
            ?? data_get($payload, 'event-data.event')
            ?? 'unknown'
        ));

        return match (true) {
            str_contains($raw, 'delivered') => 'delivered',
            str_contains($raw, 'open') => 'open',
            str_contains($raw, 'click') => 'click',
            str_contains($raw, 'bounce') => 'bounce',
            str_contains($raw, 'complaint') => 'complaint',
            str_contains($raw, 'unsubscribe') => 'unsubscribe',
            default => 'unknown',
        };
    }

    protected function extractReason(array $payload): ?string
    {
        return $payload['reason']
            ?? data_get($payload, 'description')
            ?? data_get($payload, 'error')
            ?? data_get($payload, 'event-data.reason');
    }

    protected function suppressRecipient(CampaignMessage $message): void
    {
        $email = mb_strtolower(trim((string) $message->recipient_email));

        $this->suppressionChecker->suppress($email, 'provider_event');

        if ($message->subscriber) {
            $message->subscriber->update([
                'status' => 'suppressed',
                'is_suppressed' => true,
                'unsubscribed_at' => $message->subscriber->unsubscribed_at ?: now(),
            ]);
        }
    }
}