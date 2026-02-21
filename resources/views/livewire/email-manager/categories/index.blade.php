<div class="p-6 space-y-6">
    {{-- Header --}}
    <div class="flex items-start justify-between gap-4">
        <div>
            <flux:heading size="lg">Lists & Subscribers</flux:heading>
            <flux:subheading class="mt-1">
                All lists in this account
            </flux:subheading>
        </div>

        <flux:button :href="route('email-manager.categories.create')" wire:navigate>
            Create A New List
        </flux:button>
    </div>

    {{-- Top summary cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <flux:card>
            <div class="flex items-center justify-between gap-4">
                <div>
                    <div class="text-sm text-muted-foreground">Lists</div>
                    <div class="mt-1 text-2xl font-semibold">{{ number_format($totalCategories ?? 0) }}</div>
                </div>
                <div class="h-12 w-12 rounded-xl bg-muted"></div>
            </div>
        </flux:card>

        <flux:card>
            <div class="flex items-center justify-between gap-4">
                <div>
                    <div class="text-sm text-muted-foreground">Active Subscribers</div>
                    <div class="mt-1 text-2xl font-semibold">{{ number_format($totalEmailsInCategories ?? 0) }}</div>
                </div>
                <div class="h-12 w-12 rounded-xl bg-muted"></div>
            </div>
        </flux:card>
    </div>

    {{-- Search + total --}}
    <div class="flex items-center justify-between gap-4">
        <div class="w-full max-w-md">
            <flux:input wire:model.live="search" placeholder="Search..." />
        </div>

        <div class="text-sm text-muted-foreground">
            Total: <span class="font-medium text-foreground">{{ number_format($categories->total()) }}</span>
        </div>
    </div>

    {{-- Table --}}
    <flux:card>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-left">
                    <tr class="border-b">
                        <th class="px-4 py-3 font-medium">#</th>
                        <th class="px-4 py-3 font-medium">LIST NAME</th>
                        <th class="px-4 py-3 font-medium">ACTIVE SUBSCRIBERS COUNT</th>
                        <th class="px-4 py-3 font-medium text-right">ACTIONS</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($categories as $category)
                        <tr class="border-b">
                            <td class="px-4 py-3">{{ $category->id }}</td>

                            <td class="px-4 py-3">
                                <div class="font-medium">{{ $category->name }}</div>
                                <div class="text-xs text-muted-foreground">{{ $category->slug }}</div>
                            </td>

                            <td class="px-4 py-3">
                                {{ number_format($category->emails_count ?? 0) }}
                            </td>

                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex items-center gap-1">
                                    {{-- View (category-wise emails) --}}
                                    <flux:button
                                        size="sm"
                                        variant="ghost"
                                        :href="route('email-manager.emails', ['category_id' => $category->id])"
                                        wire:navigate
                                        title="View"
                                    >
                                        View
                                    </flux:button>

                                    {{-- Download --}}
                                    <flux:button
                                        size="sm"
                                        variant="ghost"
                                        :href="route('email-manager.categories.download', $category)"
                                        title="Download"
                                    >
                                        Download
                                    </flux:button>

                                    {{-- Edit --}}
                                    <flux:button
                                        size="sm"
                                        variant="ghost"
                                        :href="route('email-manager.categories.edit', $category)"
                                        wire:navigate
                                        title="Edit"
                                    >
                                        Edit
                                    </flux:button>

                                    {{-- ✅ Delete via Flux modal trigger --}}
                                    <flux:modal.trigger name="delete-category">
                                        <flux:button
                                            size="sm"
                                            variant="ghost"
                                            type="button"
                                            wire:click="confirmDelete({{ $category->id }})"
                                            title="Delete"
                                        >
                                            Delete
                                        </flux:button>
                                    </flux:modal.trigger>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-4 py-8 text-center text-muted-foreground" colspan="4">
                                No lists found.
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

    {{-- ✅ Flux Delete Modal --}}
    <flux:modal name="delete-category" class="max-w-lg">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">Delete Category</flux:heading>
                <flux:subheading class="mt-1">
                    This action cannot be undone.
                </flux:subheading>
            </div>

            <div class="rounded-md border bg-white p-3 text-sm dark:bg-zinc-900">
                Are you sure you want to delete:
                <span class="font-semibold">{{ $deleteCategoryName ?: 'this category' }}</span>?
            </div>

            <div class="flex items-center justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost" type="button">
                        Cancel
                    </flux:button>
                </flux:modal.close>

                {{-- ✅ Confirm + Close modal --}}
                <flux:modal.close>
                    <flux:button
                        type="button"
                        wire:click="deleteConfirmed"
                        wire:loading.attr="disabled"
                        wire:target="deleteConfirmed"
                    >
                        <span wire:loading.remove wire:target="deleteConfirmed">Delete</span>
                        <span wire:loading wire:target="deleteConfirmed">Deleting...</span>
                    </flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>
</div>