<?php

namespace App\Livewire\EmailManager\Imports;

use App\Models\Category;
use App\Models\EmailAddress;
use App\Models\SuppressionEntry;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;

class Upload extends Component
{
    use WithFileUploads;

    public int $category_id = 0;

    public string $mode = 'textarea'; // textarea|csv
    public string $textarea = '';

    public $csv = null; // Livewire temp upload

    // ✅ Create category (modal form)
    public string $new_category_name = '';
    public string $new_category_slug = '';
    public ?string $new_category_notes = null;

    // ✅ Force select refresh after create
    public int $categoriesVersion = 0;

    // ✅ show summary on same page (mirrors progress cache)
    public array $result = [
        'total' => 0,
        'processed' => 0,
        'valid' => 0,
        'inserted' => 0,
        'duplicates' => 0,
        'suppressed' => 0,
        'invalid' => 0,
        'status' => 'idle', // idle|processing|done|error|cancelled
        'message' => null,
        'percent' => 0,
    ];

    public array $invalidPreview = []; // [{raw,email,reason}...]

    // Chunked upload tracking
    public ?string $upload_id = null;

    // Tune this for performance (200–1000). Safe default:
    public int $chunkSize = 500;

    // Cache TTL in seconds (2 hours)
    private int $ttlSeconds = 7200;

    private function progressKey(string $uploadId): string
    {
        return "email_upload_progress:{$uploadId}";
    }

    private function lockKey(string $uploadId): string
    {
        return "email_upload_lock:{$uploadId}";
    }

    private function tempPath(string $uploadId): string
    {
        return "tmp_uploads/{$uploadId}.txt";
    }

    /**
     * When user switches between textarea/csv, reset the other input
     * so validation and parsing never conflicts.
     */
    public function updatedMode(string $value): void
    {
        if ($value === 'textarea') {
            $this->csv = null;
        }

        if ($value === 'csv') {
            $this->textarea = '';
        }
    }

    /**
     * Reset create-category modal fields + errors.
     */
    public function resetCategoryForm(): void
    {
        $this->new_category_name = '';
        $this->new_category_slug = '';
        $this->new_category_notes = null;

        $this->resetValidation([
            'new_category_name',
            'new_category_slug',
            'new_category_notes',
        ]);
    }

    /**
     * ✅ Create category from modal, auto-select, refresh dropdown, close modal.
     */
    public function createCategory(): void
    {
        $this->new_category_name = $this->normalizeName($this->new_category_name);
        $this->new_category_slug = trim($this->new_category_slug);

        $this->validate([
            'new_category_name' => ['required', 'string', 'max:255'],
            'new_category_slug' => ['nullable', 'string', 'max:255'],
            'new_category_notes' => ['nullable', 'string'],
        ]);

        if ($this->categoryNameExists($this->new_category_name)) {
            throw ValidationException::withMessages([
                'new_category_name' => 'This category name already exists.',
            ]);
        }

        $slug = $this->new_category_slug !== ''
            ? Str::slug($this->new_category_slug)
            : Str::slug($this->new_category_name);

        $slug = $this->uniqueSlug($slug);

        $category = Category::create([
            'name' => $this->new_category_name,
            'slug' => $slug,
            'notes' => $this->new_category_notes,
        ]);

        // ✅ auto-select + refresh dropdown options
        $this->category_id = (int) $category->id;
        $this->categoriesVersion++;

        // clear modal fields
        $this->resetCategoryForm();

        // ✅ close modal (Flux listens to this)
        $this->dispatch('close-modal', name: 'create-category');

        $this->dispatch('toast', type: 'success', message: 'Category created and selected.', timeout: 5000);
    }

