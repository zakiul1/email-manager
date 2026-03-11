<?php

namespace App\Jobs\SendPortal;

use App\Models\SendPortal\CampaignMessage;
use App\Services\SendPortal\CampaignSendService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendCampaignMessageJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $campaignMessageId
    ) {
    }

    public function handle(CampaignSendService $sendService): void
    {
        $message = CampaignMessage::query()
            ->with(['campaign', 'subscriber'])
            ->find($this->campaignMessageId);

        if (!$message || $message->status !== 'pending') {
            return;
        }

        if (!$message->campaign) {
            return;
        }

        if (in_array($message->campaign->status, ['paused', 'cancelled', 'completed'], true)) {
            return;
        }

        $message->update([
            'status' => 'queued',
            'queued_at' => now(),
        ]);

        $message->refresh();
        $message->loadMissing(['campaign', 'subscriber']);

        if (!$message->campaign || in_array($message->campaign->status, ['paused', 'cancelled', 'completed'], true)) {
            $message->update([
                'status' => 'pending',
                'queued_at' => null,
            ]);

            return;
        }

        $sendService->sendMessage($message);

        $message->refresh();
        $message->loadMissing('campaign');

        if (!$message->campaign) {
            return;
        }

        $campaign = $message->campaign;

        $hasRemainingMessages = $campaign->messages()
            ->whereIn('status', ['pending', 'queued'])
            ->exists();

        if (!$hasRemainingMessages && !in_array($campaign->status, ['paused', 'cancelled', 'completed'], true)) {
            $campaign->update([
                'status' => 'completed',
                'sent_at' => $campaign->sent_at ?: now(),
            ]);
        }
    }
}