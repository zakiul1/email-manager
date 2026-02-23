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

    // ✅ Hard warning confirmation
    public string $deleteConfirmText = ''; // user must type DELETE
    public bool $dangerAcknowledge = false; // optional checkbox (use in Blade if you want)

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Set the category data that will be shown in the modal.
     */
    public function confirmDelete(int $categoryId): void
    {
        $category = Category::query()->findOrFail($categoryId);

        $this->deleteCategoryId = (int) $category->id;
        $this->deleteCategoryName = (string) $category->name;

        // reset danger confirmation each time modal opens
        $this->deleteConfirmText = '';
        $this->dangerAcknowledge = false;
    }

    /**
     * Option B (Danger):
     * - Delete pivot rows for this category
     * - Delete the category
     * - Delete orphan email_addresses not linked to any category anymore
     *
     * Hard warning: require typing DELETE
     */
    public function deleteConfirmed(): void
    {
        if ($this->deleteCategoryId <= 0) {
            $this->dispatch('toast', type: 'error', message: 'Invalid category selected.', timeout: 5000);
            return;
        }

        // ✅ Hard warning check
        if (trim($this->deleteConfirmText) !== 'DELETE') {
            $this->dispatch('toast', type: 'error', message: 'Type DELETE to confirm this dangerous action.', timeout: 6000);
            return;
        }

        $category = Category::query()->withCount('emails')->findOrFail($this->deleteCategoryId);

        DB::transaction(function () use ($category) {
            // 1) Remove all relations for this category
            DB::table('category_email')
                ->where('category_id', $category->id)
                ->delete();

            // 2) Delete the category itself
            $category->delete();

            // 3) Delete orphan email addresses (not linked to any category)
            //    This is the "danger" part: permanently deletes emails that are not used elsewhere.
            DB::table('email_addresses')
                ->whereNotExists(function ($q) {
                    $q->select(DB::raw(1))
                        ->from('category_email')
                        ->whereColumn('category_email.email_address_id', 'email_addresses.id');
                })
                ->delete();
        });

        $this->dispatch('toast', type: 'success', message: 'Category deleted. Orphan emails cleaned up.', timeout: 6000);

        // reset modal state
        $this->deleteCategoryId = 0;
        $this->deleteCategoryName = '';
        $this->deleteConfirmText = '';
        $this->dangerAcknowledge = false;

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
            ->when($this->search !== '', fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
            ->orderBy('name')
            ->paginate($perPage);

        return view('livewire.email-manager.categories.index', [
            'categories' => $categories,
            'totalCategories' => $totalCategories,
            'totalEmailsInCategories' => $totalEmailsInCategories,
        ])->layout('layouts.app');
    }
}