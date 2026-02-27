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
     * - user: local-part match (pk_d / pk_d@ / pk.dutta@)
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
     * Search domains OR local-parts from email_addresses and allow bulk DELETE emails from DB.
     */
    public string $emailSearchMode = 'all'; // all|domain|extension|user
    public string $emailSearch = '';
    public array $emailSelected = []; // selected values (strings): domains OR local_parts

    /**
     * Delete confirm modal state for deleting emails
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
                $v = rtrim($v, '@');
            } elseif ($type === 'extension') {
                $v = rtrim($v, '@');
                $v = ltrim($v, '.');
                if ($v !== '') {
                    $v = '.' . $v;
                }
            } else { // user
                // allow "pk_d@" or "pk_d" or "@pk_d@"
                $v = rtrim($v, '@');

                // if someone pasted a full email, keep only local-part
                if (str_contains($v, '@')) {
                    $v = explode('@', $v, 2)[0];
                }

                $v = ltrim($v, '.');
            }

            if ($v === '') {
                continue;
            }

            $out[] = $v;
        }

        $out = array_values(array_unique($out));
        $out = array_values(array_filter($out, fn ($x) => mb_strlen($x) <= 255));

        return $out;
    }

    /**
     * Add multiple domains/extensions/users at once
     */
    public function addMultiple(): void
    {
        $this->validate([
            'domainsText' => 'required|string',
            'type' => 'required|in:domain,extension,user',
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

        $this->selected = array_values(array_filter($this->selected, fn ($x) => (int) $x !== (int) $id));
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
     * Query distinct domains OR local_parts from EmailAddress based on search mode
     * - domain/extension/all => returns rows with "domain"
     * - user => returns rows with "local_part"
     */
    private function emailDomainQuery()
    {
        $s = mb_strtolower(trim($this->emailSearch));

        // No search => return empty list
        if ($s === '') {
            return EmailAddress::query()->select('domain')->whereRaw('1=0');
        }

        // USER MODE: search local_part
        if ($this->emailSearchMode === 'user') {
            $qb = EmailAddress::query()
                ->select('local_part')
                ->whereNotNull('local_part');

            $qb->whereRaw('LOWER(local_part) LIKE ?', ['%' . $s . '%']);

            return $qb->groupBy('local_part');
        }

        // Domain modes
        $qb = EmailAddress::query()->select('domain')->whereNotNull('domain');

        // normalize input for extension
        $ext = $s;
        if ($ext !== '' && $ext[0] !== '.') {
            $ext = '.' . ltrim($ext, '.');
        }

        if ($this->emailSearchMode === 'domain') {
            $qb->whereRaw('LOWER(domain) LIKE ?', ['%' . $s . '%']);
        } elseif ($this->emailSearchMode === 'extension') {
            $qb->whereRaw('LOWER(domain) LIKE ?', ['%' . $ext]);
        } else { // all
            $qb->where(function ($q) use ($s, $ext) {
                $q->whereRaw('LOWER(domain) LIKE ?', ['%' . $s . '%'])
                    ->orWhereRaw('LOWER(domain) LIKE ?', ['%' . $ext]);
            });
        }

        return $qb->groupBy('domain');
    }

    /**
     * Select all matched domains/local-parts from emails
     */
    public function selectAllEmailMatches(): void
    {
        if ($this->emailSearchMode === 'user') {
            $locals = $this->emailDomainQuery()
                ->pluck('local_part')
                ->map(fn ($d) => mb_strtolower(trim((string) $d)))
                ->filter(fn ($d) => $d !== '')
                ->unique()
                ->values()
                ->all();

            $this->emailSelected = $locals;

            $this->dispatch('toast', type: 'success', message: 'All matched users selected.');
            return;
        }

        $domains = $this->emailDomainQuery()
            ->pluck('domain')
            ->map(fn ($d) => mb_strtolower(trim((string) $d)))
            ->filter(fn ($d) => $d !== '')
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
     * Open confirm modal to delete emails from database by selected domains OR local_parts
     */
    public function openDeleteEmailsModal(): void
    {
        if (empty($this->emailSelected)) {
            $this->dispatch('toast', type: 'error', message: 'Select items first.');
            return;
        }

        if ($this->emailSearchMode === 'user') {
            $locals = array_values(array_unique(array_map(function ($d) {
                $d = mb_strtolower(trim((string) $d));
                $d = ltrim($d, '@');
                $d = rtrim($d, '@');
                if (str_contains($d, '@')) {
                    $d = explode('@', $d, 2)[0];
                }
                return $d;
            }, $this->emailSelected)));

            $count = EmailAddress::query()
                ->whereIn('local_part', $locals)
                ->count();

            $this->confirmDeleteEmailCount = (int) $count;
            $this->showDeleteEmailsModal = true;
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
     * Delete emails from DB
     */
    public function deleteEmailsConfirmed(): void
    {
        if (!$this->showDeleteEmailsModal) {
            return;
        }

        if (empty($this->emailSelected)) {
            $this->dispatch('toast', type: 'error', message: 'No items selected.');
            $this->cancelDeleteEmails();
            return;
        }

        if ($this->emailSearchMode === 'user') {
            $locals = array_values(array_unique(array_map(function ($d) {
                $d = mb_strtolower(trim((string) $d));
                $d = ltrim($d, '@');
                $d = rtrim($d, '@');
                if (str_contains($d, '@')) {
                    $d = explode('@', $d, 2)[0];
                }
                return $d;
            }, $this->emailSelected)));

            $deleted = EmailAddress::query()
                ->whereIn('local_part', $locals)
                ->delete();

            $this->emailSelected = [];
            $this->cancelDeleteEmails();
            $this->resetPage();

            $this->dispatch('toast', type: 'success', message: "Deleted {$deleted} email(s) from database.");
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
                $qb->whereRaw('LOWER(value) LIKE ?', ['%' . $s . '%']);
            });

        $totalMatched = (clone $main)->count();
        $items = $main->latest('id')->paginate(15);

        // Bottom: Email domain/local_part search results
        $emailQ = $this->emailDomainQuery();

        // count of unique values matched (fast count)
        $emailMatched = (clone $emailQ)->count();

        $emailDomains = $emailQ
            ->orderBy($this->emailSearchMode === 'user' ? 'local_part' : 'domain')
            ->paginate(50);

        return view('livewire.email-manager.suppression.domain-list', [
            'items' => $items,
            'totalMatched' => $totalMatched,
            'emailDomains' => $emailDomains,
            'emailMatched' => $emailMatched,
        ])->layout('layouts.app');
    }
}