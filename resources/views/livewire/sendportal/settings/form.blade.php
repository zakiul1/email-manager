<div class="space-y-6 p-6">
    <section class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-zinc-950 dark:text-white">Settings</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                SendPortal workspace defaults for sending, tracking, queue behavior, reporting, security, and mail settings.
            </p>
        </div>

        <button
            type="button"
            wire:click="save"
            class="bg-zinc-900 px-5 py-2.5 text-sm font-medium text-white dark:bg-white dark:text-zinc-900"
        >
            Save Settings
        </button>
    </section>

    <section class="grid gap-6 xl:grid-cols-[240px_1fr]">
        <div class="space-y-2 border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            @foreach ([
                'general' => 'General',
                'tracking' => 'Tracking',
                'queue' => 'Queue & Retry',
                'templates' => 'Templates',
                'reporting' => 'Reporting',
                'security' => 'Security',
                'mail' => 'Mail Settings',
            ] as $key => $label)
                <button
                    type="button"
                    wire:click="setTab('{{ $key }}')"
                    class="w-full border px-4 py-2.5 text-left text-sm font-medium {{ $activeTab === $key ? 'border-zinc-900 bg-zinc-900 text-white dark:border-white dark:bg-white dark:text-zinc-900' : 'border-zinc-200 text-zinc-700 dark:border-zinc-700 dark:text-zinc-200' }}"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <div class="border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            @if ($activeTab === 'general')
                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Default From Name</label>
                        <input type="text" wire:model="default_from_name" class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Default From Email</label>
                        <input type="email" wire:model="default_from_email" class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Default Reply-To Name</label>
                        <input type="text" wire:model="default_reply_to_name" class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Default Reply-To Email</label>
                        <input type="email" wire:model="default_reply_to_email" class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Default SMTP Pool</label>
                        <select wire:model="default_smtp_pool_id" class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                            <option value="">No default pool</option>
                            @foreach ($smtpPools as $pool)
                                <option value="{{ $pool->id }}">{{ $pool->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Default Timezone</label>
                        <input type="text" wire:model="default_timezone" class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                    </div>
                </div>
            @endif

            @if ($activeTab === 'tracking')
                <div class="space-y-4">
                    <label class="flex items-center gap-3">
                        <input type="checkbox" wire:model="tracking_opens_enabled">
                        <span class="text-sm text-zinc-700 dark:text-zinc-200">Enable open tracking</span>
                    </label>
                    <label class="flex items-center gap-3">
                        <input type="checkbox" wire:model="tracking_clicks_enabled">
                        <span class="text-sm text-zinc-700 dark:text-zinc-200">Enable click tracking</span>
                    </label>
                    <label class="flex items-center gap-3">
                        <input type="checkbox" wire:model="unsubscribe_footer_enabled">
                        <span class="text-sm text-zinc-700 dark:text-zinc-200">Enable unsubscribe footer by default</span>
                    </label>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Public Base URL</label>
                        <input type="url" wire:model="public_base_url" class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                    </div>
                </div>
            @endif

            @if ($activeTab === 'queue')
                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Retry Delay Minutes</label>
                        <input type="number" wire:model="retry_delay_minutes" class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Max Retry Attempts</label>
                        <input type="number" wire:model="max_retry_attempts" class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Dispatch Chunk Size</label>
                        <input type="number" wire:model="dispatch_chunk_size" class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                    </div>
                    <div class="flex items-center pt-8">
                        <label class="flex items-center gap-3">
                            <input type="checkbox" wire:model="allow_laravel_mailer_fallback">
                            <span class="text-sm text-zinc-700 dark:text-zinc-200">Allow Laravel mailer fallback</span>
                        </label>
                    </div>
                </div>
            @endif

            @if ($activeTab === 'templates')
                <div class="space-y-6">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Default Footer Text</label>
                        <textarea wire:model="default_footer_text" rows="5" class="w-full border border-zinc-300 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"></textarea>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Default Unsubscribe Text</label>
                        <input type="text" wire:model="default_unsubscribe_text" class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Default Editor Mode</label>
                        <select wire:model="default_editor_mode" class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                            <option value="code">Code</option>
                            <option value="builder">Builder</option>
                        </select>
                    </div>
                </div>
            @endif

            @if ($activeTab === 'reporting')
                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Default Report Range</label>
                    <select wire:model="default_report_range" class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                        <option value="7_days">Last 7 days</option>
                        <option value="30_days">Last 30 days</option>
                        <option value="90_days">Last 90 days</option>
                    </select>
                </div>
            @endif

            @if ($activeTab === 'security')
                <div class="space-y-6">
                    <label class="flex items-center gap-3">
                        <input type="checkbox" wire:model="signed_public_routes_enabled">
                        <span class="text-sm text-zinc-700 dark:text-zinc-200">Enable signed public routes</span>
                    </label>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Webhook Secret</label>
                        <input type="text" wire:model="webhook_secret" class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                        <p class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
                            Store the provider webhook secret here for future signature validation.
                        </p>
                    </div>
                </div>
            @endif

            @if ($activeTab === 'mail')
                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Mailer</label>
                        <select wire:model="mail_mailer" class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                            <option value="smtp">SMTP</option>
                            <option value="sendmail">Sendmail</option>
                            <option value="log">Log</option>
                            <option value="array">Array</option>
                            <option value="failover">Failover</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">SMTP Host</label>
                        <input type="text" wire:model="mail_host" class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">SMTP Port</label>
                        <input type="number" wire:model="mail_port" class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Encryption</label>
                        <select wire:model="mail_encryption" class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                            <option value="">None</option>
                            <option value="tls">TLS</option>
                            <option value="ssl">SSL</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">SMTP Username</label>
                        <input type="text" wire:model="mail_username" class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">SMTP Password</label>
                        <input type="password" wire:model="mail_password" class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                        <p class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
                            Leave blank if you want to keep the existing saved password.
                        </p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">From Address</label>
                        <input type="email" wire:model="mail_from_address" class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">From Name</label>
                        <input type="text" wire:model="mail_from_name" class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                    </div>
                </div>
            @endif
        </div>
    </section>
</div>