<div class="p-6">
    <div class="flex items-center justify-between">
        <h1 class="text-lg font-semibold">Global Suppressions</h1>
    </div>

    @php
        $disableForm = !empty($bulkIsRunning);
    @endphp

    <div class="mt-6 grid gap-4 md:grid-cols-2">
        <flux:card>
            <flux:heading size="md">Add Emails to Suppression</flux:heading>

            {{-- ✅ Chunk Progress + Result Summary --}}
            @if (!empty($bulkUploadId))
                @php
                    $total = (int) ($bulkTotal ?? 0);
                    $processed = (int) ($bulkProcessed ?? 0);
                    $percent = $total > 0 ? (int) round(($processed / $total) * 100) : 0;
                    $percent = max(0, min(100, $percent));

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
                            <div class="text-muted-foreground">Already Suppressed</div>
                            <div class="text-lg font-semibold">{{ number_format($bulkAlready ?? 0) }}</div>
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
                                Invalid Emails Preview (first {{ count($bulkFailurePreview ?? []) }})
                            </flux:heading>

                            <button type="button" class="rounded-md border px-3 py-2 text-sm"
                                wire:click="downloadBulkFailures" wire:loading.attr="disabled"
                                @if (($bulkInvalid ?? 0) <= 0) disabled @endif>
                                Download failures CSV
                            </button>
                        </div>

                        <div class="mt-3 overflow-x-auto rounded-md border bg-white">
                            <table class="min-w-full text-sm">
                                <thead class="text-left">
                                    <tr class="border-b">
                                        <th class="px-3 py-2 font-medium">Email</th>
                                        <th class="px-3 py-2 font-medium">Reason</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse(($bulkFailurePreview ?? []) as $row)
                                        <tr class="border-b">
                                            <td class="px-3 py-2">{{ $row['email'] ?? '' }}</td>
                                            <td class="px-3 py-2">{{ $row['reason'] ?? '' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="px-3 py-3 text-center text-muted-foreground">
                                                No invalid emails yet.
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
            @elseif (($result['total'] ?? 0) > 0)
                {{-- fallback --}}
                <div class="mt-4 rounded-md border border-emerald-200 bg-emerald-50 p-3 text-sm">
                    <div class="font-medium text-emerald-800">Completed</div>

                    <div class="mt-2 grid grid-cols-2 gap-3">
                        <div class="rounded-md border bg-white p-3">
                            <div class="text-muted-foreground">Total</div>
                            <div class="text-lg font-semibold">{{ $result['total'] ?? 0 }}</div>
                        </div>
                        <div class="rounded-md border bg-white p-3">
                            <div class="text-muted-foreground">Added</div>
                            <div class="text-lg font-semibold">{{ $result['added'] ?? 0 }}</div>
                        </div>
                        <div class="rounded-md border bg-white p-3">
                            <div class="text-muted-foreground">Already Suppressed</div>
                            <div class="text-lg font-semibold">{{ $result['already'] ?? 0 }}</div>
                        </div>
                        <div class="rounded-md border bg-white p-3">
                            <div class="text-muted-foreground">Invalid</div>
                            <div class="text-lg font-semibold">{{ $result['invalid'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- ✅ IMPORTANT: disable using FIELDSET (safe for Blade components) --}}
            <fieldset class="mt-4 space-y-3" @if ($disableForm) disabled @endif>
                <flux:field>
                    <flux:label>Emails (one per line, comma or semicolon separated)</flux:label>

                    <textarea rows="8" wire:model="emails" class="w-full rounded-md border px-3 py-2 text-sm"
                        placeholder="example@domain.com&#10;user2@gmail.com, user3@yahoo.com"></textarea>

                    <flux:error name="emails" />
                </flux:field>

                <flux:field>
                    <flux:label>Reason (optional)</flux:label>
                    <flux:input wire:model="reason" />
                    <flux:error name="reason" />
                </flux:field>

                <div class="flex items-center gap-2">
                    {{-- Start button: render disabled vs enabled WITHOUT dynamic attributes --}}
                    @if ($disableForm)
                        <flux:button disabled>Start</flux:button>
                    @else
                        <flux:button wire:click="startBulkAdd" wire:loading.attr="disabled">Start</flux:button>
                    @endif

                    @if (!empty($bulkUploadId) && !empty($bulkIsRunning))
                        <button type="button" class="rounded-md border px-3 py-2 text-sm" wire:click="processChunk"
                            wire:loading.attr="disabled">
                            Process next chunk
                        </button>
                    @endif

                    @if (!empty($bulkUploadId))
                        <button type="button" class="rounded-md border px-3 py-2 text-sm" wire:click="resetBulk"
                            wire:loading.attr="disabled">
                            Reset
                        </button>
                    @endif
                </div>

                <div class="text-xs text-muted-foreground" wire:loading>
                    Processing...
                </div>
            </fieldset>

            {{-- Backward compatible invalid preview --}}
            @if (!empty($invalidPreview) && empty($bulkFailurePreview))
                <div class="mt-6">
                    <flux:heading size="sm">
                        Invalid Emails Preview (first {{ count($invalidPreview) }})
                    </flux:heading>

                    <div class="mt-3 overflow-x-auto rounded-md border bg-white">
                        <table class="min-w-full text-sm">
                            <thead class="text-left">
                                <tr class="border-b">
                                    <th class="px-3 py-2 font-medium">Email</th>
                                    <th class="px-3 py-2 font-medium">Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($invalidPreview as $row)
                                    <tr class="border-b">
                                        <td class="px-3 py-2">{{ $row['email'] ?? '' }}</td>
                                        <td class="px-3 py-2">{{ $row['reason'] ?? '' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </flux:card>

        <flux:card>
            <flux:heading size="md">Current List</flux:heading>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left">
                        <tr class="border-b">
                            <th class="px-3 py-2 font-medium">Email</th>
                            <th class="px-3 py-2 font-medium">Reason</th>
                            <th class="px-3 py-2 font-medium text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $row)
                            <tr class="border-b">
                                <td class="px-3 py-2">{{ $row->emailAddress?->email }}</td>
                                <td class="px-3 py-2">{{ $row->reason }}</td>
                                <td class="px-3 py-2 text-right">
                                    <button class="text-sm underline" wire:click="remove({{ $row->id }})">
                                        Remove
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-3 py-4 text-center text-muted-foreground">
                                    No suppressed emails.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-3">
                    {{ $items->links() }}
                </div>
            </div>
        </flux:card>
    </div>
</div>
