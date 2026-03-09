<div class="space-y-6 p-6">
    <section class="mx-auto max-w-3xl rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold tracking-tight text-zinc-950 dark:text-white">Test Send Template</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                Send a test version of <span class="font-medium">{{ $template->name }}</span> to a real inbox before using it in campaigns.
            </p>
        </div>

        <div class="mb-6 rounded-2xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/60">
            <div class="text-sm font-medium text-zinc-900 dark:text-white">{{ $template->subject }}</div>
            @if ($template->preheader)
                <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ $template->preheader }}</div>
            @endif
            <div class="mt-3 text-xs text-zinc-500 dark:text-zinc-400">
                Status: {{ ucfirst($template->status) }} · Last test:
                {{ $template->last_test_sent_at?->diffForHumans() ?? 'Never' }}
            </div>
        </div>

        <form wire:submit="send" class="space-y-6">
            <div>
                <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Recipient Email</label>
                <input
                    type="email"
                    wire:model="recipient_email"
                    placeholder="name@example.com"
                    class="w-full rounded-2xl border border-zinc-300 bg-white px-4 py-2.5 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                >
                @error('recipient_email')
                    <div class="mt-1 text-sm text-red-600">{{ $message }}</div>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-3">
                <a
                    href="{{ route('sendportal.workspace.templates.index') }}"
                    wire:navigate
                    class="inline-flex items-center justify-center rounded-2xl border border-zinc-300 px-4 py-2.5 text-sm font-medium text-zinc-900 transition hover:bg-zinc-50 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="inline-flex items-center justify-center rounded-2xl bg-zinc-900 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-zinc-700 disabled:opacity-50 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200"
                    wire:loading.attr="disabled"
                    wire:target="send"
                >
                    <span wire:loading.remove wire:target="send">Send Test Email</span>
                    <span wire:loading wire:target="send">Sending...</span>
                </button>
            </div>
        </form>
    </section>
</div>