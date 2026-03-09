<div class="space-y-6 p-6">
    <section class="grid gap-4 md:grid-cols-4">
        <div class="rounded-3xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Campaigns</div>
            <div class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white">{{ number_format($campaignStats['total']) }}</div>
        </div>
        <div class="rounded-3xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Sent Messages</div>
            <div class="mt-2 text-2xl font-semibold text-emerald-600">{{ number_format($campaignStats['sent_messages']) }}</div>
        </div>
        <div class="rounded-3xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Failed Messages</div>
            <div class="mt-2 text-2xl font-semibold text-red-600">{{ number_format($campaignStats['failed_messages']) }}</div>
        </div>
        <div class="rounded-3xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Pending / Queued</div>
            <div class="mt-2 text-2xl font-semibold text-amber-600">{{ number_format($campaignStats['pending_messages']) }}</div>
        </div>
    </section>

    <section class="grid gap-4 md:grid-cols-3">
        <div class="rounded-3xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Cooling Down</div>
            <div class="mt-2 text-2xl font-semibold text-amber-600">{{ number_format($healthStats['cooling_down']) }}</div>
        </div>
        <div class="rounded-3xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Failing</div>
            <div class="mt-2 text-2xl font-semibold text-red-600">{{ number_format($healthStats['failing']) }}</div>
        </div>
        <div class="rounded-3xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Paused</div>
            <div class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white">{{ number_format($healthStats['paused']) }}</div>
        </div>
    </section>

    <section class="grid gap-4 md:grid-cols-2">
        <a
            href="{{ route('sendportal.workspace.reports.category-performance') }}"
            wire:navigate
            class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm transition hover:border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-zinc-600"
        >
            <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Category Performance</h2>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                View send totals and failures grouped by your canonical categories.
            </p>
        </a>

        <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Campaign Detail Reports</h2>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                Open a recent campaign below to inspect recipient-level delivery rows and export CSV.
            </p>
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-2">
        <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Recent Campaigns</h2>

            <div class="mt-4 overflow-hidden rounded-2xl border border-zinc-200 dark:border-zinc-700">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800/60">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Campaign</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Sent</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Failed</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900">
                            @forelse ($campaigns as $campaign)
                                <tr>
                                    <td class="px-4 py-3 text-sm">
                                        <a
                                            href="{{ route('sendportal.workspace.reports.campaign-detail', $campaign) }}"
                                            wire:navigate
                                            class="font-medium text-zinc-900 hover:underline dark:text-white"
                                        >
                                            {{ $campaign->name }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">{{ ucfirst($campaign->status) }}</td>
                                    <td class="px-4 py-3 text-sm text-emerald-600">{{ number_format($campaign->sent_count) }}</td>
                                    <td class="px-4 py-3 text-sm text-red-600">{{ number_format($campaign->failed_count) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-10 text-center text-sm text-zinc-500 dark:text-zinc-400">No campaign data yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">SMTP Account Usage</h2>

            <div class="mt-4 overflow-hidden rounded-2xl border border-zinc-200 dark:border-zinc-700">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800/60">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Account</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Sent Today</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Health</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Cooldown</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Last Used</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900">
                            @forelse ($smtpAccounts as $account)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-zinc-900 dark:text-white">{{ $account->name }}</td>
                                    <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">{{ ucfirst($account->status->value) }}</td>
                                    <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">{{ number_format((int) ($account->meta['sent_today'] ?? 0)) }}</td>
                                    <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">
                                        <div>Success: {{ number_format((int) $account->success_count) }}</div>
                                        <div>Fail: {{ number_format((int) $account->failure_count) }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">{{ $account->cooldown_until?->diffForHumans() ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">{{ $account->last_used_at?->diffForHumans() ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-10 text-center text-sm text-zinc-500 dark:text-zinc-400">No SMTP account data yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>