@component('layouts.app')
    <div class="p-6 space-y-4">
        <div class="flex items-center justify-between">
            <h1 class="text-lg font-semibold">Database Backup</h1>
        </div>

        @if (session('error'))
            <div class="rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif

        <flux:card>
            <flux:heading size="md">Download Current DB Backup</flux:heading>

            <div class="mt-3 text-sm text-muted-foreground">
                Click download to generate a fresh SQL dump and download it as a ZIP file.
            </div>

            <form method="POST" action="{{ route('email-manager.db-backup.download') }}" class="mt-4">
                @csrf
                <flux:button type="submit">
                    Download ZIP Backup
                </flux:button>
            </form>

            <div class="mt-2 text-xs text-muted-foreground">
                Note: For very large databases this can take time. If it times out, we can switch to queue or CLI-based
                dump.
            </div>
        </flux:card>
    </div>
@endcomponent
