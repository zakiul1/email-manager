<div class="p-6 ">
    <flux:card>
        <flux:heading size="lg">Upload Emails</flux:heading>

        @if ($errors->any())
            <div class="mt-4 rounded-md border border-red-300 bg-red-50 p-3 text-sm text-red-700">
                <div class="font-medium">Please fix these:</div>
                <ul class="list-disc pl-5 mt-2">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form class="mt-6 space-y-5" wire:submit.prevent="submit">
            <flux:field>
                <flux:label>Category</flux:label>
                <select wire:model="category_id" class="w-full rounded-md border px-3 py-2 text-sm">
                    <option value="0">Select category...</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
                @error('category_id') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
            </flux:field>

            <flux:field>
                <flux:label>Source</flux:label>
                <div class="flex gap-2">
                    <button type="button"
                        class="rounded-md border px-3 py-2 text-sm"
                        wire:click="$set('mode','textarea')"
                    >
                        Textarea
                    </button>
                    <button type="button"
                        class="rounded-md border px-3 py-2 text-sm"
                        wire:click="$set('mode','csv')"
                    >
                        CSV
                    </button>
                </div>
                @error('mode') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
            </flux:field>

            @if ($mode === 'textarea')
                <flux:field>
                    <flux:label>Emails (one per line, or comma-separated)</flux:label>
                    <flux:textarea rows="10" wire:model="textarea" />
                    @error('textarea') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
                </flux:field>
            @else
                <flux:field>
                    <flux:label>CSV file</flux:label>
                    <input type="file" wire:model="csv" class="block w-full text-sm" />
                    @error('csv') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
                </flux:field>
            @endif

            <div class="flex gap-2 items-center">
                <button
                    type="submit"
                    class="rounded-md border px-4 py-2 text-sm"
                    wire:loading.attr="disabled"
                    wire:target="submit,csv"
                >
                    <span wire:loading.remove wire:target="submit">Start Import</span>
                    <span wire:loading wire:target="submit">Starting...</span>
                </button>

                <flux:button variant="ghost" :href="route('email-manager.imports.batches')" wire:navigate>
                    View Imports
                </flux:button>
            </div>
        </form>
    </flux:card>
</div>