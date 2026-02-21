<div class="p-6 max-w-2xl">
    <flux:card>
        <div class="flex items-start justify-between gap-4">
            <div>
                <flux:heading size="lg">Create Category</flux:heading>
                <flux:subheading class="mt-1">
                    Create a new list to organize uploaded emails.
                </flux:subheading>
            </div>

            <flux:button
                variant="ghost"
                :href="route('email-manager.categories')"
                wire:navigate
            >
                Back
            </flux:button>
        </div>

        <div class="mt-6 space-y-5">
            <flux:field>
                <flux:label>Category Name</flux:label>
                <flux:input
                    wire:model.live="name"
                    placeholder="e.g. Shopify Leads"
                    autocomplete="off"
                />
                <flux:error name="name" />
            </flux:field>

            <flux:field>
                <flux:label>Slug (optional)</flux:label>
                <flux:input
                    wire:model.live="slug"
                    placeholder="Auto-generated if empty"
                    autocomplete="off"
                />
                <flux:error name="slug" />
            </flux:field>

            <flux:field>
                <flux:label>Notes (optional)</flux:label>
                <flux:textarea
                    wire:model.live="notes"
                    rows="4"
                    placeholder="Anything helpful about this category…"
                />
                <flux:error name="notes" />
            </flux:field>

            <div class="flex items-center gap-2 pt-2">
                <flux:button wire:click="save">
                    Save Category
                </flux:button>

                <flux:button
                    variant="ghost"
                    :href="route('email-manager.categories')"
                    wire:navigate
                >
                    Cancel
                </flux:button>
            </div>
        </div>
    </flux:card>
</div>