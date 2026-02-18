<div class="p-6 max-w-2xl">
    <flux:card>
        <flux:heading size="lg">Edit Category</flux:heading>

        <div class="mt-6 space-y-4">
            <flux:field>
                <flux:label>Name</flux:label>
                <flux:input wire:model="name" />
                <flux:error name="name" />
            </flux:field>

            <flux:field>
                <flux:label>Slug</flux:label>
                <flux:input wire:model="slug" />
                <flux:error name="slug" />
            </flux:field>

            <flux:field>
                <flux:label>Notes</flux:label>
                <flux:textarea wire:model="notes" />
                <flux:error name="notes" />
            </flux:field>

            <div class="flex gap-2">
                <flux:button wire:click="save">Save</flux:button>
                <flux:button variant="ghost" :href="route('email-manager.categories')" wire:navigate>
                    Cancel
                </flux:button>
            </div>
        </div>
    </flux:card>
</div>
