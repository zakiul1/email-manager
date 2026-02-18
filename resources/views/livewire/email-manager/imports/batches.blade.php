<div class="p-6">
    <div class="flex items-center justify-between">
        <h1 class="text-lg font-semibold">Import Batches</h1>
        <flux:button :href="route('email-manager.imports.upload')" wire:navigate>New Upload</flux:button>
    </div>

    <div class="mt-6">
        <flux:card>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left">
                        <tr class="border-b">
                            <th class="px-4 py-3 font-medium">ID</th>
                            <th class="px-4 py-3 font-medium">Category</th>
                            <th class="px-4 py-3 font-medium">Source</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium">Total</th>
                            <th class="px-4 py-3 font-medium">Inserted</th>
                            <th class="px-4 py-3 font-medium text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($batches as $batch)
                            <tr class="border-b">
                                <td class="px-4 py-3">{{ $batch->id }}</td>
                                <td class="px-4 py-3">{{ $batch->category?->name ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $batch->source_type }}</td>
                                <td class="px-4 py-3">{{ $batch->status }}</td>
                                <td class="px-4 py-3">{{ $batch->total_rows }}</td>
                                <td class="px-4 py-3">{{ $batch->inserted_rows }}</td>
                                <td class="px-4 py-3 text-right">
                                    <flux:button size="sm" variant="ghost"
                                        :href="route('email-manager.imports.batches.show', $batch)"
                                        wire:navigate
                                    >
                                        View
                                    </flux:button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-muted-foreground">
                                    No imports yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4">
                {{ $batches->links() }}
            </div>
        </flux:card>
    </div>
</div>