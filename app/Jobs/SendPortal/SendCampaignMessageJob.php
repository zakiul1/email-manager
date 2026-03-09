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
        $message = CampaignMessage::query()->find($this->campaignMessageId);

        if (! $message || $message->status !== 'pending') {
            return;
        }

        $message->update([
            'status' => 'queued',
            'queued_at' => now(),
        ]);

        $sendService->sendMessage($message);
    }
}