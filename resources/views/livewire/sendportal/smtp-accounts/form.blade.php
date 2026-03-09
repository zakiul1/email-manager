<div class="space-y-6 p-6">
    <section class="border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold tracking-tight text-zinc-950 dark:text-white">
                {{ $account?->exists ? 'Edit SMTP Account' : 'Create SMTP Account' }}
            </h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                Add secure SMTP credentials, sender settings, limits, and delivery defaults.
            </p>
        </div>

        <form wire:submit="save" class="space-y-6">
            <div class="grid gap-6 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Account Name</label>
                    <input type="text" wire:model="name" class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                    @error('name') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Provider Label</label>
                    <input type="text" wire:model="provider_label" class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                    @error('provider_label') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Driver Type</label>
                    <select wire:model="driver_type" class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                        <option value="smtp">SMTP</option>
                        <option value="laravel_mailer">Laravel Mailer</option>
                    </select>
                    @error('driver_type') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Mailer Name</label>
                    <input type="text" wire:model="mailer_name" class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                    @error('mailer_name') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Host</label>
                    <input type="text" wire:model="host" class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                    @error('host') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Port</label>
                    <input type="number" wire:model="port" class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                    @error('port') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Username</label>
                    <input type="text" wire:model="username" class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                    @error('username') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Encryption</label>
                    <select wire:model="encryption" class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                        <option value="">None</option>
                        <option value="tls">TLS</option>
                        <option value="ssl">SSL</option>
                    </select>
                    @error('encryption') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                </div>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Password</label>
                <input type="password" wire:model="password" class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Leave empty while editing to keep the existing encrypted password.</p>
                @error('password') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
            </div>

            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">From Name</label>
                    <input type="text" wire:model="from_name" class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                    @error('from_name') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">From Email</label>
                    <input type="email" wire:model="from_email" class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                    @error('from_email') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Reply-To Name</label>
                    <input type="text" wire:model="reply_to_name" class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                    @error('reply_to_name') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Reply-To Email</label>
                    <input type="email" wire:model="reply_to_email" class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                    @error('reply_to_email') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Daily Limit</label>
                    <input type="number" wire:model="daily_limit" class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                    @error('daily_limit') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Hourly Limit</label>
                    <input type="number" wire:model="hourly_limit" class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                    @error('hourly_limit') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Warmup Limit</label>
                    <input type="number" wire:model="warmup_limit" class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                    @error('warmup_limit') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Priority</label>
                    <input type="number" wire:model="priority" class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                    @error('priority') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Status</label>
                    <select wire:model="status" class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                        @foreach ($statusOptions as $option)
                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                    @error('status') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                </div>

                <div class="flex items-end">
                    <label class="inline-flex items-center gap-3 border border-zinc-200 px-4 py-3 dark:border-zinc-700">
                        <input type="checkbox" wire:model="is_default" class="border-zinc-300 text-zinc-900 focus:ring-zinc-500">
                        <span class="text-sm text-zinc-700 dark:text-zinc-200">Set as default sending account</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Notes</label>
                <textarea wire:model="notes" rows="5" class="w-full border border-zinc-300 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"></textarea>
                @error('notes') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                <a
                    href="{{ route('sendportal.workspace.smtp-accounts.index') }}"
                    wire:navigate
                    class="inline-flex items-center justify-center border border-zinc-300 px-4 py-2.5 text-sm font-medium text-zinc-900 transition hover:bg-zinc-50 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="inline-flex items-center justify-center bg-zinc-900 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-zinc-700 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200"
                >
                    {{ $account?->exists ? 'Update Account' : 'Create Account' }}
                </button>
            </div>
        </form>
    </section>
</div>