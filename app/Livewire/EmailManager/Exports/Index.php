<?php

namespace App\Livewire\EmailManager\Exports;

use App\Models\Export;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public function render()
    {
        $exports = Export::query()
            ->with('category','file')
            ->latest('id')
            ->paginate(15);

        return view('livewire.email-manager.exports.index', [
            'exports' => $exports,
        ])->layout('layouts.app');
    }
}