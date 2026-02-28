<?php

namespace App\Livewire\EmailManager\Suppression;

use App\Models\EmailAddress;
use App\Models\SuppressionEntry;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class GlobalList extends Component
{
    use WithPagination;
    use WithFileUploads;

    /* ============================================================
     |  Input Modes (Textarea / File)
     * ============================================================ */

    public string $inputMode = 'textarea'; // textarea|file

    // textarea input: newline/comma/semicolon separated
    public string $emails = '';
    public ?string $reason = null;

    // file input (txt/csv)
    public $uploadFile = null;

    // Result summary (kept for backward compatibility)
    public array $result = [
        'total' => 0,
        'added' => 0,
        'already' => 0,
        'invalid' => 0,
    ];

    // Optional preview (max 50)
    public array $invalidPreview = [];

    /* ============================================================
     |  Chunked Bulk Add (Global Suppressions)
     * ============================================================ */

    public ?string $bulkUploadId = null;

    public bool $bulkIsRunning = false;
    public bool $bulkIsDone = false;

    public int $bulkTotal = 0;
    public int $bulkProcessed = 0;
    public int $bulkAdded = 0;
    public int $bulkAlready = 0;
    public int $bulkInvalid = 0;

    public int $bulkChunkSize = 500;

    /** failures preview for UI (max 50) */
    public array $bulkFailurePreview = []; // [['email' => '...', 'reason' => '...'], ...]

    /* ============================================================
     |  Helpers
     * ============================================================ */

    private function parseEmails(string $text): array
    {
        $text = str_replace(["\r\n", "\r"], "\n", (string) $text);
        $chunks = preg_split('/[\n,;]+/', $text) ?: [];

        return array_values(array_filter(array_map('trim', $chunks), fn($v) => $v !== ''));
    }

    private function normalizeEmail(string $raw): string
    {
        $e = mb_strtolower(trim((string) $raw));
        $e = preg_replace('/\s+/', '', $e) ?? $e;
        return $e;
    }

    private function bulkDir(): string
    {
        return 'tmp_uploads/global_suppressions';
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
        return "gs_bulk:{$uploadId}";
    }

    private function bulkLockKey(string $uploadId): string
    {
        return "gs_bulk_lock:{$uploadId}";
    }

    private function bulkInitFailureFile(string $uploadId): void
    {
        Storage::disk('local')->makeDirectory($this->bulkDir());

        $path = $this->bulkFailurePath($uploadId);

        if (!Storage::disk('local')->exists($path)) {
            Storage::disk('local')->put($path, "email,reason\n");
        }
    }

   private function bulkAppendFailure(string $uploadId, string $email, string $reason, array &$state): void
{
    $path = $this->bulkFailurePath($uploadId);
    Storage::disk('local')->append($path, $this->csvLine([$email, $reason]));

    // keep preview in the SAME state being saved at end of chunk
    $preview = (array) ($state['failure_preview'] ?? []);
    if (count($preview) < 50) {
        $preview[] = ['email' => $email, 'reason' => $reason];
    }
    $state['failure_preview'] = $preview;
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
        $this->bulkAlready = (int) ($state['already'] ?? 0);
        $this->bulkInvalid = (int) ($state['invalid'] ?? 0);
        $this->bulkIsDone = (bool) ($state['done'] ?? false);
        $this->bulkFailurePreview = (array) ($state['failure_preview'] ?? []);

        // keep old result array in-sync too
        $this->result = [
            'total' => $this->bulkTotal,
            'added' => $this->bulkAdded,
            'already' => $this->bulkAlready,
            'invalid' => $this->bulkInvalid,
        ];

        // map preview into old invalidPreview shape
        $this->invalidPreview = array_map(fn($x) => [
            'email' => $x['email'] ?? '',
            'reason' => $x['reason'] ?? '',
        ], $this->bulkFailurePreview);
    }

    /* ============================================================
     |  Start Bulk From Textarea (existing)
     * ============================================================ */

    public function startBulkAdd(): void
    {
        // reset
        $this->result = ['total' => 0, 'added' => 0, 'already' => 0, 'invalid' => 0];
        $this->invalidPreview = [];
        $this->bulkFailurePreview = [];

        $this->validate([
            'emails' => 'required|string',
            'reason' => 'nullable|string|max:255',
        ]);

        $rows = $this->parseEmails($this->emails);
        if (count($rows) === 0) {
            $this->dispatch('toast', type: 'error', message: 'No items found.');
            return;
        }

        // normalize + de-dupe (within submission)
        $normalized = [];
        foreach ($rows as $raw) {
            $e = $this->normalizeEmail($raw);
            if ($e !== '') {
                $normalized[] = $e;
            }
        }
        $normalized = array_values(array_unique($normalized));

        if (count($normalized) === 0) {
            $this->dispatch('toast', type: 'error', message: 'No valid items found.');
            return;
        }

        $uploadId = (string) Str::uuid();

        Storage::disk('local')->makeDirectory($this->bulkDir());
        Storage::disk('local')->put($this->bulkInputPath($uploadId), implode("\n", $normalized) . "\n");

        $this->bulkInitFailureFile($uploadId);

        Cache::put($this->bulkCacheKey($uploadId), [
            'upload_id' => $uploadId,
            'reason' => $this->reason,
            'total' => count($normalized),
            'processed' => 0,
            'added' => 0,
            'already' => 0,
            'invalid' => 0,
            'offset' => 0,
            'done' => false,
            'failure_preview' => [],
        ], now()->addHours(6));

        $this->bulkUploadId = $uploadId;
        $this->bulkIsRunning = true;
        $this->bulkIsDone = false;

        // clear textarea immediately
        $this->emails = '';

        $this->bulkSyncFromCache($uploadId);
        $this->dispatch('toast', type: 'success', message: 'Bulk processing started (chunk-wise).');
    }

    /* ============================================================
     |  Start Bulk From File (NEW) - feeds SAME chunk engine
     * ============================================================ */

    public function startBulkAddFromFile(): void
    {
        // reset
        $this->result = ['total' => 0, 'added' => 0, 'already' => 0, 'invalid' => 0];
        $this->invalidPreview = [];
        $this->bulkFailurePreview = [];

        $this->validate([
            'uploadFile' => 'required|file|max:20480|mimes:txt,csv', // 20MB
            'reason' => 'nullable|string|max:255',
        ]);

        $uploadId = (string) Str::uuid();

        Storage::disk('local')->makeDirectory($this->bulkDir());
        $out = [];

        // Stream uploaded file safely
        $realPath = method_exists($this->uploadFile, 'getRealPath')
            ? $this->uploadFile->getRealPath()
            : $this->uploadFile->path();

        $name = mb_strtolower($this->uploadFile->getClientOriginalName() ?? '');
        $isCsv = str_ends_with($name, '.csv');

        $file = new \SplFileObject($realPath, 'r');
        $file->setFlags(\SplFileObject::DROP_NEW_LINE);

        $seen = [];
        while (!$file->eof()) {
            $line = trim((string) $file->fgets());
            if ($line === '') {
                continue;
            }

            // If CSV, take first column from each row
            if ($isCsv) {
                $cols = str_getcsv($line);
                $line = trim((string) ($cols[0] ?? ''));
                if ($line === '') {
                    continue;
                }
            }

            // Allow comma/semicolon separated values per line too
            foreach ($this->parseEmails($line) as $raw) {
                $email = $this->normalizeEmail($raw);
                if ($email === '') {
                    continue;
                }
                if (isset($seen[$email])) {
                    continue;
                }
                $seen[$email] = true;
                $out[] = $email;
            }
        }

        if (count($out) === 0) {
            $this->dispatch('toast', type: 'error', message: 'No valid items found in file.');
            return;
        }

        // Write normalized list to SAME temp file format used by textarea flow
        Storage::disk('local')->put($this->bulkInputPath($uploadId), implode("\n", $out) . "\n");

        // init failures csv
        $this->bulkInitFailureFile($uploadId);

        // init cache state
        Cache::put($this->bulkCacheKey($uploadId), [
            'upload_id' => $uploadId,
            'reason' => $this->reason,
            'total' => count($out),
            'processed' => 0,
            'added' => 0,
            'already' => 0,
            'invalid' => 0,
            'offset' => 0,
            'done' => false,
            'failure_preview' => [],
        ], now()->addHours(6));

        $this->bulkUploadId = $uploadId;
        $this->bulkIsRunning = true;
        $this->bulkIsDone = false;

        // clear inputs
        $this->emails = '';
        $this->uploadFile = null;

        $this->bulkSyncFromCache($uploadId);
        $this->dispatch('toast', type: 'success', message: 'Bulk processing started (file → chunk-wise).');
    }

    /* ============================================================
     |  Chunk Processor (unchanged engine)
     * ============================================================ */

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
            $this->bulkIsRunning = false;
            $this->bulkIsDone = false;
            return;
        }

        if (($state['done'] ?? false) === true) {
            $this->bulkIsRunning = false;
            $this->bulkIsDone = true;
            $this->bulkSyncFromCache($uploadId);
            return;
        }

        $lock = Cache::lock($this->bulkLockKey($uploadId), 20);
        if (!$lock->get()) {
            return;
        }

        try {
            $offset = (int) ($state['offset'] ?? 0);
            $total = (int) ($state['total'] ?? 0);
            $reason = $state['reason'] ?? $this->reason;

            $inputPath = $this->bulkInputPath($uploadId);
            if (!Storage::disk('local')->exists($inputPath)) {
                $this->dispatch('toast', type: 'error', message: 'Bulk input file missing.');

                $state['done'] = true;
                Cache::put($cacheKey, $state, now()->addHours(6));

                $this->bulkIsRunning = false;
                $this->bulkIsDone = true;

                return;
            }

            $inputFull = Storage::disk('local')->path($inputPath);

            $file = new \SplFileObject($inputFull, 'r');
            $file->setFlags(\SplFileObject::DROP_NEW_LINE);
            $file->seek($offset);

            $chunk = [];
            $read = 0;

            while (!$file->eof() && $read < $this->bulkChunkSize) {
                $line = $file->current();
                $file->next();

                $email = $this->normalizeEmail((string) $line);
                if ($email === '') {
                    $offset++;
                    continue;
                }

                $chunk[] = $email;
                $read++;
                $offset++;
            }

            if (empty($chunk)) {
                $state['offset'] = $offset;
                $state['done'] = true;
                Cache::put($cacheKey, $state, now()->addHours(6));

                $this->bulkIsRunning = false;
                $this->bulkIsDone = true;
                $this->bulkSyncFromCache($uploadId);

                $this->reset(['emails', 'reason']);
                $this->resetPage();

                $this->dispatch('toast', type: 'success', message: 'Bulk processing completed.');
                return;
            }

            $validEmails = [];
            $emailParts = []; // email => [local, domain]

            foreach ($chunk as $email) {
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $state['invalid'] = (int) ($state['invalid'] ?? 0) + 1;
                   $this->bulkAppendFailure($uploadId, $email, 'Invalid format', $state);
                    continue;
                }

                [$local, $domain] = explode('@', $email, 2);
                $local = trim((string) $local);
                $domain = trim((string) $domain);

                if ($local === '' || $domain === '') {
                    $state['invalid'] = (int) ($state['invalid'] ?? 0) + 1;
                    $this->bulkAppendFailure($uploadId, $email, 'Invalid parts');
                    continue;
                }

                $validEmails[] = $email;
                $emailParts[$email] = [$local, $domain];
            }

            $validEmails = array_values(array_unique($validEmails));

            $now = now();
            $userId = auth()->id();

            if (!empty($validEmails)) {
                DB::transaction(function () use ($validEmails, $emailParts, $now, $userId, $reason, &$state) {
                    $emailRows = [];
                    foreach ($validEmails as $email) {
                        [$local, $domain] = $emailParts[$email];
                        $emailRows[] = [
                            'email' => $email,
                            'local_part' => $local,
                            'domain' => $domain,
                            'is_valid' => true,
                            'invalid_reason' => null,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }

                    EmailAddress::query()->upsert(
                        $emailRows,
                        ['email'],
                        ['updated_at', 'local_part', 'domain', 'is_valid', 'invalid_reason']
                    );

                    $idMap = EmailAddress::query()
                        ->whereIn('email', $validEmails)
                        ->pluck('id', 'email')
                        ->all();

                    $emailIds = array_values($idMap);

                    $existingIds = SuppressionEntry::query()
                        ->where('scope', 'global')
                        ->whereIn('email_address_id', $emailIds)
                        ->pluck('email_address_id')
                        ->all();

                    $existingSet = array_fill_keys($existingIds, true);

                    $newRows = [];
                    $added = 0;
                    $already = 0;

                    foreach ($emailIds as $eid) {
                        if (isset($existingSet[$eid])) {
                            $already++;
                            continue;
                        }

                        $newRows[] = [
                            'scope' => 'global',
                            'email_address_id' => $eid,
                            'reason' => $reason,
                            'user_id' => $userId,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                        $added++;
                    }

                    if (!empty($newRows)) {
                        SuppressionEntry::query()->upsert(
                            $newRows,
                            ['scope', 'email_address_id'],
                            ['updated_at', 'reason', 'user_id']
                        );
                    }

                    $state['added'] = (int) ($state['added'] ?? 0) + $added;
                    $state['already'] = (int) ($state['already'] ?? 0) + $already;
                });
            }

            $state['offset'] = $offset;
            $state['processed'] = (int) ($state['processed'] ?? 0) + count($chunk);

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

                $this->reset(['emails', 'reason']);
                $this->resetPage();

                $this->dispatch('toast', type: 'success', message: 'Bulk processing completed.');
            }
        } finally {
            optional($lock)->release();
        }
    }

    /* ============================================================
     |  Reset / Download Failures
     * ============================================================ */

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
        $this->bulkAlready = 0;
        $this->bulkInvalid = 0;

        $this->bulkFailurePreview = [];
        $this->invalidPreview = [];

        $this->result = ['total' => 0, 'added' => 0, 'already' => 0, 'invalid' => 0];

        $this->emails = '';
        $this->reason = null;
        $this->uploadFile = null;
        $this->inputMode = 'textarea';
    }

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
        return response()->download($full, "global_suppressions_failures_{$this->bulkUploadId}.csv");
    }

    /* ============================================================
     |  CRUD
     * ============================================================ */

    public function add(): void
    {
        $this->startBulkAdd();
    }

    public function remove(int $id): void
    {
        SuppressionEntry::where('id', $id)
            ->where('scope', 'global')
            ->delete();

        $this->resetPage();
    }

    public function render()
    {
        $items = SuppressionEntry::query()
            ->with('emailAddress')
            ->where('scope', 'global')
            ->latest('id')
            ->paginate(15);

        if ($this->bulkUploadId) {
            $this->bulkSyncFromCache($this->bulkUploadId);
        }

        return view('livewire.email-manager.suppression.global-list', [
            'items' => $items,
        ])->layout('layouts.app');
    }
}