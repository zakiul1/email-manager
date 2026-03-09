<?php

namespace App\Livewire\SendPortal\CategoryBridge;

use App\Models\Category;
use App\Models\SendPortal\CategoryTagLink;
use App\Services\SendPortal\CategoryTagSyncService;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function toggleSync(int $categoryId): void
    {
        $link = CategoryTagLink::query()->firstOrCreate(
            ['category_id' => $categoryId],
            ['sync_enabled' => true]
        );

        $link->update([
            'sync_enabled' => ! $link->sync_enabled,
        ]);

        $this->dispatch('toast', type: 'success', message: 'Category bridge setting updated.');
    }

    public function syncCategory(int $categoryId, CategoryTagSyncService $syncService): void
    {
        $category = Category::query()->findOrFail($categoryId);

        $result = $syncService->syncCategory($category);

        $this->dispatch(
            'toast',
            type: 'success',
            message: "Category synced. Total {$result['total']}, active {$result['subscribed']}, suppressed {$result['suppressed']}."
        );
    }

    public function syncEnabled(CategoryTagSyncService $syncService): void
    {
        $syncService->syncAllEnabled();

        $this->dispatch('toast', type: 'success', message: 'All enabled category bridges synced successfully.');
    }

    public function render()
    {
        $categories = Category::query()
            ->withCount('emailAddresses')
            ->with('sendPortalTagLink')
            ->when($this->search !== '', function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%');
            })
            ->orderBy('name')
            ->paginate(12);

        return view('livewire.sendportal.category-bridge.index', [
            'categories' => $categories,
        ])->layout(config('sendportal-integration.layout'));
    }
}