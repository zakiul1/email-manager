<div class="space-y-6 p-6">
    <section class="border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <h1 class="text-2xl font-semibold tracking-tight text-zinc-950 dark:text-white">User Manual</h1>
        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">
            This manual explains how to configure SMTP, mail settings, pools, campaigns, and how to send email from the system.
        </p>
    </section>

    <section class="grid gap-6 xl:grid-cols-[260px_1fr]">
        <aside class="border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <nav class="space-y-2 text-sm">
                <a href="#smtp-account" class="block border border-zinc-200 px-4 py-2 text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">1. SMTP Account Setup</a>
                <a href="#mail-settings" class="block border border-zinc-200 px-4 py-2 text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">2. Mail Settings</a>
                <a href="#smtp-pool" class="block border border-zinc-200 px-4 py-2 text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">3. SMTP Pool Setup</a>
                <a href="#campaign-create" class="block border border-zinc-200 px-4 py-2 text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">4. Create Campaign</a>
                <a href="#audience-prepare" class="block border border-zinc-200 px-4 py-2 text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">5. Prepare Audience</a>
                <a href="#dispatch-send" class="block border border-zinc-200 px-4 py-2 text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">6. Dispatch / Send</a>
                <a href="#queue-worker" class="block border border-zinc-200 px-4 py-2 text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">7. Queue Worker</a>
                <a href="#troubleshooting" class="block border border-zinc-200 px-4 py-2 text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">8. Troubleshooting</a>
            </nav>
        </aside>

        <div class="space-y-6">
            <section id="smtp-account" class="border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-xl font-semibold text-zinc-950 dark:text-white">1. SMTP Account Setup</h2>
                <div class="mt-4 space-y-3 text-sm text-zinc-700 dark:text-zinc-200">
                    <p>Create an SMTP account from <strong>SMTP Accounts</strong>.</p>
                    <div class="border border-zinc-200 p-4 dark:border-zinc-700">
                        <div class="font-medium">Required fields</div>
                        <ul class="mt-2 space-y-1">
                            <li>Account Name</li>
                            <li>Driver Type = SMTP</li>
                            <li>Host</li>
                            <li>Port</li>
                            <li>Username</li>
                            <li>Password</li>
                            <li>Encryption</li>
                        </ul>
                    </div>
                    <div class="border border-zinc-200 p-4 dark:border-zinc-700">
                        <div class="font-medium">Recommended sender fields</div>
                        <ul class="mt-2 space-y-1">
                            <li>From Name = your company or brand name</li>
                            <li>From Email = real sending mailbox</li>
                            <li>Reply-To Name = sales/support team name</li>
                            <li>Reply-To Email = mailbox where replies should go</li>
                        </ul>
                    </div>
                    <p>After saving, click <strong>Test</strong>. If success is shown, the SMTP connection is working.</p>
                </div>
            </section>

            <section id="mail-settings" class="border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-xl font-semibold text-zinc-950 dark:text-white">2. Mail Settings</h2>
                <div class="mt-4 space-y-3 text-sm text-zinc-700 dark:text-zinc-200">
                    <p>Open <strong>Settings → Mail Settings</strong>.</p>
                    <p>These values are used as system-level fallback mail settings.</p>
                    <div class="border border-zinc-200 p-4 dark:border-zinc-700">
                        <div class="font-medium">Recommended fields</div>
                        <ul class="mt-2 space-y-1">
                            <li>Mailer</li>
                            <li>SMTP Host</li>
                            <li>SMTP Port</li>
                            <li>Encryption</li>
                            <li>SMTP Username</li>
                            <li>SMTP Password</li>
                            <li>From Address</li>
                            <li>From Name</li>
                        </ul>
                    </div>
                    <p>Keep these values aligned with your real mail server.</p>
                </div>
            </section>

            <section id="smtp-pool" class="border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-xl font-semibold text-zinc-950 dark:text-white">3. SMTP Pool Setup</h2>
                <div class="mt-4 space-y-3 text-sm text-zinc-700 dark:text-zinc-200">
                    <p>Create a pool from <strong>SMTP Pools</strong>.</p>
                    <p>Add one or more SMTP accounts into the pool.</p>
                    <div class="border border-zinc-200 p-4 dark:border-zinc-700">
                        <div class="font-medium">Pool setup flow</div>
                        <ol class="mt-2 space-y-1 list-decimal list-inside">
                            <li>Create pool name</li>
                            <li>Select strategy</li>
                            <li>Add SMTP account</li>
                            <li>Set weight / max percent if needed</li>
                            <li>Keep pool active</li>
                        </ol>
                    </div>
                    <p>A campaign uses a pool, and the pool uses SMTP accounts.</p>
                </div>
            </section>

            <section id="campaign-create" class="border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-xl font-semibold text-zinc-950 dark:text-white">4. Create Campaign</h2>
                <div class="mt-4 space-y-3 text-sm text-zinc-700 dark:text-zinc-200">
                    <p>Go to <strong>Campaigns</strong> and create or edit a campaign.</p>
                    <div class="border border-zinc-200 p-4 dark:border-zinc-700">
                        <div class="font-medium">Important fields</div>
                        <ul class="mt-2 space-y-1">
                            <li>Campaign Name</li>
                            <li>Subject</li>
                            <li>Template</li>
                            <li>SMTP Pool</li>
                            <li>From Name</li>
                            <li>From Email</li>
                            <li>Reply-To Name</li>
                            <li>Reply-To Email</li>
                            <li>Audience Type = Category</li>
                            <li>Select one or more categories</li>
                        </ul>
                    </div>
                    <p>Save the campaign after all fields are completed.</p>
                </div>
            </section>

            <section id="audience-prepare" class="border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-xl font-semibold text-zinc-950 dark:text-white">5. Prepare Audience</h2>
                <div class="mt-4 space-y-3 text-sm text-zinc-700 dark:text-zinc-200">
                    <p>Open the campaign detail page and click <strong>Audience</strong>.</p>
                    <p>This step creates message rows for each recipient from the selected category.</p>
                    <div class="border border-zinc-200 p-4 dark:border-zinc-700">
                        <div class="font-medium">Important</div>
                        <p class="mt-2">
                            If <strong>Prepared Messages = 0</strong>, dispatch will not work.
                        </p>
                    </div>
                    <p>After preparation, go back to the campaign page and confirm that prepared messages are greater than zero.</p>
                </div>
            </section>

            <section id="dispatch-send" class="border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-xl font-semibold text-zinc-950 dark:text-white">6. Dispatch / Send Mail</h2>
                <div class="mt-4 space-y-3 text-sm text-zinc-700 dark:text-zinc-200">
                    <p>Once prepared messages exist, open the campaign page and click <strong>Dispatch</strong>.</p>
                    <div class="border border-zinc-200 p-4 dark:border-zinc-700">
                        <div class="font-medium">Sending flow</div>
                        <ol class="mt-2 space-y-1 list-decimal list-inside">
                            <li>Campaign uses selected SMTP Pool</li>
                            <li>SMTP Pool selects available SMTP Account</li>
                            <li>Email body is rendered from template/campaign content</li>
                            <li>Messages are queued for sending</li>
                        </ol>
                    </div>
                </div>
            </section>

            <section id="queue-worker" class="border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-xl font-semibold text-zinc-950 dark:text-white">7. Queue Worker</h2>
                <div class="mt-4 space-y-3 text-sm text-zinc-700 dark:text-zinc-200">
                    <p>Queued emails will not send unless Laravel queue worker is running.</p>
                    <div class="border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-950">
