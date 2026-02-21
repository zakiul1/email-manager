<?php

namespace App\Livewire\EmailManager\Categories;

use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    // ✅ Delete modal state
    public int $deleteCategoryId = 0;
    public string $deleteCategoryName = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Set the category data that will be shown in the modal.
     * ✅ Do NOT dispatch open-modal here (Flux will open via <flux:modal.trigger> in Blade)
     */
    public function confirmDelete(int $categoryId): void
    {
        $category = Category::query()->findOrFail($categoryId);

        $this->deleteCategoryId = (int) $category->id;
        $this->deleteCategoryName = (string) $category->name;
    }

    /**
     * Delete only after user confirms in modal.
     * ✅ Do NOT dispatch close-modal here (Flux will close via <flux:modal.close> in Blade)
     */
    public function deleteConfirmed(): void
    {
        if ($this->deleteCategoryId <= 0) {
            $this->dispatch('toast', type: 'error', message: 'Invalid category selected.', timeout: 5000);
            return;
        }

        $category = Category::query()->withCount('emails')->findOrFail($this->deleteCategoryId);

        if (($category->emails_count ?? 0) > 0) {
            $this->dispatch('toast', type: 'error', message: 'You cannot delete this category because it has emails.', timeout: 5000);
            return;
        }

        $category->delete();

        $this->dispatch('toast', type: 'success', message: 'Category deleted successfully.', timeout: 5000);

        // reset modal state
        $this->deleteCategoryId = 0;
        $this->deleteCategoryName = '';

        // ✅ Safe after delete
        $this->resetPage();
    }

    public function render()
    {
        $perPage = 15;

        $totalCategories = Category::query()->count();
        $totalEmailsInCategories = (int) DB::table('category_email')->count();

        $categories = Category::query()
            ->select('categories.*')
            ->withCount('emails')
            ->when($this->search !== '', fn ($q) => $q->where('name', 'like', '%' . $this->search . '%'))
            ->orderBy('name')
            ->paginate($perPage);

        return view('livewire.email-manager.categories.index', [
            'categories' => $categories,
            'totalCategories' => $totalCategories,
            'totalEmailsInCategories' => $totalEmailsInCategories,
        ])->layout('layouts.app');
    }
}