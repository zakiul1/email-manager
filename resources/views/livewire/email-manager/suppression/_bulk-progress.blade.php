{{-- resources/views/livewire/email-manager/suppression/_bulk-progress.blade.php --}}

@php
    // Required vars:
    // $bulkUploadId, $bulkIsRunning, $bulkIsDone
    // $bulkTotal, $bulkProcessed, $bulkAdded, $bulkInvalid
    // $bulkFailurePreview (array)
    //
    // Config vars (optional):
    // $metric4Label (string)     e.g. "Already Suppressed" or "Updated"
    // $metric4Value (int)        e.g. $bulkAlready or $bulkUpdated
    // $previewKey (string)       e.g. "email" or "value"
    // $previewTitle (string)     e.g. "Invalid Emails Preview" or "Failures Preview"
    // $downloadDisabledWhenZero (bool) default true

    $total = (int) ($bulkTotal ?? 0);
    $processed = (int) ($bulkProcessed ?? 0);

    $percent = $total > 0 ? (int) round(($processed / $total) * 100) : 0;
    $percent = max(0, min(100, $percent));

    $metric4Label = $metric4Label ?? 'Already Suppressed';
    $metric4Value = (int) ($metric4Value ?? 0);

    $previewKey = $previewKey ?? 'email';
    $previewTitle = $previewTitle ?? 'Invalid Emails Preview';
    $downloadDisabledWhenZero = $downloadDisabledWhenZero ?? true;

    $invalidCount = (int) ($bulkInvalid ?? 0);

    $boxClass = 'mt-4 rounded-md border p-3 text-sm ';
    if (!empty($bulkIsDone)) {
        $boxClass .= 'border-emerald-200 bg-emerald-50';
    } elseif (!empty($bulkIsRunning)) {
        $boxClass .= 'border-amber-200 bg-amber-50';
    } else {
        $boxClass .= 'border-gray-200 bg-gray-50';
    }

    $titleClass = 'font-medium ';
    if (!empty($bulkIsDone)) {
        $titleClass .= 'text-emerald-800';
    } elseif (!empty($bulkIsRunning)) {
        $titleClass .= 'text-amber-800';
    } else {
        $titleClass .= 'text-gray-800';
    }
@endphp

<div class="{{ $boxClass }}">
    <div class="flex items-center justify-between">
        <div class="{{ $titleClass }}">
            @if (!empty($bulkIsDone))
                Completed
            @elseif (!empty($bulkIsRunning))
                Processing...
            @else
                Ready
            @endif
        </div>

        <div class="font-semibold text-foreground">
            {{ $percent }}%
        </div>
    </div>

    <div class="mt-2 h-2 w-full rounded bg-white/70 overflow-hidden">
        <div class="h-2 bg-black/70" style="width: {{ $percent }}%"></div>
    </div>

    <div class="mt-3 grid grid-cols-2 gap-3">
        <div class="rounded-md border bg-white p-3">
            <div class="text-muted-foreground">Total</div>
            <div class="text-lg font-semibold">{{ number_format($bulkTotal ?? 0) }}</div>
        </div>

        <div class="rounded-md border bg-white p-3">
            <div class="text-muted-foreground">Processed</div>
            <div class="text-lg font-semibold">{{ number_format($bulkProcessed ?? 0) }}</div>
        </div>

        <div class="rounded-md border bg-white p-3">
            <div class="text-muted-foreground">Added</div>
            <div class="text-lg font-semibold">{{ number_format($bulkAdded ?? 0) }}</div>
        </div>

        <div class="rounded-md border bg-white p-3">
            <div class="text-muted-foreground">{{ $metric4Label }}</div>
            <div class="text-lg font-semibold">{{ number_format($metric4Value) }}</div>
        </div>

        <div class="rounded-md border bg-white p-3 col-span-2">
            <div class="text-muted-foreground">Invalid</div>
            <div class="text-lg font-semibold">{{ number_format($bulkInvalid ?? 0) }}</div>
        </div>
    </div>

    {{-- Auto chunk processing --}}
    @if (!empty($bulkIsRunning) && empty($bulkIsDone))
        <div class="mt-2 text-xs text-muted-foreground">Auto-processing chunks...</div>
        <div wire:poll.750ms="processChunk"></div>
    @endif

    {{-- Failures preview + download --}}
    <div class="mt-4">
        <div class="flex items-center justify-between">
            <flux:heading size="sm">
                {{ $previewTitle }} (first {{ count($bulkFailurePreview ?? []) }})
            </flux:heading>

            <button type="button"
                class="rounded-md border px-3 py-2 text-sm"
                wire:click="downloadBulkFailures"
                wire:loading.attr="disabled"
                @if ($downloadDisabledWhenZero && $invalidCount <= 0) disabled @endif>
                Download failures CSV
            </button>
        </div>

        <div class="mt-3 overflow-x-auto rounded-md border bg-white">
            <table class="min-w-full text-sm">
                <thead class="text-left">
                    <tr class="border-b">
                        <th class="px-3 py-2 font-medium">{{ ucfirst($previewKey) }}</th>
                        <th class="px-3 py-2 font-medium">Reason</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($bulkFailurePreview ?? []) as $row)
                        <tr class="border-b">
                            <td class="px-3 py-2">{{ $row[$previewKey] ?? '' }}</td>
                            <td class="px-3 py-2">{{ $row['reason'] ?? '' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-3 py-3 text-center text-muted-foreground">
                                No failures yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-1 text-xs text-muted-foreground">
            Preview shows up to 50 items. Download CSV to get full error list.
        </div>
    </div>
</div>