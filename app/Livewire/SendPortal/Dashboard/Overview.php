<?php

namespace App\Livewire\SendPortal\Dashboard;

use App\Models\SendPortal\Campaign;
use App\Models\SendPortal\CampaignMessage;
use App\Services\SendPortal\DashboardStatsService;
use Livewire\Component;

class Overview extends Component
{
    public function render(DashboardStatsService $statsService)
    {
        abort_unless(auth()->check(), 403);

        $recentCampaigns = Campaign::query()
            ->latest('id')
            ->limit(8)
            ->get();

        $recentFailures = CampaignMessage::query()
            ->with(['campaign', 'smtpAccount'])
            ->where('status', 'failed')
            ->latest('id')
            ->limit(8)
            ->get();

        return view('livewire.sendportal.dashboard.overview', [
            'stats' => $statsService->summary(),
            'deliveryTrend' => $statsService->dailyDeliveryTrend(),
            'engagementTrend' => $statsService->engagementTrend(),
            'smtpUsage' => $statsService->smtpUsage(),
            'recentCampaigns' => $recentCampaigns,
            'recentFailures' => $recentFailures,
        ])->layout(config('sendportal-integration.layout'));
    }
}