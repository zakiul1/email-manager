<?php

namespace App\Jobs\SendPortal;

use App\Models\SendPortal\CampaignMessage;
use App\Services\SendPortal\CampaignSendService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RetryFailedCampaignMessageJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $campaignMessageId
    ) {
    }

    public function handle(CampaignSendService $sendService): void
    {
        $message = CampaignMessage::query()->find($this->campaignMessageId);

        if (! $message || ! in_array($message->status, ['failed', 'pending'], true)) {
            return;
        }

        if ($message->retry_at && $message->retry_at->isFuture()) {
            return;
        }

        $message->update([
            'status' => 'queued',
            'queued_at' => now(),
        ]);

        $sendService->sendMessage($message);
    }
}