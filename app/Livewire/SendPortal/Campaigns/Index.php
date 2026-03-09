<?php

namespace App\Livewire\SendPortal\Campaigns;

use App\Models\SendPortal\Campaign;
use App\Services\SendPortal\ActivityLogService;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function duplicateCampaign(int $campaignId, ActivityLogService $activityLogService): void
    {
        $campaign = Campaign::query()->with('audiences')->findOrFail($campaignId);

        $copy = $campaign->replicate();
        $copy->name = $campaign->name.' Copy';
        $copy->status = 'draft';
        $copy->delivery_mode = 'draft';
        $copy->scheduled_at = null;
        $copy->queued_at = null;
        $copy->sent_at = null;
        $copy->recipient_count = 0;
        $copy->sent_count = 0;
        $copy->failed_count = 0;
        $copy->save();

        foreach ($campaign->audiences as $audience) {
            $copy->audiences()->create([
                'source_type' => $audience->source_type,
                'source_id' => $audience->source_id,
                'filters' => $audience->filters,
            ]);
        }

        $activityLogService->log('campaign.duplicated', $copy, [
            'source_campaign_id' => $campaign->id,
        ]);

        $this->dispatch('toast', type: 'success', message: 'Campaign duplicated successfully.');
    }

    public function deleteCampaign(int $campaignId, ActivityLogService $activityLogService): void
    {
        $campaign = Campaign::query()->findOrFail($campaignId);

        $activityLogService->log('campaign.deleted', $campaign, [
            'name' => $campaign->name,
        ]);

        $campaign->delete();

        $this->dispatch('toast', type: 'success', message: 'Campaign deleted successfully.');
    }

    public function render()
    {
        $campaigns = Campaign::query()
            ->with(['template', 'smtpPool'])
            ->withCount(['messages', 'audiences'])
            ->when($this->search !== '', function ($query) {
                $query->where(function ($inner) {
                    $inner->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('subject', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->status !== '', function ($query) {
                $query->where('status', $this->status);
            })
            ->latest('id')
            ->paginate(12);

        return view('livewire.sendportal.campaigns.index', [
            'campaigns' => $campaigns,
            'stats' => [
                'total' => Campaign::query()->count(),
                'draft' => Campaign::query()->where('status', 'draft')->count(),
                'scheduled' => Campaign::query()->where('status', 'scheduled')->count(),
                'active' => Campaign::query()->where('status', 'active')->count(),
            ],
        ])->layout(config('sendportal-integration.layout'));
    }
}