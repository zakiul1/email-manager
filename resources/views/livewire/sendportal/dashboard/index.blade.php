<div class="space-y-6 p-6">
    <section class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="space-y-3">
                <div class="inline-flex items-center rounded-full border border-violet-200 bg-violet-50 px-3 py-1 text-xs font-medium text-violet-700 dark:border-violet-800 dark:bg-violet-950 dark:text-violet-200">
                    Phase 2 native foundation
                </div>
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-zinc-950 dark:text-white">SendPortal workspace</h1>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-zinc-600 dark:text-zinc-300">
                        This workspace now follows a native Laravel 12 implementation path. It reuses your app auth, layout, categories, suppression rules, and queue patterns while rebuilding SendPortal-style modules in your project architecture.
                    </p>
                </div>
            </div>

           <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-7">
    <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/80">
        <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Campaigns</div>
        <div class="mt-2 text-lg font-semibold text-zinc-950 dark:text-white">{{ $stats['campaigns'] }}</div>
    </div>
    <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/80">
        <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Subscribers</div>
        <div class="mt-2 text-lg font-semibold text-zinc-950 dark:text-white">{{ $stats['subscribers'] }}</div>
    </div>
    <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/80">
        <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Tags</div>
        <div class="mt-2 text-lg font-semibold text-zinc-950 dark:text-white">{{ $stats['tags'] }}</div>
    </div>
    <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/80">
        <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Category Links</div>
        <div class="mt-2 text-lg font-semibold text-zinc-950 dark:text-white">{{ $stats['category_links'] }}</div>
    </div>
    <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/80">
        <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Templates</div>
        <div class="mt-2 text-lg font-semibold text-zinc-950 dark:text-white">{{ $stats['templates'] }}</div>
    </div>
    <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/80">
        <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">SMTP Accounts</div>
        <div class="mt-2 text-lg font-semibold text-zinc-950 dark:text-white">{{ $stats['smtp_accounts'] }}</div>
    </div>
    <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/80">
        <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">SMTP Pools</div>
        <div class="mt-2 text-lg font-semibold text-zinc-950 dark:text-white">{{ $stats['smtp_pools'] }}</div>
    </div>
</div>
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-[1.4fr_1fr]">
        <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Phase 2 deliverables</h2>
            <div class="mt-6 space-y-4">
                @foreach ($this->phaseChecklist as $item)
                    <div class="flex items-start justify-between gap-4 rounded-2xl border border-zinc-200 p-4 dark:border-zinc-700">
                        <div>
                            <div class="text-sm font-medium text-zinc-950 dark:text-white">{{ $item['phase'] }} · {{ $item['title'] }}</div>
                        </div>
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium {{ $item['status'] === 'Complete' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200' : ($item['status'] === 'In progress' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-200' : 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200') }}">
                            {{ $item['status'] }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Current focus</h2>
                <ul class="mt-4 space-y-3 text-sm text-zinc-600 dark:text-zinc-300">
                    <li>• Native database schema for subscribers, tags, templates, campaigns, and email services.</li>
                    <li>• Reusable models aligned with your existing codebase style.</li>
                    <li>• Clean base for category bridge and SMTP pool features in later phases.</li>
                </ul>
            </div>

            <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Next phase focus</h2>
                <ul class="mt-4 space-y-3 text-sm text-zinc-600 dark:text-zinc-300">
                    <li>• Category ↔ subscriber bridge.</li>
                    <li>• Tag syncing and audience selection.</li>
                    <li>• Subscriber creation and import mapping.</li>
                </ul>
            </div>
        </div>
    </section>
</div>