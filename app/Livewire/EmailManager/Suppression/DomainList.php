<?php

namespace App\Livewire\EmailManager\Suppression;

use App\Models\DomainUnsubscribe;
use App\Models\EmailAddress;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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

    /* ============================================================
     |  Chunked Bulk Add (for Domain Unsubscribes)
     * ============================================================ */

    public ?string $bulkUploadId = null;

    public bool $bulkIsRunning = false;
    public bool $bulkIsDone = false;

    public int $bulkTotal = 0;
    public int $bulkProcessed = 0;
    public int $bulkAdded = 0;
    public int $bulkUpdated = 0;
    public int $bulkInvalid = 0;

    /** small preview for UI */
    public array $bulkFailurePreview = []; // [['value' => '...', 'reason' => '...'], ...]

    /** chunk size */
    public int $bulkChunkSize = 500;

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
        $out = array_values(array_filter($out, fn($x) => mb_strlen($x) <= 255));

        return $out;
    }

    /**
     * Validate a single value based on type
     */
    private function validateValue(string $value, string $type): ?string
    {
        $v = trim(mb_strtolower($value));
        if ($v === '') {
            return 'Empty value';
        }
        if (mb_strlen($v) > 255) {
            return 'Too long (max 255)';
        }

        if ($type === 'domain') {
            if (str_contains($v, '@')) {
                return 'Domain must not contain @';
            }
            if (!str_contains($v, '.')) {
                return 'Invalid domain (missing dot)';
            }
            if (preg_match('/\s/', $v)) {
                return 'Invalid domain (contains whitespace)';
            }
            return null;
        }

        if ($type === 'extension') {
            if ($v[0] !== '.') {
                return 'Extension must start with "."';
            }
            if (mb_strlen($v) < 2) {
                return 'Invalid extension';
            }
            if (str_contains($v, '@')) {
                return 'Extension must not contain @';
            }
            return null;
        }

        // user: local-part only
        if (str_contains($v, '@')) {
            return 'User value must be local-part only (no "@domain")';
        }
        if ($v[0] === '.') {
            return 'User value must not start with "."';
        }

        return null;
    }

    private function bulkDir(): string
    {
        return 'tmp_uploads/domain_unsubscribes';
    }

    private function bulkInputPath(string $uploadId): string
    {
        return $this->bulkDir() . "/{$uploadId}.txt";
    }

    private function bulkFailurePath(string $uploadId): string
    {
        return $this->bulkDir() . "/{$uploadId}_failures.csv";
    }

    private function bulkCacheKey(string $uploadId): string
    {
        return "du_bulk:{$uploadId}";
    }

    private function bulkLockKey(string $uploadId): string
    {
        return "du_bulk_lock:{$uploadId}";
    }

    private function csvLine(array $cols): string
    {
        $escaped = array_map(function ($v) {
            $v = (string) $v;
            $v = str_replace('"', '""', $v);
            return '"' . $v . '"';
        }, $cols);

        return implode(',', $escaped);
    }

    private function bulkSyncFromCache(?string $uploadId = null): void
    {
        $uploadId = $uploadId ?? $this->bulkUploadId;
        if (!$uploadId) {
            return;
        }

        $state = Cache::get($this->bulkCacheKey($uploadId));
        if (!is_array($state)) {
            return;
        }

        $this->bulkTotal = (int) ($state['total'] ?? 0);
        $this->bulkProcessed = (int) ($state['processed'] ?? 0);
        $this->bulkAdded = (int) ($state['added'] ?? 0);
        $this->bulkUpdated = (int) ($state['updated'] ?? 0);
        $this->bulkInvalid = (int) ($state['invalid'] ?? 0);
        $this->bulkIsDone = (bool) ($state['done'] ?? false);
        $this->bulkFailurePreview = (array) ($state['failure_preview'] ?? []);
    }

    private function bulkInitFailureFile(string $uploadId): void
    {
        // ✅ ensure directory exists, use Storage consistently
        Storage::disk('local')->makeDirectory($this->bulkDir());

        $path = $this->bulkFailurePath($uploadId);

        if (!Storage::disk('local')->exists($path)) {
            Storage::disk('local')->put($path, "value,reason\n");
        }
    }

    private function bulkAppendFailure(string $uploadId, string $value, string $reason): void
    {
        $path = $this->bulkFailurePath($uploadId);

        // ✅ append safely via Storage
        Storage::disk('local')->append($path, $this->csvLine([$value, $reason]));

        // keep small preview in cache (max 50)
        $key = $this->bulkCacheKey($uploadId);
        $state = Cache::get($key, []);
        if (!is_array($state)) {
            $state = [];
        }

        $preview = (array) ($state['failure_preview'] ?? []);
        if (count($preview) < 50) {
            $preview[] = ['value' => $value, 'reason' => $reason];
        }

        $state['failure_preview'] = $preview;
        Cache::put($key, $state, now()->addHours(6));
    }

    /**
     * START chunked bulk add
     */
    public function startBulkAdd(): void
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

        $uploadId = (string) Str::uuid();
        $inputPath = $this->bulkInputPath($uploadId);

        // ✅ ensure directory exists
        Storage::disk('local')->makeDirectory($this->bulkDir());

        // write normalized values to file (one per line)
        Storage::disk('local')->put($inputPath, implode("\n", $values) . "\n");

        // init failure file
        $this->bulkInitFailureFile($uploadId);

        // init state in cache
        Cache::put($this->bulkCacheKey($uploadId), [
            'upload_id' => $uploadId,
            'type' => $this->type,
            'reason' => $this->reason,
            'total' => count($values),
            'processed' => 0,
            'added' => 0,
            'updated' => 0,
            'invalid' => 0,
            'offset' => 0,
            'done' => false,
            'failure_preview' => [],
        ], now()->addHours(6));

        // set livewire state
        $this->bulkUploadId = $uploadId;
        $this->bulkIsRunning = true;
        $this->bulkIsDone = false;

        // ✅ clear textarea immediately
        $this->domainsText = '';

        $this->bulkSyncFromCache($uploadId);

        $this->dispatch('toast', type: 'success', message: 'Bulk processing started (chunk-wise).');
    }

    /**
     * Process next chunk
     */
    public function processChunk(): void
    {
        if (!$this->bulkUploadId) {
            $this->dispatch('toast', type: 'error', message: 'No bulk session found.');
            return;
        }

        $uploadId = $this->bulkUploadId;
        $cacheKey = $this->bulkCacheKey($uploadId);

        $state = Cache::get($cacheKey);
        if (!is_array($state)) {
            $this->dispatch('toast', type: 'error', message: 'Bulk session expired.');

            // ✅ unlock UI
            $this->bulkIsRunning = false;
            $this->bulkIsDone = false;
            $this->bulkUploadId = null;

            return;
        }

        if (($state['done'] ?? false) === true) {
            $this->bulkIsRunning = false;
            $this->bulkIsDone = true;
            $this->bulkUploadId = null; // ✅ unlock UI
            $this->bulkSyncFromCache($uploadId);
            return;
        }

        // Avoid double-processing on fast clicks/polling
        $lock = Cache::lock($this->bulkLockKey($uploadId), 20);
        if (!$lock->get()) {
            return;
        }

        try {
            $type = (string) ($state['type'] ?? $this->type);
            $reason = $state['reason'] ?? $this->reason;
            $offset = (int) ($state['offset'] ?? 0);
            $total = (int) ($state['total'] ?? 0);

            // ✅ use Storage for existence + path
            $inputPath = $this->bulkInputPath($uploadId);
            if (!Storage::disk('local')->exists($inputPath)) {
                $this->dispatch('toast', type: 'error', message: 'Bulk input file missing.');

                $state['done'] = true;
                Cache::put($cacheKey, $state, now()->addHours(6));

                // ✅ unlock UI
                $this->bulkIsRunning = false;
                $this->bulkIsDone = true;
                $this->bulkUploadId = null;

                return;
            }

            $inputFull = Storage::disk('local')->path($inputPath);

            $file = new \SplFileObject($inputFull, 'r');
            $file->setFlags(\SplFileObject::DROP_NEW_LINE);
            $file->seek($offset);

            $chunkValues = [];
            $read = 0;

            while (!$file->eof() && $read < $this->bulkChunkSize) {
                $line = $file->current();
                $file->next();

                $v = mb_strtolower(trim((string) $line));
                if ($v === '') {
                    $offset++;
                    continue;
                }

                $chunkValues[] = $v;
                $read++;
                $offset++;
            }

            if (empty($chunkValues)) {
                // finished
                $state['offset'] = $offset;
                $state['processed'] = min($total, (int) ($state['processed'] ?? 0));
                $state['done'] = true;
                Cache::put($cacheKey, $state, now()->addHours(6));

                $this->bulkIsRunning = false;
                $this->bulkIsDone = true;
                $this->bulkSyncFromCache($uploadId);

                $this->reason = null;
                $this->selected = [];
                $this->resetPage();

                // ✅ unlock UI
                $this->bulkUploadId = null;

                $this->dispatch('toast', type: 'success', message: 'Bulk processing completed.');
                return;
            }

            // Validate + split valid/invalid
            $valid = [];
            foreach ($chunkValues as $v) {
                $err = $this->validateValue($v, $type);
                if ($err !== null) {
                    $state['invalid'] = (int) ($state['invalid'] ?? 0) + 1;
                    $this->bulkAppendFailure($uploadId, $v, $err);
                    continue;
                }
                $valid[] = $v;
            }

            $now = now();

            // Count existing to estimate added vs updated
            $existing = [];
            if (!empty($valid)) {
                $existing = DomainUnsubscribe::query()
                    ->where('type', $type)
                    ->whereIn('value', $valid)
                    ->pluck('value')
                    ->map(fn($x) => mb_strtolower((string) $x))
                    ->all();
            }

            $existingSet = array_fill_keys($existing, true);

            $rows = [];
            $added = 0;
            $updated = 0;

            foreach ($valid as $v) {
                if (isset($existingSet[$v])) {
                    $updated++;
                } else {
                    $added++;
                }

                $rows[] = [
                    'type' => $type,
                    'value' => $v,
                    'reason' => $reason,
                    'user_id' => auth()->id(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if (!empty($rows)) {
                DomainUnsubscribe::query()->upsert(
                    $rows,
                    ['type', 'value'],
                    ['updated_at', 'reason', 'user_id']
                );
            }

            $state['offset'] = $offset;
            $state['processed'] = (int) ($state['processed'] ?? 0) + count($chunkValues);
            $state['added'] = (int) ($state['added'] ?? 0) + $added;
            $state['updated'] = (int) ($state['updated'] ?? 0) + $updated;

            if ($total > 0 && $state['processed'] > $total) {
                $state['processed'] = $total;
            }
            if ($total > 0 && (int) $state['processed'] >= $total) {
                $state['done'] = true;
            }

            Cache::put($cacheKey, $state, now()->addHours(6));

            $this->bulkSyncFromCache($uploadId);

            if (($state['done'] ?? false) === true) {
                $this->bulkIsRunning = false;
                $this->bulkIsDone = true;

                $this->reason = null;
                $this->selected = [];
                $this->resetPage();

                // ✅ unlock UI
                $this->bulkUploadId = null;

                $this->dispatch('toast', type: 'success', message: 'Bulk processing completed.');
            }
        } finally {
            optional($lock)->release();
        }
    }

    /**
     * Cancel/Reset current bulk session (does not delete DB rows)
     */
    public function resetBulk(): void
    {
        if ($this->bulkUploadId) {
            Cache::forget($this->bulkCacheKey($this->bulkUploadId));
            Cache::forget($this->bulkLockKey($this->bulkUploadId));

            Storage::disk('local')->delete($this->bulkInputPath($this->bulkUploadId));
            Storage::disk('local')->delete($this->bulkFailurePath($this->bulkUploadId));
        }

        $this->bulkUploadId = null;
        $this->bulkIsRunning = false;
        $this->bulkIsDone = false;

        $this->bulkTotal = 0;
        $this->bulkProcessed = 0;
        $this->bulkAdded = 0;
        $this->bulkUpdated = 0;
        $this->bulkInvalid = 0;
        $this->bulkFailurePreview = [];
    }

    /**
     * Download failures CSV (invalid rows)
     */
    public function downloadBulkFailures()
    {
        if (!$this->bulkUploadId) {
            $this->dispatch('toast', type: 'error', message: 'No bulk session found.');
            return null;
        }

        $path = $this->bulkFailurePath($this->bulkUploadId);

        if (!Storage::disk('local')->exists($path)) {
            $this->dispatch('toast', type: 'error', message: 'Failure file not found.');
            return null;
        }

        $full = Storage::disk('local')->path($path);

        return response()->download($full, "domain_unsubscribes_failures_{$this->bulkUploadId}.csv");
    }

    /**
     * Old single-request addMultiple()
     */
    public function addMultiple(): void
    {
        $this->dispatch('toast', type: 'error', message: 'Please use the new chunked bulk add (Start).');
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
     * Query distinct domains OR local_parts from EmailAddress based on search mode
     * - domain/extension/all => returns rows with "domain"
     * - user => returns rows with "local_part"
     */
    private function emailDomainQuery()
    {
        $s = mb_strtolower(trim($this->emailSearch));

        if ($s === '') {
            return EmailAddress::query()->select('domain')->whereRaw('1=0');
        }

        if ($this->emailSearchMode === 'user') {
            $qb = EmailAddress::query()
                ->select('local_part')
                ->whereNotNull('local_part');

            $qb->whereRaw('LOWER(local_part) LIKE ?', ['%' . $s . '%']);

            return $qb->groupBy('local_part');
        }

        $qb = EmailAddress::query()->select('domain')->whereNotNull('domain');

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
                ->map(fn($d) => mb_strtolower(trim((string) $d)))
                ->filter(fn($d) => $d !== '')
                ->unique()
                ->values()
                ->all();

            $this->emailSelected = $locals;

            $this->dispatch('toast', type: 'success', message: 'All matched users selected.');
            return;
        }

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

        $emailQ = $this->emailDomainQuery();
        $emailMatched = (clone $emailQ)->count();

        $emailDomains = $emailQ
            ->orderBy($this->emailSearchMode === 'user' ? 'local_part' : 'domain')
            ->paginate(50);

        if ($this->bulkUploadId) {
            $this->bulkSyncFromCache($this->bulkUploadId);
        }

        return view('livewire.email-manager.suppression.domain-list', [
            'items' => $items,
            'totalMatched' => $totalMatched,
            'emailDomains' => $emailDomains,
            'emailMatched' => $emailMatched,
        ])->layout('layouts.app');
    }
}