<?php

namespace App\Livewire\EmailManager\Emails;

use App\Models\Category;
use App\Models\EmailAddress;
use App\Models\SavedFilter;
use App\Models\SuppressionEntry;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    // ✅ Make filters work via URL query string (category-wise view)
    protected array $queryString = [
        'category_id' => ['except' => 0],
        'q' => ['except' => ''],
        'domain' => ['except' => ''],
        'valid' => ['except' => 'all'],
        'suppressed' => ['except' => 'all'],
        'added_from' => ['except' => ''],
        'added_to' => ['except' => ''],
    ];

    // Filters
    public int $category_id = 0;            // optional filter
    public string $q = '';                  // search email
    public string $domain = '';             // filter domain
    public string $valid = 'all';           // all|valid|invalid
    public string $suppressed = 'all';      // all|yes|no
    public string $added_from = '';         // date (YYYY-MM-DD)
    public string $added_to = '';           // date (YYYY-MM-DD)

    // Bulk selection
    public array $selected = [];            // list of email_address_id
    public bool $selectPage = false;

    // Bulk action inputs
    public int $target_category_id = 0;
    public string $action = '';

    // Saved filters
    public int $saved_filter_id = 0;
    public string $save_filter_name = '';

    public function updating($name): void
    {
        if (in_array($name, ['category_id', 'q', 'domain', 'valid', 'suppressed', 'added_from', 'added_to'], true)) {
            $this->resetPage();
            $this->resetSelection();
        }
    }

    private function resetSelection(): void
    {
        $this->selected = [];
        $this->selectPage = false;
    }

    public function toggleSelectPage(): void
    {
        $this->selectPage = !$this->selectPage;

        if (!$this->selectPage) {
            $this->selected = [];
            return;
        }

        $this->selected = $this->queryEmails()
            ->limit(200)
            ->pluck('email_addresses.id')
            ->toArray();
    }

    private function queryEmails()
    {
        // ✅ is_suppressed computed once in SQL (fix N+1)
        $suppressionSub = SuppressionEntry::query()
            ->selectRaw('1')
            ->where('scope', 'global')
            ->whereColumn('suppression_entries.email_address_id', 'email_addresses.id')
            ->limit(1);

        // base: emails joined to pivot to enable category filtering + date range
        $query = EmailAddress::query()
            ->select('email_addresses.*')
            ->selectSub($suppressionSub, 'is_suppressed')
            ->when($this->category_id > 0, function ($q) {
                $q->join('category_email', 'category_email.email_address_id', '=', 'email_addresses.id')
                    ->where('category_email.category_id', $this->category_id);
            });

        if ($this->q !== '') {
            $query->where('email_addresses.email', 'like', '%' . mb_strtolower(trim($this->q)) . '%');
        }

        if ($this->domain !== '') {
            $query->where('email_addresses.domain', mb_strtolower(trim($this->domain)));
        }

        if ($this->valid === 'valid') {
            $query->where('email_addresses.is_valid', true);
        } elseif ($this->valid === 'invalid') {
            $query->where('email_addresses.is_valid', false);
        }

        if ($this->suppressed !== 'all') {
            $query->when($this->suppressed === 'yes', function ($q) {
                $q->whereExists(function ($sub) {
                    $sub->selectRaw(1)
                        ->from('suppression_entries')
                        ->where('suppression_entries.scope', 'global')
                        ->whereColumn('suppression_entries.email_address_id', 'email_addresses.id');
                });
            });

            $query->when($this->suppressed === 'no', function ($q) {
                $q->whereNotExists(function ($sub) {
                    $sub->selectRaw(1)
                        ->from('suppression_entries')
                        ->where('suppression_entries.scope', 'global')
                        ->whereColumn('suppression_entries.email_address_id', 'email_addresses.id');
                });
            });
        }

        // date range only if category join exists
        if ($this->category_id > 0 && ($this->added_from || $this->added_to)) {
            if ($this->added_from) {
                $query->whereDate('category_email.created_at', '>=', $this->added_from);
            }
            if ($this->added_to) {
                $query->whereDate('category_email.created_at', '<=', $this->added_to);
            }
        }

        return $query->orderBy('email_addresses.id', 'desc');
    }

    // ---------- Row Actions (used by emails table) ----------
    public function rowCopyToCategory(int $emailId): void
    {
        if ($this->target_category_id <= 0) {
            $this->dispatch('toast', type: 'error', message: 'Select a target category first.');
            return;
        }

        $this->bulkCopyToCategory([$emailId]);
        $this->dispatch('toast', type: 'success', message: 'Copied to target category.');
        $this->resetPage();
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

    public function rowUnsuppress(int $emailId): void
    {
        SuppressionEntry::where('scope', 'global')
            ->where('email_address_id', $emailId)
            ->delete();

        $this->dispatch('toast', type: 'success', message: 'Suppression removed.');
        $this->resetPage();
    }

    public function rowDetach(int $emailId): void
    {
        if ($this->category_id <= 0) {
            $this->dispatch('toast', type: 'error', message: 'Select a category first to detach.');
            return;
        }

        DB::table('category_email')
            ->where('category_id', $this->category_id)
            ->where('email_address_id', $emailId)
            ->delete();

        $this->dispatch('toast', type: 'success', message: 'Email detached from category.');
        $this->resetPage();
    }

    // ---------- Saved Filters ----------
    public function saveFilter(): void
    {
        $this->validate([
            'save_filter_name' => 'required|string|max:100',
        ]);

        SavedFilter::create([
            'user_id' => auth()->id(),
            'scope' => 'emails',
            'name' => $this->save_filter_name,
            'filters' => $this->currentFilters(),
        ]);

        $this->save_filter_name = '';

        $this->dispatch('toast', type: 'success', message: 'Filter saved.');
    }

    public function applySavedFilter(): void
    {
        if ($this->saved_filter_id <= 0) {
            return;
        }

        $sf = SavedFilter::where('user_id', auth()->id())
            ->where('scope', 'emails')
            ->find($this->saved_filter_id);

        if (!$sf) {
            return;
        }

        $f = $sf->filters ?? [];

        $this->category_id = (int)($f['category_id'] ?? 0);
        $this->q = (string)($f['q'] ?? '');
        $this->domain = (string)($f['domain'] ?? '');
        $this->valid = (string)($f['valid'] ?? 'all');
        $this->suppressed = (string)($f['suppressed'] ?? 'all');
        $this->added_from = (string)($f['added_from'] ?? '');
        $this->added_to = (string)($f['added_to'] ?? '');

        $this->resetPage();
        $this->resetSelection();

        $this->dispatch('toast', type: 'success', message: 'Saved filter applied.');
    }

    private function currentFilters(): array
    {
        return [
            'category_id' => $this->category_id,
            'q' => $this->q,
            'domain' => $this->domain,
            'valid' => $this->valid,
            'suppressed' => $this->suppressed,
            'added_from' => $this->added_from,
            'added_to' => $this->added_to,
        ];
    }

    // ---------- Bulk Actions ----------
    public function runBulkAction(): void
    {
        if (empty($this->selected)) {
            $this->dispatch('toast', type: 'error', message: 'No emails selected.');
            return;
        }

        $this->validate([
            'action' => 'required|in:copy_to,move_to,merge_categories,suppress,unsuppress,detach',
        ]);

        $ids = array_map('intval', $this->selected);

        if ($this->action === 'suppress') {
            $this->bulkSuppress($ids);
            $this->dispatch('toast', type: 'success', message: 'Selected emails suppressed.');
        } elseif ($this->action === 'unsuppress') {
            $this->bulkUnSuppress($ids);
            $this->dispatch('toast', type: 'success', message: 'Selected emails unsuppressed.');
        } elseif ($this->action === 'copy_to') {
            $this->bulkCopyToCategory($ids);
            $this->dispatch('toast', type: 'success', message: 'Copied to category.');
        } elseif ($this->action === 'move_to') {
            $this->bulkMoveToCategory($ids);
            $this->dispatch('toast', type: 'success', message: 'Moved to category.');
        } elseif ($this->action === 'detach') {
            $this->bulkDetachFromCategory($ids);
            $this->dispatch('toast', type: 'success', message: 'Detached from category.');
        } elseif ($this->action === 'merge_categories') {
            $this->bulkMergeCategories();
            $this->dispatch('toast', type: 'success', message: 'Categories merged.');
        }

        $this->resetSelection();
        $this->resetPage();
    }

    private function bulkSuppress(array $emailIds): void
    {
        foreach ($emailIds as $id) {
            SuppressionEntry::firstOrCreate(
                ['scope' => 'global', 'email_address_id' => $id],
                ['reason' => 'Bulk action', 'user_id' => auth()->id()]
            );
        }
    }

    private function bulkUnSuppress(array $emailIds): void
    {
        SuppressionEntry::where('scope', 'global')
            ->whereIn('email_address_id', $emailIds)
            ->delete();
    }

    private function bulkCopyToCategory(array $emailIds): void
    {
        $this->validate(['target_category_id' => 'required|integer|exists:categories,id']);

        $now = now();
        $rows = [];

        foreach ($emailIds as $emailId) {
            $rows[] = [
                'category_id' => $this->target_category_id,
                'email_address_id' => $emailId,
                'times_added' => 1,
                'import_batch_id' => null,
                'last_seen_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('category_email')->upsert(
            $rows,
            ['category_id', 'email_address_id'],
            ['updated_at', 'last_seen_at']
        );
    }

    private function bulkMoveToCategory(array $emailIds): void
    {
        $this->validate([
            'category_id' => 'required|integer|exists:categories,id',
            'target_category_id' => 'required|integer|exists:categories,id',
        ]);

        $this->bulkCopyToCategory($emailIds);

        DB::table('category_email')
            ->where('category_id', $this->category_id)
            ->whereIn('email_address_id', $emailIds)
            ->delete();
    }

    private function bulkDetachFromCategory(array $emailIds): void
    {
        $this->validate([
            'category_id' => 'required|integer|exists:categories,id',
        ]);

        DB::table('category_email')
            ->where('category_id', $this->category_id)
            ->whereIn('email_address_id', $emailIds)
            ->delete();
    }

    private function bulkMergeCategories(): void
    {
        $this->validate([
            'category_id' => 'required|integer|exists:categories,id',
            'target_category_id' => 'required|integer|exists:categories,id',
        ]);

        $source = $this->category_id;
        $target = $this->target_category_id;

        if ($source === $target) return;

        $ids = DB::table('category_email')
            ->where('category_id', $source)
            ->pluck('email_address_id')
            ->toArray();

        $this->bulkCopyToCategory(array_map('intval', $ids));

        DB::table('category_email')->where('category_id', $source)->delete();
    }

    public function render()
    {
        $emails = $this->queryEmails()->paginate(25);

        return view('livewire.email-manager.emails.index', [
            'emails' => $emails,
            'categories' => Category::orderBy('name')->get(),
            'savedFilters' => SavedFilter::query()
                ->where('user_id', auth()->id())
                ->where('scope', 'emails')
                ->orderBy('name')
                ->get(),
        ])->layout('layouts.app');
    }
}