<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-lg font-semibold">Domain Unsubscribes</h1>
    </div>

    @php
        $disableAddForm = !empty($bulkIsRunning);
    @endphp

    <div class="grid gap-4 md:grid-cols-2">
        {{-- Add Multiple --}}
        <flux:card>
            <flux:heading size="md">Add Domains / Extensions / Users</flux:heading>

            {{-- ✅ Progress UI (shared partial like Global Suppressions) --}}
            @if (!empty($bulkUploadId))
                @include('livewire.email-manager.suppression._bulk-progress', [
                    'metric4Label' => 'Updated',
                    'metric4Value' => $bulkUpdated ?? 0,
                    'previewKey' => 'value',
                    'previewTitle' => 'Failures Preview',
                ])
            @endif

            {{-- ✅ IMPORTANT: disable the whole form safely --}}
            <fieldset class="mt-4 space-y-3" @if ($disableAddForm) disabled @endif>
                <flux:field>
                    <flux:label>Type</flux:label>
                    <select wire:model.live="type" class="w-full rounded-md border px-3 py-2 text-sm">
                        <option value="domain">Domain (example.com)</option>
                        <option value="extension">Extension (.bd, .com.bd)</option>
                        <option value="user">User (local-part) (pk_d@, pk.dutta@)</option>
                    </select>
                    <flux:error name="type" />
                </flux:field>

                {{-- Tabs --}}
                <div class="flex gap-2">
                    <button type="button"
                        class="rounded-md border px-3 py-2 text-sm {{ ($inputMode ?? 'textarea') === 'textarea' ? 'bg-black text-white' : '' }}"
                        wire:click="$set('inputMode','textarea')">
                        Textarea
                    </button>

                    <button type="button"
                        class="rounded-md border px-3 py-2 text-sm {{ ($inputMode ?? 'textarea') === 'file' ? 'bg-black text-white' : '' }}"
                        wire:click="$set('inputMode','file')">
                        File
                    </button>
                </div>

                {{-- Input area --}}
                @if (($inputMode ?? 'textarea') === 'textarea')
                    <flux:field>
                        <flux:label>Paste multiple (line break / comma / semicolon)</flux:label>

                        <textarea wire:model="domainsText" rows="8" class="w-full rounded-md border px-3 py-2 text-sm"
                            placeholder=""></textarea>

                        <flux:error name="domainsText" />

                        <div class="text-xs text-muted-foreground mt-1">
                            @if (($type ?? 'domain') === 'extension')
                                For extensions use: <span class="font-medium">.bd</span> or
                                <span class="font-medium">.com.bd</span>
                            @elseif (($type ?? 'domain') === 'user')
                                For users use: <span class="font-medium">pk_d@</span> or
                                <span class="font-medium">pk.dutta@</span> (blocks any domain)
                            @else
                                For domains use: <span class="font-medium">example.com</span> (exact match)
                            @endif
                        </div>
                    </flux:field>
                @else
                    <flux:field>
                        <flux:label>Upload file (.txt or .csv)</flux:label>

                        <input type="file" wire:model="uploadFile"
                            class="w-full rounded-md border px-3 py-2 text-sm" />

                        <flux:error name="uploadFile" />

                        <div class="text-xs text-muted-foreground mt-1">
                            TXT: one value per line (comma/semicolon also supported).
                            CSV: first column is the value.
                            <span class="block mt-1">
                                @if (($type ?? 'domain') === 'extension')
                                    Example: <span class="font-medium">.bd</span>, <span class="font-medium">.com.bd</span>
                                @elseif (($type ?? 'domain') === 'user')
                                    Example: <span class="font-medium">pk_d</span>, <span class="font-medium">pk_d@</span>, <span class="font-medium">pk.dutta@</span>
                                @else
                                    Example: <span class="font-medium">example.com</span>
                                @endif
                            </span>
                        </div>
                    </flux:field>
                @endif

                <flux:field>
                    <flux:label>Reason (optional)</flux:label>
                    <flux:input wire:model="reason" />
                    <flux:error name="reason" />
                </flux:field>

                {{-- Actions --}}
                <div class="flex items-center gap-2">
                    @if ($disableAddForm)
                        <flux:button disabled>Start</flux:button>
                    @else
                        @if (($inputMode ?? 'textarea') === 'file')
                            <flux:button wire:click="startBulkAddFromFile" wire:loading.attr="disabled">
                                Start
                            </flux:button>
                        @else
                            <flux:button wire:click="startBulkAdd" wire:loading.attr="disabled">
                                Start
                            </flux:button>
                        @endif
                    @endif

                    {{-- Continue Chunk --}}
                    @if (!empty($bulkUploadId) && !empty($bulkIsRunning))
                        <flux:button wire:click="processChunk" wire:loading.attr="disabled"
                            class="!bg-white !text-black !border">
                            Process next chunk
                        </flux:button>
                    @endif

                    {{-- Reset --}}
                    @if (!empty($bulkUploadId))
                        <button type="button" class="rounded-md border px-3 py-2 text-sm" wire:click="resetBulk"
                            wire:loading.attr="disabled">
                            Reset
                        </button>
                    @endif

                    <div class="text-xs text-muted-foreground" wire:loading>
                        Processing...
                    </div>
                </div>
            </fieldset>
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
                            @php
                                $t = $row->type ?? '';

                                $badgeClass = 'inline-flex items-center rounded-md border px-2 py-0.5 text-xs ';
                                if ($t === 'domain') {
                                    $badgeClass .= 'bg-blue-50 text-blue-700 border-blue-200';
                                } elseif ($t === 'extension') {
                                    $badgeClass .= 'bg-emerald-50 text-emerald-700 border-emerald-200';
                                } else {
                                    $badgeClass .= 'bg-amber-50 text-amber-700 border-amber-200';
                                }
                            @endphp
                            <tr class="border-b">
                                <td class="px-3 py-2">
                                    <input type="checkbox" value="{{ $row->id }}" wire:model="selected">
                                </td>

                                <td class="px-3 py-2">
                                    <span class="{{ $badgeClass }}">
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

            <div class="mt-2 text-xs text-muted-foreground">
                Types:
                <span class="font-medium">domain</span> = exact domain,
                <span class="font-medium">extension</span> = ends-with,
                <span class="font-medium">user</span> = local-part match (blocks any domain)
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
                        <option value="user">User local-part contains (ex: pk_)</option>
                    </select>
                </div>

                <div class="flex-1">
                    <flux:input wire:model.live="emailSearch"
                        placeholder="Search from Emails (domain/local-part)..." />
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

                    <button type="button" class="rounded-md border px-3 py-2 text-sm"
                        wire:click="clearEmailSelection" wire:loading.attr="disabled"
                        @if (empty($emailSelected)) disabled @endif>
                        Clear
                    </button>

                    <button type="button" class="rounded-md border px-3 py-2 text-sm"
                        wire:click="openDeleteEmailsModal" wire:loading.attr="disabled"
                        @if (empty($emailSelected)) disabled @endif
                        title="Deletes emails from database for selected domains/local-parts">
                        Delete selected emails
                    </button>
                </div>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left">
                        <tr class="border-b">
                            <th class="px-3 py-2 w-10"></th>
                            <th class="px-3 py-2 font-medium">
                                @if (($emailSearchMode ?? 'all') === 'user')
                                    User (local-part) (from Emails)
                                @else
                                    Domain (from Emails)
                                @endif
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($emailDomains as $row)
                            @php
                                $val =
                                    ($emailSearchMode ?? 'all') === 'user'
                                        ? $row->local_part ?? ($row->domain ?? '')
                                        : $row->domain ?? '';
                            @endphp
                            <tr class="border-b">
                                <td class="px-3 py-2">
                                    <input type="checkbox" value="{{ $val }}" wire:model="emailSelected">
                                </td>

                                <td class="px-3 py-2">
                                    <span class="font-medium">{{ $val }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-3 py-4 text-center text-muted-foreground">
                                    @if (($emailSearchMode ?? 'all') === 'user')
                                        No matched users (local-part) from emails.
                                    @else
                                        No matched domains from emails.
                                    @endif
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
                Note: This section searches
                @if (($emailSearchMode ?? 'all') === 'user')
                    <span class="font-medium">email_addresses.local_part</span> and deletes all emails under selected
                    local-parts from the database.
                @else
                    <span class="font-medium">email_addresses.domain</span> and deletes all emails under selected
                    domains from the database.
                @endif
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
                    email(s) from the database for the selected
                    @if (($emailSearchMode ?? 'all') === 'user')
                        users (local-part).
                    @else
                        domains.
                    @endif
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