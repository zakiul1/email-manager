<?php

namespace App\Livewire\SendPortal\Campaigns;

use App\Jobs\SendPortal\DispatchCampaignJob;
use App\Models\SendPortal\Campaign;
use App\Services\SendPortal\ActivityLogService;
use Livewire\Component;

class Show extends Component
{
    public Campaign $campaign;

    public function duplicate(ActivityLogService $activityLogService): void
    {
        abort_unless(auth()->user()?->can('update', $this->campaign), 403);

        $copy = $this->campaign->replicate();
        $copy->name = $this->campaign->name.' Copy';
        $copy->status = 'draft';
        $copy->delivery_mode = 'draft';
        $copy->scheduled_at = null;
        $copy->queued_at = null;
        $copy->sent_at = null;
        $copy->recipient_count = 0;
        $copy->sent_count = 0;
        $copy->failed_count = 0;
        $copy->save();

        foreach ($this->campaign->audiences as $audience) {
            $copy->audiences()->create([
                'source_type' => $audience->source_type,
                'source_id' => $audience->source_id,
                'filters' => $audience->filters,
            ]);
        }

        $activityLogService->log('campaign.duplicated', $copy, [
            'source_campaign_id' => $this->campaign->id,
        ]);

        session()->flash('toast', [
            'type' => 'success',
            'message' => 'Campaign duplicated successfully.',
        ]);

        $this->redirectRoute('sendportal.workspace.campaigns.edit', ['campaign' => $copy->id], navigate: true);
    }

    public function setStatus(string $status, ActivityLogService $activityLogService): void
    {
        abort_unless(auth()->user()?->can('update', $this->campaign), 403);

        if (! in_array($status, ['paused', 'cancelled', 'active'], true)) {
            return;
        }

        $this->campaign->update([
            'status' => $status,
        ]);

        $activityLogService->log('campaign.status_changed', $this->campaign, [
            'status' => $status,
        ]);

        $this->dispatch('toast', type: 'success', message: 'Campaign status updated.');
    }

    public function dispatchCampaign(ActivityLogService $activityLogService): void
    {
        abort_unless(auth()->user()?->can('dispatch', $this->campaign), 403);

        if ($this->campaign->messages()->count() === 0) {
            $this->dispatch('toast', type: 'error', message: 'Prepare audience messages before dispatching.');

            return;
        }

        DispatchCampaignJob::dispatch($this->campaign->id);

        $activityLogService->log('campaign.dispatch_requested', $this->campaign, [
            'campaign_id' => $this->campaign->id,
        ]);

        $this->dispatch('toast', type: 'success', message: 'Campaign dispatch queued successfully.');
    }

    public function render()
    {
        abort_unless(auth()->user()?->can('view', $this->campaign), 403);

        $campaign = Campaign::query()
            ->with(['template', 'smtpPool', 'audiences'])
            ->withCount('messages')
            ->findOrFail($this->campaign->id);

        return view('livewire.sendportal.campaigns.show', [
            'campaign' => $campaign,
        ])->layout(config('sendportal-integration.layout'));
    }
}