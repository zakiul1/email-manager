<?php

namespace App\Livewire\SendPortal\Reports;

use App\Models\Category;
use App\Models\SendPortal\CampaignMessage;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CategoryPerformance extends Component
{
    public function render()
    {
        $rows = Category::query()
            ->leftJoin('category_email', 'category_email.category_id', '=', 'categories.id')
            ->leftJoin('email_addresses', 'email_addresses.id', '=', 'category_email.email_address_id')
            ->leftJoin('sendportal_subscribers', 'sendportal_subscribers.email', '=', 'email_addresses.email')
            ->leftJoin('sendportal_campaign_messages', 'sendportal_campaign_messages.subscriber_id', '=', 'sendportal_subscribers.id')
            ->select([
                'categories.id',
                'categories.name',
                DB::raw('COUNT(DISTINCT sendportal_campaign_messages.id) as total_messages'),
                DB::raw("SUM(CASE WHEN sendportal_campaign_messages.status = 'sent' THEN 1 ELSE 0 END) as sent_messages"),
                DB::raw("SUM(CASE WHEN sendportal_campaign_messages.status = 'failed' THEN 1 ELSE 0 END) as failed_messages"),
            ])
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('categories.name')
            ->get();

        return view('livewire.sendportal.reports.category-performance', [
            'rows' => $rows,
            'summary' => [
                'categories' => $rows->count(),
                'messages' => (int) $rows->sum('total_messages'),
                'sent' => (int) $rows->sum('sent_messages'),
                'failed' => (int) $rows->sum('failed_messages'),
            ],
        ])->layout(config('sendportal-integration.layout'));
    }
}