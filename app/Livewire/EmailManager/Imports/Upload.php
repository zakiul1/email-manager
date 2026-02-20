<?php

namespace App\Livewire\EmailManager\Imports;

use App\Models\Category;
use App\Models\EmailAddress;
use App\Models\SuppressionEntry;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class Upload extends Component
{
    use WithFileUploads;

    public int $category_id = 0;

    public string $mode = 'textarea'; // textarea|csv
    public string $textarea = '';

    public $csv = null; // Livewire temp upload

    // ✅ show summary on same page
    public array $result = [
        'total' => 0,
        'valid' => 0,
        'inserted' => 0,
        'duplicates' => 0,
        'suppressed' => 0,
        'invalid' => 0,
    ];

    // preview invalid items (optional UI)
    public array $invalidPreview = []; // [{raw,email,reason}...]

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

    public function submit(): void
    {
        // reset previous result
        $this->result = [
            'total' => 0,
            'valid' => 0,
            'inserted' => 0,
            'duplicates' => 0,
            'suppressed' => 0,
            'invalid' => 0,
        ];
        $this->invalidPreview = [];

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

        $category = Category::findOrFail($this->category_id);

        $rows = $this->mode === 'textarea'
            ? $this->parseTextarea($this->textarea)
            : $this->parseCsvUpload();

        $this->result['total'] = count($rows);

        // Normalize + pre-validate + remove empty
        $normalized = [];
        foreach ($rows as $raw) {
            $raw = trim((string) $raw);
            if ($raw === '')
                continue;

            // canonical lower
            $email = mb_strtolower($raw);

            $normalized[] = [
                'raw' => $raw,
                'email' => $email,
            ];
        }

        // quick de-dupe in same upload to reduce work
        $seen = [];
        $unique = [];
        foreach ($normalized as $r) {
            if (isset($seen[$r['email']]))
                continue;
            $seen[$r['email']] = true;
            $unique[] = $r;
        }

        // Preload domain suppressions for speed
        $domains = [];
        foreach ($unique as $r) {
            $parts = explode('@', $r['email']);
            if (count($parts) === 2 && $parts[1] !== '') {
                $domains[] = $parts[1];
            }
        }
        $domains = array_values(array_unique($domains));

        $suppressedDomains = [];
        if (!empty($domains)) {
            $suppressedDomains = SuppressionEntry::query()
                ->where('scope', 'domain')
                ->whereIn('domain', $domains)
                ->pluck('domain')
                ->all();
            $suppressedDomains = array_fill_keys($suppressedDomains, true);
        }

        // Process in DB transaction
        DB::transaction(function () use ($unique, $category, $suppressedDomains) {

            foreach ($unique as $row) {
                $raw = $row['raw'];
                $email = $row['email'];

                // Validate format
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $this->result['invalid']++;

                    if (count($this->invalidPreview) < 50) {
                        $this->invalidPreview[] = [
                            'raw' => $raw,
                            'email' => $email,
                            'reason' => 'Invalid format',
                        ];
                    }
                    continue;
                }

                [$local, $domain] = explode('@', $email, 2);
                $local = trim($local);
                $domain = trim($domain);

                if ($local === '' || $domain === '') {
                    $this->result['invalid']++;

                    if (count($this->invalidPreview) < 50) {
                        $this->invalidPreview[] = [
                            'raw' => $raw,
                            'email' => $email,
                            'reason' => 'Invalid parts',
                        ];
                    }
                    continue;
                }

                // Domain unsubscribe check
                if (isset($suppressedDomains[$domain])) {
                    $this->result['suppressed']++;
                    continue;
                }

                // Upsert EmailAddress
                $emailAddress = EmailAddress::firstOrCreate(
                    ['email' => $email],
                    [
                        'local_part' => $local,
                        'domain' => $domain,
                        'is_valid' => true,
                        'invalid_reason' => null,
                    ]
                );

                // Global suppression check (by email_address_id)
                $isGloballySuppressed = SuppressionEntry::query()
                    ->where('scope', 'global')
                    ->where('email_address_id', $emailAddress->id)
                    ->exists();

                if ($isGloballySuppressed) {
                    $this->result['suppressed']++;
                    continue;
                }

                $this->result['valid']++;

                // Attach to category (duplicate per category handled here)
                $pivot = DB::table('category_email')
                    ->where('category_id', $category->id)
                    ->where('email_address_id', $emailAddress->id)
                    ->first();

                if ($pivot) {
                    DB::table('category_email')
                        ->where('id', $pivot->id)
                        ->update([
                            'times_added' => (int) $pivot->times_added + 1,
                            'updated_at' => now(),
                        ]);

                    $this->result['duplicates']++;
                } else {
                    DB::table('category_email')->insert([
                        'category_id' => $category->id,
                        'email_address_id' => $emailAddress->id,
                        'times_added' => 1,
                        'import_batch_id' => null, // ✅ no batches
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $this->result['inserted']++;
                }
            }
        });

        // optional: clear input after success
        $this->textarea = '';
        $this->csv = null;

        // You can show toast/message in blade using $result
    }

    private function parseTextarea(string $text): array
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $chunks = preg_split('/[\n,;]+/', $text) ?: [];

        return array_values(array_filter(array_map('trim', $chunks), fn($v) => $v !== ''));
    }

    private function parseCsvUpload(): array
    {
        $rows = [];

        if (!$this->csv)
            return $rows;

        $path = $this->csv->getRealPath();
        if (!$path)
            return $rows;

        $handle = fopen($path, 'r');
        if (!$handle)
            return $rows;

        while (($data = fgetcsv($handle)) !== false) {
            foreach ($data as $cell) {
                $cell = trim((string) $cell);
                if ($cell !== '')
                    $rows[] = $cell;
            }
        }

        fclose($handle);

        return $rows;
    }

    public function render()
    {
        return view('livewire.email-manager.imports.upload', [
            'categories' => Category::orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}