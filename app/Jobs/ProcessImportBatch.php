<?php

namespace App\Jobs;

use App\Models\ImportBatch;
use App\Services\EmailImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessImportBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $batchId, public array $rows)
    {
    }

    public function handle(EmailImportService $service): void
    {
        $batch = ImportBatch::findOrFail($this->batchId);

        try {
            $service->processBatch($batch, $this->rows);
        } catch (\Throwable $e) {
            $batch->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);
            throw $e;
        }
    }
}