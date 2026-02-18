<div class="p-6">
    <div class="flex items-center justify-between gap-4">
        <div class="w-full max-w-md">
            <flux:input wire:model.live="search" placeholder="Search categories..." />
        </div>

        <flux:button :href="route('email-manager.categories.create')" wire:navigate>
            Create
        </flux:button>
    </div>

    <div class="mt-6">
        <flux:card>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left">
                        <tr class="border-b">
                            <th class="px-4 py-3 font-medium">Name</th>
                            <th class="px-4 py-3 font-medium">Slug</th>
                            <th class="px-4 py-3 font-medium text-right">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($categories as $category)
                            <tr class="border-b">
                                <td class="px-4 py-3">{{ $category->name }}</td>
                                <td class="px-4 py-3">{{ $category->slug }}</td>
                                <td class="px-4 py-3 text-right">
                                    <flux:button size="sm" variant="ghost"
                                        :href="route('email-manager.categories.edit', $category)" wire:navigate>
                                        Edit
                                    </flux:button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-4 py-6 text-center text-muted-foreground" colspan="3">
                                    No categories found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4">
                {{ $categories->links() }}
            </div>
        </flux:card>
    </div>
</div>
