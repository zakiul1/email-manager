<div class="space-y-6 p-6">
    <section class="grid gap-4 md:grid-cols-4">
        <div class="border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Total Pools</div>
            <div class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white">{{ number_format($stats['total']) }}</div>
        </div>
        <div class="border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Active</div>
            <div class="mt-2 text-2xl font-semibold text-emerald-600">{{ number_format($stats['active']) }}</div>
        </div>
        <div class="border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Inactive</div>
            <div class="mt-2 text-2xl font-semibold text-amber-600">{{ number_format($stats['inactive']) }}</div>
        </div>
        <div class="border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Linked Accounts</div>
            <div class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white">{{ number_format($stats['accounts_linked']) }}</div>
        </div>
    </section>

    <section class="border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-zinc-950 dark:text-white">SMTP Pools</h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Group SMTP accounts and prepare them for weighted and randomized delivery strategies.</p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search pools..."
                    class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm text-zinc-900 outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                />

                <a
                    href="{{ route('sendportal.workspace.smtp-pools.create') }}"
                    wire:navigate
                    class="inline-flex items-center justify-center bg-zinc-900 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-zinc-700 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200"
                >
                    Create Pool
                </a>
            </div>
        </div>

        <div class="mt-6 overflow-hidden border border-zinc-200 dark:border-zinc-700">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/60">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Pool</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Strategy</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Accounts</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-zinc-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900">
                        @forelse ($pools as $pool)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="text-sm font-medium text-zinc-900 dark:text-white">{{ $pool->name }}</div>
                                    <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ $pool->notes ?: 'No notes' }}</div>
                                </td>

                                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">
                                    {{ str($pool->strategy->value)->replace('_', ' ')->title() }}
                                </td>

                                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">
                                    {{ number_format($pool->accounts_count) }}
                                </td>

                                <td class="px-4 py-3">
                                    <span class="inline-flex px-3 py-1 text-xs font-medium {{ $pool->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-200' : 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200' }}">
                                        {{ $pool->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>

                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <button
                                            type="button"
                                            wire:click="toggleStatus({{ $pool->id }})"
                                            class="border border-zinc-300 px-3 py-2 text-xs font-medium text-zinc-900 hover:bg-zinc-50 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800"
                                        >
                                            {{ $pool->is_active ? 'Disable' : 'Enable' }}
                                        </button>

                                        <a
                                            href="{{ route('sendportal.workspace.smtp-pools.edit', $pool) }}"
                                            wire:navigate
                                            class="border border-zinc-300 px-3 py-2 text-xs font-medium text-zinc-900 hover:bg-zinc-50 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800"
                                        >
                                            Edit
                                        </a>

                                        <button
                                            type="button"
                                            wire:click="confirmDelete({{ $pool->id }}, {{ \Illuminate\Support\Js::from($pool->name) }})"
                                            x-on:click="$flux.modal('delete-smtp-pool').show()"
                                            class="border border-red-300 px-3 py-2 text-xs font-medium text-red-700 hover:bg-red-50 dark:border-red-800 dark:text-red-300 dark:hover:bg-red-950"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-sm text-zinc-500 dark:text-zinc-400">No SMTP pools found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-5">
            {{ $pools->links() }}
        </div>
    </section>

    @include('livewire.sendportal.partials.confirm-delete-modal', [
        'modalName' => 'delete-smtp-pool',
        'title' => 'Delete SMTP Pool',
        'message' => 'Are you sure you want to delete this SMTP pool?',
        'itemName' => $deleteName ?? null,
        'warning' => 'This action cannot be undone.',
        'confirmAction' => 'deleteConfirmed',
        'confirmTarget' => 'deleteConfirmed',
        'confirmText' => 'Delete Pool',
        'loadingText' => 'Deleting...',
    ])
</div>