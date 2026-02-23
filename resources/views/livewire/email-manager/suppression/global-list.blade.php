<div class="p-6">
    <div class="flex items-center justify-between">
        <h1 class="text-lg font-semibold">Global Suppressions</h1>
    </div>

    <div class="mt-6 grid gap-4 md:grid-cols-2">
        <flux:card>
            <flux:heading size="md">Add Emails to Suppression</flux:heading>

            {{-- ✅ Result Summary --}}
            @if (($result['total'] ?? 0) > 0)
                <div class="mt-4 rounded-md border border-emerald-200 bg-emerald-50 p-3 text-sm">
                    <div class="font-medium text-emerald-800">Completed</div>

                    <div class="mt-2 grid grid-cols-2 gap-3">
                        <div class="rounded-md border bg-white p-3">
                            <div class="text-muted-foreground">Total</div>
                            <div class="text-lg font-semibold">{{ $result['total'] ?? 0 }}</div>
                        </div>
                        <div class="rounded-md border bg-white p-3">
                            <div class="text-muted-foreground">Added</div>
                            <div class="text-lg font-semibold">{{ $result['added'] ?? 0 }}</div>
                        </div>
                        <div class="rounded-md border bg-white p-3">
                            <div class="text-muted-foreground">Already Suppressed</div>
                            <div class="text-lg font-semibold">{{ $result['already'] ?? 0 }}</div>
                        </div>
                        <div class="rounded-md border bg-white p-3">
                            <div class="text-muted-foreground">Invalid</div>
                            <div class="text-lg font-semibold">{{ $result['invalid'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="mt-4 space-y-3">
                <flux:field>
                    <flux:label>Emails (one per line, comma or semicolon separated)</flux:label>

                    {{-- ✅ Use normal textarea for reliability --}}
                    <textarea rows="8" wire:model="emails" class="w-full rounded-md border px-3 py-2 text-sm"
                        placeholder="example@domain.com&#10;user2@gmail.com, user3@yahoo.com"></textarea>

                    <flux:error name="emails" />
                </flux:field>

                <flux:field>
                    <flux:label>Reason (optional)</flux:label>
                    <flux:input wire:model="reason" />
                    <flux:error name="reason" />
                </flux:field>

                <flux:button wire:click="add">Add</flux:button>
            </div>

            {{-- ✅ Invalid Preview --}}
            @if (!empty($invalidPreview))
                <div class="mt-6">
                    <flux:heading size="sm">Invalid Emails Preview (first {{ count($invalidPreview) }})
                    </flux:heading>

                    <div class="mt-3 overflow-x-auto rounded-md border bg-white">
                        <table class="min-w-full text-sm">
                            <thead class="text-left">
                                <tr class="border-b">
                                    <th class="px-3 py-2 font-medium">Email</th>
                                    <th class="px-3 py-2 font-medium">Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($invalidPreview as $row)
                                    <tr class="border-b">
                                        <td class="px-3 py-2">{{ $row['email'] ?? '' }}</td>
                                        <td class="px-3 py-2">{{ $row['reason'] ?? '' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </flux:card>

        <flux:card>
            <flux:heading size="md">Current List</flux:heading>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left">
                        <tr class="border-b">
                            <th class="px-3 py-2 font-medium">Email</th>
                            <th class="px-3 py-2 font-medium">Reason</th>
                            <th class="px-3 py-2 font-medium text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $row)
                            <tr class="border-b">
                                <td class="px-3 py-2">{{ $row->emailAddress?->email }}</td>
                                <td class="px-3 py-2">{{ $row->reason }}</td>
                                <td class="px-3 py-2 text-right">
                                    <button class="text-sm underline" wire:click="remove({{ $row->id }})">
                                        Remove
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-3 py-4 text-center text-muted-foreground">
                                    No suppressed emails.
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
</div>
