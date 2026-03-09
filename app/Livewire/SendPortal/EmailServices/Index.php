<?php

namespace App\Livewire\SendPortal\EmailServices;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.sendportal.placeholders.index', [
            'title' => 'Email Services',
            'description' => 'Email services, SMTP configs, and mailer selection UI will be expanded in Phase 5.',
        ])->layout(config('sendportal-integration.layout'));
    }
}