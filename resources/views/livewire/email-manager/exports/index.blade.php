<div class="p-6">
    <div class="flex items-center justify-between">
        <h1 class="text-lg font-semibold">Exports</h1>
        <flux:button :href="route('email-manager.exports.create')" wire:navigate>Create Export</flux:button>
    </div>

    <div class="mt-6">
        <flux:card>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left">
                        <tr class="border-b">
                            <th class="px-4 py-3 font-medium">ID</th>
                            <th class="px-4 py-3 font-medium">Category</th>
                            <th class="px-4 py-3 font-medium">Format</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium">Rows</th>
                            <th class="px-4 py-3 font-medium text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($exports as $ex)
                            <tr class="border-b">
                                <td class="px-4 py-3">{{ $ex->id }}</td>
                                <td class="px-4 py-3">{{ $ex->category?->name ?? 'All' }}</td>
                                <td class="px-4 py-3">{{ $ex->format }}</td>
                                <td class="px-4 py-3">{{ $ex->status }}</td>
                                <td class="px-4 py-3">{{ $ex->exported_rows }}</td>
                                <td class="px-4 py-3 text-right">
                                    @if($ex->status === 'completed' && $ex->file)
                                        <a
                                            href="{{ route('email-manager.exports.download-file', $ex) }}"
                                            class="text-sm underline"
                                        >
                                            Download
                                        </a>
                                    @else
                                        <span class="text-muted-foreground text-sm">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-muted-foreground">No exports yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4">{{ $exports->links() }}</div>
        </flux:card>
    </div>
</div>