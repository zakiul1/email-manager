<div class="space-y-6 p-6">
    <section class="grid gap-4 md:grid-cols-4">
        <div class="border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Total Templates</div>
            <div class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white">{{ number_format($stats['total']) }}</div>
        </div>
        <div class="border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Draft</div>
            <div class="mt-2 text-2xl font-semibold text-amber-600">{{ number_format($stats['draft']) }}</div>
        </div>
        <div class="border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Active</div>
            <div class="mt-2 text-2xl font-semibold text-emerald-600">{{ number_format($stats['active']) }}</div>
        </div>
        <div class="border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Archived</div>
            <div class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white">{{ number_format($stats['archived']) }}</div>
        </div>
    </section>

    <section class="border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-zinc-950 dark:text-white">Templates</h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Modern reusable email templates with preview, duplicate, and test-send support.</p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search templates..."
                    class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm text-zinc-900 outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                />

                <select
                    wire:model.live="status"
                    class="border border-zinc-300 bg-white px-4 py-2.5 text-sm text-zinc-900 outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                >
                    <option value="">All Status</option>
                    <option value="draft">Draft</option>
                    <option value="active">Active</option>
                    <option value="archived">Archived</option>
                </select>

                <a
                    href="{{ route('sendportal.workspace.templates.create') }}"
                    wire:navigate
                    class="inline-flex items-center justify-center bg-zinc-900 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-zinc-700 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200"
                >
                    Create Template
                </a>
            </div>
        </div>

        <div class="mt-6 overflow-hidden border border-zinc-200 dark:border-zinc-700">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/60">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Template</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Subject</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Usage</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Tests</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Updated</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-zinc-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900">
                        @forelse ($templates as $template)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="text-sm font-medium text-zinc-900 dark:text-white">{{ $template->name }}</div>
                                    <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ $template->slug }}</div>
                                </td>

                                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">
                                    {{ $template->subject }}
                                </td>

                                <td class="px-4 py-3">
                                    <span class="inline-flex px-3 py-1 text-xs font-medium {{ $template->status === 'active' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-200' : ($template->status === 'draft' ? 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-200' : 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200') }}">
                                        {{ ucfirst($template->status) }}
                                    </span>
                                </td>

                                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">
                                    {{ number_format($template->usage_count) }}
                                </td>

                                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">
                                    <div>{{ number_format($template->tests_count) }}</div>
                                    <div class="text-xs text-zinc-400">
                                        {{ $template->last_test_sent_at?->diffForHumans() ?? 'No test yet' }}
                                    </div>
                                </td>

                                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">
                                    {{ $template->updated_at?->diffForHumans() }}
                                </td>

                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <a
                                            href="{{ route('sendportal.workspace.templates.preview', $template) }}"
                                            wire:navigate
                                            class="border border-zinc-300 px-3 py-2 text-xs font-medium text-zinc-900 hover:bg-zinc-50 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800"
                                        >
                                            Preview
                                        </a>

                                        <a
                                            href="{{ route('sendportal.workspace.templates.test-send', $template) }}"
                                            wire:navigate
                                            class="border border-sky-300 px-3 py-2 text-xs font-medium text-sky-700 hover:bg-sky-50 dark:border-sky-800 dark:text-sky-300 dark:hover:bg-sky-950"
                                        >
                                            Test Send
                                        </a>

                                        <button
                                            type="button"
                                            wire:click="duplicateTemplate({{ $template->id }})"
                                            class="border border-zinc-300 px-3 py-2 text-xs font-medium text-zinc-900 hover:bg-zinc-50 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800"
                                        >
                                            Duplicate
                                        </button>

                                        <a
                                            href="{{ route('sendportal.workspace.templates.edit', $template) }}"
                                            wire:navigate
                                            class="border border-zinc-300 px-3 py-2 text-xs font-medium text-zinc-900 hover:bg-zinc-50 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800"
                                        >
                                            Edit
                                        </a>

                                        <button
                                            type="button"
                                            wire:click="confirmDelete({{ $template->id }}, {{ \Illuminate\Support\Js::from($template->name) }})"
                                            x-on:click="$flux.modal('delete-template').show()"
                                            class="border border-red-300 px-3 py-2 text-xs font-medium text-red-700 hover:bg-red-50 dark:border-red-800 dark:text-red-300 dark:hover:bg-red-950"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-10 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                    No templates found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-5">
            {{ $templates->links() }}
        </div>
    </section>

    @include('livewire.sendportal.partials.confirm-delete-modal', [
        'modalName' => 'delete-template',
        'title' => 'Delete Template',
        'message' => 'Are you sure you want to delete this template?',
        'itemName' => $deleteName ?? null,
        'warning' => 'This action cannot be undone.',
        'confirmAction' => 'deleteConfirmed',
        'confirmTarget' => 'deleteConfirmed',
        'confirmText' => 'Delete Template',
        'loadingText' => 'Deleting...',
    ])
</div>