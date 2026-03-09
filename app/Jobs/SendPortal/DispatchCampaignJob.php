<?php

namespace App\Jobs\SendPortal;

use App\Models\SendPortal\Campaign;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DispatchCampaignJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $campaignId
    ) {
    }

    public function handle(): void
    {
        $campaign = Campaign::query()
            ->with('messages')
            ->find($this->campaignId);

        if (! $campaign) {
            return;
        }

        $campaign->update([
            'status' => 'active',
            'queued_at' => now(),
        ]);

        $campaign->messages()
            ->where('status', 'pending')
            ->orderBy('id')
            ->chunk(200, function ($messages) {
                foreach ($messages as $message) {
                    SendCampaignMessageJob::dispatch($message->id);
                }
            });
    }
}