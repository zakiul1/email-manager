<?php

namespace App\Services\SendPortal;

use App\Models\SendPortal\SmtpAccount;
use Carbon\Carbon;

class SmtpAccountLimitService
{
    public function canSend(SmtpAccount $account): bool
    {
        if (! $account->isActive()) {
            return false;
        }

        $meta = $account->meta ?? [];
        $today = Carbon::now()->toDateString();
        $hourKey = Carbon::now()->format('Y-m-d H');

        $dailyCount = (int) ($meta['daily_counts'][$today] ?? 0);
        $hourlyCount = (int) ($meta['hourly_counts'][$hourKey] ?? 0);

        if ($account->daily_limit && $dailyCount >= $account->daily_limit) {
            return false;
        }

        if ($account->hourly_limit && $hourlyCount >= $account->hourly_limit) {
            return false;
        }

        if ($account->warmup_limit && $dailyCount >= $account->warmup_limit) {
            return false;
        }

        return true;
    }

    public function recordSend(SmtpAccount $account): void
    {
        $meta = $account->meta ?? [];
        $today = Carbon::now()->toDateString();
        $hourKey = Carbon::now()->format('Y-m-d H');

        $dailyCounts = $meta['daily_counts'] ?? [];
        $hourlyCounts = $meta['hourly_counts'] ?? [];

        $dailyCounts[$today] = (int) ($dailyCounts[$today] ?? 0) + 1;
        $hourlyCounts[$hourKey] = (int) ($hourlyCounts[$hourKey] ?? 0) + 1;

        $meta['daily_counts'] = $this->trimDailyCounts($dailyCounts);
        $meta['hourly_counts'] = $this->trimHourlyCounts($hourlyCounts);
        $meta['sent_today'] = (int) ($dailyCounts[$today] ?? 0);

        $account->update([
            'last_used_at' => now(),
            'meta' => $meta,
        ]);
    }

    protected function trimDailyCounts(array $dailyCounts): array
    {
        ksort($dailyCounts);

        return array_slice($dailyCounts, -14, null, true);
    }

    protected function trimHourlyCounts(array $hourlyCounts): array
    {
        ksort($hourlyCounts);

        return array_slice($hourlyCounts, -48, null, true);
    }
}