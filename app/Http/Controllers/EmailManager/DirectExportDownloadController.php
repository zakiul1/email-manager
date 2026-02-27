<?php

namespace App\Http\Controllers\EmailManager;

use App\Services\ExportQueryService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DirectExportDownloadController extends Controller
{
    public function download(Request $request, ExportQueryService $exportQueryService): StreamedResponse
    {
        abort_unless(auth()->check(), 403);

        $validated = $request->validate([
            'format' => ['required', 'in:csv,txt,json'],
            'category_id' => ['nullable', 'integer', 'min:0'],
            'domain' => ['nullable', 'string', 'max:255'],
            'valid' => ['required', 'in:all,valid,invalid'],
            'exclude_global_suppression' => ['nullable'],
            'exclude_domain_unsubscribes' => ['nullable'],
        ]);

        $format = $validated['format'];

        // normalize filters to match your ExportQueryService expectations
        $filters = [
            'category_id' => (int)($validated['category_id'] ?? 0),
            'domain' => isset($validated['domain']) ? trim(mb_strtolower(ltrim($validated['domain'], '@'))) : null,
            'valid' => $validated['valid'] ?? 'all',
            'exclude_global_suppression' => $this->toBool($validated['exclude_global_suppression'] ?? 1),
            'exclude_domain_unsubscribes' => $this->toBool($validated['exclude_domain_unsubscribes'] ?? 1),
        ];

        // clean null domain
        if (($filters['domain'] ?? '') === '') {
            $filters['domain'] = null;
        }

        $query = $exportQueryService->build($filters);

        $timestamp = now()->format('Ymd_His');
        $filename = "emails_export_{$timestamp}." . $format;

        return response()->streamDownload(function () use ($query, $format) {
            if ($format === 'csv') {
                $this->streamCsv($query);
                return;
            }

            if ($format === 'txt') {
                $this->streamTxt($query);
                return;
            }

            $this->streamJson($query);
        }, $filename, [
            'Content-Type' => match ($format) {
                'csv' => 'text/csv; charset=UTF-8',
                'txt' => 'text/plain; charset=UTF-8',
                'json' => 'application/json; charset=UTF-8',
                default => 'application/octet-stream',
            },
        ]);
    }

    private function streamCsv($query): void
    {
        $out = fopen('php://output', 'w');
        if ($out === false) return;

        // UTF-8 BOM (helps Excel)
        fwrite($out, "\xEF\xBB\xBF");

        fputcsv($out, ['email', 'domain', 'is_valid', 'invalid_reason']);

        // ✅ IMPORTANT: chunkById column should be just 'id'
        $query->chunkById(2000, function ($rows) use ($out) {
            foreach ($rows as $row) {
                fputcsv($out, [
                    $row->email,
                    $row->domain,
                    $row->is_valid ? 1 : 0,
                    $row->invalid_reason,
                ]);
            }
        }, 'id');

        fclose($out);
    }

    private function streamTxt($query): void
    {
        $query->chunkById(5000, function ($rows) {
            foreach ($rows as $row) {
                echo $row->email . "\n";
            }
        }, 'id');
    }

    private function streamJson($query): void
    {
        echo '[';
        $first = true;

        $query->chunkById(2000, function ($rows) use (&$first) {
            foreach ($rows as $row) {
                $item = [
                    'email' => $row->email,
                    'domain' => $row->domain,
                    'is_valid' => (bool)$row->is_valid,
                    'invalid_reason' => $row->invalid_reason,
                ];

                if (!$first) echo ',';
                $first = false;

                echo json_encode($item, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
        }, 'id');

        echo ']';
    }

    private function toBool(mixed $value): bool
    {
        if (is_bool($value)) return $value;
        if (is_int($value)) return $value === 1;

        $v = Str::lower(trim((string)$value));
        return in_array($v, ['1', 'true', 'yes', 'on'], true);
    }
}