    /**
     * START upload: parse input, normalize, de-dupe, write to temp file,
     * initialize progress in cache. (No heavy DB processing here.)
     *
     * Then frontend will repeatedly call processChunk() until done.
     */
    public function submit(): void
    {
        // reset preview and result
        $this->invalidPreview = [];
        $this->result = [
            'total' => 0,
            'processed' => 0,
            'valid' => 0,
            'inserted' => 0,
            'duplicates' => 0,
            'suppressed' => 0,
            'invalid' => 0,
            'status' => 'idle',
            'message' => null,
            'percent' => 0,
        ];

        // Base validation
        $this->validate([
            'category_id' => 'required|integer|exists:categories,id',
            'mode' => 'required|in:textarea,csv',
        ]);

        // Conditional validation
        if ($this->mode === 'textarea') {
            $this->validate([
                'textarea' => 'required|string',
            ]);
        } else {
            $this->validate([
                'csv' => 'required|file|mimes:csv,txt|max:5120',
            ]);
        }

        $rows = $this->mode === 'textarea'
            ? $this->parseTextarea($this->textarea)
            : $this->parseCsvUpload();

        // Normalize + remove empty
        $normalized = [];
        foreach ($rows as $raw) {
            $raw = trim((string) $raw);
            if ($raw === '') {
                continue;
            }
            $email = mb_strtolower($raw);
            $normalized[] = $email;
        }

        // De-dupe inside same upload
        $normalized = array_values(array_unique($normalized));

        if (count($normalized) === 0) {
            $this->dispatch('toast', type: 'warning', message: 'No emails found to upload.', timeout: 5000);
            return;
        }

        // Create upload_id and save temp file
        $uploadId = (string) Str::uuid();
        $this->upload_id = $uploadId;

        // Ensure directory exists on local disk
        if (!Storage::exists('tmp_uploads')) {
            Storage::makeDirectory('tmp_uploads');
        }

        Storage::put($this->tempPath($uploadId), implode("\n", $normalized));

        // Initialize progress in cache
        $progress = [
            'status' => 'processing',
            'message' => null,
            'category_id' => (int) $this->category_id,
            'total' => count($normalized),
            'processed' => 0,
            'valid' => 0,
            'inserted' => 0,
            'duplicates' => 0,
            'suppressed' => 0,
            'invalid' => 0,
            'cursor' => 0, // line number cursor
            'invalidPreview' => [],
        ];

        Cache::put($this->progressKey($uploadId), $progress, $this->ttlSeconds);

        // clear inputs right away (so user doesn’t re-submit same payload)
        $this->textarea = '';
        $this->csv = null;

        $this->dispatch('toast', type: 'info', message: 'Upload started. Processing in chunks...', timeout: 4000);

        // Update local UI snapshot
        $this->refreshProgress();
    }

    /**
     * Refresh UI progress from cache.
     * Call with wire:poll.1s
     */
    public function refreshProgress(): void
    {
        if (!$this->upload_id) {
            return;
        }

        $p = Cache::get($this->progressKey($this->upload_id));
        if (!is_array($p)) {
            return;
        }

        // Keep invalid preview small for UI
        $this->invalidPreview = $p['invalidPreview'] ?? [];

        $total = (int) ($p['total'] ?? 0);
        $processed = (int) ($p['processed'] ?? 0);
        $percent = $total > 0 ? (int) floor(($processed / $total) * 100) : 0;

        $this->result = [
            'total' => $total,
            'processed' => $processed,
            'valid' => (int) ($p['valid'] ?? 0),
            'inserted' => (int) ($p['inserted'] ?? 0),
            'duplicates' => (int) ($p['duplicates'] ?? 0),
            'suppressed' => (int) ($p['suppressed'] ?? 0),
            'invalid' => (int) ($p['invalid'] ?? 0),
            'status' => (string) ($p['status'] ?? 'idle'),
            'message' => $p['message'] ?? null,
            'percent' => $percent,
        ];
    }

