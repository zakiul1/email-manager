<div x-data="{ activeTab: 'preview' }" class="space-y-6 p-6">
    <section class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-zinc-950 dark:text-white">Campaign Preview</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">{{ $campaign->name }}</p>
        </div>

        <div class="flex flex-wrap gap-3">
            <a
                href="{{ route('sendportal.workspace.campaigns.index') }}"
                wire:navigate
                class="inline-flex items-center justify-center border border-zinc-300 px-4 py-2.5 text-sm font-medium text-zinc-900 transition hover:bg-zinc-50 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800"
            >
                Back
            </a>

            <button
                type="button"
                wire:click="setDevice('desktop')"
                class="px-4 py-2.5 text-sm font-medium {{ $device === 'desktop' ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'border border-zinc-300 text-zinc-900 dark:border-zinc-700 dark:text-white' }}"
            >
                Desktop
            </button>

            <button
                type="button"
                wire:click="setDevice('mobile')"
                class="px-4 py-2.5 text-sm font-medium {{ $device === 'mobile' ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'border border-zinc-300 text-zinc-900 dark:border-zinc-700 dark:text-white' }}"
            >
                Mobile
            </button>
        </div>
    </section>

    <section class="border-b border-zinc-200 dark:border-zinc-700">
        <nav class="flex flex-wrap gap-2">
            <button
                type="button"
                @click="activeTab = 'preview'"
                :class="activeTab === 'preview'
                    ? 'border-zinc-900 bg-zinc-900 text-white dark:border-white dark:bg-white dark:text-zinc-900'
                    : 'border-zinc-300 bg-white text-zinc-700 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200'"
                class="border px-4 py-2 text-sm font-medium"
            >
                Preview
            </button>

            <button
                type="button"
                @click="activeTab = 'meta'"
                :class="activeTab === 'meta'
                    ? 'border-zinc-900 bg-zinc-900 text-white dark:border-white dark:bg-white dark:text-zinc-900'
                    : 'border-zinc-300 bg-white text-zinc-700 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200'"
                class="border px-4 py-2 text-sm font-medium"
            >
                Campaign Meta
            </button>

            <button
                type="button"
                @click="activeTab = 'sample-data'"
                :class="activeTab === 'sample-data'
                    ? 'border-zinc-900 bg-zinc-900 text-white dark:border-white dark:bg-white dark:text-zinc-900'
                    : 'border-zinc-300 bg-white text-zinc-700 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200'"
                class="border px-4 py-2 text-sm font-medium"
            >
                Sample Data
            </button>

            <button
                type="button"
                @click="activeTab = 'html-source'"
                :class="activeTab === 'html-source'
                    ? 'border-zinc-900 bg-zinc-900 text-white dark:border-white dark:bg-white dark:text-zinc-900'
                    : 'border-zinc-300 bg-white text-zinc-700 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200'"
                class="border px-4 py-2 text-sm font-medium"
            >
                HTML Source
            </button>
        </nav>
    </section>

    <section x-show="activeTab === 'preview'" x-cloak class="border border-zinc-200 bg-zinc-100 p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-950">
        <div class="{{ $device === 'mobile' ? 'mx-auto max-w-[420px]' : 'mx-auto w-full max-w-[1200px]' }}">
            <div class="overflow-hidden border border-zinc-200 bg-white shadow-sm dark:border-zinc-700">
                <div class="border-b border-zinc-200 px-5 py-4 dark:border-zinc-700">
                    <div class="text-sm font-medium text-zinc-900 dark:text-white">{{ $campaign->subject }}</div>
                    @if ($campaign->preheader)
                        <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ $campaign->preheader }}</div>
                    @endif
                </div>

                <div class="p-4">
                    {!! $previewHtml !!}
                </div>
            </div>
        </div>
    </section>

    <section x-show="activeTab === 'meta'" x-cloak class="border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Campaign Meta</h2>

        <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-4 text-sm text-zinc-600 dark:text-zinc-300">
            <div class="border border-zinc-200 p-4 dark:border-zinc-700">
                <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Status</div>
                <div class="mt-2 font-medium text-zinc-900 dark:text-white">{{ ucfirst($campaign->status) }}</div>
            </div>

            <div class="border border-zinc-200 p-4 dark:border-zinc-700">
                <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Delivery Mode</div>
                <div class="mt-2 font-medium text-zinc-900 dark:text-white">{{ ucfirst($campaign->delivery_mode) }}</div>
            </div>

            <div class="border border-zinc-200 p-4 dark:border-zinc-700">
                <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Audience Type</div>
                <div class="mt-2 font-medium text-zinc-900 dark:text-white">{{ ucfirst($campaign->audience_type ?? '—') }}</div>
            </div>

            <div class="border border-zinc-200 p-4 dark:border-zinc-700">
                <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Template</div>
                <div class="mt-2 font-medium text-zinc-900 dark:text-white">{{ $campaign->template?->name ?? 'Custom body' }}</div>
            </div>
        </div>
    </section>

    <section x-show="activeTab === 'sample-data'" x-cloak class="border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Sample Data</h2>

        <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($sampleValues as $key => $value)
                <div class="border border-zinc-200 p-3 dark:border-zinc-700">
                    <div class="font-mono text-sm text-zinc-900 dark:text-white">{{ $key }}</div>
                    <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ $value }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <section x-show="activeTab === 'html-source'" x-cloak class="border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">HTML Source</h2>

        <pre class="mt-4 overflow-x-auto border border-zinc-200 bg-zinc-100 p-4 text-xs text-zinc-800 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-200"><code>{{ $campaign->html_content ?: $campaign->template?->html_content }}</code></pre>
    </section>
</div>