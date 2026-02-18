<div class="p-6 max-w-3xl">
    <flux:card>
        <flux:heading size="lg">Create Export</flux:heading>

        <form class="mt-6 space-y-4" wire:submit.prevent="submit">
            <flux:field>
                <flux:label>Category (optional)</flux:label>
                <select wire:model="category_id" class="w-full rounded-md border px-3 py-2 text-sm">
                    <option value="0">All categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </flux:field>

            <flux:field>
                <flux:label>Format</flux:label>
                <select wire:model="format" class="w-full rounded-md border px-3 py-2 text-sm">
                    <option value="csv">CSV</option>
                    <option value="txt">TXT</option>
                    <option value="json">JSON</option>
                </select>
            </flux:field>

            <div class="grid gap-3 md:grid-cols-2">
                <flux:field>
                    <flux:label>Domain (optional)</flux:label>
                    <flux:input wire:model="domain" placeholder="gmail.com" />
                </flux:field>

                <flux:field>
                    <flux:label>Validity</flux:label>
                    <select wire:model="valid" class="w-full rounded-md border px-3 py-2 text-sm">
                        <option value="all">All</option>
                        <option value="valid">Valid only</option>
                        <option value="invalid">Invalid only</option>
                    </select>
                </flux:field>
            </div>

            <div class="space-y-2">
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" wire:model="exclude_global_suppression">
                    Exclude global suppression emails
                </label>

                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" wire:model="exclude_domain_unsubscribes">
                    Exclude domain unsubscribes
                </label>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="rounded-md border px-4 py-2 text-sm">
                    Start Export
                </button>

                <flux:button variant="ghost" :href="route('email-manager.exports')" wire:navigate>
                    Back
                </flux:button>
            </div>
        </form>
    </flux:card>
</div>