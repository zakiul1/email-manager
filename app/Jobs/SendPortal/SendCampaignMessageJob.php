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
            ->with('campaign')
            ->find($this->campaignMessageId);

        if (! $message || $message->status !== 'pending') {
            return;
        }

        if (! $message->campaign) {
            return;
        }

        if (in_array($message->campaign->status, ['paused', 'cancelled'], true)) {
            return;
        }

        $message->update([
            'status' => 'queued',
            'queued_at' => now(),
        ]);

        $message->refresh();

        if (! $message->campaign || in_array($message->campaign->status, ['paused', 'cancelled'], true)) {
            $message->update([
                'status' => 'pending',
                'queued_at' => null,
            ]);

            return;
        }

        $sendService->sendMessage($message);
    }
}