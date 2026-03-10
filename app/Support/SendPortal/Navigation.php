<?php

namespace App\Support\SendPortal;

class Navigation
{
    public static function items(): array
    {
        return [
            [
                'label' => 'Dashboard',
                'route' => 'sendportal.workspace.dashboard',
                'patterns' => ['sendportal.workspace.dashboard'],
            ],
            [
                'label' => 'Subscribers',
                'route' => 'sendportal.workspace.subscribers.index',
                'patterns' => ['sendportal.workspace.subscribers.*'],
            ],
            [
                'label' => 'Category Bridge',
                'route' => 'sendportal.workspace.category-bridge.index',
                'patterns' => ['sendportal.workspace.category-bridge.*'],
            ],
            [
                'label' => 'Campaigns',
                'route' => 'sendportal.workspace.campaigns.index',
                'patterns' => ['sendportal.workspace.campaigns.*'],
            ],
            [
                'label' => 'Templates',
                'route' => 'sendportal.workspace.templates.index',
                'patterns' => ['sendportal.workspace.templates.*'],
            ],
            [
                'label' => 'SMTP Accounts',
                'route' => 'sendportal.workspace.smtp-accounts.index',
                'patterns' => ['sendportal.workspace.smtp-accounts.*'],
            ],
            [
                'label' => 'SMTP Pools',
                'route' => 'sendportal.workspace.smtp-pools.index',
                'patterns' => ['sendportal.workspace.smtp-pools.*'],
            ],
            [
                'label' => 'Reports',
                'route' => 'sendportal.workspace.reports.index',
                'patterns' => ['sendportal.workspace.reports.*'],
            ],
            [
                'label' => 'Settings',
                'route' => 'sendportal.workspace.settings.index',
                'patterns' => ['sendportal.workspace.settings.*'],
            ],
            [
    'label' => 'Manual',
    'route' => 'sendportal.workspace.manual.index',
    'patterns' => ['sendportal.workspace.manual.*'],
],
        ];
    }
}