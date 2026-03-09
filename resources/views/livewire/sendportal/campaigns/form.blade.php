<div class="space-y-6 p-6">
    <section>
        <div class="border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mb-6 flex flex-col gap-4 border-b border-zinc-200 pb-4 dark:border-zinc-700 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-zinc-950 dark:text-white">
                        {{ $campaign?->exists ? 'Edit Campaign' : 'Create Campaign' }}
                    </h1>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                        Build the campaign definition, audience source, and scheduling workflow.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <a
                        href="{{ route('sendportal.workspace.campaigns.index') }}"
                        wire:navigate
                        class="inline-flex items-center justify-center border border-zinc-300 px-4 py-2.5 text-sm font-medium text-zinc-900 transition hover:bg-zinc-50 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800"
                    >
                        Back
                    </a>
                </div>
            </div>

            <form wire:submit="save" class="space-y-6">
                <div class="grid gap-6 lg:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Campaign Name</label>
                        <input type="text" wire:model="name" class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                        @error('name') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Subject</label>
                        <input type="text" wire:model="subject" class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                        @error('subject') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="grid gap-6 md:grid-cols-3">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Preheader</label>
                        <input type="text" wire:model="preheader" class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                        @error('preheader') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Status</label>
                        <select wire:model="status" class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                            <option value="draft">Draft</option>
                            <option value="scheduled">Scheduled</option>
                            <option value="active">Active</option>
                            <option value="paused">Paused</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="failed">Failed</option>
                        </select>
                        @error('status') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Delivery Mode</label>
                        <select wire:model="delivery_mode" class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                            <option value="draft">Draft</option>
                            <option value="manual">Manual</option>
                            <option value="schedule">Schedule</option>
                        </select>
                        @error('delivery_mode') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Template</label>
                        <select wire:model.live="template_id" class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                            <option value="">No template</option>
                            @foreach ($templates as $template)
                                <option value="{{ $template->id }}">{{ $template->name }}</option>
                            @endforeach
                        </select>
                        @error('template_id') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">SMTP Pool</label>
                        <select wire:model="smtp_pool_id" class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                            <option value="">No pool selected</option>
                            @foreach ($smtpPools as $pool)
                                <option value="{{ $pool->id }}">{{ $pool->name }}</option>
                            @endforeach
                        </select>
                        @error('smtp_pool_id') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                    </div>
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

                <div class="grid gap-6 lg:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Audience Type</label>
                        <input type="text" value="Category" disabled class="w-full border border-zinc-300 bg-zinc-50 px-4 py-2.5 text-sm text-zinc-600 outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                        <input type="hidden" wire:model="audience_type" value="category">
                        @error('audience_type') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                    </div>

                    @if ($delivery_mode === 'schedule')
                        <div>
                            <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Scheduled At</label>
                            <input type="datetime-local" wire:model="scheduled_at" class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                            @error('scheduled_at') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>
                    @endif
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Categories</label>

                    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                        @foreach ($categories as $item)
                            <label class="inline-flex items-center gap-3 border border-zinc-300 px-4 py-3 dark:border-zinc-700">
                                <input type="checkbox" value="{{ $item->id }}" wire:model="audience_ids" class="border-zinc-300 text-zinc-900 focus:ring-zinc-500">
                                <span class="text-sm text-zinc-700 dark:text-zinc-200">{{ $item->name }}</span>
                            </label>
                        @endforeach
                    </div>

                    @error('audience_ids') <div class="mt-2 text-sm text-red-600">{{ $message }}</div> @enderror
                    @error('audience_ids.*') <div class="mt-2 text-sm text-red-600">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">HTML Body</label>
                    <textarea wire:model="html_content" rows="12" class="w-full border border-zinc-300 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"></textarea>
                    @error('html_content') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Plain Text Body</label>
                    <textarea wire:model="text_content" rows="8" class="w-full border border-zinc-300 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"></textarea>
                    @error('text_content') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Notes</label>
                    <textarea wire:model="notes" rows="5" class="w-full border border-zinc-300 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"></textarea>
                    @error('notes') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                    <a href="{{ route('sendportal.workspace.campaigns.index') }}" wire:navigate class="inline-flex items-center justify-center border border-zinc-300 px-4 py-2.5 text-sm font-medium text-zinc-900 transition hover:bg-zinc-50 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex items-center justify-center bg-zinc-900 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-zinc-700 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200">
                        {{ $campaign?->exists ? 'Update Campaign' : 'Create Campaign' }}
                    </button>
                </div>
            </form>
        </div>
    </section>
</div>