<div class="p-6 space-y-6">
    <flux:card>
        <div class="flex items-start justify-between gap-4">
            <div>
                <flux:heading size="lg">Upload Emails</flux:heading>
                <flux:subheading class="mt-1">
                    Upload emails into a category.
                </flux:subheading>
            </div>
        </div>

        {{-- ✅ Chunked Progress + Poll --}}
        <div class="mt-4" wire:poll.1s="refreshProgress">
            @php
                $status = $result['status'] ?? 'idle';
                $total = (int) ($result['total'] ?? 0);
                $processed = (int) ($result['processed'] ?? 0);
                $percent = (int) ($result['percent'] ?? 0);
                $message = $result['message'] ?? null;

                // ✅ bar color that ALWAYS exists in Tailwind
                $barColor = match ($status) {
                    'processing' => 'bg-blue-600',
                    'done' => 'bg-emerald-600',
                    'cancelled' => 'bg-amber-600',
                    'error' => 'bg-red-600',
                    default => 'bg-zinc-400',
                };
            @endphp

            @if (in_array($status, ['processing', 'done', 'error', 'cancelled'], true))
                <div
                    class="rounded-md border p-4 text-sm
                    @if ($status === 'processing') border-blue-200 bg-blue-50
                    @elseif($status === 'done') border-emerald-200 bg-emerald-50
                    @elseif($status === 'cancelled') border-amber-200 bg-amber-50
                    @else border-red-300 bg-red-50 @endif
                ">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div
                                class="font-medium
                                @if ($status === 'processing') text-blue-800
                                @elseif($status === 'done') text-emerald-800
                                @elseif($status === 'cancelled') text-amber-800
                                @else text-red-800 @endif
                            ">
                                @if ($status === 'processing')
                                    Import in progress
                                @elseif($status === 'done')
                                    Import completed
                                @elseif($status === 'cancelled')
                                    Import cancelled
                                @else
                                    Import failed
                                @endif
                            </div>

                            <div class="mt-1 text-xs text-muted-foreground">
                                Progress: <span class="font-semibold">{{ $processed }}</span> / <span
                                    class="font-semibold">{{ $total }}</span>
                                <span class="ml-2">({{ $percent }}%)</span>
                                @if (!empty($message))
                                    <span class="ml-2">— {{ $message }}</span>
                                @endif
                            </div>
                        </div>

                        @if ($status === 'processing')
                            <button type="button" wire:click="cancelUpload"
                                class="rounded-md border px-3 py-2 text-xs bg-white hover:bg-muted">
                                Cancel
                            </button>
                        @endif
                    </div>

                    {{-- ✅ Progress bar --}}
                    <div class="mt-3 h-2 w-full rounded bg-white/60 overflow-hidden border">
                        <div class="h-2 {{ $barColor }}" style="width: {{ $percent }}%"></div>
                    </div>

                    {{-- Counters --}}
                    <div class="mt-3 grid grid-cols-2 md:grid-cols-6 gap-3">
                        <div class="rounded-md border bg-white p-3">
                            <div class="text-muted-foreground">Total</div>
                            <div class="text-lg font-semibold">{{ $result['total'] ?? 0 }}</div>
                        </div>
                        <div class="rounded-md border bg-white p-3">
                            <div class="text-muted-foreground">Processed</div>
                            <div class="text-lg font-semibold">{{ $result['processed'] ?? 0 }}</div>
                        </div>
                        <div class="rounded-md border bg-white p-3">
                            <div class="text-muted-foreground">Valid</div>
                            <div class="text-lg font-semibold">{{ $result['valid'] ?? 0 }}</div>
                        </div>
                        <div class="rounded-md border bg-white p-3">
                            <div class="text-muted-foreground">Inserted</div>
                            <div class="text-lg font-semibold">{{ $result['inserted'] ?? 0 }}</div>
                        </div>
                        <div class="rounded-md border bg-white p-3">
                            <div class="text-muted-foreground">Duplicates</div>
                            <div class="text-lg font-semibold">{{ $result['duplicates'] ?? 0 }}</div>
                        </div>
                        <div class="rounded-md border bg-white p-3">
                            <div class="text-muted-foreground">Invalid</div>
                            <div class="text-lg font-semibold">{{ $result['invalid'] ?? 0 }}</div>
                        </div>
                    </div>

                    <div class="mt-3 text-xs text-muted-foreground">
                        Suppressed: <span class="font-semibold">{{ $result['suppressed'] ?? 0 }}</span>
                    </div>
                </div>
            @endif
        </div>

        {{-- ✅ Run chunk processing automatically --}}
        @if (($result['status'] ?? 'idle') === 'processing')
            <div class="hidden" wire:poll.500ms="processChunk"></div>
        @endif

        {{-- Validation Errors (general) --}}
        @if ($errors->any())
            <div class="mt-4 rounded-md border border-red-300 bg-red-50 p-3 text-sm text-red-700">
                <div class="font-medium">Please fix these:</div>
                <ul class="list-disc pl-5 mt-2">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Import Form --}}
        <form class="mt-6 space-y-5" wire:submit.prevent="submit">
            {{-- Category select + plus modal button --}}
            <div class="space-y-2">
                <flux:label>Category</flux:label>

                <div class="flex gap-2">
                    <select wire:key="categories-select-{{ $categoriesVersion }}" wire:model="category_id"
                        class="w-full rounded-md border px-3 py-2 text-sm" @disabled(($result['status'] ?? 'idle') === 'processing')>
                        <option value="0">Select category...</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>

                    <flux:modal.trigger name="create-category">
                        <flux:button type="button" class="shrink-0"
                            :disabled="($result['status'] ?? 'idle') === 'processing'">
                            +
                        </flux:button>
                    </flux:modal.trigger>
                </div>

                @error('category_id')
                    <div class="text-sm text-red-600">{{ $message }}</div>
                @enderror
            </div>

            <flux:field>
                <flux:label>Source</flux:label>
                <div class="flex gap-2">
                    <button type="button"
                        class="rounded-md border px-3 py-2 text-sm {{ $mode === 'textarea' ? 'bg-muted' : '' }}"
                        wire:click="$set('mode','textarea')" @disabled(($result['status'] ?? 'idle') === 'processing')>
                        Textarea
                    </button>

                    <button type="button"
                        class="rounded-md border px-3 py-2 text-sm {{ $mode === 'csv' ? 'bg-muted' : '' }}"
                        wire:click="$set('mode','csv')" @disabled(($result['status'] ?? 'idle') === 'processing')>
                        CSV
                    </button>
                </div>
                @error('mode')
                    <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
                @enderror
            </flux:field>

            @if ($mode === 'textarea')
                <flux:field>
                    <flux:label>Emails (one per line, or comma-separated)</flux:label>

                    <textarea rows="10" wire:model="textarea" class="w-full rounded-md border px-3 py-2 text-sm"
                        @disabled(($result['status'] ?? 'idle') === 'processing')></textarea>

                    @error('textarea')
                        <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
                    @enderror
                </flux:field>
            @else
                <flux:field>
                    <flux:label>CSV file</flux:label>
                    <input type="file" wire:model="csv" class="block w-full text-sm" @disabled(($result['status'] ?? 'idle') === 'processing') />
                    @error('csv')
                        <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
                    @enderror

                    {{-- Upload Progress (file transfer only) --}}
                    <div class="mt-3" wire:loading wire:target="csv">
                        <div class="text-xs text-muted-foreground mb-1">Uploading file...</div>
                        <div class="h-2 w-full rounded bg-muted overflow-hidden">
                            <div class="h-2 bg-zinc-400 animate-pulse" style="width: 100%"></div>
                        </div>
                    </div>
                </flux:field>
            @endif

            <div class="flex gap-2 items-center">
                <button type="submit" class="rounded-md border px-4 py-2 text-sm" wire:loading.attr="disabled"
                    wire:target="submit,csv" @disabled(($result['status'] ?? 'idle') === 'processing')>
                    <span wire:loading.remove wire:target="submit">Start Import</span>
                    <span wire:loading wire:target="submit">Starting...</span>
                </button>

                <span class="text-xs text-muted-foreground">
                    Chunked import (no queue)
                </span>
            </div>
        </form>

        {{-- ✅ Not Inserted Emails (Invalid + Duplicate + Blocked) --}}
        @if (!empty($failurePreview))
            <div class="mt-8">
                <div class="flex items-center justify-between gap-3">
                    <flux:heading size="sm">
                        Not Inserted Emails (first {{ count($failurePreview) }})
                    </flux:heading>

                    <button type="button" wire:click="downloadFailures"
                        class="rounded-md border px-3 py-2 text-xs bg-white hover:bg-muted">
                        Download Full List (CSV)
                    </button>
                </div>

                <div class="mt-3 overflow-x-auto rounded-md border bg-white">
                    <table class="min-w-full text-sm">
                        <thead class="text-left">
                            <tr class="border-b">
                                <th class="px-4 py-3 font-medium">Type</th>
                                <th class="px-4 py-3 font-medium">Email</th>
                                <th class="px-4 py-3 font-medium">Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($failurePreview as $row)
                                <tr class="border-b">
                                    <td class="px-4 py-3">
                                        @php
                                            $t = $row['type'] ?? '';
                                        @endphp
                                        <span
                                            class="inline-flex items-center rounded px-2 py-1 text-xs font-medium
                                            @if ($t === 'invalid') bg-red-50 text-red-700 border border-red-200
                                            @elseif($t === 'duplicate') bg-amber-50 text-amber-700 border border-amber-200
                                            @else bg-zinc-50 text-zinc-700 border border-zinc-200 @endif
                                        ">
                                            {{ ucfirst($t) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">{{ $row['email'] ?? '' }}</td>
                                    <td class="px-4 py-3">{{ $row['reason'] ?? '' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-2 text-xs text-muted-foreground">
                    This list includes invalid format, duplicates, and blocked/suppressed emails.
                </div>
            </div>
        @endif

        {{-- (Optional) keep old invalid-only table if you still want it.
            If you don’t want duplication, you can remove this whole block. --}}
        @if (!empty($invalidPreview))
            <div class="mt-6">
                <flux:heading size="sm">Invalid Emails Preview (first {{ count($invalidPreview) }})</flux:heading>

                <div class="mt-3 overflow-x-auto rounded-md border bg-white">
                    <table class="min-w-full text-sm">
                        <thead class="text-left">
                            <tr class="border-b">
                                <th class="px-4 py-3 font-medium">Raw</th>
                                <th class="px-4 py-3 font-medium">Normalized</th>
                                <th class="px-4 py-3 font-medium">Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($invalidPreview as $row)
                                <tr class="border-b">
                                    <td class="px-4 py-3">{{ $row['raw'] ?? '' }}</td>
                                    <td class="px-4 py-3">{{ $row['email'] ?? '' }}</td>
                                    <td class="px-4 py-3">{{ $row['reason'] ?? 'Invalid' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- ✅ Create Category Modal --}}
        <flux:modal name="create-category" class="max-w-lg">
            <div class="space-y-4">
                <div>
                    <flux:heading size="lg">Create Category</flux:heading>
                    <flux:subheading class="mt-1">
                        Create a new category and auto-select it.
                    </flux:subheading>
                </div>

                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="text-sm font-medium">Name</label>
                        <input type="text" class="mt-1 w-full rounded-md border px-3 py-2 text-sm"
                            wire:model.live="new_category_name" placeholder="e.g. Clothing-all" autocomplete="off"
                            @disabled(($result['status'] ?? 'idle') === 'processing') />
                        @error('new_category_name')
                            <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-medium">Slug (optional)</label>
                        <input type="text" class="mt-1 w-full rounded-md border px-3 py-2 text-sm"
                            wire:model.live="new_category_slug" placeholder="Auto-generated if empty"
                            autocomplete="off" @disabled(($result['status'] ?? 'idle') === 'processing') />
                        @error('new_category_slug')
                            <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-medium">Notes (optional)</label>
                        <input type="text" class="mt-1 w-full rounded-md border px-3 py-2 text-sm"
                            wire:model.live="new_category_notes" placeholder="Optional notes" autocomplete="off"
                            @disabled(($result['status'] ?? 'idle') === 'processing') />
                        @error('new_category_notes')
                            <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2">
                    <flux:modal.close>
                        <flux:button variant="ghost" type="button" wire:click="resetCategoryForm"
                            :disabled="($result['status'] ?? 'idle') === 'processing'">
                            Cancel
                        </flux:button>
                    </flux:modal.close>

                    <flux:button type="button" wire:click="createCategory" wire:loading.attr="disabled"
                        wire:target="createCategory" :disabled="($result['status'] ?? 'idle') === 'processing'">
                        <span wire:loading.remove wire:target="createCategory">Create</span>
                        <span wire:loading wire:target="createCategory">Creating...</span>
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    </flux:card>
</div>