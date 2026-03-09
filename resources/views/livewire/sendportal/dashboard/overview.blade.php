<div class="space-y-6 p-6">
    <section class="border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div class="grid gap-3 sm:grid-cols-2 xl:flex xl:flex-wrap">
                <a href="{{ route('sendportal.workspace.campaigns.create') }}" wire:navigate class="inline-flex items-center justify-center bg-zinc-900 px-4 py-2.5 text-sm font-medium text-white dark:bg-white dark:text-zinc-900">
                    Create Campaign
                </a>
                <a href="{{ route('sendportal.workspace.templates.create') }}" wire:navigate class="inline-flex items-center justify-center border border-zinc-300 px-4 py-2.5 text-sm font-medium text-zinc-900 dark:border-zinc-700 dark:text-white">
                    Create Template
                </a>
                <a href="{{ route('sendportal.workspace.smtp-accounts.create') }}" wire:navigate class="inline-flex items-center justify-center border border-zinc-300 px-4 py-2.5 text-sm font-medium text-zinc-900 dark:border-zinc-700 dark:text-white">
                    Add SMTP Account
                </a>
                <a href="{{ route('sendportal.workspace.reports.index') }}" wire:navigate class="inline-flex items-center justify-center border border-zinc-300 px-4 py-2.5 text-sm font-medium text-zinc-900 dark:border-zinc-700 dark:text-white">
                    Open Reports
                </a>
            </div>
        </div>
    </section>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['label' => 'Total Campaigns', 'value' => $stats['campaigns_total']],
            ['label' => 'Draft Campaigns', 'value' => $stats['campaigns_draft']],
            ['label' => 'Active / Scheduled', 'value' => $stats['campaigns_active']],
            ['label' => 'Sent', 'value' => $stats['messages_sent']],
            ['label' => 'Failed', 'value' => $stats['messages_failed']],
            ['label' => 'Opened', 'value' => $stats['messages_opened']],
            ['label' => 'Clicked', 'value' => $stats['messages_clicked']],
            ['label' => 'Unsubscribed', 'value' => $stats['messages_unsubscribed']],
            ['label' => 'SMTP Active', 'value' => $stats['smtp_active']],
            ['label' => 'SMTP Failing', 'value' => $stats['smtp_failing']],
            ['label' => 'SMTP Paused', 'value' => $stats['smtp_paused']],
            ['label' => 'SMTP Cooldown', 'value' => $stats['smtp_cooldown']],
        ] as $card)
            <div class="border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ $card['label'] }}</div>
                <div class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white">{{ number_format($card['value']) }}</div>
            </div>
        @endforeach
    </section>

    <section class="grid gap-6 xl:grid-cols-2">
        <div class="border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between border-b border-zinc-200 pb-3 dark:border-zinc-700">
                <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Delivery Trend</h2>
                <span class="text-xs text-zinc-500 dark:text-zinc-400">Last 14 days</span>
            </div>

            <div class="mt-6 space-y-4">
                @foreach ($deliveryTrend['labels'] as $index => $label)
                    @php
                        $sent = $deliveryTrend['sent'][$index];
                        $failed = $deliveryTrend['failed'][$index];
                        $max = max(1, max($deliveryTrend['sent']) + max($deliveryTrend['failed']));
                        $sentWidth = ($sent / $max) * 100;
                        $failedWidth = ($failed / $max) * 100;
                    @endphp
                    <div class="border-b border-zinc-100 pb-4 last:border-b-0 last:pb-0 dark:border-zinc-800">
                        <div class="mb-2 flex items-center justify-between text-xs text-zinc-500 dark:text-zinc-400">
                            <span>{{ $label }}</span>
                            <span>Sent {{ $sent }} · Failed {{ $failed }}</span>
                        </div>
                        <div class="space-y-2">
                            <div class="h-2 bg-zinc-100 dark:bg-zinc-800">
                                <div class="h-full bg-zinc-900 dark:bg-white" style="width: {{ $sentWidth }}%"></div>
                            </div>
                            <div class="h-2 bg-zinc-100 dark:bg-zinc-800">
                                <div class="h-full bg-red-500" style="width: {{ $failedWidth }}%"></div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between border-b border-zinc-200 pb-3 dark:border-zinc-700">
                <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Engagement Trend</h2>
                <span class="text-xs text-zinc-500 dark:text-zinc-400">Last 14 days</span>
            </div>

            <div class="mt-6 space-y-4">
                @foreach ($engagementTrend['labels'] as $index => $label)
                    @php
                        $opens = $engagementTrend['opens'][$index];
                        $clicks = $engagementTrend['clicks'][$index];
                        $max = max(1, max($engagementTrend['opens']) + max($engagementTrend['clicks']));
                        $opensWidth = ($opens / $max) * 100;
                        $clicksWidth = ($clicks / $max) * 100;
                    @endphp
                    <div class="border-b border-zinc-100 pb-4 last:border-b-0 last:pb-0 dark:border-zinc-800">
                        <div class="mb-2 flex items-center justify-between text-xs text-zinc-500 dark:text-zinc-400">
                            <span>{{ $label }}</span>
                            <span>Opens {{ $opens }} · Clicks {{ $clicks }}</span>
                        </div>
                        <div class="space-y-2">
                            <div class="h-2 bg-zinc-100 dark:bg-zinc-800">
                                <div class="h-full bg-sky-500" style="width: {{ $opensWidth }}%"></div>
                            </div>
                            <div class="h-2 bg-zinc-100 dark:bg-zinc-800">
                                <div class="h-full bg-violet-500" style="width: {{ $clicksWidth }}%"></div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-[1.3fr_1fr]">
        <div class="border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mb-4 border-b border-zinc-200 pb-3 dark:border-zinc-700">
                <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Recent Campaigns</h2>
            </div>

            <div class="overflow-hidden border border-zinc-200 dark:border-zinc-700">
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
                            @forelse ($recentCampaigns as $campaign)
                                <tr>
                                    <td class="px-4 py-3 text-sm">
                                        <a href="{{ route('sendportal.workspace.campaigns.show', $campaign) }}" wire:navigate class="font-medium text-zinc-900 hover:underline dark:text-white">
                                            {{ $campaign->name }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">{{ ucfirst($campaign->status) }}</td>
                                    <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">{{ number_format($campaign->sent_count ?? 0) }}</td>
                                    <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">{{ number_format($campaign->failed_count ?? 0) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-10 text-center text-sm text-zinc-500 dark:text-zinc-400">No campaigns yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <div class="mb-4 border-b border-zinc-200 pb-3 dark:border-zinc-700">
                    <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">SMTP Health</h2>
                </div>

                <div class="space-y-3">
                    @forelse ($smtpUsage as $account)
                        <div class="border border-zinc-200 p-4 dark:border-zinc-700">
                            <div class="flex items-center justify-between">
                                <div class="text-sm font-medium text-zinc-900 dark:text-white">{{ $account['name'] }}</div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ ucfirst($account['status']) }}</div>
                            </div>
                            <div class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
                                Sent today {{ number_format($account['sent_today']) }} · Success {{ number_format($account['success_count']) }} · Fail {{ number_format($account['failure_count']) }}
                            </div>
                            @if ($account['cooldown_until'])
                                <div class="mt-1 text-xs text-amber-600">Cooldown until {{ $account['cooldown_until'] }}</div>
                            @endif
                        </div>
                    @empty
                        <div class="text-sm text-zinc-500 dark:text-zinc-400">No SMTP accounts available.</div>
                    @endforelse
                </div>
            </div>

            <div class="border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <div class="mb-4 border-b border-zinc-200 pb-3 dark:border-zinc-700">
                    <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Recent Failures</h2>
                </div>

                <div class="space-y-3">
                    @forelse ($recentFailures as $message)
                        <div class="border border-zinc-200 p-4 dark:border-zinc-700">
                            <div class="text-sm font-medium text-zinc-900 dark:text-white">{{ $message->recipient_email }}</div>
                            <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $message->campaign?->name ?? '—' }} · {{ $message->smtpAccount?->name ?? '—' }}
                            </div>
                            <div class="mt-2 text-xs text-red-600">{{ $message->failure_reason ?: 'Unknown failure' }}</div>
                        </div>
                    @empty
                        <div class="text-sm text-zinc-500 dark:text-zinc-400">No recent failures.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>
</div>