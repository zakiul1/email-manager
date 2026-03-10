@props([
    'modalName' => 'confirm-delete',
    'title' => 'Confirm Delete',
    'message' => 'Are you sure you want to delete this item?',
    'itemName' => null,
    'warning' => 'This action cannot be undone.',
    'confirmAction' => 'deleteConfirmed',
    'confirmTarget' => 'deleteConfirmed',
    'confirmText' => 'Delete',
    'loadingText' => 'Deleting...',
])

<flux:modal :name="$modalName" class="max-w-lg">
    <div class="space-y-4">
        <div>
            <flux:heading size="lg">{{ $title }}</flux:heading>

            @if ($warning)
                <flux:subheading class="mt-1">
                    {{ $warning }}
                </flux:subheading>
            @endif
        </div>

        <div class="rounded-md border border-zinc-200 bg-white p-4 text-sm text-zinc-700 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200">
            <p>{{ $message }}</p>

            @if (filled($itemName))
                <div class="mt-3 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700 dark:border-red-800 dark:bg-red-950 dark:text-red-300">
                    <span class="font-semibold">{{ $itemName }}</span>
                </div>
            @endif
        </div>

        <div class="flex items-center justify-end gap-2">
            <flux:modal.close>
                <flux:button variant="ghost" type="button">
                    Cancel
                </flux:button>
            </flux:modal.close>

            <flux:button
                type="button"
                wire:click="{{ $confirmAction }}"
                wire:loading.attr="disabled"
                wire:target="{{ $confirmTarget }}"
                class="cursor-pointer"
            >
                <span wire:loading.remove wire:target="{{ $confirmTarget }}">
                    {{ $confirmText }}
                </span>

                <span wire:loading wire:target="{{ $confirmTarget }}">
                    {{ $loadingText }}
                </span>
            </flux:button>
        </div>
    </div>
</flux:modal>