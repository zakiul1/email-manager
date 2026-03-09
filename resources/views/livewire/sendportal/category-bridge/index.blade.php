<div class="space-y-6 p-6">
    <section class="border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-zinc-950 dark:text-white">Category Bridge</h1>
                <p class="mt-1 max-w-3xl text-sm text-zinc-600 dark:text-zinc-300">
                    Sync your existing Email Manager categories into SendPortal-native subscribers and category sync stats. Suppressed emails stay marked as suppressed and are included in sync counts.
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search category..."
                    class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm text-zinc-900 outline-none ring-0 placeholder:text-zinc-400 focus:border-zinc-400 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                />

                <button
                    type="button"
                    wire:click="syncEnabled"
                    class="inline-flex items-center justify-center bg-zinc-900 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-zinc-700 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200"
                >
                    Sync Enabled
                </button>
            </div>
        </div>
    </section>

    <section class="border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="overflow-hidden border border-zinc-200 dark:border-zinc-700">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/60">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Category</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Emails</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Sync</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Last Sync</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-zinc-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900">
                        @forelse ($categories as $category)
                            @php($link = $category->sendPortalTagLink)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="text-sm font-medium text-zinc-900 dark:text-white">{{ $category->name }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">
                                    {{ number_format($category->email_addresses_count ?? 0) }}
                                </td>
                                <td class="px-4 py-3">
                                    <button
                                        type="button"
                                        wire:click="toggleSync({{ $category->id }})"
                                        class="inline-flex px-3 py-1 text-xs font-medium {{ $link?->sync_enabled !== false ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-200' : 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200' }}"
                                    >
                                        {{ $link?->sync_enabled !== false ? 'Enabled' : 'Disabled' }}
                                    </button>
                                </td>
                                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">
                                    @if ($link?->last_synced_at)
                                        <div>{{ $link->last_synced_at->diffForHumans() }}</div>
                                        <div class="text-xs text-zinc-400">
                                            Total {{ $link->last_synced_total }},
                                            Active {{ $link->last_synced_subscribed }},
                                            Suppressed {{ $link->last_synced_suppressed }}
                                        </div>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <button
                                        type="button"
                                        wire:click="syncCategory({{ $category->id }})"
                                        class="inline-flex items-center border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-900 transition hover:bg-zinc-50 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800"
                                    >
                                        Sync Now
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-sm text-zinc-500 dark:text-zinc-400">No categories found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-5">
            {{ $categories->links() }}
        </div>
    </section>
</div>