<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-lg font-semibold">Domain Unsubscribes</h1>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        {{-- Add Multiple --}}
        <flux:card>
            <flux:heading size="md">Add Domains / Extensions</flux:heading>

            <div class="mt-4 space-y-3">
                <flux:field>
                    <flux:label>Type</flux:label>
                    <select wire:model.live="type" class="w-full rounded-md border px-3 py-2 text-sm">
                        <option value="domain">Domain (example.com)</option>
                        <option value="extension">Extension (.bd, .com.bd)</option>
                    </select>
                    <flux:error name="type" />
                </flux:field>

                <flux:field>
                    <flux:label>Paste multiple (line break / comma / semicolon)</flux:label>

                    <textarea wire:model="domainsText" rows="8" class="w-full rounded-md border px-3 py-2 text-sm"
                        placeholder="example.com
gmail.com, yahoo.com; outlook.com"></textarea>

                    <flux:error name="domainsText" />
                    <div class="text-xs text-muted-foreground mt-1">
                        For extensions use: <span class="font-medium">.bd</span> or
                        <span class="font-medium">.com.bd</span>
                    </div>
                </flux:field>

                <flux:field>
                    <flux:label>Reason (optional)</flux:label>
                    <flux:input wire:model="reason" />
                    <flux:error name="reason" />
                </flux:field>

                <div class="flex items-center gap-2">
                    <flux:button wire:click="addMultiple" wire:loading.attr="disabled">
                        Add
                    </flux:button>

                    <div class="text-xs text-muted-foreground" wire:loading>
                        Processing...
                    </div>
                </div>
            </div>
        </flux:card>

        {{-- Current List --}}
        <flux:card>
            <flux:heading size="md">Current List</flux:heading>

            {{-- Search + Count + Bulk delete --}}
            <div class="mt-4 flex flex-col gap-3">
                <div class="flex items-center gap-2">
                    <div class="flex-1">
                        <flux:input wire:model.live="search" placeholder="Search value..." />
                    </div>

                    <div class="text-sm text-muted-foreground">
                        Found:
                        <span class="font-semibold text-foreground">
                            {{ number_format($totalMatched ?? 0) }}
                        </span>
                    </div>
                </div>

                <div class="flex items-center justify-between gap-2">
                    <div class="text-sm text-muted-foreground">
                        Selected:
                        <span class="font-semibold text-foreground">{{ count($selected ?? []) }}</span>
                    </div>

                    <button type="button" class="rounded-md border px-3 py-2 text-sm" wire:click="bulkDeleteSelected"
                        wire:loading.attr="disabled" @if (empty($selected)) disabled @endif>
                        Delete selected
                    </button>
                </div>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left">
                        <tr class="border-b">
                            <th class="px-3 py-2 w-10"></th>
                            <th class="px-3 py-2 font-medium">Type</th>
                            <th class="px-3 py-2 font-medium">Value</th>
                            <th class="px-3 py-2 font-medium">Reason</th>
                            <th class="px-3 py-2 font-medium text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $row)
                            <tr class="border-b">
                                <td class="px-3 py-2">
                                    <input type="checkbox" value="{{ $row->id }}" wire:model="selected">
                                </td>

                                <td class="px-3 py-2">
                                    <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-xs">
                                        {{ $row->type }}
                                    </span>
                                </td>

                                <td class="px-3 py-2">
                                    <span class="font-medium">{{ $row->value }}</span>
                                </td>

                                <td class="px-3 py-2">{{ $row->reason }}</td>

                                <td class="px-3 py-2 text-right">
                                    <button type="button" class="text-sm underline"
                                        wire:click="remove({{ $row->id }})">
                                        Remove
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-3 py-4 text-center text-muted-foreground">
                                    No items found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-3">
                    {{ $items->links() }}
                </div>
            </div>
        </flux:card>
    </div>

    {{-- Bottom Global Search (from Emails table) + Select all + Delete Emails --}}
    <flux:card>
        <flux:heading size="md">Global Search & Bulk Delete (From Emails)</flux:heading>

        <div class="mt-4 space-y-3">
            <div class="flex flex-col gap-3 md:flex-row md:items-center">
                <div class="w-full md:w-64">
                    <select wire:model.live="emailSearchMode" class="w-full rounded-md border px-3 py-2 text-sm">
                        <option value="all">All (Domain contains + Extension ends-with)</option>
                        <option value="domain">Domain contains (ex: mail)</option>
                        <option value="extension">Extension ends-with (ex: .net, .com.bd)</option>
                    </select>
                </div>

                <div class="flex-1">
                    <flux:input wire:model.live="emailSearch"
                        placeholder="Search domains from Emails (ex: .net or mail)..." />
                </div>

                <div class="text-sm text-muted-foreground whitespace-nowrap">
                    Found:
                    <span class="font-semibold text-foreground">
                        {{ number_format($emailMatched ?? 0) }}
                    </span>
                </div>
            </div>

            <div class="flex items-center justify-between gap-2">
                <div class="text-sm text-muted-foreground">
                    Selected:
                    <span class="font-semibold text-foreground">{{ count($emailSelected ?? []) }}</span>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" class="rounded-md border px-3 py-2 text-sm"
                        wire:click="selectAllEmailMatches" wire:loading.attr="disabled"
                        @if (($emailMatched ?? 0) == 0) disabled @endif>
                        Select all matched
                    </button>

                    <button type="button" class="rounded-md border px-3 py-2 text-sm" wire:click="clearEmailSelection"
                        wire:loading.attr="disabled" @if (empty($emailSelected)) disabled @endif>
                        Clear
                    </button>

                    <button type="button" class="rounded-md border px-3 py-2 text-sm"
                        wire:click="openDeleteEmailsModal" wire:loading.attr="disabled"
                        @if (empty($emailSelected)) disabled @endif
                        title="Deletes emails from database for selected domains">
                        Delete selected emails
                    </button>
                </div>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left">
                        <tr class="border-b">
                            <th class="px-3 py-2 w-10"></th>
                            <th class="px-3 py-2 font-medium">Domain (from Emails)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($emailDomains as $row)
                            <tr class="border-b">
                                <td class="px-3 py-2">
                                    <input type="checkbox" value="{{ $row->domain }}" wire:model="emailSelected">
                                </td>

                                <td class="px-3 py-2">
                                    <span class="font-medium">{{ $row->domain }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-3 py-4 text-center text-muted-foreground">
                                    No matched domains from emails.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-3">
                    {{ $emailDomains->links() }}
                </div>
            </div>

            <div class="text-xs text-muted-foreground">
                Note: This section searches <span class="font-medium">email_addresses.domain</span> and deletes all
                emails
                under the selected domains from the database.
            </div>
        </div>
    </flux:card>

    {{-- Confirm Delete Modal --}}
    @if ($showDeleteEmailsModal)
        <div class="fixed inset-0 flex items-center justify-center" style="z-index: 9999;">
            {{-- overlay --}}
            <div class="absolute inset-0 bg-black/40" wire:click="cancelDeleteEmails"></div>

            {{-- modal --}}
            <div class="relative w-full max-w-md rounded-lg bg-white p-5 shadow-lg">
                <div class="text-base font-semibold">Confirm delete emails</div>

                <div class="mt-2 text-sm text-muted-foreground">
                    This will delete
                    <span class="font-semibold text-foreground">
                        {{ number_format($confirmDeleteEmailCount ?? 0) }}
                    </span>
                    email(s) from the database for the selected domains.
                    <div class="mt-1">
                        This action cannot be undone.
                    </div>
                </div>

                <div class="mt-4 flex justify-end gap-2">
                    <button type="button" class="rounded-md border px-3 py-2 text-sm"
                        wire:click="cancelDeleteEmails" wire:loading.attr="disabled">
                        Cancel
                    </button>

                    <button type="button" class="rounded-md border px-3 py-2 text-sm"
                        wire:click="deleteEmailsConfirmed" wire:loading.attr="disabled">
                        Yes, Delete
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
