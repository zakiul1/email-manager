<?php

namespace App\Livewire\SendPortal\Templates;

use App\Models\SendPortal\Template;
use App\Services\SendPortal\ActivityLogService;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = '';

    public ?int $deleteId = null;
    public ?string $deleteName = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function duplicateTemplate(int $templateId): void
    {
        $template = Template::query()->findOrFail($templateId);

        $copy = $template->replicate();
        $copy->name = $template->name . ' Copy';
        $copy->slug = Str::slug($copy->name . '-' . Str::random(6));
        $copy->status = 'draft';
        $copy->usage_count = 0;
        $copy->last_test_sent_at = null;
        $copy->save();

        app(ActivityLogService::class)->log('template.duplicated', $copy, [
            'source_template_id' => $template->id,
        ]);

        $this->dispatch('toast', type: 'success', message: 'Template duplicated successfully.');
    }

    public function confirmDelete(int $templateId, string $name): void
    {
        $this->deleteId = $templateId;
        $this->deleteName = $name;
    }

    public function cancelDelete(): void
    {
        $this->resetDeleteState();
    }

    public function deleteConfirmed(): void
    {
        if (! $this->deleteId) {
            return;
        }

        $template = Template::query()->findOrFail($this->deleteId);

        app(ActivityLogService::class)->log('template.deleted', $template, [
            'name' => $template->name,
        ]);

        $template->delete();

        $this->resetDeleteState();

        $this->dispatch('toast', type: 'success', message: 'Template deleted successfully.');
        $this->dispatch('close-modal', name: 'delete-template');
    }

    protected function resetDeleteState(): void
    {
        $this->deleteId = null;
        $this->deleteName = null;
    }

    public function render()
    {
        $templates = Template::query()
            ->withCount('tests')
            ->when($this->search !== '', function ($query) {
                $query->where(function ($inner) {
                    $inner->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('slug', 'like', '%' . $this->search . '%')
                        ->orWhere('subject', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->status !== '', function ($query) {
                $query->where('status', $this->status);
            })
            ->latest('updated_at')
            ->paginate(12);

        return view('livewire.sendportal.templates.index', [
            'templates' => $templates,
            'stats' => [
                'total' => Template::query()->count(),
                'draft' => Template::query()->where('status', 'draft')->count(),
                'active' => Template::query()->where('status', 'active')->count(),
                'archived' => Template::query()->where('status', 'archived')->count(),
            ],
        ])->layout(config('sendportal-integration.layout'));
    }
}