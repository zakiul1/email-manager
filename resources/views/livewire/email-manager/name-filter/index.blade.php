<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-lg font-semibold">Email Filter</h1>
    </div>

    {{-- 50/50 --}}
    <div class="grid gap-4 md:grid-cols-2">

        {{-- LEFT --}}
        <flux:card>
            <flux:heading size="md">Input Emails</flux:heading>

            <div class="mt-3 space-y-3">

                {{-- Tabs --}}
                <div class="flex gap-2">
                    <flux:button
                        type="button"
                        size="sm"
                        wire:click.prevent="$set('inputMode','textarea')"
                        :disabled="$filterRunning"
                        :variant="$inputMode === 'textarea' ? 'primary' : 'outline'">
                        Textarea
                    </flux:button>

                    <flux:button
                        type="button"
                        size="sm"
                        wire:click.prevent="$set('inputMode','file')"
                        :disabled="$filterRunning"
                        :variant="$inputMode === 'file' ? 'primary' : 'outline'">
                        File
                    </flux:button>
                </div>

                {{-- REPORT BOX (TOP) --}}
                @if($filterId)
                    <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 text-sm space-y-2">
                        @if($filterRunning)
                            <div wire:poll.800ms="processFilterChunk" class="text-zinc-600 font-medium">
                                Filtering... ({{ $filterProcessed }} / {{ $filterTotal }})
                            </div>
                        @elseif($filterDone)
                            <div class="text-zinc-700 font-medium">
                                Filter completed ✅
                            </div>
                        @endif

                        <div class="grid grid-cols-2 gap-2">
                            <div>Total: <b>{{ $filterTotal }}</b></div>
                            <div>Processed: <b>{{ $filterProcessed }}</b></div>

                            <div>Invalid: <b>{{ $filterInvalid }}</b></div>
                            <div>Dup in input: <b>{{ $filterDuplicatesInput }}</b></div>

                            <div>Global Suppressed: <b>{{ $filterGlobalSuppressed }}</b></div>
                            <div>Domain Unsubscribed: <b>{{ $filterDomainUnsubscribed }}</b></div>

                            <div>Matched Total: <b>{{ $filterMatched }}</b></div>
                            <div>OK (Eligible): <b>{{ $filterOk }}</b></div>
                        </div>
                    </div>
                @endif

                {{-- INPUT --}}
                @if($inputMode === 'textarea')
                    <flux:textarea
                        wire:model.live="textarea"
                        label="Paste emails (line break / comma / semicolon)"
                        rows="10" />
                @else
                    <flux:input type="file" wire:model="uploadFile" label="Upload CSV/TXT" />
                @endif

                <flux:button
                    type="button"
                    wire:click.prevent="startFilter"
                    :disabled="$filterRunning">
                    Start Filter
                </flux:button>

                @error('textarea') <div class="text-sm text-red-600">{{ $message }}</div> @enderror
                @error('uploadFile') <div class="text-sm text-red-600">{{ $message }}</div> @enderror
            </div>
        </flux:card>

        {{-- RIGHT --}}
        <flux:card>
            <flux:heading size="md">Eligible Emails (Uploadable)</flux:heading>

            <div class="mt-3 space-y-3">

                {{-- UPLOAD REPORT BOX (TOP RIGHT) --}}
                @if($uploadId)
                    <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 text-sm space-y-2">
                        @if($uploadRunning)
                            <div wire:poll.800ms="processUploadChunk" class="text-zinc-600 font-medium">
                                Uploading... ({{ $uploadProcessed }} / {{ $uploadTotal }})
                            </div>
                        @elseif($uploadDone)
                            <div class="text-zinc-700 font-medium">
                                Upload completed ✅
                            </div>
                        @endif

                        <div class="grid grid-cols-2 gap-2">
                            <div>Total: <b>{{ $uploadTotal }}</b></div>
                            <div>Processed: <b>{{ $uploadProcessed }}</b></div>

                            <div>Inserted: <b>{{ $uploadInserted }}</b></div>
                            <div>Dup DB: <b>{{ $uploadDuplicatesDb }}</b></div>

                            <div>Dup Category: <b>{{ $uploadDuplicatesCategory }}</b></div>
                            <div>Invalid: <b>{{ $uploadInvalid }}</b></div>
                        </div>
                    </div>
                @endif

                {{-- TOP ROW: category + clear + upload --}}
                <div class="flex flex-col gap-2 md:flex-row md:items-end">
                    <div class="flex-1">
                        <flux:select wire:model.live="category_id" label="Select Category">
                            <option value="0">-- select --</option>
                            @foreach($categories as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </flux:select>

                        @error('category_id') <div class="text-sm text-red-600">{{ $message }}</div> @enderror
                    </div>

                    <div class="flex gap-2">
                        <flux:button
                            type="button"
                            variant="outline"
                            wire:click.prevent="clearEligible"
                            :disabled="$uploadRunning">
                            Clear
                        </flux:button>

                        <flux:button
                            type="button"
                            wire:click.prevent="startUploadEligible"
                            :disabled="!$filterDone || $uploadRunning || $category_id<=0">
                            Upload to Category
                        </flux:button>
                    </div>
                </div>

                {{-- Eligible emails textarea --}}
                <flux:textarea
                    label="Eligible emails (not matched)"
                    rows="12"
                    wire:model.live="okText" />

                @error('okText') <div class="text-sm text-red-600">{{ $message }}</div> @enderror

            </div>
        </flux:card>
    </div>
</div>