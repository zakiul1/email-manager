<div class="p-6 space-y-6">
    {{-- Header --}}
    <div class="flex items-start justify-between gap-4">
        <div>
            <flux:heading size="lg">Category List & Emails</flux:heading>
            <flux:subheading class="mt-1">
                All lists in this account
            </flux:subheading>
        </div>

        <flux:button :href="route('email-manager.categories.create')" wire:navigate>
            Create A Category
        </flux:button>
    </div>

    {{-- Top summary cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <flux:card>
            <div class="flex items-center justify-between gap-4">
                <div>
                    <div class="text-sm text-muted-foreground">Categories</div>
                    <div class="mt-1 text-2xl font-semibold">{{ number_format($totalCategories ?? 0) }}</div>
                </div>
                <div class="h-12 w-12 rounded-xl bg-muted"></div>
            </div>
        </flux:card>

        <flux:card>
            <div class="flex items-center justify-between gap-4">
                <div>
                    <div class="text-sm text-muted-foreground">Active Emails</div>
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
                        <th class="px-4 py-3 font-medium">CATEGORY NAME</th>
                        <th class="px-4 py-3 font-medium">EMAILS COUNT</th>
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
                                    {{-- View --}}
                                    <flux:button size="sm" variant="ghost"
                                        :href="route('email-manager.emails', ['category_id' => $category->id])"
                                        wire:navigate title="View" class="cursor-pointer">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </flux:button>

                                    {{-- Download --}}
                                    <flux:button size="sm" variant="ghost"
                                        :href="route('email-manager.categories.download', $category)" title="Download"
                                        class="cursor-pointer">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                            <polyline points="7 10 12 15 17 10"></polyline>
                                            <line x1="12" x2="12" y1="15" y2="3"></line>
                                        </svg>
                                    </flux:button>

                                    {{-- Edit --}}
                                    <flux:button size="sm" variant="ghost"
                                        :href="route('email-manager.categories.edit', $category)" wire:navigate
                                        title="Edit" class="cursor-pointer">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 20h9"></path>
                                            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"></path>
                                        </svg>
                                    </flux:button>

                                    {{-- Delete (opens modal) --}}
                                    <flux:modal.trigger name="delete-category">
                                        <flux:button size="sm" variant="ghost" type="button"
                                            wire:click="confirmDelete({{ $category->id }})" title="Delete"
                                            class="cursor-pointer">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M3 6h18"></path>
                                                <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path>
                                                <path d="M10 11v6"></path>
                                                <path d="M14 11v6"></path>
                                            </svg>
                                        </flux:button>
                                    </flux:modal.trigger>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-4 py-8 text-center text-muted-foreground" colspan="4">
                                No Category found.
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

    {{-- ✅ Flux Delete Modal (Option B - Danger) --}}
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

            {{-- 🔥 Hard warning (Option B Danger) --}}
            <div class="rounded-md border border-red-300 bg-red-50 p-3 text-sm text-red-800">
                <div class="font-semibold">Danger</div>
                <div class="mt-1">
                    This will:
                    <ul class="mt-2 list-disc pl-5">
                        <li>Remove all emails from this category</li>
                        <li>Delete this category permanently</li>
                        <li><span class="font-semibold">Permanently delete</span> any email addresses that are not used
                            in any other category</li>
                    </ul>
                </div>
                <div class="mt-2 font-medium">
                    Type <span class="px-1 rounded border bg-white text-red-900">DELETE</span> to confirm.
                </div>
            </div>

            {{-- Confirm input --}}
            <div class="space-y-2">
                <label class="text-sm font-medium">Confirm</label>
                <input type="text" class="w-full rounded-md border px-3 py-2 text-sm"
                    wire:model.live="deleteConfirmText" placeholder="Type DELETE" autocomplete="off" />
            </div>

            <div class="flex items-center justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost" type="button">
                        Cancel
                    </flux:button>
                </flux:modal.close>

                {{-- ✅ Confirm + Close modal --}}
                <flux:modal.close>
                    <flux:button type="button" wire:click="deleteConfirmed" wire:loading.attr="disabled"
                        wire:target="deleteConfirmed" :disabled="trim($deleteConfirmText ?? '') !== 'DELETE'"
                        class="cursor-pointer" title="Delete">
                        <span wire:loading.remove wire:target="deleteConfirmed">Delete</span>
                        <span wire:loading wire:target="deleteConfirmed">Deleting...</span>
                    </flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>
</div>
