<?php

namespace App\Jobs;

use App\Models\Export;
use App\Models\ExportFile;
use App\Services\ExportQueryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $exportId) {}

    public function handle(ExportQueryService $service): void
    {
        $export = Export::with('category')->findOrFail($this->exportId);

        $export->update([
            'status' => 'processing',
            'started_at' => now(),
            'error_message' => null,
        ]);

        try {
            $filters = $export->filters ?? [];
            $query = $service->build($filters);

            $total = (clone $query)->count();
            $export->update(['total_rows' => $total]);

            $format = $export->format;
            $filename = 'export_' . $export->id . '_' . now()->format('Ymd_His') . '.' . $format;
            $path = 'exports/' . $filename;

            $disk = 'local';
            $tmp = fopen('php://temp', 'w+');

            if ($format === 'csv') {
                fputcsv($tmp, ['email']);
                $query->chunk(2000, function ($rows) use ($tmp) {
                    foreach ($rows as $r) fputcsv($tmp, [$r->email]);
                });
            } elseif ($format === 'txt') {
                $query->chunk(2000, function ($rows) use ($tmp) {
                    foreach ($rows as $r) fwrite($tmp, $r->email . PHP_EOL);
                });
            } else { // json
                fwrite($tmp, '[');
                $first = true;
                $query->chunk(2000, function ($rows) use ($tmp, &$first) {
                    foreach ($rows as $r) {
                        $json = json_encode(['email' => $r->email], JSON_UNESCAPED_SLASHES);
                        if (!$first) fwrite($tmp, ',');
                        fwrite($tmp, $json);
                        $first = false;
                    }
                });
                fwrite($tmp, ']');
            }

            rewind($tmp);
            $contents = stream_get_contents($tmp);
            fclose($tmp);

            Storage::disk($disk)->put($path, $contents);

            $size = Storage::disk($disk)->size($path) ?? 0;

            ExportFile::updateOrCreate(
                ['export_id' => $export->id],
                ['disk' => $disk, 'path' => $path, 'filename' => $filename, 'size_bytes' => $size]
            );

            $export->update([
                'exported_rows' => $total,
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        } catch (\Throwable $e) {
            $export->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);
            throw $e;
        }
    }
}