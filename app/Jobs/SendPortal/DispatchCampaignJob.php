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

        if ($campaign->status === 'cancelled') {
            return;
        }

        if ($campaign->status === 'paused') {
            return;
        }

        if ($campaign->messages()->where('status', 'pending')->count() === 0) {
            return;
        }

        $campaign->update([
            'status' => 'active',
            'queued_at' => now(),
        ]);

        $campaign->messages()
            ->where('status', 'pending')
            ->orderBy('id')
            ->chunk(200, function ($messages) use ($campaign) {
                $campaign->refresh();

                if (in_array($campaign->status, ['paused', 'cancelled'], true)) {
                    return false;
                }

                foreach ($messages as $message) {
                    SendCampaignMessageJob::dispatch($message->id);
                }

                return true;
            });
    }
}