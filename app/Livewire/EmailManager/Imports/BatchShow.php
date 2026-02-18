<?php

namespace App\Livewire\EmailManager\Imports;

use App\Models\ImportBatch;
use Livewire\Component;
use Livewire\WithPagination;

class BatchShow extends Component
{
    use WithPagination;

    public ImportBatch $batch;

    public string $filter = 'all'; // all|inserted|invalid|duplicate|suppressed

    public function render()
    {
        $items = $this->batch->items()
            ->when($this->filter !== 'all', fn ($q) => $q->where('status', $this->filter))
            ->latest('id')
            ->paginate(25);

        return view('livewire.email-manager.imports.batch-show', [
            'items' => $items,
        ])->layout('layouts.app');
    }
}