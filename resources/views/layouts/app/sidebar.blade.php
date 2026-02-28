<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">
    <flux:sidebar sticky collapsible="mobile"
        class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.header>
            <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
            <flux:sidebar.collapse class="lg:hidden" />
        </flux:sidebar.header>

        <flux:sidebar.nav>
            <flux:sidebar.group class="grid">
                <flux:sidebar.item :href="route('email-manager.dashboard')"
                    :current="request()->routeIs('email-manager.dashboard')" wire:navigate>
                    Dashboard
                </flux:sidebar.item>

                <flux:sidebar.item :href="route('email-manager.categories')"
                    :current="request()->routeIs('email-manager.categories*')" wire:navigate>
                    Categories
                </flux:sidebar.item>

                <flux:sidebar.item :href="route('email-manager.imports.upload')"
                    :current="request()->routeIs('email-manager.imports.upload')" wire:navigate>
                    Import
                </flux:sidebar.item>

                <flux:sidebar.item :href="route('email-manager.emails')"
                    :current="request()->routeIs('email-manager.emails')" wire:navigate>
                    Emails
                </flux:sidebar.item>

                <flux:sidebar.item :href="route('email-manager.suppressions')"
                    :current="request()->routeIs('email-manager.suppressions')" wire:navigate>
                    Unsubscribe
                </flux:sidebar.item>

                <flux:sidebar.item :href="route('email-manager.domain-unsubscribes')"
                    :current="request()->routeIs('email-manager.domain-unsubscribes')" wire:navigate>
                    Domain Unsubscribes
                </flux:sidebar.item>

                <flux:sidebar.item :href="route('email-manager.exports')"
                    :current="request()->routeIs('email-manager.exports*')" wire:navigate>
                    Exports
                </flux:sidebar.item>

                {{-- ✅ NEW: Database Backup --}}
                <flux:sidebar.item :href="route('email-manager.db-backup.index')"
                    :current="request()->routeIs('email-manager.db-backup.*')" wire:navigate>
                    Database Backup
                </flux:sidebar.item>
            </flux:sidebar.group>
        </flux:sidebar.nav>

        <flux:spacer />

        <flux:sidebar.nav>
        </flux:sidebar.nav>

        <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
    </flux:sidebar>

    <!-- Mobile User Menu -->
    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" inset="left" />

        <flux:spacer />

        <flux:dropdown position="top" align="end">
            <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />

            <flux:menu>
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <flux:avatar :name="auth()->user()->name" :initials="auth()->user()->initials()" />

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('profile.edit')" wire:navigate>
                        {{ __('Settings') }}
                    </flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" class="w-full cursor-pointer"
                        data-test="logout-button">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    {{ $slot }}

    {{-- ✅ Global Toast Container (top-right) --}}
    <div id="toast-container" class="fixed top-4 right-4 z-[9999] flex flex-col gap-2"></div>

    @fluxScripts

    {{-- ✅ Global Toast Script (dispatch + session flash support) --}}
    <script>
        (function() {
            const container = document.getElementById('toast-container');

            function toastClass(type) {
                switch (type) {
                    case 'success':
                        return 'border-emerald-200 bg-emerald-50 text-emerald-900';
                    case 'error':
                        return 'border-red-200 bg-red-50 text-red-900';
                    case 'warning':
                        return 'border-amber-200 bg-amber-50 text-amber-900';
                    default:
                        return 'border-zinc-200 bg-white text-zinc-900';
                }
            }

            function showToast({
                type = 'info',
                message = 'Done',
                timeout = 5000
            }) {
                if (!container) return;

                const el = document.createElement('div');
                el.className = `w-[320px] max-w-[90vw] rounded-lg border px-4 py-3 text-sm shadow ${toastClass(type)}`;

                el.innerHTML = `
                    <div class="flex items-start justify-between gap-3">
                        <div class="leading-snug">${message}</div>
                        <button class="text-xs opacity-70 hover:opacity-100" aria-label="Close">✕</button>
                    </div>
                `;

                el.querySelector('button').addEventListener('click', () => el.remove());

                container.appendChild(el);

                setTimeout(() => el.remove(), timeout);
            }

            // Livewire dispatch event: $this->dispatch('toast', type: 'success', message: '...')
            window.addEventListener('toast', function(event) {
                showToast(event.detail || {});
            });

            // Show toast after redirect (session flash)
            const flash = @json(session('toast'));
            if (flash && flash.message) {
                setTimeout(() => showToast(flash), 150);
            }
        })();
    </script>
</body>

</html>
