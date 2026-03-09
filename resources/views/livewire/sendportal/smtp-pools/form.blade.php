<div class="space-y-6 p-6">
    <section class="border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold tracking-tight text-zinc-950 dark:text-white">
                {{ $pool?->exists ? 'Edit SMTP Pool' : 'Create SMTP Pool' }}
            </h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                Build reusable SMTP pools and control which accounts participate in delivery.
            </p>
        </div>

        <form wire:submit="save" class="space-y-6">
            <div class="grid gap-6 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Pool Name</label>
                    <input type="text" wire:model="name" class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                    @error('name') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Strategy</label>
                    <select wire:model="strategy" class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                        @foreach ($strategyOptions as $option)
                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                    @error('strategy') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                </div>
            </div>

            <div>
                <label class="inline-flex items-center gap-3 border border-zinc-200 px-4 py-3 dark:border-zinc-700">
                    <input type="checkbox" wire:model="is_active" class="border-zinc-300 text-zinc-900 focus:ring-zinc-500">
                    <span class="text-sm text-zinc-700 dark:text-zinc-200">Pool is active</span>
                </label>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Notes</label>
                <textarea wire:model="notes" rows="4" class="w-full border border-zinc-300 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"></textarea>
                @error('notes') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
            </div>

            <div class="border border-zinc-200 p-5 dark:border-zinc-700">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Pool Members</h2>
                        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Choose SMTP accounts and set their pool participation values.</p>
                    </div>

                    <button
                        type="button"
                        wire:click="addMembership"
                        class="inline-flex items-center justify-center border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-900 transition hover:bg-zinc-50 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800"
                    >
                        Add Account
                    </button>
                </div>

                @error('memberships') <div class="mb-4 text-sm text-red-600">{{ $message }}</div> @enderror

                <div class="space-y-4">
                    @foreach ($memberships as $index => $member)
                        <div class="grid gap-4 border border-zinc-200 p-4 dark:border-zinc-700 md:grid-cols-[2fr_1fr_1fr_auto_auto]">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">SMTP Account</label>
                                <select wire:model="memberships.{{ $index }}.smtp_account_id" class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                                    <option value="">Select account</option>
                                    @foreach ($availableAccounts as $account)
                                        <option value="{{ $account->id }}">{{ $account->name }} @if($account->from_email) ({{ $account->from_email }}) @endif</option>
                                    @endforeach
                                </select>
                                @error("memberships.$index.smtp_account_id") <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Weight</label>
                                <input type="number" wire:model="memberships.{{ $index }}.weight" class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                                @error("memberships.$index.weight") <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Max %</label>
                                <input type="number" wire:model="memberships.{{ $index }}.max_percent" class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                                @error("memberships.$index.max_percent") <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div class="flex items-end">
                                <label class="inline-flex items-center gap-2 border border-zinc-200 px-4 py-3 dark:border-zinc-700">
                                    <input type="checkbox" wire:model="memberships.{{ $index }}.is_active" class="border-zinc-300 text-zinc-900 focus:ring-zinc-500">
                                    <span class="text-sm text-zinc-700 dark:text-zinc-200">Active</span>
                                </label>
                            </div>

                            <div class="flex items-end">
                                <button
                                    type="button"
                                    wire:click="removeMembership({{ $index }})"
                                    class="inline-flex items-center justify-center border border-red-300 px-4 py-2.5 text-sm font-medium text-red-700 transition hover:bg-red-50 dark:border-red-800 dark:text-red-300 dark:hover:bg-red-950"
                                >
                                    Remove
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                <a
                    href="{{ route('sendportal.workspace.smtp-pools.index') }}"
                    wire:navigate
                    class="inline-flex items-center justify-center border border-zinc-300 px-4 py-2.5 text-sm font-medium text-zinc-900 transition hover:bg-zinc-50 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="inline-flex items-center justify-center bg-zinc-900 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-zinc-700 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200"
                >
                    {{ $pool?->exists ? 'Update Pool' : 'Create Pool' }}
                </button>
            </div>
        </form>
    </section>
</div>