<div class="space-y-6 p-6">
    <section class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-zinc-950 dark:text-white">{{ $campaign->name }}</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">{{ $campaign->subject }}</p>
        </div>

     <div class="flex flex-wrap gap-3">
    <a href="{{ route('sendportal.workspace.campaigns.preview', $campaign) }}" wire:navigate class="rounded-2xl border border-sky-300 px-4 py-2.5 text-sm font-medium text-sky-700 dark:border-sky-800 dark:text-sky-300">Preview</a>
    <a href="{{ route('sendportal.workspace.campaigns.audience', $campaign) }}" wire:navigate class="rounded-2xl border border-violet-300 px-4 py-2.5 text-sm font-medium text-violet-700 dark:border-violet-800 dark:text-violet-300">Audience</a>
    <button type="button" wire:click="dispatchCampaign" class="rounded-2xl border border-emerald-300 px-4 py-2.5 text-sm font-medium text-emerald-700 dark:border-emerald-800 dark:text-emerald-300">Dispatch</button>
    <button type="button" wire:click="duplicate" class="rounded-2xl border border-zinc-300 px-4 py-2.5 text-sm font-medium text-zinc-900 dark:border-zinc-700 dark:text-white">Duplicate</button>
    <button type="button" wire:click="setStatus('paused')" class="rounded-2xl border border-amber-300 px-4 py-2.5 text-sm font-medium text-amber-700 dark:border-amber-800 dark:text-amber-300">Pause</button>
    <button type="button" wire:click="setStatus('active')" class="rounded-2xl border border-emerald-300 px-4 py-2.5 text-sm font-medium text-emerald-700 dark:border-emerald-800 dark:text-emerald-300">Activate</button>
    <button type="button" wire:click="setStatus('cancelled')" class="rounded-2xl border border-red-300 px-4 py-2.5 text-sm font-medium text-red-700 dark:border-red-800 dark:text-red-300">Cancel</button>
    <a href="{{ route('sendportal.workspace.campaigns.edit', $campaign) }}" wire:navigate class="rounded-2xl bg-zinc-900 px-4 py-2.5 text-sm font-medium text-white dark:bg-white dark:text-zinc-900">Edit</a>
</div>
    </section>

    <section class="grid gap-6 xl:grid-cols-[1.3fr_1fr]">
        <div class="space-y-6">
            <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Campaign details</h2>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <div>
                        <div class="text-xs uppercase tracking-wide text-zinc-500">Status</div>
                        <div class="mt-1 text-sm text-zinc-900 dark:text-white">{{ ucfirst($campaign->status) }}</div>
                    </div>
                    <div>
                        <div class="text-xs uppercase tracking-wide text-zinc-500">Delivery Mode</div>
                        <div class="mt-1 text-sm text-zinc-900 dark:text-white">{{ ucfirst($campaign->delivery_mode) }}</div>
                    </div>
                    <div>
                        <div class="text-xs uppercase tracking-wide text-zinc-500">Template</div>
                        <div class="mt-1 text-sm text-zinc-900 dark:text-white">{{ $campaign->template?->name ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="text-xs uppercase tracking-wide text-zinc-500">SMTP Pool</div>
                        <div class="mt-1 text-sm text-zinc-900 dark:text-white">{{ $campaign->smtpPool?->name ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="text-xs uppercase tracking-wide text-zinc-500">Scheduled</div>
                        <div class="mt-1 text-sm text-zinc-900 dark:text-white">{{ $campaign->scheduled_at?->format('Y-m-d H:i') ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="text-xs uppercase tracking-wide text-zinc-500">Prepared Messages</div>
                        <div class="mt-1 text-sm text-zinc-900 dark:text-white">{{ number_format($campaign->messages_count) }}</div>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Body preview</h2>
                <div class="mt-4 rounded-2xl border border-zinc-200 p-4 dark:border-zinc-700">
                    <div class="text-sm font-medium text-zinc-900 dark:text-white">{{ $campaign->subject }}</div>
                    @if ($campaign->preheader)
                        <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ $campaign->preheader }}</div>
                    @endif
                    <div class="mt-4 prose prose-sm max-w-none dark:prose-invert">
                        {!! $campaign->html_content ?: nl2br(e($campaign->text_content ?: 'No campaign body saved.')) !!}
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Audience definition</h2>
                <div class="mt-4 space-y-3">
                    <div class="text-sm text-zinc-600 dark:text-zinc-300">
                        Type: <span class="font-medium text-zinc-900 dark:text-white">{{ ucfirst($campaign->audience_type ?? '—') }}</span>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        @forelse ($campaign->audiences as $audience)
                            <span class="inline-flex rounded-full border border-zinc-200 px-3 py-1 text-xs font-medium text-zinc-700 dark:border-zinc-700 dark:text-zinc-200">
                                {{ $audience->source_type }} #{{ $audience->source_id }}
                            </span>
                        @empty
                            <span class="text-sm text-zinc-500 dark:text-zinc-400">No audience source linked.</span>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Audience preparation</h2>
               <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">
    Use the Audience page to prepare message rows. Once prepared, Dispatch will queue individual send jobs for execution through the selected SMTP pool.
</p>
            </div>
        </div>
    </section>
</div>