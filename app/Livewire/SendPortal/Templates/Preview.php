<?php

namespace App\Livewire\SendPortal\Templates;

use App\Models\SendPortal\Template;
use App\Services\SendPortal\TemplatePlaceholderService;
use App\Services\SendPortal\TemplatePreviewSanitizer;
use Livewire\Component;

class Preview extends Component
{
    public Template $template;
    public string $device = 'desktop';

    public function mount(Template $template): void
    {
        $this->template = $template;
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

        $rendered = $placeholderService->render((string) $this->template->html_content);

        return view('livewire.sendportal.templates.preview', [
            'previewHtml' => $sanitizer->sanitize($rendered),
            'sampleValues' => $placeholderService->sampleValues(),
        ])->layout(config('sendportal-integration.layout'));
    }
}