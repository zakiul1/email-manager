<div class="p-6 space-y-6">
    <flux:card>
        <div class="flex items-start justify-between gap-4">
            <div>
                <flux:heading size="lg">Upload Emails</flux:heading>
                <flux:subheading class="mt-1">
                    Upload emails into a category (create category here if needed).
                </flux:subheading>
            </div>
        </div>

        {{-- Result Summary --}}
        @if (($result['total'] ?? 0) > 0)
            <div class="mt-4 rounded-md border border-emerald-200 bg-emerald-50 p-4 text-sm">
                <div class="font-medium text-emerald-800">Import completed</div>

                <div class="mt-3 grid grid-cols-2 md:grid-cols-6 gap-3">
                    <div class="rounded-md border bg-white p-3">
                        <div class="text-muted-foreground">Total</div>
                        <div class="text-lg font-semibold">{{ $result['total'] ?? 0 }}</div>
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
                        <div class="text-muted-foreground">Suppressed</div>
                        <div class="text-lg font-semibold">{{ $result['suppressed'] ?? 0 }}</div>
                    </div>
                    <div class="rounded-md border bg-white p-3">
                        <div class="text-muted-foreground">Invalid</div>
                        <div class="text-lg font-semibold">{{ $result['invalid'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
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

        {{-- Create Category (Realtime) --}}
        <div class="mt-6 rounded-md border bg-white p-4">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <div class="font-medium">Create Category</div>
                    <div class="text-sm text-muted-foreground">Create and auto-select category before importing.</div>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="text-sm font-medium">Name</label>
                    <input
                        type="text"
                        class="mt-1 w-full rounded-md border px-3 py-2 text-sm"
                        wire:model.live="new_category_name"
                        placeholder="e.g. Clothing-all"
                        autocomplete="off"
                    />
                    @error('new_category_name')
                        <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="text-sm font-medium">Slug (optional)</label>
                    <input
                        type="text"
                        class="mt-1 w-full rounded-md border px-3 py-2 text-sm"
                        wire:model.live="new_category_slug"
                        placeholder="Auto-generated if empty"
                        autocomplete="off"
                    />
                    @error('new_category_slug')
                        <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="text-sm font-medium">Notes (optional)</label>
                    <input
                        type="text"
                        class="mt-1 w-full rounded-md border px-3 py-2 text-sm"
                        wire:model.live="new_category_notes"
                        placeholder="Optional notes"
                        autocomplete="off"
                    />
                    @error('new_category_notes')
                        <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mt-4">
                <button
                    type="button"
                    class="rounded-md border px-4 py-2 text-sm"
                    wire:click="createCategory"
                    wire:loading.attr="disabled"
                    wire:target="createCategory"
                >
                    <span wire:loading.remove wire:target="createCategory">Create & Select</span>
                    <span wire:loading wire:target="createCategory">Creating...</span>
                </button>
            </div>
        </div>

        {{-- Import Form --}}
        <form class="mt-6 space-y-5" wire:submit.prevent="submit">
            <flux:field>
                <flux:label>Category</flux:label>
                <select wire:model="category_id" class="w-full rounded-md border px-3 py-2 text-sm">
                    <option value="0">Select category...</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
                @error('category_id')
                    <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
                @enderror
            </flux:field>

            <flux:field>
                <flux:label>Source</flux:label>
                <div class="flex gap-2">
                    <button
                        type="button"
                        class="rounded-md border px-3 py-2 text-sm {{ $mode === 'textarea' ? 'bg-muted' : '' }}"
                        wire:click="$set('mode','textarea')"
                    >
                        Textarea
                    </button>

                    <button
                        type="button"
                        class="rounded-md border px-3 py-2 text-sm {{ $mode === 'csv' ? 'bg-muted' : '' }}"
                        wire:click="$set('mode','csv')"
                    >
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
                    <flux:textarea rows="10" wire:model="textarea" />
                    @error('textarea')
                        <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
                    @enderror
                </flux:field>
            @else
                <flux:field>
                    <flux:label>CSV file</flux:label>
                    <input type="file" wire:model="csv" class="block w-full text-sm" />
                    @error('csv')
                        <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
                    @enderror

                    {{-- Upload Progress --}}
                    <div class="mt-3" wire:loading wire:target="csv">
                        <div class="text-xs text-muted-foreground mb-1">Uploading...</div>
                        <div class="h-2 w-full rounded bg-muted overflow-hidden">
                            <div class="h-2 bg-foreground/60 animate-pulse" style="width: 100%"></div>
                        </div>
                    </div>
                </flux:field>
            @endif

            <div class="flex gap-2 items-center">
                <button
                    type="submit"
                    class="rounded-md border px-4 py-2 text-sm"
                    wire:loading.attr="disabled"
                    wire:target="submit,csv"
                >
                    <span wire:loading.remove wire:target="submit">Start Import</span>
                    <span wire:loading wire:target="submit">Importing...</span>
                </button>

                <span class="text-xs text-muted-foreground">
                    Direct import (no batches)
                </span>
            </div>
        </form>

        {{-- Invalid Preview --}}
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
    </flux:card>
</div>