<?php

namespace App\Livewire\SendPortal\Templates;

use App\Models\SendPortal\Template;
use App\Services\SendPortal\ActivityLogService;
use App\Services\SendPortal\TemplateTestSendService;
use Livewire\Component;

class TestSend extends Component
{
    public Template $template;

    public string $recipient_email = '';

    public function mount(Template $template): void
    {
        $this->template = $template;
    }

    public function send(TemplateTestSendService $testSendService): void
    {
        $validated = $this->validate([
            'recipient_email' => ['required', 'email:rfc,dns', 'max:255'],
        ]);

        $result = $testSendService->send($this->template, $validated['recipient_email']);

        app(ActivityLogService::class)->log('template.test_sent', $this->template, [
            'recipient_email' => $validated['recipient_email'],
            'ok' => $result['ok'],
            'message' => $result['message'],
        ]);

        session()->flash('toast', [
            'type' => $result['ok'] ? 'success' : 'error',
            'message' => $result['message'],
        ]);

        if ($result['ok']) {
            $this->redirectRoute('sendportal.workspace.templates.index', navigate: true);
        }
    }

    public function render()
    {
        return view('livewire.sendportal.templates.test-send')
            ->layout(config('sendportal-integration.layout'));
    }
}