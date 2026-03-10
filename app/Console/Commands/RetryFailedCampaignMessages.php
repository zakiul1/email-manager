<?php

namespace App\Console\Commands;

use App\Jobs\SendPortal\RetryFailedCampaignMessageJob;
use App\Models\SendPortal\CampaignMessage;
use Illuminate\Console\Command;

class RetryFailedCampaignMessages extends Command
{
    protected $signature = 'sendportal:retry-failed-messages {--limit=200}';

    protected $description = 'Retry failed campaign messages that are ready for retry';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');

        $messages = CampaignMessage::query()
            ->with('campaign')
            ->where('status', 'failed')
            ->whereNotNull('retry_at')
            ->where('retry_at', '<=', now())
            ->whereHas('campaign', function ($query) {
                $query->whereNotIn('status', ['paused', 'cancelled']);
            })
            ->orderBy('retry_at')
            ->limit($limit)
            ->get();

        foreach ($messages as $message) {
            if (! $message->campaign) {
                continue;
            }

            if (in_array($message->campaign->status, ['paused', 'cancelled'], true)) {
                continue;
            }

            RetryFailedCampaignMessageJob::dispatch($message->id);
        }

        $this->info("Queued {$messages->count()} failed campaign messages for retry.");

        return self::SUCCESS;
    }
}