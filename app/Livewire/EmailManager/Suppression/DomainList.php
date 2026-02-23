<?php

namespace App\Livewire\EmailManager\Suppression;

use App\Models\DomainUnsubscribe;
use App\Models\EmailAddress;
use Livewire\Component;
use Livewire\WithPagination;

class DomainList extends Component
{
    use WithPagination;

    /**
     * Add multiple (textarea)
     * Supports: newline, comma, semicolon
     */
    public string $domainsText = '';
    public ?string $reason = null;

    /**
     * Add mode + main list filter
     * - domain: exact domain match (gmail.com)
     * - extension: suffix match (.bd / .com.bd)
     */
    public string $type = 'domain';

    /**
     * Main list search (value like)
     */
    public string $search = '';

    /**
     * Main list selected (ids)
     */
    public array $selected = [];

    /**
     * Bottom Global Search (from EMAILS table)
     * Search domains from email_addresses and allow bulk DELETE emails from DB.
     */
    public string $emailSearchMode = 'all'; // all|domain|extension
    public string $emailSearch = '';
    public array $emailSelected = []; // selected domains (strings)

    /**
     * Delete confirm modal state for deleting emails
     * (Renamed to avoid conflict with method name)
     */
    public bool $showDeleteEmailsModal = false;
    public int $confirmDeleteEmailCount = 0;

    public function updating($name): void
    {
        // reset pagination & selection on main list filters change
        if (in_array($name, ['type', 'search'], true)) {
            $this->resetPage();
            $this->selected = [];
        }

        // reset pagination & selection on email-global filters change
        if (in_array($name, ['emailSearchMode', 'emailSearch'], true)) {
            $this->resetPage();
            $this->emailSelected = [];
        }
    }

    /**
     * Parse textarea into unique normalized values
     */
    private function parseValues(string $text, string $type): array
    {
        $text = (string) $text;

        $parts = preg_split('/[\r\n,;]+/', $text) ?: [];

        $out = [];
        foreach ($parts as $p) {
            $v = mb_strtolower(trim((string) $p));
            if ($v === '') {
                continue;
            }

            $v = preg_replace('/\s+/', '', $v) ?? $v;
            $v = ltrim($v, '@');

            if ($type === 'domain') {
                $v = ltrim($v, '.');
            } else {
                $v = ltrim($v, '.');
                $v = '.' . $v;
            }

            $out[] = $v;
        }

        $out = array_values(array_unique($out));
        $out = array_values(array_filter($out, fn($x) => mb_strlen($x) <= 255));

        return $out;
    }

