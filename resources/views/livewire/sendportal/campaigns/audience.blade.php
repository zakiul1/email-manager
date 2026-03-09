<div class="space-y-6 p-6">
    <section class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-zinc-950 dark:text-white">Campaign Audience</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">{{ $campaign->name }}</p>
        </div>

        <div class="flex flex-wrap gap-3">
            <a
                href="{{ route('sendportal.workspace.campaigns.show', $campaign) }}"
                wire:navigate
                class="rounded-2xl border border-zinc-300 px-4 py-2.5 text-sm font-medium text-zinc-900 dark:border-zinc-700 dark:text-white"
            >
                Back
            </a>

            <button
                type="button"
                wire:click="prepareMessages"
                class="rounded-2xl bg-zinc-900 px-4 py-2.5 text-sm font-medium text-white dark:bg-white dark:text-zinc-900"
            >
                Prepare Messages
            </button>
        </div>
    </section>

    <section class="grid gap-4 md:grid-cols-4">
        <div class="rounded-3xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Audience Total</div>
            <div class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white">{{ number_format($audienceStats['total']) }}</div>
        </div>
        <div class="rounded-3xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Active</div>
            <div class="mt-2 text-2xl font-semibold text-emerald-600">{{ number_format($audienceStats['active']) }}</div>
        </div>
        <div class="rounded-3xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Suppressed</div>
            <div class="mt-2 text-2xl font-semibold text-red-600">{{ number_format($audienceStats['suppressed']) }}</div>
        </div>
        <div class="rounded-3xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Prepared Messages</div>
            <div class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white">{{ number_format($campaign->recipient_count) }}</div>
        </div>
    </section>

    <section class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Prepared Messages</h2>

        <div class="mt-6 overflow-hidden rounded-2xl border border-zinc-200 dark:border-zinc-700">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/60">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Email</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Subject</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Created</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900">
                        @forelse ($messages as $message)
                            <tr>
                                <td class="px-4 py-3 text-sm text-zinc-900 dark:text-white">{{ $message->recipient_email }}</td>
                                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">{{ ucfirst($message->status) }}</td>
                                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">{{ $message->subject ?: '—' }}</td>
                                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">{{ $message->created_at?->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-10 text-center text-sm text-zinc-500 dark:text-zinc-400">No prepared messages yet.</td>
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