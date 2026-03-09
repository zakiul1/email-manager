<x-layouts::sendportal.sidebar :title="$title ?? 'SendPortal'">
    <flux:main>
        {{ $slot }}
    </flux:main>
</x-layouts::sendportal.sidebar>