    /**
     * Add multiple domains/extensions at once
     */
    public function addMultiple(): void
    {
        $this->validate([
            'domainsText' => 'required|string',
            'type' => 'required|in:domain,extension',
            'reason' => 'nullable|string|max:255',
        ]);

        $values = $this->parseValues($this->domainsText, $this->type);

        if (empty($values)) {
            $this->dispatch('toast', type: 'error', message: 'No valid items found.');
            return;
        }

        $now = now();
        $rows = [];

        foreach ($values as $v) {
            $rows[] = [
                'type' => $this->type,
                'value' => $v,
                'reason' => $this->reason,
                'user_id' => auth()->id(),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DomainUnsubscribe::query()->upsert(
            $rows,
            ['type', 'value'],
            ['updated_at', 'reason', 'user_id']
        );

        $this->domainsText = '';
        $this->reason = null;
        $this->selected = [];
        $this->resetPage();

        $this->dispatch('toast', type: 'success', message: 'Added successfully.');
    }

    public function remove(int $id): void
    {
        DomainUnsubscribe::where('id', $id)->delete();

        $this->selected = array_values(array_filter($this->selected, fn($x) => (int) $x !== (int) $id));
        $this->resetPage();
    }

    /**
     * MAIN list bulk delete
     */
    public function bulkDeleteSelected(): void
    {
        if (empty($this->selected)) {
            $this->dispatch('toast', type: 'error', message: 'No items selected.');
            return;
        }

        $ids = array_values(array_unique(array_map('intval', $this->selected)));

        DomainUnsubscribe::query()
            ->whereIn('id', $ids)
            ->delete();

        $this->selected = [];
        $this->resetPage();

        $this->dispatch('toast', type: 'success', message: 'Deleted selected items.');
    }

    /**
     * Query distinct domains from EmailAddress based on search mode
     */
    private function emailDomainQuery()
    {
        $s = mb_strtolower(trim($this->emailSearch));

        $qb = EmailAddress::query()->select('domain')->whereNotNull('domain');

        // No search => return empty list
        if ($s === '') {
            $qb->whereRaw('1=0');
            return $qb;
        }

        // normalize input for extension
        $ext = $s;
        if ($ext !== '' && $ext[0] !== '.') {
            $ext = '.' . ltrim($ext, '.');
        }

        if ($this->emailSearchMode === 'domain') {
            $qb->whereRaw('LOWER(domain) LIKE ?', ['%' . $s . '%']);
        } elseif ($this->emailSearchMode === 'extension') {
            $qb->whereRaw('LOWER(domain) LIKE ?', ['%' . $ext]);
        } else {
            $qb->where(function ($q) use ($s, $ext) {
                $q->whereRaw('LOWER(domain) LIKE ?', ['%' . $s . '%'])
                    ->orWhereRaw('LOWER(domain) LIKE ?', ['%' . $ext]);
            });
        }

        // distinct domains
        return $qb->groupBy('domain');
    }

    /**
     * Select all matched domains from emails
     */
    public function selectAllEmailMatches(): void
    {
        $domains = $this->emailDomainQuery()
            ->pluck('domain')
            ->map(fn($d) => mb_strtolower(trim((string) $d)))
            ->filter(fn($d) => $d !== '')
            ->unique()
            ->values()
            ->all();

        $this->emailSelected = $domains;

        $this->dispatch('toast', type: 'success', message: 'All matched domains selected.');
    }

    public function clearEmailSelection(): void
    {
        $this->emailSelected = [];
    }

    /**
     * Open confirm modal to delete emails from database by selected domains
     */
    public function openDeleteEmailsModal(): void
    {
        if (empty($this->emailSelected)) {
            $this->dispatch('toast', type: 'error', message: 'Select domains first.');
            return;
        }

        $domains = array_values(array_unique(array_map(function ($d) {
            $d = mb_strtolower(trim((string) $d));
            $d = ltrim($d, '@');
            return $d;
        }, $this->emailSelected)));

        $count = EmailAddress::query()
            ->whereIn('domain', $domains)
            ->count();

        $this->confirmDeleteEmailCount = (int) $count;
        $this->showDeleteEmailsModal = true;
    }

    public function cancelDeleteEmails(): void
    {
        $this->showDeleteEmailsModal = false;
        $this->confirmDeleteEmailCount = 0;
    }

    /**
     * Delete emails from DB (all emails under selected domains)
     */
    public function deleteEmailsConfirmed(): void
    {
        if (!$this->showDeleteEmailsModal) {
            return;
        }

        if (empty($this->emailSelected)) {
            $this->dispatch('toast', type: 'error', message: 'No domains selected.');
            $this->cancelDeleteEmails();
            return;
        }

        $domains = array_values(array_unique(array_map(function ($d) {
            $d = mb_strtolower(trim((string) $d));
            $d = ltrim($d, '@');
            return $d;
        }, $this->emailSelected)));

        $deleted = EmailAddress::query()
            ->whereIn('domain', $domains)
            ->delete();

        $this->emailSelected = [];
        $this->cancelDeleteEmails();
        $this->resetPage();

        $this->dispatch('toast', type: 'success', message: "Deleted {$deleted} email(s) from database.");
    }

    public function render()
    {
        // MAIN list
        $main = DomainUnsubscribe::query()
            ->when($this->type !== '', function ($qb) {
                $qb->where('type', $this->type);
            })
            ->when(trim($this->search) !== '', function ($qb) {
                $s = mb_strtolower(trim($this->search));
                $qb->where('value', 'like', '%' . $s . '%');
            });

        $totalMatched = (clone $main)->count();
        $items = $main->latest('id')->paginate(15);

        // Bottom: Email domain search results
        $emailQ = $this->emailDomainQuery();

        // count of unique domains matched
        $emailMatched = (clone $emailQ)->pluck('domain')->count();

        $emailDomains = $emailQ
            ->orderBy('domain')
            ->paginate(50);

        return view('livewire.email-manager.suppression.domain-list', [
            'items' => $items,
            'totalMatched' => $totalMatched,
            'emailDomains' => $emailDomains,
            'emailMatched' => $emailMatched,
        ])->layout('layouts.app');
    }
}