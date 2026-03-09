<div
    x-data="{ activeTab: 'template' }"
    class="space-y-6 p-6"
>
    <section>
        <div class="border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mb-6">
                <h1 class="text-2xl font-semibold tracking-tight text-zinc-950 dark:text-white">
                    {{ $template?->exists ? 'Edit Template' : 'Create Template' }}
                </h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                    Build a lightweight modern email template with placeholders, preheader, and preview-ready content.
                </p>
            </div>

            <div class="mb-6 border-b border-zinc-200 dark:border-zinc-700">
                <nav class="flex flex-wrap gap-2">
                    <button
                        type="button"
                        @click="activeTab = 'template'"
                        :class="activeTab === 'template'
                            ? 'border-zinc-900 bg-zinc-900 text-white dark:border-white dark:bg-white dark:text-zinc-900'
                            : 'border-zinc-300 bg-white text-zinc-700 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200'"
                        class="border px-4 py-2 text-sm font-medium"
                    >
                        Template
                    </button>

                    <button
                        type="button"
                        @click="activeTab = 'placeholders'"
                        :class="activeTab === 'placeholders'
                            ? 'border-zinc-900 bg-zinc-900 text-white dark:border-white dark:bg-white dark:text-zinc-900'
                            : 'border-zinc-300 bg-white text-zinc-700 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200'"
                        class="border px-4 py-2 text-sm font-medium"
                    >
                        Placeholder Helper
                    </button>

                    <button
                        type="button"
                        @click="activeTab = 'detected'"
                        :class="activeTab === 'detected'
                            ? 'border-zinc-900 bg-zinc-900 text-white dark:border-white dark:bg-white dark:text-zinc-900'
                            : 'border-zinc-300 bg-white text-zinc-700 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200'"
                        class="border px-4 py-2 text-sm font-medium"
                    >
                        Detected Placeholders
                    </button>

                    <button
                        type="button"
                        @click="activeTab = 'preview'"
                        :class="activeTab === 'preview'
                            ? 'border-zinc-900 bg-zinc-900 text-white dark:border-white dark:bg-white dark:text-zinc-900'
                            : 'border-zinc-300 bg-white text-zinc-700 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200'"
                        class="border px-4 py-2 text-sm font-medium"
                    >
                        Preview
                    </button>
                </nav>
            </div>

            <form wire:submit="save" class="space-y-6">
                <div x-show="activeTab === 'template'" x-cloak class="space-y-6">
                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Template Name</label>
                            <input
                                type="text"
                                wire:model.live="name"
                                class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                            >
                            @error('name') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Slug</label>
                            <input
                                type="text"
                                wire:model="slug"
                                class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                            >
                            @error('slug') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Subject</label>
                            <input
                                type="text"
                                wire:model="subject"
                                class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                            >
                            @error('subject') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Preheader</label>
                            <input
                                type="text"
                                wire:model="preheader"
                                class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                            >
                            @error('preheader') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="grid gap-6 md:grid-cols-3">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Editor</label>
                            <select
                                wire:model="editor"
                                class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                            >
                                <option value="code">Code</option>
                                <option value="hybrid">Hybrid</option>
                            </select>
                            @error('editor') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Status</label>
                            <select
                                wire:model="status"
                                class="w-full border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                            >
                                <option value="draft">Draft</option>
                                <option value="active">Active</option>
                                <option value="archived">Archived</option>
                            </select>
                            @error('status') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        <div class="flex items-end">
                            <label class="inline-flex items-center gap-3 border border-zinc-300 px-4 py-3 dark:border-zinc-700">
                                <input type="checkbox" wire:model="is_active" class="border-zinc-300 text-zinc-900 focus:ring-zinc-500">
                                <span class="text-sm text-zinc-700 dark:text-zinc-200">Template active</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">HTML Body</label>
                        <textarea
                            wire:model.live.debounce.300ms="html_content"
                            rows="18"
                            class="w-full border border-zinc-300 bg-white px-4 py-3 font-mono text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                        ></textarea>
                        @error('html_content') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Plain Text Body</label>
                        <textarea
                            wire:model.live.debounce.300ms="text_content"
                            rows="10"
                            class="w-full border border-zinc-300 bg-white px-4 py-3 font-mono text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                        ></textarea>
                        @error('text_content') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Version Notes</label>
                        <textarea
                            wire:model="version_notes"
                            rows="4"
                            class="w-full border border-zinc-300 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-500 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                        ></textarea>
                        @error('version_notes') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div x-show="activeTab === 'placeholders'" x-cloak class="space-y-4">
                    <div>
                        <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Placeholder Helper</h2>
                        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Supported placeholders for this phase.</p>
                    </div>

                    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($supportedPlaceholders as $key => $label)
                            <div class="border border-zinc-200 p-3 dark:border-zinc-700">
                                <div class="font-mono text-sm text-zinc-900 dark:text-white">{{ '{' . '{ ' . $key . ' }' . '}' }}</div>
                                <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ $label }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div x-show="activeTab === 'detected'" x-cloak class="space-y-4">
                    <div>
                        <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Detected Placeholders</h2>
                        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Placeholders detected from HTML and plain text content.</p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        @forelse ($detectedPlaceholders as $placeholder)
                            <span class="inline-flex border px-3 py-1 text-xs font-medium {{ in_array($placeholder, $unsupportedPlaceholders, true) ? 'border-red-300 text-red-700 dark:border-red-800 dark:text-red-300' : 'border-zinc-200 text-zinc-700 dark:border-zinc-700 dark:text-zinc-200' }}">
                                {{ $placeholder }}
                            </span>
                        @empty
                            <span class="text-sm text-zinc-500 dark:text-zinc-400">No placeholders detected yet.</span>
                        @endforelse
                    </div>

                    @if ($unsupportedPlaceholders !== [])
                        <div class="border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-800 dark:bg-red-950 dark:text-red-200">
                            Unsupported placeholders: {{ implode(', ', $unsupportedPlaceholders) }}
                        </div>
                    @endif
                </div>

                <div x-show="activeTab === 'preview'" x-cloak class="space-y-4">
                    <div>
                        <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Preview</h2>
                        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                            Live preview of the current template content. This is a lightweight in-form preview.
                        </p>
                    </div>

                    <div class="border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="mb-3 border-b border-zinc-200 pb-3 dark:border-zinc-700">
                            <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                {{ $subject !== '' ? $subject : 'No subject yet' }}
                            </div>
                            @if ($preheader !== '')
                                <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $preheader }}
                                </div>
                            @endif
                        </div>

                        <div class="prose prose-sm max-w-none dark:prose-invert">
                            {!! $html_content !== '' ? $html_content : '<p class="text-sm text-zinc-500">No HTML content yet.</p>' !!}
                        </div>
                    </div>

                    <div class="border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                        <div class="mb-2 text-sm font-medium text-zinc-900 dark:text-white">Plain text preview</div>
                        <pre class="whitespace-pre-wrap break-words text-sm text-zinc-700 dark:text-zinc-200">{{ $text_content !== '' ? $text_content : 'No plain text content yet.' }}</pre>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                    <a
                        href="{{ route('sendportal.workspace.templates.index') }}"
                        wire:navigate
                        class="inline-flex items-center justify-center border border-zinc-300 px-4 py-2.5 text-sm font-medium text-zinc-900 transition hover:bg-zinc-50 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800"
                    >
                        Cancel
                    </a>
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center bg-zinc-900 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-zinc-700 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200"
                    >
                        {{ $template?->exists ? 'Update Template' : 'Create Template' }}
                    </button>
                </div>
            </form>
        </div>
    </section>
</div>