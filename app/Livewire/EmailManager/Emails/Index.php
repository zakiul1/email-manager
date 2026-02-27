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

    // Filters
    public int $category_id = 0;    // 0 = All
    public string $q = '';          // search email

    // Delete confirm modal state (single)
    public ?int $confirmingDeleteId = null;

    // Bulk delete confirm modal state
    public bool $showBulkDeleteModal = false;
    public int $bulkDeleteCount = 0;

    // Selection
    public array $selected = [];            // selected email IDs (strings/ints)
    public bool $selectAllOnPage = false;   // header checkbox

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
     * (Livewire calls updatedSelected($value) when selected changes.)
     */
    public function updatedSelected($value = null): void
    {
        $this->syncSelectAllOnPage();
    }

    /**
     * Header checkbox toggled from UI.
     */
    public function updatedSelectAllOnPage($value): void
    {
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
        $this->selected = [];
        $this->selectAllOnPage = false;
    }

    public function selectAllOnCurrentPage(): void
    {
        $pageIds = $this->currentPageIds();
        $selected = $this->selectedIds();

        $this->selected = array_values(array_unique(array_merge($selected, $pageIds)));
        $this->selectAllOnPage = true;
    }

    public function unselectAllOnCurrentPage(): void
    {
        $pageIds = $this->currentPageIds();
        $selected = $this->selectedIds();

        $this->selected = array_values(array_diff($selected, $pageIds));
        $this->selectAllOnPage = false;
    }

    // ---------------- Row actions ----------------

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

    public function rowSuppress(int $emailId): void
    {
        SuppressionEntry::firstOrCreate(
            ['scope' => 'global', 'email_address_id' => $emailId],
            ['reason' => 'Row action', 'user_id' => auth()->id()]
        );

        $this->dispatch('toast', type: 'success', message: 'Email blocked (suppressed).');
    }

    // ---------------- Delete confirm flow (single) ----------------

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

    // ---------------- Bulk delete flow ----------------

    public function openBulkDeleteModal(): void
    {
        $ids = $this->selectedIds();

        if (empty($ids)) {
            $this->dispatch('toast', type: 'error', message: 'Select emails first.');
            return;
        }

        $this->bulkDeleteCount = EmailAddress::query()
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

        $ids = $this->selectedIds();

        if (empty($ids)) {
            $this->dispatch('toast', type: 'error', message: 'No emails selected.');
            $this->cancelBulkDelete();
            return;
        }

        $deleted = EmailAddress::query()
            ->whereIn('id', $ids)
            ->delete();

        $this->clearSelection();
        $this->cancelBulkDelete();

        $this->dispatch('toast', type: 'success', message: "Deleted {$deleted} email(s).");
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

        $this->syncSelectAllOnPage();

        return view('livewire.email-manager.emails.index', [
            'emails' => $emails,
            'categories' => Category::query()->orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}