<div class="space-y-6 p-6">
    <section class="grid gap-4 md:grid-cols-4">
        <div class="border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Total Campaigns</div>
            <div class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white">{{ number_format($stats['total']) }}</div>
        </div>
        <div class="border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Draft</div>
            <div class="mt-2 text-2xl font-semibold text-amber-600">{{ number_format($stats['draft']) }}</div>
        </div>
        <div class="border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Scheduled</div>
            <div class="mt-2 text-2xl font-semibold text-sky-600">{{ number_format($stats['scheduled']) }}</div>
        </div>
        <div class="border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Active</div>
            <div class="mt-2 text-2xl font-semibold text-emerald-600">{{ number_format($stats['active']) }}</div>
        </div>
    </section>

    <section class="border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-zinc-950 dark:text-white">Campaigns</h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                    Create campaigns, choose audience sources, preview content, and prepare scheduling without sending yet.
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search campaigns..."
                    class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm text-zinc-900 outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                />

                <select
                    wire:model.live="status"
                    class="border border-zinc-300 bg-white px-4 py-2.5 text-sm text-zinc-900 outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                >
                    <option value="">All Status</option>
                    <option value="draft">Draft</option>
                    <option value="scheduled">Scheduled</option>
                    <option value="active">Active</option>
                    <option value="paused">Paused</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                    <option value="failed">Failed</option>
                </select>

                <a
                    href="{{ route('sendportal.workspace.campaigns.create') }}"
                    wire:navigate
                    class="inline-flex items-center justify-center bg-zinc-900 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-zinc-700 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200"
                >
                    Create Campaign
                </a>
            </div>
        </div>

        <div class="mt-6 overflow-hidden border border-zinc-200 dark:border-zinc-700">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/60">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Campaign</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Audience</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Template</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Pool</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Schedule</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-zinc-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900">
                        @forelse ($campaigns as $campaign)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="text-sm font-medium text-zinc-900 dark:text-white">{{ $campaign->name }}</div>
                                    <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ $campaign->subject }}</div>
                                </td>

                                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">
                                    {{ ucfirst($campaign->audience_type ?? '—') }} · {{ number_format($campaign->audiences_count) }}
                                </td>

                                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">
                                    {{ $campaign->template?->name ?? '—' }}
                                </td>

                                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">
                                    {{ $campaign->smtpPool?->name ?? '—' }}
                                </td>

                                <td class="px-4 py-3">
                                    <span class="inline-flex px-3 py-1 text-xs font-medium bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                                        {{ ucfirst($campaign->status) }}
                                    </span>
                                </td>

                                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">
                                    {{ $campaign->scheduled_at?->format('Y-m-d H:i') ?? '—' }}
                                </td>

                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <a
                                            href="{{ route('sendportal.workspace.campaigns.show', $campaign) }}"
                                            wire:navigate
                                            class="border border-zinc-300 px-3 py-2 text-xs font-medium text-zinc-900 hover:bg-zinc-50 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800"
                                        >
                                            View
                                        </a>

                                        <a
                                            href="{{ route('sendportal.workspace.campaigns.preview', $campaign) }}"
                                            wire:navigate
                                            class="border border-sky-300 px-3 py-2 text-xs font-medium text-sky-700 hover:bg-sky-50 dark:border-sky-800 dark:text-sky-300 dark:hover:bg-sky-950"
                                        >
                                            Preview
                                        </a>

                                        <button
                                            type="button"
                                            wire:click="duplicateCampaign({{ $campaign->id }})"
                                            class="border border-zinc-300 px-3 py-2 text-xs font-medium text-zinc-900 hover:bg-zinc-50 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800"
                                        >
                                            Duplicate
                                        </button>

                                        <a
                                            href="{{ route('sendportal.workspace.campaigns.edit', $campaign) }}"
                                            wire:navigate
                                            class="border border-zinc-300 px-3 py-2 text-xs font-medium text-zinc-900 hover:bg-zinc-50 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800"
                                        >
                                            Edit
                                        </a>

                                        <button
                                            type="button"
                                            wire:click="confirmDelete({{ $campaign->id }}, {{ \Illuminate\Support\Js::from($campaign->name) }})"
                                            x-on:click="$flux.modal('delete-campaign').show()"
                                            class="border border-red-300 px-3 py-2 text-xs font-medium text-red-700 hover:bg-red-50 dark:border-red-800 dark:text-red-300 dark:hover:bg-red-950"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-10 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                    No campaigns found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-5">
            {{ $campaigns->links() }}
        </div>
    </section>

    @include('livewire.sendportal.partials.confirm-delete-modal', [
        'modalName' => 'delete-campaign',
        'title' => 'Delete Campaign',
        'message' => 'Are you sure you want to delete this campaign?',
        'itemName' => $deleteName ?? null,
        'warning' => 'This action cannot be undone.',
        'confirmAction' => 'deleteConfirmed',
        'confirmTarget' => 'deleteConfirmed',
        'confirmText' => 'Delete Campaign',
        'loadingText' => 'Deleting...',
    ])
</div>