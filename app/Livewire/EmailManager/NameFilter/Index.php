<?php

namespace App\Livewire\EmailManager\NameFilter;

use App\Models\Category;
use App\Models\DomainUnsubscribe;
use App\Models\EmailAddress;
use App\Models\SuppressionEntry;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class Index extends Component
{
    use WithFileUploads;

    // ===== UI inputs =====
    public string $inputMode = 'textarea'; // textarea|file
    public string $textarea = '';
    public $uploadFile = null; // csv/txt

    // ===== Filter result texts (shown in UI) =====
    public string $matchedText = ''; // not shown in UI
    public string $okText = '';

    // ===== Filter progress =====
    public ?string $filterId = null;
    public bool $filterRunning = false;
    public bool $filterDone = false;

    public int $filterTotal = 0;
    public int $filterProcessed = 0;
    public int $filterInvalid = 0;
    public int $filterDuplicatesInput = 0;

    public int $filterGlobalSuppressed = 0;
    public int $filterDomainUnsubscribed = 0;

    public int $filterMatched = 0; // ONLY (global suppressed + domain unsub)
    public int $filterOk = 0;      // allowed

    // ===== Upload right-side (eligible) =====
    public int $category_id = 0;

    public ?string $uploadId = null;
    public bool $uploadRunning = false;
    public bool $uploadDone = false;

    public int $uploadTotal = 0;
    public int $uploadProcessed = 0;
    public int $uploadInserted = 0;

    public int $uploadDuplicatesDb = 0;
    public int $uploadDuplicatesCategory = 0;

    public int $uploadInvalid = 0;

    // chunk sizes
    public int $filterChunkSize = 200;
    public int $uploadChunkSize = 150;

    private int $ttlSeconds = 3600;

    public function render()
    {
        return view('livewire.email-manager.name-filter.index', [
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
        ])->layout('layouts.app');
    }

    /* ============================================================
     |  FILTER: Start
     * ============================================================ */
    public function startFilter(): void
    {
        $this->resetFilterUi();
        $this->resetUploadUi();

        $this->validate([
            'inputMode' => 'required|in:textarea,file',
        ]);

        if ($this->inputMode === 'textarea') {
            $this->validate(['textarea' => 'required|string']);
            $rows = $this->parseTextarea($this->textarea);
        } else {
            $this->validate(['uploadFile' => 'required|file|mimes:csv,txt|max:5120']);
            $rows = $this->parseFileUpload();
        }

        // Normalize + dedupe
        $normalized = [];
        foreach ($rows as $raw) {
            $raw = trim((string) $raw);
            if ($raw === '') continue;
            $normalized[] = mb_strtolower($raw);
        }

        if (count($normalized) === 0) {
            $this->dispatch('toast', type: 'warning', message: 'No emails found.', timeout: 5000);
            return;
        }

        $seen = [];
        $deduped = [];
        $dupCount = 0;

        foreach ($normalized as $email) {
            if (isset($seen[$email])) {
                $dupCount++;
                continue;
            }
            $seen[$email] = true;
            $deduped[] = $email;
        }

        $id = (string) Str::uuid();
        $this->filterId = $id;

        Storage::disk('local')->makeDirectory($this->filterDir($id));

        Storage::disk('local')->put($this->filterInputPath($id), implode("\n", $deduped) . "\n");
        Storage::disk('local')->put($this->filterMatchedPath($id), '');
        Storage::disk('local')->put($this->filterOkPath($id), '');

        $state = [
            'status' => 'processing',
            'cursor' => 0,
            'total' => count($deduped),
            'processed' => 0,
            'invalid' => 0,
            'dup_input' => $dupCount,

            // Only these matter for matching
            'global_suppressed' => 0,
            'domain_unsubscribed' => 0,

            'matched' => 0,
            'ok' => 0,
        ];

        Cache::put($this->filterCacheKey($id), $state, $this->ttlSeconds);

        $this->filterRunning = true;
        $this->filterDone = false;

        $this->dispatch('toast', type: 'success', message: 'Filtering started...', timeout: 3000);
    }

    /* ============================================================
     |  FILTER: Chunk worker
     * ============================================================ */
    public function processFilterChunk(): void
    {
        if (!$this->filterId) return;

        $id = $this->filterId;

        $lock = Cache::lock("namefilter:lock:$id", 15);
        if (!$lock->get()) return;

        try {
            $p = Cache::get($this->filterCacheKey($id));
            if (!is_array($p) || ($p['status'] ?? '') !== 'processing') return;

            $filePath = $this->filterInputPath($id);
            if (!Storage::exists($filePath)) {
                $p['status'] = 'error';
                Cache::put($this->filterCacheKey($id), $p, $this->ttlSeconds);
                return;
            }

            $absolute = Storage::path($filePath);
            $cursor = (int) ($p['cursor'] ?? 0);

            $file = new \SplFileObject($absolute, 'r');
            $file->setFlags(\SplFileObject::DROP_NEW_LINE);
            $file->seek($cursor);

            $chunk = [];
            $read = 0;

            while (!$file->eof() && $read < max(50, $this->filterChunkSize)) {
                $line = trim((string) $file->current());
                $file->next();
                $cursor++;
                $read++;

                if ($line !== '') $chunk[] = $line;
            }

            if (count($chunk) === 0) {
                $p['status'] = 'done';
                $p['cursor'] = $cursor;
                Cache::put($this->filterCacheKey($id), $p, $this->ttlSeconds);

                $this->loadFilterOutputsToUi($id);

                $this->dispatch(
                    'toast',
                    type: 'success',
                    timeout: 6000,
                    message: "Filter done. OK {$p['ok']}, Matched {$p['matched']} (Global {$p['global_suppressed']}, Unsub {$p['domain_unsubscribed']}), Invalid {$p['invalid']}."
                );

                return;
            }

            // Build local parts + domains (for domain unsubscribe checks)
            $domains = [];
            $locals = [];
            foreach ($chunk as $e) {
                $parts = explode('@', $e, 2);
                if (count($parts) === 2) {
                    $lp = trim(mb_strtolower($parts[0]));
                    $dm = trim(mb_strtolower($parts[1]));
                    if ($lp !== '') $locals[] = $lp;
                    if ($dm !== '') $domains[] = $dm;
                }
            }
            $domains = array_values(array_unique(array_filter($domains)));
            $locals  = array_values(array_unique(array_filter($locals)));

            /**
             * ✅ Global suppression check requires email_address_id.
             * We lookup existing EmailAddress IDs ONLY for that purpose.
             * BUT: we do NOT block/count just because email exists in DB.
             */
            $existingRows = EmailAddress::query()
                ->whereIn('email', $chunk)
                ->get(['id', 'email']);

            $existingIdByEmail = [];
            foreach ($existingRows as $row) {
                $existingIdByEmail[$row->email] = $row->id;
            }

            $suppressedEmailMap = [];
            if (!empty($existingIdByEmail)) {
                $suppressedIds = SuppressionEntry::query()
                    ->where('scope', 'global')
                    ->whereIn('email_address_id', array_values($existingIdByEmail))
                    ->pluck('email_address_id')
                    ->all();

                $suppressedIdSet = array_fill_keys($suppressedIds, true);

                foreach ($existingIdByEmail as $email => $eid) {
                    if (isset($suppressedIdSet[$eid])) {
                        $suppressedEmailMap[$email] = true;
                    }
                }
            }

            // Domain Unsubscribes
            $blockedUsers = [];
            if (!empty($locals)) {
                $blocked = DomainUnsubscribe::query()
                    ->where('type', 'user')
                    ->whereIn('value', $locals)
                    ->pluck('value')
                    ->all();
                $blockedUsers = array_fill_keys($blocked, true);
            }

            $blockedExactDomains = [];
            if (!empty($domains)) {
                $exact = DomainUnsubscribe::query()
                    ->where('type', 'domain')
                    ->whereIn('value', $domains)
                    ->pluck('value')
                    ->all();
                $blockedExactDomains = array_fill_keys($exact, true);
            }

            $blockedExtensions = DomainUnsubscribe::query()
                ->where('type', 'extension')
                ->pluck('value')
                ->all();

            $blockedExtensions = array_values(array_unique(array_map(function ($v) {
                $v = mb_strtolower(trim((string) $v));
                if ($v === '') return '';
                if ($v[0] !== '.') $v = '.' . ltrim($v, '.');
                return $v;
            }, $blockedExtensions)));

            // Process emails
            $matchedLines = [];
            $okLines = [];

            foreach ($chunk as $email) {
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $p['invalid']++;
                    continue;
                }

                $parts = explode('@', $email, 2);
                $local = mb_strtolower(trim($parts[0] ?? ''));
                $domain = mb_strtolower(trim($parts[1] ?? ''));

                $isGlobalSuppressed = isset($suppressedEmailMap[$email]);

                $isUserUnsub = ($local !== '' && isset($blockedUsers[$local]));
                $isDomainUnsub = $this->isDomainUnsubscribed($domain, $blockedExactDomains, $blockedExtensions);

                if ($isGlobalSuppressed) $p['global_suppressed']++;
                if ($isUserUnsub || $isDomainUnsub) $p['domain_unsubscribed']++;

                // ✅ ONLY these rules decide blocked
                $blocked = ($isGlobalSuppressed || $isUserUnsub || $isDomainUnsub);

                if ($blocked) {
                    $p['matched']++;
                    $matchedLines[] = $email;
                } else {
                    $p['ok']++;
                    $okLines[] = $email;
                }

                $p['processed']++;
            }

            if (!empty($matchedLines)) {
                Storage::append($this->filterMatchedPath($id), implode("\n", $matchedLines));
            }
            if (!empty($okLines)) {
                Storage::append($this->filterOkPath($id), implode("\n", $okLines));
            }

            $p['cursor'] = $cursor;

            Cache::put($this->filterCacheKey($id), $p, $this->ttlSeconds);

            $this->syncFilterProgressFromCache($p);

        } finally {
            optional($lock)->release();
        }
    }

    /* ============================================================
     |  RIGHT SIDE: Clear eligible textarea
     * ============================================================ */
    public function clearEligible(): void
    {
        $this->okText = '';
        $this->resetUploadUi();
        $this->dispatch('toast', type: 'success', message: 'Eligible emails cleared.', timeout: 2500);
    }

    /* ============================================================
     |  UPLOAD: Start (eligible emails into category)
     * ============================================================ */
    public function startUploadEligible(): void
    {
        if (!$this->filterDone) {
            $this->dispatch('toast', type: 'error', message: 'Please finish filtering first.', timeout: 4000);
            return;
        }

        $this->validate([
            'category_id' => 'required|integer|exists:categories,id',
        ]);

        $emails = $this->parseTextarea($this->okText);
        if (count($emails) === 0) {
            $this->dispatch('toast', type: 'warning', message: 'No eligible emails to upload.', timeout: 4000);
            return;
        }

        // normalize + dedupe again (right side)
        $normalized = [];
        foreach ($emails as $e) {
            $e = trim(mb_strtolower((string) $e));
            if ($e !== '') $normalized[] = $e;
        }

        $seen = [];
        $deduped = [];
        foreach ($normalized as $e) {
            if (isset($seen[$e])) continue;
            $seen[$e] = true;
            $deduped[] = $e;
        }

        $this->resetUploadUi();

        $id = (string) Str::uuid();
        $this->uploadId = $id;

        Storage::disk('local')->makeDirectory($this->uploadDir($id));
        Storage::disk('local')->put($this->uploadInputPath($id), implode("\n", $deduped) . "\n");

        $state = [
            'status' => 'processing',
            'cursor' => 0,
            'category_id' => (int) $this->category_id,
            'total' => count($deduped),
            'processed' => 0,
            'inserted' => 0,
            'dup_db' => 0,
            'dup_category' => 0,
            'invalid' => 0,
        ];

        Cache::put($this->uploadCacheKey($id), $state, $this->ttlSeconds);

        $this->uploadRunning = true;
        $this->uploadDone = false;

        $this->dispatch('toast', type: 'success', message: 'Upload started...', timeout: 3000);
    }

    public function processUploadChunk(): void
    {
        if (!$this->uploadId) return;

        $id = $this->uploadId;

        $lock = Cache::lock("namefilter:uploadlock:$id", 15);
        if (!$lock->get()) return;

        try {
            $p = Cache::get($this->uploadCacheKey($id));
            if (!is_array($p) || ($p['status'] ?? '') !== 'processing') return;

            $categoryId = (int) ($p['category_id'] ?? 0);
            if ($categoryId <= 0) return;

            $filePath = $this->uploadInputPath($id);
            if (!Storage::exists($filePath)) return;

            $absolute = Storage::path($filePath);
            $cursor = (int) ($p['cursor'] ?? 0);

            $file = new \SplFileObject($absolute, 'r');
            $file->setFlags(\SplFileObject::DROP_NEW_LINE);
            $file->seek($cursor);

            $emails = [];
            $read = 0;

            while (!$file->eof() && $read < max(50, $this->uploadChunkSize)) {
                $line = trim((string) $file->current());
                $file->next();
                $cursor++;
                $read++;

                if ($line !== '') $emails[] = $line;
            }

            if (count($emails) === 0) {
                $p['status'] = 'done';
                $p['cursor'] = $cursor;
                Cache::put($this->uploadCacheKey($id), $p, $this->ttlSeconds);

                $this->syncUploadProgressFromCache($p);

                $this->dispatch(
                    'toast',
                    type: 'success',
                    timeout: 6000,
                    message: "Upload completed. Inserted {$p['inserted']}, dup DB {$p['dup_db']}, dup Category {$p['dup_category']}, invalid {$p['invalid']}."
                );

                return;
            }

            $existingDb = EmailAddress::query()
                ->whereIn('email', $emails)
                ->pluck('id', 'email')
                ->all(); // [email => id]

            DB::transaction(function () use (&$p, $emails, $categoryId, $existingDb) {
                foreach ($emails as $email) {
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $p['invalid']++;
                        $p['processed']++;
                        continue;
                    }

                    $emailId = $existingDb[$email] ?? null;

                    if ($emailId) {
                        $p['dup_db']++;

                        $existsInCategory = DB::table('category_email')
                            ->where('category_id', $categoryId)
                            ->where('email_address_id', $emailId)
                            ->exists();

                        if ($existsInCategory) {
                            $p['dup_category']++;
                            $p['processed']++;
                            continue;
                        }

                        DB::table('category_email')->insert([
                            'category_id' => $categoryId,
                            'email_address_id' => $emailId,
                            'times_added' => 1,
                            'import_batch_id' => null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        $p['inserted']++;
                        $p['processed']++;
                        continue;
                    }

                    [$local, $domain] = array_pad(explode('@', $email, 2), 2, '');
                    $local = mb_strtolower(trim($local));
                    $domain = mb_strtolower(trim($domain));

                    $emailAddress = EmailAddress::create([
                        'email' => $email,
                        'local_part' => $local,
                        'domain' => $domain,
                        'is_valid' => true,
                        'invalid_reason' => null,
                    ]);

                    DB::table('category_email')->insert([
                        'category_id' => $categoryId,
                        'email_address_id' => $emailAddress->id,
                        'times_added' => 1,
                        'import_batch_id' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $p['inserted']++;
                    $p['processed']++;
                }
            });

            $p['cursor'] = $cursor;
            Cache::put($this->uploadCacheKey($id), $p, $this->ttlSeconds);

            $this->syncUploadProgressFromCache($p);

        } finally {
            optional($lock)->release();
        }
    }

    /* ============================================================
     |  Helpers
     * ============================================================ */

    private function parseTextarea(string $text): array
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = str_replace([";", ","], "\n", $text);
        $parts = array_map('trim', explode("\n", $text));
        return array_values(array_filter($parts, fn($v) => $v !== ''));
    }

    private function parseFileUpload(): array
    {
        $rows = [];
        if (!$this->uploadFile) return $rows;

        $path = $this->uploadFile->getRealPath();
        if (!$path) return $rows;

        $handle = fopen($path, 'r');
        if (!$handle) return $rows;

        while (($data = fgetcsv($handle)) !== false) {
            foreach ($data as $cell) {
                $cell = trim((string) $cell);
                if ($cell !== '') $rows[] = $cell;
            }
        }

        fclose($handle);
        return $rows;
    }

    private function isDomainUnsubscribed(string $domain, array $blockedExactDomains, array $blockedExtensions): bool
    {
        $d = mb_strtolower(trim($domain));
        $d = ltrim($d, '@');

        if ($d === '') return false;

        if (isset($blockedExactDomains[$d])) return true;

        foreach ($blockedExtensions as $ext) {
            if ($ext === '') continue;
            if (str_ends_with($d, $ext)) return true;
        }

        return false;
    }

    private function resetFilterUi(): void
    {
        $this->matchedText = '';
        $this->okText = '';

        $this->filterRunning = false;
        $this->filterDone = false;

        $this->filterTotal = 0;
        $this->filterProcessed = 0;
        $this->filterInvalid = 0;
        $this->filterDuplicatesInput = 0;

        $this->filterGlobalSuppressed = 0;
        $this->filterDomainUnsubscribed = 0;

        $this->filterMatched = 0;
        $this->filterOk = 0;
    }

    private function resetUploadUi(): void
    {
        $this->uploadId = null;
        $this->uploadRunning = false;
        $this->uploadDone = false;

        $this->uploadTotal = 0;
        $this->uploadProcessed = 0;
        $this->uploadInserted = 0;

        $this->uploadDuplicatesDb = 0;
        $this->uploadDuplicatesCategory = 0;

        $this->uploadInvalid = 0;
    }

    private function syncFilterProgressFromCache(array $p): void
    {
        $this->filterTotal = (int)($p['total'] ?? 0);
        $this->filterProcessed = (int)($p['processed'] ?? 0);
        $this->filterInvalid = (int)($p['invalid'] ?? 0);
        $this->filterDuplicatesInput = (int)($p['dup_input'] ?? 0);

        $this->filterGlobalSuppressed = (int)($p['global_suppressed'] ?? 0);
        $this->filterDomainUnsubscribed = (int)($p['domain_unsubscribed'] ?? 0);

        $this->filterMatched = (int)($p['matched'] ?? 0);
        $this->filterOk = (int)($p['ok'] ?? 0);

        if (($p['status'] ?? '') === 'done') {
            $this->filterRunning = false;
            $this->filterDone = true;
        }
    }

    private function syncUploadProgressFromCache(array $p): void
    {
        $this->uploadTotal = (int)($p['total'] ?? 0);
        $this->uploadProcessed = (int)($p['processed'] ?? 0);
        $this->uploadInserted = (int)($p['inserted'] ?? 0);

        $this->uploadDuplicatesDb = (int)($p['dup_db'] ?? 0);
        $this->uploadDuplicatesCategory = (int)($p['dup_category'] ?? 0);

        $this->uploadInvalid = (int)($p['invalid'] ?? 0);

        if (($p['status'] ?? '') === 'done') {
            $this->uploadRunning = false;
            $this->uploadDone = true;
        }
    }

    private function loadFilterOutputsToUi(string $id): void
    {
        $p = Cache::get($this->filterCacheKey($id));
        if (is_array($p)) $this->syncFilterProgressFromCache($p);

        $this->filterRunning = false;
        $this->filterDone = true;

        $this->matchedText = ''; // not shown
        $this->okText = trim((string) Storage::get($this->filterOkPath($id)));
    }

    private function filterDir(string $id): string
    {
        return "tmp_name_filter/$id";
    }

    private function filterInputPath(string $id): string
    {
        return $this->filterDir($id) . "/input.txt";
    }

    private function filterMatchedPath(string $id): string
    {
        return $this->filterDir($id) . "/matched.txt";
    }

    private function filterOkPath(string $id): string
    {
        return $this->filterDir($id) . "/ok.txt";
    }

    private function filterCacheKey(string $id): string
    {
        return "namefilter:progress:$id";
    }

    private function uploadDir(string $id): string
    {
        return "tmp_name_filter_upload/$id";
    }

    private function uploadInputPath(string $id): string
    {
        return $this->uploadDir($id) . "/input.txt";
    }

    private function uploadCacheKey(string $id): string
    {
        return "namefilter:uploadprogress:$id";
    }
}