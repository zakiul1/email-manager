<div class="p-6">
    <div class="flex items-center justify-between gap-4">
        <h1 class="text-lg font-semibold">Emails</h1>

        <div class="flex gap-2">
            <select wire:model="saved_filter_id" class="rounded-md border px-3 py-2 text-sm">
                <option value="0">Saved filters...</option>
                @foreach($savedFilters as $sf)
                    <option value="{{ $sf->id }}">{{ $sf->name }}</option>
                @endforeach
            </select>
            <button class="rounded-md border px-3 py-2 text-sm" wire:click="applySavedFilter">
                Apply
            </button>
        </div>
    </div>

    <div class="mt-4 grid gap-3 md:grid-cols-6">
        <div class="md:col-span-2">
            <flux:input wire:model.live="q" placeholder="Search email..." />
        </div>

        <div>
            <select wire:model="category_id" class="w-full rounded-md border px-3 py-2 text-sm">
                <option value="0">All categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <flux:input wire:model.live="domain" placeholder="Domain (gmail.com)" />
        </div>

        <div>
            <select wire:model="valid" class="w-full rounded-md border px-3 py-2 text-sm">
                <option value="all">All</option>
                <option value="valid">Valid</option>
                <option value="invalid">Invalid</option>
            </select>
        </div>

        <div>
            <select wire:model="suppressed" class="w-full rounded-md border px-3 py-2 text-sm">
                <option value="all">Suppression: All</option>
                <option value="yes">Suppressed</option>
                <option value="no">Not suppressed</option>
            </select>
        </div>
    </div>

    <div class="mt-3 grid gap-3 md:grid-cols-6">
        <div>
            <label class="text-xs text-muted-foreground">Added from (needs category filter)</label>
            <input type="date" wire:model="added_from" class="w-full rounded-md border px-3 py-2 text-sm">
        </div>
        <div>
            <label class="text-xs text-muted-foreground">Added to (needs category filter)</label>
            <input type="date" wire:model="added_to" class="w-full rounded-md border px-3 py-2 text-sm">
        </div>

        <div class="md:col-span-2">
            <label class="text-xs text-muted-foreground">Save current filter</label>
            <div class="flex gap-2">
                <input wire:model="save_filter_name" class="w-full rounded-md border px-3 py-2 text-sm" placeholder="Filter name">
                <button class="rounded-md border px-3 py-2 text-sm" wire:click="saveFilter">Save</button>
            </div>
        </div>

        <div class="md:col-span-2">
            <label class="text-xs text-muted-foreground">Bulk action</label>
            <div class="flex gap-2">
                <select wire:model="action" class="w-full rounded-md border px-3 py-2 text-sm">
                    <option value="">Select action...</option>
                    <option value="copy_to">Copy to category</option>
                    <option value="move_to">Move to category</option>
                    <option value="merge_categories">Merge categories</option>
                    <option value="suppress">Add to suppression</option>
                    <option value="unsuppress">Remove suppression</option>
                    <option value="detach">Remove from category (detach)</option>
                </select>

                <select wire:model="target_category_id" class="w-full rounded-md border px-3 py-2 text-sm">
                    <option value="0">Target category...</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>

                <button class="rounded-md border px-3 py-2 text-sm" wire:click="runBulkAction">
                    Run
                </button>
            </div>
            <div class="text-xs text-muted-foreground mt-1">
                For “Move / Detach / Merge”, choose a source Category first.
            </div>
        </div>
    </div>

    <div class="mt-6">
        <flux:card>
            <div class="flex items-center justify-between p-4 border-b">
                <div class="text-sm">
                    Selected: <span class="font-semibold">{{ count($selected) }}</span>
                </div>
                <button class="rounded-md border px-3 py-2 text-sm" wire:click="toggleSelectPage">
                    {{ $selectPage ? 'Clear selection' : 'Select first 200 on page' }}
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left">
                        <tr class="border-b">
                            <th class="px-4 py-3">
                                <input type="checkbox" wire:model="selectPage">
                            </th>
                            <th class="px-4 py-3 font-medium">Email</th>
                            <th class="px-4 py-3 font-medium">Domain</th>
                            <th class="px-4 py-3 font-medium">Valid</th>
                            <th class="px-4 py-3 font-medium">Suppressed</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($emails as $e)
                            <tr class="border-b">
                                <td class="px-4 py-3">
                                    <input type="checkbox" value="{{ $e->id }}" wire:model="selected">
                                </td>
                                <td class="px-4 py-3">{{ $e->email }}</td>
                                <td class="px-4 py-3">{{ $e->domain }}</td>
                                <td class="px-4 py-3">{{ $e->is_valid ? 'Yes' : 'No' }}</td>
                                <td class="px-4 py-3">
                                    @php
                                        $isSupp = \App\Models\SuppressionEntry::where('scope','global')
                                            ->where('email_address_id', $e->id)->exists();
                                    @endphp
                                    {{ $isSupp ? 'Yes' : 'No' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-muted-foreground">
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
    </div>
</div>