    /**
     * Process next chunk of emails.
     * Frontend should call this repeatedly until status = done/error/cancelled.
     */
    public function processChunk(): void
    {
        if (!$this->upload_id) {
            return;
        }

        $uploadId = $this->upload_id;

        $lock = Cache::lock($this->lockKey($uploadId), 15);
        if (!$lock->get()) {
            // Another request is processing; just return.
            return;
        }

        try {
            $p = Cache::get($this->progressKey($uploadId));
            if (!is_array($p)) {
                return;
            }

            if (($p['status'] ?? '') !== 'processing') {
                return;
            }

            $categoryId = (int) ($p['category_id'] ?? 0);
            if ($categoryId <= 0) {
                $p['status'] = 'error';
                $p['message'] = 'Missing category id.';
                Cache::put($this->progressKey($uploadId), $p, $this->ttlSeconds);
                return;
            }

            $filePath = $this->tempPath($uploadId);
            if (!Storage::exists($filePath)) {
                $p['status'] = 'error';
                $p['message'] = 'Upload temp file not found.';
                Cache::put($this->progressKey($uploadId), $p, $this->ttlSeconds);
                return;
            }

            $absolute = Storage::path($filePath);

            $cursor = (int) ($p['cursor'] ?? 0);
            $chunkSize = max(50, (int) $this->chunkSize);

            // Read next chunk lines using SplFileObject (low memory)
            $file = new \SplFileObject($absolute, 'r');
            $file->setFlags(\SplFileObject::DROP_NEW_LINE);

            $emails = [];
            $file->seek($cursor);

            $read = 0;
            while (!$file->eof() && $read < $chunkSize) {
                $line = (string) $file->current();
                $file->next();
                $cursor++;
                $read++;

                $email = trim($line);
                if ($email === '') {
                    continue;
                }
                $emails[] = $email;
            }

            if (count($emails) === 0) {
                // finished
                $p['status'] = 'done';
                $p['cursor'] = $cursor;
                Cache::put($this->progressKey($uploadId), $p, $this->ttlSeconds);
                Storage::delete($filePath);

                $this->dispatch('toast', type: 'success', timeout: 6000, message: "Upload completed. Inserted {$p['inserted']}, duplicates {$p['duplicates']}, invalid {$p['invalid']}.");
                $this->refreshProgress();
                return;
            }

            // Build domain suppression map for this chunk
            $domains = [];
            foreach ($emails as $e) {
                $parts = explode('@', $e, 2);
                if (count($parts) === 2 && $parts[1] !== '') {
                    $domains[] = trim($parts[1]);
                }
            }
            $domains = array_values(array_unique(array_filter($domains)));

            $suppressedDomains = [];
            if (!empty($domains)) {
                $suppressedDomains = SuppressionEntry::query()
                    ->where('scope', 'domain')
                    ->whereIn('domain', $domains)
                    ->pluck('domain')
                    ->all();
                $suppressedDomains = array_fill_keys($suppressedDomains, true);
            }

            // Chunk counters
            $valid = 0;
            $inserted = 0;
            $duplicates = 0;
            $suppressed = 0;
            $invalid = 0;

            // Use transaction per chunk (safer + consistent)
            DB::transaction(function () use (
                $emails,
                $categoryId,
                $suppressedDomains,
                &$valid,
                &$inserted,
                &$duplicates,
                &$suppressed,
                &$invalid,
                &$p
            ) {
                foreach ($emails as $email) {
                    $raw = $email;

                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $invalid++;
                        $this->pushInvalidPreview($p, $raw, $email, 'Invalid format');
                        continue;
                    }

                    [$local, $domain] = explode('@', $email, 2);
                    $local = trim($local);
                    $domain = trim($domain);

                    if ($local === '' || $domain === '') {
                        $invalid++;
                        $this->pushInvalidPreview($p, $raw, $email, 'Invalid parts');
                        continue;
                    }

                    if (isset($suppressedDomains[$domain])) {
                        $suppressed++;
                        continue;
                    }

                    $emailAddress = EmailAddress::firstOrCreate(
                        ['email' => $email],
                        [
                            'local_part' => $local,
                            'domain' => $domain,
                            'is_valid' => true,
                            'invalid_reason' => null,
                        ]
                    );

                    $isGloballySuppressed = SuppressionEntry::query()
                        ->where('scope', 'global')
                        ->where('email_address_id', $emailAddress->id)
                        ->exists();

                    if ($isGloballySuppressed) {
                        $suppressed++;
                        continue;
                    }

                    $valid++;

                    // ✅ Same category duplicate rule:
                    // If already exists in this category -> DO NOTHING (skip), just count duplicate.
                    $exists = DB::table('category_email')
                        ->where('category_id', $categoryId)
                        ->where('email_address_id', $emailAddress->id)
                        ->exists();

                    if ($exists) {
                        $duplicates++;
                        continue;
                    }

                    DB::table('category_email')->insert([
                        'category_id' => $categoryId,
                        'email_address_id' => $emailAddress->id,
                        'times_added' => 1,
                        'import_batch_id' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $inserted++;
                }
            });

            // Update progress
            $p['cursor'] = $cursor;
            $p['processed'] = (int) ($p['processed'] ?? 0) + count($emails);
            $p['valid'] = (int) ($p['valid'] ?? 0) + $valid;
            $p['inserted'] = (int) ($p['inserted'] ?? 0) + $inserted;
            $p['duplicates'] = (int) ($p['duplicates'] ?? 0) + $duplicates;
            $p['suppressed'] = (int) ($p['suppressed'] ?? 0) + $suppressed;
            $p['invalid'] = (int) ($p['invalid'] ?? 0) + $invalid;

            // If reached or exceeded total, finalize (safety)
            $total = (int) ($p['total'] ?? 0);
            if ($total > 0 && $p['processed'] >= $total) {
                $p['status'] = 'done';
                Storage::delete($this->tempPath($uploadId));
            }

            Cache::put($this->progressKey($uploadId), $p, $this->ttlSeconds);

            $this->refreshProgress();
        } catch (\Throwable $e) {
            $p = Cache::get($this->progressKey($this->upload_id));
            if (is_array($p)) {
                $p['status'] = 'error';
                $p['message'] = $e->getMessage();
                Cache::put($this->progressKey($this->upload_id), $p, $this->ttlSeconds);
            }

            $this->dispatch('toast', type: 'error', message: 'Upload failed: ' . $e->getMessage(), timeout: 7000);
            $this->refreshProgress();
        } finally {
            optional($lock)->release();
        }
    }

    public function cancelUpload(): void
    {
        if (!$this->upload_id) {
            return;
        }

        $uploadId = $this->upload_id;
        $p = Cache::get($this->progressKey($uploadId));

        if (is_array($p)) {
            $p['status'] = 'cancelled';
            $p['message'] = 'Cancelled by user.';
            Cache::put($this->progressKey($uploadId), $p, $this->ttlSeconds);
        }

        Storage::delete($this->tempPath($uploadId));

        $this->dispatch('toast', type: 'warning', message: 'Upload cancelled.', timeout: 4000);
        $this->refreshProgress();
    }

    private function pushInvalidPreview(array &$progress, string $raw, string $email, string $reason): void
    {
        if (!isset($progress['invalidPreview']) || !is_array($progress['invalidPreview'])) {
            $progress['invalidPreview'] = [];
        }

        if (count($progress['invalidPreview']) >= 50) {
            return;
        }

        $progress['invalidPreview'][] = [
            'raw' => $raw,
            'email' => $email,
            'reason' => $reason,
        ];
    }

    private function parseTextarea(string $text): array
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $chunks = preg_split('/[\n,;]+/', $text) ?: [];

        return array_values(array_filter(array_map('trim', $chunks), fn ($v) => $v !== ''));
    }

