<?php

namespace App\Livewire\EmailManager\Emails;

use App\Models\Category;
use App\Models\EmailAddress;
use App\Models\SuppressionEntry;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected array $queryString = [
        'category_id' => ['except' => 0],
        'q' => ['except' => ''],
    ];

    // ✅ total matched count (for Select All Matched mode)
    public int $matchedCount = 0;

    // Filters
    public int $category_id = 0;    // 0 = All
    public string $q = '';          // search email

    // Delete confirm modal state (single)
    public ?int $confirmingDeleteId = null;

    // Bulk delete confirm modal state
    public bool $showBulkDeleteModal = false;
    public int $bulkDeleteCount = 0;

    // Selection (explicit ids)
    public array $selected = [];            // selected email IDs (strings/ints)
    public bool $selectAllOnPage = false;   // header checkbox

    /**
     * Select all matched across ALL pages (by current filters)
     * When true: we do not keep all ids in memory; delete uses the filtered query.
     */
    public bool $selectAllMatchedMode = false;

    /**
     * Internal flag to prevent hooks from cancelling selection mode during programmatic changes.
     */
    public bool $selectionInternalUpdate = false;

    /**
     * IDs for CURRENT PAGE (computed in render()).
     */
    public array $pageIds = [];

    public function updating($name): void
    {
        if (in_array($name, ['category_id', 'q'], true)) {
            $this->resetPage();
            $this->clearSelection();
        }
    }

    /**
     * Always sanitize selected ids (Livewire checkboxes can send strings).
     */
    private function selectedIds(): array
    {
        $ids = array_map('intval', $this->selected);
        $ids = array_filter($ids, fn ($v) => $v > 0);
        return array_values(array_unique($ids));
    }

    /**
     * Keep header checkbox in sync when selection changes manually.
     */
    public function updatedSelected($value = null): void
    {
        // If we changed selection internally, don't cancel modes.
        if ($this->selectionInternalUpdate) {
            $this->syncSelectAllOnPage();
            return;
        }

        // User selection manually cancels "select all matched"
        if ($this->selectAllMatchedMode) {
            $this->selectAllMatchedMode = false;
            $this->matchedCount = 0;
        }

        $this->syncSelectAllOnPage();
    }

    /**
     * Header checkbox toggled from UI (select current page only).
     */
    public function updatedSelectAllOnPage($value): void
    {
        if ($this->selectionInternalUpdate) {
            return;
        }

        // Toggling current page selection cancels "select all matched"
        if ($this->selectAllMatchedMode) {
            $this->selectAllMatchedMode = false;
            $this->matchedCount = 0;
        }

        if ((bool) $value) {
            $this->selectAllOnCurrentPage();
        } else {
            $this->unselectAllOnCurrentPage();
        }
    }

    private function queryEmails(): Builder
    {
        $suppressionSub = SuppressionEntry::query()
            ->selectRaw('1')
            ->where('scope', 'global')
            ->whereColumn('suppression_entries.email_address_id', 'email_addresses.id')
            ->limit(1);

        $query = EmailAddress::query()
            ->select('email_addresses.*')
            ->selectSub($suppressionSub, 'is_suppressed')
            ->with(['categories:id,name']);

        if ($this->category_id > 0) {
            $query->whereHas('categories', function (Builder $q) {
                $q->where('categories.id', $this->category_id);
            });
        }

        $search = mb_strtolower(trim($this->q));
        if ($search !== '') {
            $query->where('email_addresses.email', 'like', '%' . $search . '%');
        }

        return $query->orderByDesc('email_addresses.id');
    }

    /**
     * Base filter query for deletes (no eager loads, only EmailAddress table filters).
     * Must match queryEmails() filters.
     */
    private function deleteBaseQuery(): Builder
    {
        $query = EmailAddress::query();

        if ($this->category_id > 0) {
            $query->whereHas('categories', function (Builder $q) {
                $q->where('categories.id', $this->category_id);
            });
        }

        $search = mb_strtolower(trim($this->q));
        if ($search !== '') {
            $query->where('email_addresses.email', 'like', '%' . $search . '%');
        }

        return $query;
    }

    private function currentPageIds(): array
    {
        $ids = array_map('intval', $this->pageIds ?? []);
        $ids = array_filter($ids, fn ($v) => $v > 0);
        return array_values(array_unique($ids));
    }

    private function syncSelectAllOnPage(): void
    {
        $pageIds = $this->currentPageIds();
        if (empty($pageIds)) {
            $this->selectAllOnPage = false;
            return;
        }

        $selected = $this->selectedIds();
        $missing = array_diff($pageIds, $selected);

        $this->selectAllOnPage = empty($missing);
    }

    public function clearSelection(): void
    {
        $this->selectionInternalUpdate = true;

        $this->selected = [];
        $this->selectAllOnPage = false;
        $this->selectAllMatchedMode = false;
        $this->matchedCount = 0; // ✅ reset

        $this->selectionInternalUpdate = false;
    }

    public function selectAllOnCurrentPage(): void
    {
        $this->selectionInternalUpdate = true;

        $pageIds = $this->currentPageIds();
        $selected = $this->selectedIds();

        $this->selected = array_values(array_unique(array_merge($selected, $pageIds)));
        $this->selectAllOnPage = true;

        $this->selectionInternalUpdate = false;
    }

    public function unselectAllOnCurrentPage(): void
    {
        $this->selectionInternalUpdate = true;

        $pageIds = $this->currentPageIds();
        $selected = $this->selectedIds();

        $this->selected = array_values(array_diff($selected, $pageIds));
        $this->selectAllOnPage = false;

        $this->selectionInternalUpdate = false;
    }

    /* ============================================================
     |  Select all matched (across pages)
     * ============================================================ */

    public function activateSelectAllMatched(): void
    {
        $this->selectionInternalUpdate = true;

        // Enable matched mode
        $this->selectAllMatchedMode = true;

        // Count ALL matched by current filters (for display)
        $this->matchedCount = (int) $this->deleteBaseQuery()->count();

        // Select current page so checkboxes look checked
        $pageIds = $this->currentPageIds();
        $this->selected = $pageIds;
        $this->selectAllOnPage = true;

        $this->selectionInternalUpdate = false;

        $this->dispatch('toast', type: 'success', message: 'All matched emails selected.');
    }

    /* ============================================================
     |  Row actions
     * ============================================================ */

    public function rowCopy(int $emailId): void
    {
        $email = EmailAddress::query()->find($emailId);

        if (!$email) {
            $this->dispatch('toast', type: 'error', message: 'Email not found.');
            return;
        }

        $this->dispatch('copy-email', text: $email->email);
        $this->dispatch('toast', type: 'success', message: 'Email copied.');
    }

    /**
     * Toggle block/unblock for a single email.
     */
    public function rowSuppress(int $emailId): void
    {
        $existing = SuppressionEntry::query()
            ->where('scope', 'global')
            ->where('email_address_id', $emailId)
            ->first();

        if ($existing) {
            $existing->delete();
            $this->dispatch('toast', type: 'success', message: 'Email unblocked.');
            return;
        }

        SuppressionEntry::create([
            'scope' => 'global',
            'email_address_id' => $emailId,
            'reason' => 'Row action',
            'user_id' => auth()->id(),
        ]);

        $this->dispatch('toast', type: 'success', message: 'Email blocked (suppressed).');
    }

    /* ============================================================
     |  Delete confirm flow (single)
     * ============================================================ */

    public function confirmDelete(int $emailId): void
    {
        $this->confirmingDeleteId = $emailId;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDeleteId = null;
    }

    public function deleteConfirmed(): void
    {
        if (!$this->confirmingDeleteId) {
            return;
        }

        $email = EmailAddress::query()->find($this->confirmingDeleteId);

        if (!$email) {
            $this->dispatch('toast', type: 'error', message: 'Email not found.');
            $this->confirmingDeleteId = null;
            return;
        }

        $email->delete();

        $selected = $this->selectedIds();
        $this->selected = array_values(array_diff($selected, [(int) $email->id]));

        $this->confirmingDeleteId = null;

        $this->dispatch('toast', type: 'success', message: 'Email deleted.');
        $this->syncSelectAllOnPage();
    }

    /* ============================================================
     |  Bulk delete flow
     * ============================================================ */

    public function openBulkDeleteModal(): void
    {
        // If select-all-matched mode, show count for whole filtered dataset
        if ($this->selectAllMatchedMode) {
            $this->bulkDeleteCount = (int) $this->deleteBaseQuery()->count();
            if ($this->bulkDeleteCount <= 0) {
                $this->dispatch('toast', type: 'error', message: 'No matched emails to delete.');
                return;
            }
            $this->showBulkDeleteModal = true;
            return;
        }

        // Otherwise, delete explicit selected ids
        $ids = $this->selectedIds();
        if (empty($ids)) {
            $this->dispatch('toast', type: 'error', message: 'Select emails first.');
            return;
        }

        $this->bulkDeleteCount = (int) EmailAddress::query()
            ->whereIn('id', $ids)
            ->count();

        $this->showBulkDeleteModal = true;
    }

    public function cancelBulkDelete(): void
    {
        $this->showBulkDeleteModal = false;
        $this->bulkDeleteCount = 0;
    }

    public function bulkDeleteConfirmed(): void
    {
        if (!$this->showBulkDeleteModal) {
            return;
        }

        // Delete ALL matched by filters (safe chunks)
        if ($this->selectAllMatchedMode) {
            $deleted = 0;

            $this->deleteBaseQuery()
                ->select('email_addresses.id')
                ->orderBy('email_addresses.id')
                ->chunkById(5000, function ($rows) use (&$deleted) {
                    $ids = $rows->pluck('id')->all();
                    $deleted += EmailAddress::query()->whereIn('id', $ids)->delete();
                });

            $this->clearSelection();
            $this->cancelBulkDelete();

            $this->dispatch('toast', type: 'success', message: "Deleted {$deleted} email(s).");
            $this->resetPage();
            return;
        }

        // Delete explicit selected ids
        $ids = $this->selectedIds();
        if (empty($ids)) {
            $this->dispatch('toast', type: 'error', message: 'No emails selected.');
            $this->cancelBulkDelete();
            return;
        }

        $deleted = 0;
        foreach (array_chunk($ids, 1000) as $chunk) {
            $deleted += EmailAddress::query()->whereIn('id', $chunk)->delete();
        }

        $this->clearSelection();
        $this->cancelBulkDelete();

        $this->dispatch('toast', type: 'success', message: "Deleted {$deleted} email(s).");
        $this->resetPage();
    }

    public function render()
    {
        $emails = $this->queryEmails()->paginate(25);

        // Keep current page ids in component state
        $this->pageIds = $emails->getCollection()
            ->pluck('id')
            ->map(fn ($v) => (int) $v)
            ->values()
            ->all();

        // ✅ If Select-All-Matched mode is ON, keep current page checked
        if ($this->selectAllMatchedMode) {
            $this->selectionInternalUpdate = true;
            $this->selected = $this->currentPageIds();
            $this->selectAllOnPage = true;
            $this->selectionInternalUpdate = false;
        } else {
            $this->syncSelectAllOnPage();
        }

        return view('livewire.email-manager.emails.index', [
            'emails' => $emails,
            'categories' => Category::query()->orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}