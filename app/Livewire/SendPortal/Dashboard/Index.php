<?php

namespace App\Livewire\SendPortal\Dashboard;

use App\Models\SendPortal\Campaign;
use App\Models\SendPortal\CategoryTagLink;
use App\Models\SendPortal\SmtpAccount;
use App\Models\SendPortal\SmtpPool;
use App\Models\SendPortal\Subscriber;
use App\Models\SendPortal\Template;
use App\Support\SendPortal\Navigation;
use Livewire\Component;

class Index extends Component
{
    public array $phaseChecklist = [
        ['phase' => 'Phase 1', 'title' => 'Foundation & admin shell', 'status' => 'Complete'],
        ['phase' => 'Phase 2', 'title' => 'Native data model foundation', 'status' => 'Complete'],
        ['phase' => 'Phase 3', 'title' => 'Subscribers, categories bridge', 'status' => 'Complete'],
        ['phase' => 'Phase 4', 'title' => 'SMTP accounts and pools foundation', 'status' => 'Complete'],
        ['phase' => 'Phase 5', 'title' => 'Template manager', 'status' => 'Complete'],
        ['phase' => 'Phase 6', 'title' => 'Campaign foundation', 'status' => 'Complete'],
        ['phase' => 'Phase 7', 'title' => 'Audience engine and queued sending', 'status' => 'Complete'],
        ['phase' => 'Phase 8', 'title' => 'Limits and reporting', 'status' => 'Complete'],
        ['phase' => 'Phase 9', 'title' => 'Retry and failover control', 'status' => 'Complete'],
        ['phase' => 'Phase 10', 'title' => 'Advanced reports and exports', 'status' => 'Complete'],
        ['phase' => 'Phase 11', 'title' => 'Tracking, webhook reconciliation, and hardening', 'status' => 'Complete'],
        ['phase' => 'Phase 12', 'title' => 'Final cleanup and handoff', 'status' => 'Complete'],
    ];

    public function render()
    {
        return view('livewire.sendportal.dashboard.index', [
            'navigationItems' => Navigation::items(),
            'stats' => [
                'campaigns' => Campaign::query()->count(),
                'subscribers' => Subscriber::query()->count(),
                'templates' => Template::query()->count(),
                'category_links' => CategoryTagLink::query()->count(),
                'smtp_accounts' => SmtpAccount::query()->count(),
                'smtp_pools' => SmtpPool::query()->count(),
            ],
        ])->layout(config('sendportal-integration.layout'));
    }
}