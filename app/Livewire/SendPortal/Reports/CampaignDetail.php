<?php

namespace App\Livewire\SendPortal\Reports;

use App\Exports\SendPortal\CampaignRecipientsExport;
use App\Models\SendPortal\Campaign;
use App\Models\SendPortal\CampaignMessage;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CampaignDetail extends Component
{
    use WithPagination;

    public Campaign $campaign;
    public string $status = '';
    public string $search = '';

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function export(): StreamedResponse
    {
        abort_unless(auth()->user()?->can('sendportal.reports.export'), 403);

        return app(CampaignRecipientsExport::class)->download($this->campaign);
    }

    public function render()
    {
        abort_unless(auth()->user()?->can('sendportal.reports.view'), 403);

        $messages = CampaignMessage::query()
            ->with(['subscriber', 'smtpAccount'])
            ->where('campaign_id', $this->campaign->id)
            ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
            ->when($this->search !== '', function ($query) {
                $query->where(function ($inner) {
                    $inner->where('recipient_email', 'like', '%'.$this->search.'%')
                        ->orWhere('failure_reason', 'like', '%'.$this->search.'%');
                });
            })
            ->latest('id')
            ->paginate(20);

        $stats = [
            'total' => CampaignMessage::query()
                ->where('campaign_id', $this->campaign->id)
                ->count(),
            'sent' => CampaignMessage::query()
                ->where('campaign_id', $this->campaign->id)
                ->where('status', 'sent')
                ->count(),
            'failed' => CampaignMessage::query()
                ->where('campaign_id', $this->campaign->id)
                ->where('status', 'failed')
                ->count(),
            'pending' => CampaignMessage::query()
                ->where('campaign_id', $this->campaign->id)
                ->whereIn('status', ['pending', 'queued'])
                ->count(),
            'opened' => CampaignMessage::query()
                ->where('campaign_id', $this->campaign->id)
                ->where('open_count', '>', 0)
                ->count(),
            'clicked' => CampaignMessage::query()
                ->where('campaign_id', $this->campaign->id)
                ->where('click_count', '>', 0)
                ->count(),
            'unsubscribed' => CampaignMessage::query()
                ->where('campaign_id', $this->campaign->id)
                ->whereNotNull('unsubscribed_at')
                ->count(),
        ];

        return view('livewire.sendportal.reports.campaign-detail', [
            'messages' => $messages,
            'stats' => $stats,
        ])->layout(config('sendportal-integration.layout'));
    }
}