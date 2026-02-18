<?php

namespace App\Services;

use App\Models\Category;
use App\Models\EmailAddress;
use App\Models\ImportBatch;
use App\Models\ImportItem;
use App\Models\SuppressionEntry;
use Illuminate\Support\Facades\DB;

class EmailImportService
{
    public function processBatch(ImportBatch $batch, array $rawRows): void
    {
        $batch->update([
            'status' => 'processing',
            'started_at' => now(),
            'error_message' => null,
        ]);

        $category = $batch->category;

        $total = 0;
        $valid = 0;
        $invalid = 0;
        $duplicate = 0;
        $suppressed = 0;
        $inserted = 0;

        DB::transaction(function () use (
            $batch, $category, $rawRows,
            &$total, &$valid, &$invalid, &$duplicate, &$suppressed, &$inserted
        ) {
            foreach ($rawRows as $i => $raw) {
                $total++;
                $rowNumber = $i + 1;

                $rawEmail = is_string($raw) ? $raw : '';
                $normalized = $this->normalizeEmail($rawEmail);

                if ($normalized === null) {
                    $invalid++;
                    ImportItem::create([
                        'import_batch_id' => $batch->id,
                        'row_number' => $rowNumber,
                        'raw_email' => $rawEmail,
                        'status' => 'invalid',
                        'reason' => 'Empty',
                    ]);
                    continue;
                }

                $domain = $this->extractDomain($normalized);

                if (!$this->isValidEmail($normalized)) {
                    $invalid++;
                    ImportItem::create([
                        'import_batch_id' => $batch->id,
                        'row_number' => $rowNumber,
                        'raw_email' => $rawEmail,
                        'email' => $normalized,
                        'domain' => $domain,
                        'status' => 'invalid',
                        'reason' => 'Invalid format',
                    ]);
                    continue;
                }

                $valid++;

                // check suppression (domain or global)
                if ($this->isSuppressed($normalized, $domain)) {
                    $suppressed++;
                    ImportItem::create([
                        'import_batch_id' => $batch->id,
                        'row_number' => $rowNumber,
                        'raw_email' => $rawEmail,
                        'email' => $normalized,
                        'domain' => $domain,
                        'status' => 'suppressed',
                        'reason' => 'Suppressed (domain/global)',
                    ]);
                    continue;
                }

                // canonical email record
                $email = EmailAddress::firstOrCreate(
                    ['email' => $normalized],
                    [
                        'local_part' => $this->extractLocal($normalized),
                        'domain' => $domain,
                        'is_valid' => true,
                    ]
                );

                // attach to category with duplicate handling
                $existing = DB::table('category_email')
                    ->where('category_id', $category->id)
                    ->where('email_address_id', $email->id)
                    ->first();

                if ($existing) {
                    $duplicate++;

                    DB::table('category_email')
                        ->where('id', $existing->id)
                        ->update([
                            'times_added' => (int)$existing->times_added + 1,
                            'updated_at' => now(),
                        ]);

                    ImportItem::create([
                        'import_batch_id' => $batch->id,
                        'row_number' => $rowNumber,
                        'raw_email' => $rawEmail,
                        'email' => $normalized,
                        'domain' => $domain,
                        'status' => 'duplicate',
                        'reason' => 'Already exists in category',
                        'email_address_id' => $email->id,
                    ]);

                    continue;
                }

                DB::table('category_email')->insert([
                    'category_id' => $category->id,
                    'email_address_id' => $email->id,
                    'times_added' => 1,
                    'import_batch_id' => $batch->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $inserted++;

                ImportItem::create([
                    'import_batch_id' => $batch->id,
                    'row_number' => $rowNumber,
                    'raw_email' => $rawEmail,
                    'email' => $normalized,
                    'domain' => $domain,
                    'status' => 'inserted',
                    'reason' => null,
                    'email_address_id' => $email->id,
                ]);
            }
        });

        $batch->update([
            'total_rows' => $total,
            'valid_rows' => $valid,
            'invalid_rows' => $invalid,
            'duplicate_rows' => $duplicate,
            'inserted_rows' => $inserted,
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    private function normalizeEmail(string $value): ?string
    {
        $v = trim($value);
        if ($v === '') return null;

        // remove spaces and common separators around
        $v = trim($v, " \t\n\r\0\x0B,;");

        return mb_strtolower($v);
    }

    private function extractDomain(string $email): string
    {
        $parts = explode('@', $email);
        return $parts[1] ?? '';
    }

    private function extractLocal(string $email): string
    {
        $parts = explode('@', $email);
        return $parts[0] ?? '';
    }

    private function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function isSuppressed(string $email, string $domain): bool
    {
        // domain suppression
        $domainBlocked = SuppressionEntry::query()
            ->where('scope', 'domain')
            ->where('domain', $domain)
            ->exists();

        if ($domainBlocked) return true;

        // global suppression by email record (if exists)
        $emailId = EmailAddress::query()->where('email', $email)->value('id');
        if (!$emailId) return false;

        return SuppressionEntry::query()
            ->where('scope', 'global')
            ->where('email_address_id', $emailId)
            ->exists();
    }
}