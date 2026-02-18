<?php

namespace App\Livewire\EmailManager\Imports;

use App\Jobs\ProcessImportBatch;
use App\Models\Category;
use App\Models\ImportBatch;
use Livewire\Component;
use Livewire\WithFileUploads;

class Upload extends Component
{
    use WithFileUploads;

    public int $category_id = 0;

    public string $mode = 'textarea'; // textarea|csv
    public string $textarea = '';

    public $csv = null; // Livewire temp upload

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
        // Base validation
        $this->validate([
            'category_id' => 'required|integer|exists:categories,id',
            'mode' => 'required|in:textarea,csv',
        ]);

        // Conditional validation (prevents CSV errors when using textarea)
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

        $batch = ImportBatch::create([
            'user_id' => auth()->id(),
            'category_id' => $category->id,
            'source_type' => $this->mode,
            'original_filename' => $this->mode === 'csv'
                ? ($this->csv?->getClientOriginalName() ?? null)
                : null,
            'status' => 'queued',
        ]);

        $rows = $this->mode === 'textarea'
            ? $this->parseTextarea($this->textarea)
            : $this->parseCsvUpload();

        // store quick stats for UI
        $batch->update([
            'total_rows' => count($rows),
        ]);

        // Dispatch background job (queue worker must be running)
        ProcessImportBatch::dispatch($batch->id, $rows);

        // ✅ Redirect to batch show page (matches: email-manager.imports.batches.show)
        $this->redirect(
            route('email-manager.imports.batches.show', ['batch' => $batch->id]),
            navigate: true
        );
    }

    private function parseTextarea(string $text): array
    {
        // split by new line and commas/semicolons too
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $chunks = preg_split('/[\n,;]+/', $text) ?: [];

        return array_values(array_filter(array_map('trim', $chunks), fn ($v) => $v !== ''));
    }

    private function parseCsvUpload(): array
    {
        $rows = [];

        if (!$this->csv) return $rows;

        $path = $this->csv->getRealPath();
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

    public function render()
    {
        return view('livewire.email-manager.imports.upload', [
            'categories' => Category::orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}