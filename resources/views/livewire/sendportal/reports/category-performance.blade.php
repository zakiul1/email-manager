<div class="space-y-6 p-6">
    <section class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-zinc-950 dark:text-white">Category Performance</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Category-based send performance using your canonical category and email tables.</p>
        </div>

        <a
            href="{{ route('sendportal.workspace.reports.index') }}"
            wire:navigate
            class="border border-zinc-300 px-4 py-2.5 text-sm font-medium text-zinc-900 dark:border-zinc-700 dark:text-white"
        >
            Back to Reports
        </a>
    </section>

    <section class="grid gap-4 md:grid-cols-4">
        <div class="border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Categories</div>
            <div class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white">{{ number_format($summary['categories']) }}</div>
        </div>
        <div class="border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Messages</div>
            <div class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white">{{ number_format($summary['messages']) }}</div>
        </div>
        <div class="border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Sent</div>
            <div class="mt-2 text-2xl font-semibold text-emerald-600">{{ number_format($summary['sent']) }}</div>
        </div>
        <div class="border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Failed</div>
            <div class="mt-2 text-2xl font-semibold text-red-600">{{ number_format($summary['failed']) }}</div>
        </div>
    </section>

    <section class="border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="overflow-hidden border border-zinc-200 dark:border-zinc-700">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/60">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Category</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Messages</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Sent</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Failed</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900">
                        @forelse ($rows as $row)
                            <tr>
                                <td class="px-4 py-3 text-sm text-zinc-900 dark:text-white">{{ $row->name }}</td>
                                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">{{ number_format((int) $row->total_messages) }}</td>
                                <td class="px-4 py-3 text-sm text-emerald-600">{{ number_format((int) $row->sent_messages) }}</td>
                                <td class="px-4 py-3 text-sm text-red-600">{{ number_format((int) $row->failed_messages) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-10 text-center text-sm text-zinc-500 dark:text-zinc-400">No category performance data yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>