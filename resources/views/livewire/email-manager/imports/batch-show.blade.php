<div class="p-6">
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-lg font-semibold">Import #{{ $batch->id }}</h1>
            <div class="text-sm text-muted-foreground mt-1">
                Category: <span class="font-medium">{{ $batch->category?->name ?? '-' }}</span> |
                Source: <span class="font-medium">{{ $batch->source_type }}</span> |
                Status: <span class="font-medium">{{ $batch->status }}</span>
            </div>

            @if($batch->error_message)
                <div class="mt-2 text-sm text-red-600">
                    Error: {{ $batch->error_message }}
                </div>
            @endif
        </div>

        <flux:button :href="route('email-manager.imports.batches')" wire:navigate>Back</flux:button>
    </div>

    <div class="mt-6 grid grid-cols-2 md:grid-cols-5 gap-3">
        <div class="rounded-md border p-3 text-sm">
            <div class="text-muted-foreground">Total</div>
            <div class="text-lg font-semibold">{{ $batch->total_rows }}</div>
        </div>
        <div class="rounded-md border p-3 text-sm">
            <div class="text-muted-foreground">Valid</div>
            <div class="text-lg font-semibold">{{ $batch->valid_rows }}</div>
        </div>
        <div class="rounded-md border p-3 text-sm">
            <div class="text-muted-foreground">Inserted</div>
            <div class="text-lg font-semibold">{{ $batch->inserted_rows }}</div>
        </div>
        <div class="rounded-md border p-3 text-sm">
            <div class="text-muted-foreground">Invalid</div>
            <div class="text-lg font-semibold">{{ $batch->invalid_rows }}</div>
        </div>
        <div class="rounded-md border p-3 text-sm">
            <div class="text-muted-foreground">Duplicates</div>
            <div class="text-lg font-semibold">{{ $batch->duplicate_rows }}</div>
        </div>
    </div>

    <div class="mt-6 flex gap-2 flex-wrap">
        <button class="rounded-md border px-3 py-2 text-sm" wire:click="$set('filter','all')">All</button>
        <button class="rounded-md border px-3 py-2 text-sm" wire:click="$set('filter','inserted')">Inserted</button>
        <button class="rounded-md border px-3 py-2 text-sm" wire:click="$set('filter','invalid')">Invalid</button>
        <button class="rounded-md border px-3 py-2 text-sm" wire:click="$set('filter','duplicate')">Duplicate</button>
        <button class="rounded-md border px-3 py-2 text-sm" wire:click="$set('filter','suppressed')">Suppressed</button>
    </div>

    <div class="mt-4">
        <flux:card>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left">
                        <tr class="border-b">
                            <th class="px-4 py-3 font-medium">Row</th>
                            <th class="px-4 py-3 font-medium">Raw</th>
                            <th class="px-4 py-3 font-medium">Email</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium">Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            <tr class="border-b">
                                <td class="px-4 py-3">{{ $item->row_number }}</td>
                                <td class="px-4 py-3">{{ $item->raw_email }}</td>
                                <td class="px-4 py-3">{{ $item->email }}</td>
                                <td class="px-4 py-3">{{ $item->status }}</td>
                                <td class="px-4 py-3">{{ $item->reason }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-muted-foreground">
                                    No rows.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4">
                {{ $items->links() }}
            </div>
        </flux:card>
    </div>
</div>