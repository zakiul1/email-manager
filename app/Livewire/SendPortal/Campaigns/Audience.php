<?php

namespace App\Livewire\SendPortal\Campaigns;

use App\Models\SendPortal\Campaign;
use App\Services\SendPortal\CampaignAudienceResolver;
use App\Services\SendPortal\CampaignPreparationService;
use Livewire\Component;
use Livewire\WithPagination;

class Audience extends Component
{
    use WithPagination;

    public Campaign $campaign;

    public function prepareMessages(CampaignPreparationService $preparationService): void
    {
        $result = $preparationService->prepare($this->campaign);

        $this->dispatch(
            'toast',
            type: 'success',
            message: "Audience prepared. Active {$result['prepared']}, suppressed {$result['suppressed']}."
        );
    }

    public function render(CampaignAudienceResolver $audienceResolver)
    {
        $campaign = Campaign::query()
            ->with(['audiences', 'messages.subscriber'])
            ->findOrFail($this->campaign->id);

        $stats = $audienceResolver->stats($campaign);

        $messages = $campaign->messages()
            ->with('subscriber')
            ->latest('id')
            ->paginate(20);

        return view('livewire.sendportal.campaigns.audience', [
            'campaign' => $campaign,
            'audienceStats' => $stats,
            'messages' => $messages,
        ])->layout(config('sendportal-integration.layout'));
    }
}