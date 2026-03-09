<?php

namespace App\Livewire\SendPortal\Reports;

use App\Models\SendPortal\Campaign;
use App\Models\SendPortal\CampaignMessage;
use App\Models\SendPortal\SmtpAccount;
use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        abort_unless(auth()->user()?->can('sendportal.reports.view'), 403);

        $campaigns = Campaign::query()
            ->latest('id')
            ->limit(10)
            ->get();

        $smtpAccounts = SmtpAccount::query()
            ->latest('id')
            ->limit(20)
            ->get();

        return view('livewire.sendportal.reports.index', [
            'campaignStats' => [
                'total' => Campaign::query()->count(),
                'sent_messages' => CampaignMessage::query()->where('status', 'sent')->count(),
                'failed_messages' => CampaignMessage::query()->where('status', 'failed')->count(),
                'pending_messages' => CampaignMessage::query()->whereIn('status', ['pending', 'queued'])->count(),
            ],
            'campaigns' => $campaigns,
            'smtpAccounts' => $smtpAccounts,
            'healthStats' => [
                'cooling_down' => SmtpAccount::query()
                    ->whereNotNull('cooldown_until')
                    ->where('cooldown_until', '>', now())
                    ->count(),
                'failing' => SmtpAccount::query()->where('status', 'failing')->count(),
                'paused' => SmtpAccount::query()->where('status', 'paused')->count(),
            ],
        ])->layout(config('sendportal-integration.layout'));
    }
}