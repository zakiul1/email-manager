<div class="p-6 space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between gap-4">
        <h1 class="text-lg font-semibold">Emails</h1>

        {{-- Bulk actions --}}
        <div class="flex items-center gap-2">
            <div class="text-sm text-muted-foreground">
             Selected:
@if ($selectAllMatchedMode ?? false)
    <span class="font-semibold text-foreground">
        {{ number_format($matchedCount ?? 0) }} (All matched)
    </span>
@else
    <span class="font-semibold text-foreground">{{ count($selected ?? []) }}</span>
@endif
            </div>

            {{-- Select all matched (across pages / current filters) --}}
            <button type="button" class="rounded-md border px-3 py-2 text-sm cursor-pointer"
                wire:click="activateSelectAllMatched" wire:loading.attr="disabled"
                @if (($emails->count() ?? 0) === 0) disabled @endif>
                Select All
            </button>

            <button type="button" class="rounded-md border px-3 py-2 text-sm cursor-pointer"
                wire:click="clearSelection" wire:loading.attr="disabled"
                @if (empty($selected) && !($selectAllMatchedMode ?? false)) disabled @endif>
                Clear
            </button>

            <button type="button" class="rounded-md border px-3 py-2 text-sm cursor-pointer"
                wire:click="openBulkDeleteModal" wire:loading.attr="disabled"
                @if (empty($selected) && !($selectAllMatchedMode ?? false)) disabled @endif>
                Delete selected
            </button>
        </div>
    </div>

    {{-- Filter bar (Left: Category, Right: Search) --}}
    <flux:card>
        <div class="flex items-center justify-between p-4 border-b gap-3">
            <div class="w-64">
                <select wire:model.live="category_id" class="w-full rounded-md border px-3 py-2 text-sm">
                    <option value="0">All categories</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-full max-w-md">
                <flux:input wire:model.live="q" placeholder="Search email..." />
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-left">
                    <tr class="border-b">
                        {{-- ✅ Select all checkbox (current page) --}}
                        <th class="px-4 py-3 w-10">
                            <input type="checkbox"
                                wire:model.live="selectAllOnPage"
                                @if (($emails->count() ?? 0) === 0) disabled @endif>
                        </th>

                        <th class="px-4 py-3 font-medium">Email</th>
                        <th class="px-4 py-3 font-medium">Domain</th>
                        <th class="px-4 py-3 font-medium">Blocked Mail</th>
                        <th class="px-4 py-3 font-medium">Category</th>
                        <th class="px-4 py-3 font-medium text-right">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($emails as $e)
                        @php
                            $isSupp = (bool) ($e->is_suppressed ?? false);
                            $cats = $e->categories ?? collect();
                        @endphp

                        <tr class="border-b">
                            {{-- ✅ row checkbox (LIVE sync) --}}
                            <td class="px-4 py-3">
                                <input type="checkbox"
                                    value="{{ $e->id }}"
                                    wire:model.live="selected">
                            </td>

                            <td class="px-4 py-3">{{ $e->email }}</td>
                            <td class="px-4 py-3">{{ $e->domain }}</td>

                            <td class="px-4 py-3">
                                {{ $isSupp ? 'Yes' : 'No' }}
                            </td>

                            <td class="px-4 py-3">
                                @if ($cats->count())
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($cats as $c)
                                            <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-xs">
                                                {{ $c->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted-foreground text-xs">—</span>
                                @endif
                            </td>

                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex items-center gap-1">
                                    <button class="rounded-md border px-2 py-1 text-xs"
                                        wire:click="rowCopy({{ $e->id }})" wire:loading.attr="disabled"
                                        title="Copy email">
                                        Copy
                                    </button>

                                    {{-- ✅ Toggle Block/Unblock --}}
                                    <button class="rounded-md border px-2 py-1 text-xs"
                                        wire:click="rowSuppress({{ $e->id }})" wire:loading.attr="disabled"
                                        title="{{ $isSupp ? 'Unblock email' : 'Block email' }}">
                                        {{ $isSupp ? 'Unblock' : 'Block' }}
                                    </button>

                                    {{-- Confirm delete (opens modal) --}}
                                    <button class="rounded-md border px-2 py-1 text-xs"
                                        wire:click="confirmDelete({{ $e->id }})" wire:loading.attr="disabled"
                                        title="Delete email">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-muted-foreground">
                                No emails found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4">
            {{ $emails->links() }}
        </div>
    </flux:card>

    {{-- Delete Confirmation Modal (single) --}}
    @if ($confirmingDeleteId)
        <div class="fixed inset-0 z-50 flex items-center justify-center">
            {{-- overlay --}}
            <div class="absolute inset-0 bg-black/40" wire:click="cancelDelete"></div>

            {{-- modal --}}
            <div class="relative w-full max-w-md rounded-lg bg-white p-5 shadow-lg">
                <div class="text-base font-semibold">Confirm delete</div>
                <div class="mt-2 text-sm text-muted-foreground">
                    Are you sure you want to delete this email? This action cannot be undone.
                </div>

                <div class="mt-4 flex justify-end gap-2">
                    <button class="rounded-md border px-3 py-2 text-sm"
                        wire:click="cancelDelete" wire:loading.attr="disabled">
                        Cancel
                    </button>

                    <button class="rounded-md border px-3 py-2 text-sm"
                        wire:click="deleteConfirmed" wire:loading.attr="disabled">
                        Yes, Delete
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ✅ Bulk Delete Confirmation Modal --}}
    @if ($showBulkDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center">
            {{-- overlay --}}
            <div class="absolute inset-0 bg-black/40" wire:click="cancelBulkDelete"></div>

            {{-- modal --}}
            <div class="relative w-full max-w-md rounded-lg bg-white p-5 shadow-lg">
                <div class="text-base font-semibold">Confirm bulk delete</div>

                <div class="mt-2 text-sm text-muted-foreground">
                    This will delete
                    <span class="font-semibold text-foreground">{{ number_format($bulkDeleteCount ?? 0) }}</span>
                    email(s). This action cannot be undone.
                </div>

                <div class="mt-4 flex justify-end gap-2">
                    <button class="rounded-md border px-3 py-2 text-sm"
                        wire:click="cancelBulkDelete" wire:loading.attr="disabled">
                        Cancel
                    </button>

                    <button class="rounded-md border px-3 py-2 text-sm"
                        wire:click="bulkDeleteConfirmed" wire:loading.attr="disabled">
                        Yes, Delete
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Clipboard copy listener --}}
    <script>
        window.addEventListener('copy-email', (e) => {
            const text = e.detail?.text ?? '';
            if (!text) return;
            navigator.clipboard.writeText(text);
        });
    </script>
</div>