<div class="space-y-6 p-6">
    <section class="grid gap-4 md:grid-cols-4">
        <div class="border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Total Accounts</div>
            <div class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white">{{ number_format($stats['total']) }}</div>
        </div>
        <div class="border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Active</div>
            <div class="mt-2 text-2xl font-semibold text-emerald-600">{{ number_format($stats['active']) }}</div>
        </div>
        <div class="border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Paused</div>
            <div class="mt-2 text-2xl font-semibold text-amber-600">{{ number_format($stats['paused']) }}</div>
        </div>
        <div class="border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Default</div>
            <div class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white">{{ number_format($stats['default']) }}</div>
        </div>
    </section>

    <section class="border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-zinc-950 dark:text-white">SMTP Accounts</h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Manage dedicated sender accounts with validation, encrypted credentials, and connection testing.</p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search accounts..."
                    class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm text-zinc-900 outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                />

                <select
                    wire:model.live="status"
                    class="border border-zinc-300 bg-white px-4 py-2.5 text-sm text-zinc-900 outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                >
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="paused">Paused</option>
                    <option value="failing">Failing</option>
                    <option value="disabled">Disabled</option>
                    <option value="testing">Testing</option>
                </select>

                <a
                    href="{{ route('sendportal.workspace.smtp-accounts.create') }}"
                    wire:navigate
                    class="inline-flex items-center justify-center bg-zinc-900 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-zinc-700 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200"
                >
                    Add Account
                </a>
            </div>
        </div>

        <div class="mt-6 overflow-hidden border border-zinc-200 dark:border-zinc-700">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/60">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Account</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Provider</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Host</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Limits</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Last Test</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-zinc-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900">
                        @forelse ($accounts as $account)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                        {{ $account->name }}
                                        @if ($account->is_default)
                                            <span class="ml-2 inline-flex bg-sky-100 px-2.5 py-1 text-xs font-medium text-sky-700 dark:bg-sky-950 dark:text-sky-200">Default</span>
                                        @endif
                                    </div>
                                    <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                        {{ $account->from_email ?: 'No from email' }}
                                    </div>
                                </td>

                                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">
                                    {{ $account->provider_label ?: 'Custom SMTP' }}
                                </td>

                                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">
                                    {{ $account->host ?: '—' }}@if($account->port) :{{ $account->port }} @endif
                                </td>

                                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">
                                    <div>Daily: {{ $account->daily_limit ?: '—' }}</div>
                                    <div>Hourly: {{ $account->hourly_limit ?: '—' }}</div>
                                </td>

                                <td class="px-4 py-3">
                                    <span class="inline-flex px-3 py-1 text-xs font-medium
                                        @if($account->status->value === 'active') bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-200
                                        @elseif($account->status->value === 'paused') bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-200
                                        @elseif($account->status->value === 'failing') bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-200
                                        @else bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 @endif">
                                        {{ ucfirst($account->status->value) }}
                                    </span>
                                </td>

                                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">
                                    @if ($account->last_tested_at)
                                        <div>{{ $account->last_tested_at->diffForHumans() }}</div>
                                        <div class="text-xs {{ $account->last_test_status === 'success' ? 'text-emerald-600' : 'text-red-600' }}">
                                            {{ $account->last_test_status ?? '—' }}
                                        </div>
                                    @else
                                        —
                                    @endif
                                </td>

                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <button
                                            type="button"
                                            wire:click="testConnection({{ $account->id }})"
                                            class="border border-zinc-300 px-3 py-2 text-xs font-medium text-zinc-900 hover:bg-zinc-50 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800"
                                        >
                                            Test
                                        </button>

                                        <button
                                            type="button"
                                            wire:click="toggleStatus({{ $account->id }})"
                                            class="border border-zinc-300 px-3 py-2 text-xs font-medium text-zinc-900 hover:bg-zinc-50 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800"
                                        >
                                            {{ $account->status->value === 'active' ? 'Pause' : 'Activate' }}
                                        </button>

                                        <a
                                            href="{{ route('sendportal.workspace.smtp-accounts.edit', $account) }}"
                                            wire:navigate
                                            class="border border-zinc-300 px-3 py-2 text-xs font-medium text-zinc-900 hover:bg-zinc-50 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800"
                                        >
                                            Edit
                                        </a>

                                        <button
                                            type="button"
                                            wire:click="confirmDelete({{ $account->id }}, {{ \Illuminate\Support\Js::from($account->name) }})"
                                            x-on:click="$flux.modal('delete-smtp-account').show()"
                                            class="border border-red-300 px-3 py-2 text-xs font-medium text-red-700 hover:bg-red-50 dark:border-red-800 dark:text-red-300 dark:hover:bg-red-950"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-10 text-center text-sm text-zinc-500 dark:text-zinc-400">No SMTP accounts found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-5">
            {{ $accounts->links() }}
        </div>
    </section>

    @include('livewire.sendportal.partials.confirm-delete-modal', [
        'modalName' => 'delete-smtp-account',
        'title' => 'Delete SMTP Account',
        'message' => 'Are you sure you want to delete this SMTP account?',
        'itemName' => $deleteName ?? null,
        'warning' => 'This action cannot be undone.',
        'confirmAction' => 'deleteConfirmed',
        'confirmTarget' => 'deleteConfirmed',
        'confirmText' => 'Delete Account',
        'loadingText' => 'Deleting...',
    ])
</div>