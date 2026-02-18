<?php

namespace App\Livewire\EmailManager\Imports;

use App\Models\ImportBatch;
use Livewire\Component;
use Livewire\WithPagination;

class Batches extends Component
{
    use WithPagination;

    public function render()
    {
        $batches = ImportBatch::query()
            ->with('category')
            ->latest('id')
            ->paginate(15);

        return view('livewire.email-manager.imports.batches', [
            'batches' => $batches,
        ])->layout('layouts.app');
    }
}