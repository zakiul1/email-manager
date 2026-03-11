<?php

namespace App\Console\Commands;

use App\Jobs\SendPortal\DispatchCampaignJob;
use App\Models\SendPortal\Campaign;
use Illuminate\Console\Command;

class DispatchScheduledCampaigns extends Command
{
    protected $signature = 'sendportal:dispatch-scheduled-campaigns {--limit=100}';

    protected $description = 'Dispatch scheduled campaigns that are due for sending';

    public function handle(): int
    {
        $limit = max((int) $this->option('limit'), 1);

        $campaigns = Campaign::query()
            ->withCount([
                'messages as pending_messages_count' => function ($query) {
                    $query->where('status', 'pending');
                },
            ])
            ->where('delivery_mode', 'schedule')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->whereNotIn('status', ['active', 'paused', 'cancelled', 'completed'])
            ->having('pending_messages_count', '>', 0)
            ->orderBy('scheduled_at')
            ->limit($limit)
            ->get();

        $queuedCount = 0;

        foreach ($campaigns as $campaign) {
            if ((int) $campaign->pending_messages_count <= 0) {
                continue;
            }

            DispatchCampaignJob::dispatch($campaign->id);
            $queuedCount++;
        }

        $this->info("Queued {$queuedCount} scheduled campaign(s) for dispatch.");

        return self::SUCCESS;
    }
}