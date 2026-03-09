<?php

namespace App\Livewire\SendPortal\Campaigns;

use App\Models\SendPortal\Campaign;
use App\Services\SendPortal\TemplatePlaceholderService;
use App\Services\SendPortal\TemplatePreviewSanitizer;
use Livewire\Component;

class Preview extends Component
{
    public Campaign $campaign;
    public string $device = 'desktop';

    public function mount(Campaign $campaign): void
    {
        $this->campaign = $campaign;
    }

    public function setDevice(string $device): void
    {
        if (in_array($device, ['desktop', 'mobile'], true)) {
            $this->device = $device;
        }
    }

    public function render()
    {
        $placeholderService = app(TemplatePlaceholderService::class);
        $sanitizer = app(TemplatePreviewSanitizer::class);

        $html = (string) ($this->campaign->html_content ?: $this->campaign->template?->html_content ?: '');

        $rendered = $placeholderService->render($html, [
            'campaign_name' => $this->campaign->name,
        ]);

        return view('livewire.sendportal.campaigns.preview', [
            'previewHtml' => $sanitizer->sanitize($rendered),
            'sampleValues' => $placeholderService->sampleValues(),
        ])->layout(config('sendportal-integration.layout'));
    }
}