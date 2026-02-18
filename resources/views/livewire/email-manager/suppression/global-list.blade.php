<div class="p-6">
    <div class="flex items-center justify-between">
        <h1 class="text-lg font-semibold">Global Suppressions</h1>
    </div>

    <div class="mt-6 grid gap-4 md:grid-cols-2">
        <flux:card>
            <flux:heading size="md">Add Email to Suppression</flux:heading>

            <div class="mt-4 space-y-3">
                <flux:field>
                    <flux:label>Email</flux:label>
                    <flux:input wire:model="email" placeholder="example@domain.com" />
                    <flux:error name="email" />
                </flux:field>

                <flux:field>
                    <flux:label>Reason (optional)</flux:label>
                    <flux:input wire:model="reason" />
                    <flux:error name="reason" />
                </flux:field>

                <flux:button wire:click="add">Add</flux:button>
            </div>
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