<pre class="overflow-x-auto text-xs text-zinc-800 dark:text-zinc-200"><code>php artisan queue:work</code></pre>
                    </div>
                    <p>Run the worker on your server or local development environment.</p>
                </div>
            </section>

            <section id="troubleshooting" class="border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-xl font-semibold text-zinc-950 dark:text-white">8. Troubleshooting</h2>
                <div class="mt-4 space-y-4 text-sm text-zinc-700 dark:text-zinc-200">
                    <div class="border border-zinc-200 p-4 dark:border-zinc-700">
                        <div class="font-medium">Mailer is not defined</div>
                        <p class="mt-2">Usually caused by temporary mailer registration problem in SMTP test logic.</p>
                    </div>

                    <div class="border border-zinc-200 p-4 dark:border-zinc-700">
                        <div class="font-medium">SSL certificate mismatch</div>
                        <p class="mt-2">Do not use raw IP with SSL. Use the real SMTP hostname, such as a mail domain.</p>
                    </div>

                    <div class="border border-zinc-200 p-4 dark:border-zinc-700">
                        <div class="font-medium">Prepared messages before dispatching</div>
                        <p class="mt-2">Go to the Audience page first and generate message rows before clicking Dispatch.</p>
                    </div>

                    <div class="border border-zinc-200 p-4 dark:border-zinc-700">
                        <div class="font-medium">Test email destination confusion</div>
                        <p class="mt-2">The SMTP test mail is sent to the SMTP account From Email if filled. Otherwise it uses the fallback system mail from address.</p>
                    </div>

                    <div class="border border-zinc-200 p-4 dark:border-zinc-700">
                        <div class="font-medium">No mail sent after dispatch</div>
                        <p class="mt-2">Check queue worker, SMTP pool selection, campaign preparation, and SMTP account status.</p>
                    </div>
                </div>
            </section>
        </div>
    </section>
</div>