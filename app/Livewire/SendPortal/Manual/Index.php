<?php

namespace App\Livewire\SendPortal\Manual;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.sendportal.manual.index')
            ->layout(config('sendportal-integration.layout'));
    }
}