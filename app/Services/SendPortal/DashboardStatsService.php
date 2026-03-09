<?php

namespace App\Services\SendPortal;

use App\Models\SendPortal\Campaign;
use App\Models\SendPortal\CampaignMessage;
use App\Models\SendPortal\SmtpAccount;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardStatsService
{
    public function summary(): array
    {
        return [
            'campaigns_total' => Campaign::query()->count(),
            'campaigns_draft' => Campaign::query()->where('status', 'draft')->count(),
            'campaigns_active' => Campaign::query()->whereIn('status', ['active', 'scheduled'])->count(),
            'messages_sent' => CampaignMessage::query()->where('status', 'sent')->count(),
            'messages_failed' => CampaignMessage::query()->where('status', 'failed')->count(),
            'messages_opened' => CampaignMessage::query()->where('open_count', '>', 0)->count(),
            'messages_clicked' => CampaignMessage::query()->where('click_count', '>', 0)->count(),
            'messages_unsubscribed' => CampaignMessage::query()->whereNotNull('unsubscribed_at')->count(),
            'smtp_active' => SmtpAccount::query()->where('status', 'active')->count(),
            'smtp_failing' => SmtpAccount::query()->where('status', 'failing')->count(),
            'smtp_paused' => SmtpAccount::query()->where('status', 'paused')->count(),
            'smtp_cooldown' => SmtpAccount::query()
                ->whereNotNull('cooldown_until')
                ->where('cooldown_until', '>', now())
                ->count(),
        ];
    }

    public function dailyDeliveryTrend(int $days = 14): array
    {
        $start = now()->startOfDay()->subDays($days - 1);

        $rows = CampaignMessage::query()
            ->selectRaw('DATE(COALESCE(sent_at, created_at)) as day')
            ->selectRaw("SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent_count")
            ->selectRaw("SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_count")
            ->where(function ($query) use ($start) {
                $query->where('sent_at', '>=', $start)
                    ->orWhere('created_at', '>=', $start);
            })
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        $labels = [];
        $sent = [];
        $failed = [];

        for ($i = 0; $i < $days; $i++) {
            $date = $start->copy()->addDays($i)->toDateString();
            $labels[] = Carbon::parse($date)->format('M d');
            $sent[] = (int) ($rows[$date]->sent_count ?? 0);
            $failed[] = (int) ($rows[$date]->failed_count ?? 0);
        }

        return [
            'labels' => $labels,
            'sent' => $sent,
            'failed' => $failed,
        ];
    }

    public function engagementTrend(int $days = 14): array
    {
        $start = now()->startOfDay()->subDays($days - 1);

        $rows = CampaignMessage::query()
            ->selectRaw('DATE(COALESCE(sent_at, created_at)) as day')
            ->selectRaw('SUM(open_count) as opens')
            ->selectRaw('SUM(click_count) as clicks')
            ->where(function ($query) use ($start) {
                $query->where('sent_at', '>=', $start)
                    ->orWhere('created_at', '>=', $start);
            })
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        $labels = [];
        $opens = [];
        $clicks = [];

        for ($i = 0; $i < $days; $i++) {
            $date = $start->copy()->addDays($i)->toDateString();
            $labels[] = Carbon::parse($date)->format('M d');
            $opens[] = (int) ($rows[$date]->opens ?? 0);
            $clicks[] = (int) ($rows[$date]->clicks ?? 0);
        }

        return [
            'labels' => $labels,
            'opens' => $opens,
            'clicks' => $clicks,
        ];
    }

    public function smtpUsage(): Collection
    {
        return SmtpAccount::query()
            ->orderBy('priority')
            ->limit(8)
            ->get()
            ->map(function (SmtpAccount $account) {
                return [
                    'name' => $account->name,
                    'status' => $account->status->value,
                    'sent_today' => (int) ($account->meta['sent_today'] ?? 0),
                    'success_count' => (int) $account->success_count,
                    'failure_count' => (int) $account->failure_count,
                    'cooldown_until' => $account->cooldown_until?->toDateTimeString(),
                ];
            });
    }
}