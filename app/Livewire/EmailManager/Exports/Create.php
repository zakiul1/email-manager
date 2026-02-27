<?php

namespace App\Livewire\EmailManager\Exports;

use App\Models\Category;
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
            'exclude_global_suppression' => 'boolean',
            'exclude_domain_unsubscribes' => 'boolean',
        ]);

        // clean domain
        $domain = trim(mb_strtolower($this->domain));
        $domain = $domain !== '' ? $domain : null;

        // Build query params for direct download endpoint
        $params = [
            'format' => $this->format,
            'category_id' => $this->category_id > 0 ? $this->category_id : null,
            'domain' => $domain,
            'valid' => $this->valid,
            'exclude_global_suppression' => $this->exclude_global_suppression ? 1 : 0,
            'exclude_domain_unsubscribes' => $this->exclude_domain_unsubscribes ? 1 : 0,
        ];

        // Remove null values so URL is clean
        $params = array_filter($params, fn ($v) => $v !== null);

        // Redirect browser to download URL -> download starts instantly
        $this->redirect(route('email-manager.exports.download', $params), navigate: false);
    }

    public function render()
    {
        return view('livewire.email-manager.exports.create', [
            'categories' => Category::orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}