<?php

namespace App\Livewire\SendPortal\Settings;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.sendportal.placeholders.index', [
            'title' => 'Settings',
            'description' => 'SendPortal workspace settings will be implemented after the core sending modules are complete.',
        ])->layout(config('sendportal-integration.layout'));
    }
}