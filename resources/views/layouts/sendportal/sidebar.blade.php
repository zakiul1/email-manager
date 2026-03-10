@php($navigationItems = \App\Support\SendPortal\Navigation::items())
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">
    <flux:sidebar sticky collapsible="mobile"
        class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.header>
            <x-app-logo :sidebar="true" href="{{ route('sendportal.workspace.dashboard') }}" wire:navigate />
            <flux:sidebar.collapse class="lg:hidden" />
        </flux:sidebar.header>

        <flux:sidebar.nav>
            <flux:sidebar.group class="grid">
                @foreach ($navigationItems as $item)
                    @php($isCurrent = collect($item['patterns'])->contains(fn ($pattern) => request()->routeIs($pattern)))
                    <flux:sidebar.item
                        :href="route($item['route'])"
                        :current="$isCurrent"
                        wire:navigate
                        class="{{ !empty($item['disabled']) ? 'opacity-70' : '' }}">
                        {{ $item['label'] }}
                    </flux:sidebar.item>
                @endforeach
            </flux:sidebar.group>
        </flux:sidebar.nav>

        <flux:spacer />

        <div class="px-3 pb-3">
            <flux:sidebar.item
                :href="route('email-manager.dashboard')"
                :current="request()->routeIs('email-manager.*')"
                wire:navigate>
                Email Manager
            </flux:sidebar.item>
        </div>

        <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
    </flux:sidebar>

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

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('email-manager.dashboard')" wire:navigate>
                        Email Manager
                    </flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" class="w-full cursor-pointer" data-test="logout-button">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    {{ $slot }}

    <div id="toast-container" class="fixed top-4 right-4 z-[9999] flex flex-col gap-2 pointer-events-none"></div>

    @fluxScripts

    <script>
        (function () {
            const container = document.getElementById('toast-container');

            function toastClass(type) {
                switch (type) {
                    case 'success':
                        return 'border-emerald-200 bg-emerald-50 text-emerald-900 dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-100';
                    case 'error':
                        return 'border-red-200 bg-red-50 text-red-900 dark:border-red-800 dark:bg-red-950 dark:text-red-100';
                    case 'warning':
                        return 'border-amber-200 bg-amber-50 text-amber-900 dark:border-amber-800 dark:bg-amber-950 dark:text-amber-100';
                    default:
                        return 'border-zinc-200 bg-white text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100';
                }
            }

            function escapeHtml(value) {
                return String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function normalizeDetail(detail) {
                if (Array.isArray(detail)) {
                    return detail[0] || {};
                }

                return detail || {};
            }

            function showToast(payload = {}) {
                if (!container) return;

                const {
                    type = 'info',
                    message = 'Done',
                    timeout = 5000,
                } = normalizeDetail(payload);

                if (!message) return;

                const el = document.createElement('div');
                el.className = `pointer-events-auto w-[320px] max-w-[90vw] rounded-lg border px-4 py-3 text-sm shadow ${toastClass(type)}`;
                el.innerHTML = `
                    <div class="flex items-start justify-between gap-3">
                        <div class="leading-snug">${escapeHtml(message)}</div>
                        <button type="button" class="text-xs opacity-70 transition hover:opacity-100" aria-label="Close">✕</button>
                    </div>
                `;

                const removeToast = () => {
                    if (el.parentNode) {
                        el.remove();
                    }
                };

                el.querySelector('button')?.addEventListener('click', removeToast);

                container.appendChild(el);
                window.setTimeout(removeToast, Number(timeout) || 5000);
            }

            function closeFluxModal(modalName) {
                if (!modalName || !window.$flux || typeof window.$flux.modal !== 'function') {
                    return;
                }

                try {
                    const modal = window.$flux.modal(modalName);

                    if (modal && typeof modal.close === 'function') {
                        modal.close();
                    }
                } catch (error) {
                    console.warn('Unable to close Flux modal:', error);
                }
            }

            window.addEventListener('toast', function (event) {
                showToast(event.detail);
            });

            window.addEventListener('close-modal', function (event) {
                const detail = normalizeDetail(event.detail);
                closeFluxModal(detail.name);
            });

            document.addEventListener('livewire:init', () => {
                Livewire.on('toast', (event) => {
                    showToast(event);
                });

                Livewire.on('close-modal', (event) => {
                    const detail = normalizeDetail(event);
                    closeFluxModal(detail.name);
                });
            });

            const flash = @json(session('toast'));

            if (flash && flash.message) {
                window.setTimeout(() => showToast(flash), 150);
            }
        })();
    </script>
</body>

</html>