    private function parseCsvUpload(): array
    {
        $rows = [];

        if (!$this->csv) {
            return $rows;
        }

        $path = $this->csv->getRealPath();
        if (!$path) {
            return $rows;
        }

        $handle = fopen($path, 'r');
        if (!$handle) {
            return $rows;
        }

        while (($data = fgetcsv($handle)) !== false) {
            foreach ($data as $cell) {
                $cell = trim((string) $cell);
                if ($cell !== '') {
                    $rows[] = $cell;
                }
            }
        }

        fclose($handle);

        return $rows;
    }

    private function normalizeName(string $value): string
    {
        $value = trim($value);
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;

        return $value;
    }

    private function categoryNameExists(string $name): bool
    {
        $lower = mb_strtolower($name);

        return Category::query()
            ->whereRaw('LOWER(name) = ?', [$lower])
            ->exists();
    }

    private function uniqueSlug(string $baseSlug): string
    {
        $slug = $baseSlug !== '' ? $baseSlug : Str::random(8);
        $original = $slug;
        $i = 2;

        while (Category::query()->where('slug', $slug)->exists()) {
            $slug = $original . '-' . $i;
            $i++;
        }

        return $slug;
    }

    public function render()
    {
        return view('livewire.email-manager.imports.upload', [
            'categories' => Category::orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}