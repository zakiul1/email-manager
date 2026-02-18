<?php

namespace App\Livewire\EmailManager\Exports;

use App\Jobs\ProcessExport;
use App\Models\Category;
use App\Models\Export;
use Livewire\Component;

class Create extends Component
{
    public int $category_id = 0;
    public string $format = 'csv';

    public string $domain = '';
    public string $valid = 'all';

    public bool $exclude_global_suppression = true;
    public bool $exclude_domain_unsubscribes = true;

    public function submit(): void
    {
        $this->validate([
            'format' => 'required|in:csv,txt,json',
            'category_id' => 'nullable|integer',
            'domain' => 'nullable|string|max:255',
            'valid' => 'required|in:all,valid,invalid',
        ]);

        $filters = [
            'category_id' => $this->category_id,
            'domain' => $this->domain !== '' ? $this->domain : null,
            'valid' => $this->valid,
            'exclude_global_suppression' => $this->exclude_global_suppression,
            'exclude_domain_unsubscribes' => $this->exclude_domain_unsubscribes,
        ];

        $export = Export::create([
            'user_id' => auth()->id(),
            'category_id' => $this->category_id ?: null,
            'format' => $this->format,
            'status' => 'queued',
            'filters' => $filters,
        ]);

        ProcessExport::dispatch($export->id);

        $this->redirect(route('email-manager.exports'), navigate: true);
    }

    public function render()
    {
        return view('livewire.email-manager.exports.create', [
            'categories' => Category::orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}