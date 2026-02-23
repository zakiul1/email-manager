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

    // Keep only category + search in URL (optional but useful)
    protected array $queryString = [
        'category_id' => ['except' => 0],
        'q' => ['except' => ''],
    ];

    // Filters (ONLY what you want)
    public int $category_id = 0;    // 0 = All
    public string $q = '';          // search email

    // Delete confirm modal state
    public ?int $confirmingDeleteId = null;

    public function updating($name): void
    {
        // Auto-run filtering: when category or search changes, reset pagination
        if (in_array($name, ['category_id', 'q'], true)) {
            $this->resetPage();
        }
    }

    private function queryEmails(): Builder
    {
        // is_suppressed computed in SQL (no N+1)
        $suppressionSub = SuppressionEntry::query()
            ->selectRaw('1')
            ->where('scope', 'global')
            ->whereColumn('suppression_entries.email_address_id', 'email_addresses.id')
            ->limit(1);

        $query = EmailAddress::query()
            ->select('email_addresses.*')
            ->selectSub($suppressionSub, 'is_suppressed')
            ->with(['categories:id,name']); // needed for "Category" column

        // Category filter (auto)
        if ($this->category_id > 0) {
            $query->whereHas('categories', function (Builder $q) {
                $q->where('categories.id', $this->category_id);
            });
        }

        // Search filter (auto)
        $search = mb_strtolower(trim($this->q));
        if ($search !== '') {
            $query->where('email_addresses.email', 'like', '%' . $search . '%');
        }

        return $query->orderByDesc('email_addresses.id');
    }

    // ---------------- Row actions ----------------

    public function rowCopy(int $emailId): void
    {
        $email = EmailAddress::query()->find($emailId);

        if (!$email) {
            $this->dispatch('toast', type: 'error', message: 'Email not found.');
            return;
        }

        // Frontend should listen and copy to clipboard
        $this->dispatch('copy-email', text: $email->email);

        // Notification after copy trigger
        $this->dispatch('toast', type: 'success', message: 'Email copied.');
    }

    public function rowSuppress(int $emailId): void
    {
        SuppressionEntry::firstOrCreate(
            ['scope' => 'global', 'email_address_id' => $emailId],
            ['reason' => 'Row action', 'user_id' => auth()->id()]
        );

        $this->dispatch('toast', type: 'success', message: 'Email suppressed.');
        $this->resetPage();
    }

    // ---------------- Delete confirm flow ----------------

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

        $this->confirmingDeleteId = null;

        $this->dispatch('toast', type: 'success', message: 'Email deleted.');
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.email-manager.emails.index', [
            'emails' => $this->queryEmails()->paginate(25),
            'categories' => Category::query()->orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}