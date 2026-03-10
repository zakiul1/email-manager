<div class="space-y-6 p-6">
    <section class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-zinc-950 dark:text-white">Campaign Report</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">{{ $campaign->name }}</p>
        </div>

        <div class="flex flex-wrap gap-3">
            <button
                type="button"
                wire:click="export"
                class="border border-emerald-300 px-4 py-2.5 text-sm font-medium text-emerald-700 dark:border-emerald-800 dark:text-emerald-300"
            >
                Export CSV
            </button>

            <a
                href="{{ route('sendportal.workspace.reports.index') }}"
                wire:navigate
                class="border border-zinc-300 px-4 py-2.5 text-sm font-medium text-zinc-900 dark:border-zinc-700 dark:text-white"
            >
                Back to Reports
            </a>
        </div>
    </section>

    <section class="grid gap-4 md:grid-cols-4 xl:grid-cols-7">
        <div class="border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Total</div>
            <div class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white">{{ number_format($stats['total']) }}</div>
        </div>
        <div class="border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Sent</div>
            <div class="mt-2 text-2xl font-semibold text-emerald-600">{{ number_format($stats['sent']) }}</div>
        </div>
        <div class="border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Failed</div>
            <div class="mt-2 text-2xl font-semibold text-red-600">{{ number_format($stats['failed']) }}</div>
        </div>
        <div class="border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Pending / Queued</div>
            <div class="mt-2 text-2xl font-semibold text-amber-600">{{ number_format($stats['pending']) }}</div>
        </div>
        <div class="border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Opened</div>
            <div class="mt-2 text-2xl font-semibold text-sky-600">{{ number_format($stats['opened']) }}</div>
        </div>
        <div class="border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Clicked</div>
            <div class="mt-2 text-2xl font-semibold text-violet-600">{{ number_format($stats['clicked']) }}</div>
        </div>
        <div class="border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Unsubscribed</div>
            <div class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white">{{ number_format($stats['unsubscribed']) }}</div>
        </div>
    </section>

    <section class="border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex flex-col gap-3 lg:flex-row">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Search recipient or failure reason..."
                class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
            />

            <select
                wire:model.live="status"
                class="border border-zinc-300 bg-white px-4 py-2.5 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
            >
                <option value="">All Status</option>
                <option value="sent">Sent</option>
                <option value="failed">Failed</option>
                <option value="pending">Pending</option>
                <option value="queued">Queued</option>
            </select>
        </div>

        <div class="mt-6 overflow-hidden border border-zinc-200 dark:border-zinc-700">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/60">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Recipient</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">SMTP</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Attempts</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Opened</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Clicked</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Unsubscribed</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Sent</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Failure</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900">
                        @forelse ($messages as $message)
                            <tr>
                                <td class="px-4 py-3 text-sm text-zinc-900 dark:text-white">{{ $message->recipient_email }}</td>
                                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">{{ ucfirst($message->status) }}</td>
                                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">{{ $message->smtpAccount?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">{{ number_format((int) $message->attempt_count) }}</td>
                                <td class="px-4 py-3 text-sm text-sky-600">{{ number_format((int) $message->open_count) }}</td>
                                <td class="px-4 py-3 text-sm text-violet-600">{{ number_format((int) $message->click_count) }}</td>
                                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">{{ $message->unsubscribed_at?->diffForHumans() ?? '—' }}</td>
                                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">{{ $message->sent_at?->diffForHumans() ?? '—' }}</td>
                                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">{{ $message->failure_reason ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-10 text-center text-sm text-zinc-500 dark:text-zinc-400">No report rows found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-5">
            {{ $messages->links() }}
        </div>
    </section>
</div>