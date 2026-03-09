<?php

namespace App\Livewire\SendPortal\Templates;

use App\Models\SendPortal\Template;
use App\Services\SendPortal\ActivityLogService;
use App\Services\SendPortal\TemplatePlaceholderService;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Form extends Component
{
    public ?Template $template = null;

    public string $name = '';
    public string $slug = '';
    public string $subject = '';
    public string $preheader = '';
    public string $html_content = '';
    public string $text_content = '';
    public string $editor = 'code';
    public string $status = 'draft';
    public string $version_notes = '';
    public bool $is_active = true;

    public array $detectedPlaceholders = [];
    public array $unsupportedPlaceholders = [];

    public function mount(?Template $template = null): void
    {
        $this->template = $template && $template->exists ? $template : null;

        abort_unless(
            auth()->user()?->can(
                $this->template ? 'update' : 'create',
                $this->template ?? Template::class
            ),
            403
        );

        if (! $this->template) {
            return;
        }

        $this->name = (string) $this->template->name;
        $this->slug = (string) $this->template->slug;
        $this->subject = (string) ($this->template->subject ?? '');
        $this->preheader = (string) ($this->template->preheader ?? '');
        $this->html_content = (string) ($this->template->html_content ?? '');
        $this->text_content = (string) ($this->template->text_content ?? '');
        $this->editor = (string) $this->template->editor;
        $this->status = (string) $this->template->status;
        $this->version_notes = (string) ($this->template->version_notes ?? '');
        $this->is_active = (bool) $this->template->is_active;

        $this->refreshPlaceholderState(app(TemplatePlaceholderService::class));
    }

    public function updatedName(): void
    {
        if (! $this->template) {
            $this->slug = Str::slug($this->name);
        }
    }

    public function updatedHtmlContent(TemplatePlaceholderService $placeholderService): void
    {
        $this->refreshPlaceholderState($placeholderService);
    }

    public function updatedTextContent(TemplatePlaceholderService $placeholderService): void
    {
        $this->refreshPlaceholderState($placeholderService);
    }

    public function save(TemplatePlaceholderService $placeholderService): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('sendportal_templates', 'slug')->ignore($this->template?->id),
            ],
            'subject' => ['required', 'string', 'max:255'],
            'preheader' => ['nullable', 'string', 'max:255'],
            'html_content' => ['required', 'string', 'min:10'],
            'text_content' => ['nullable', 'string'],
            'editor' => ['required', 'string', 'max:30'],
            'status' => ['required', Rule::in(['draft', 'active', 'archived'])],
            'version_notes' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['boolean'],
        ]);

        $analysis = $placeholderService->validateContent(
            $validated['html_content'],
            $validated['text_content']
        );

        $this->detectedPlaceholders = $analysis['placeholders'];
        $this->unsupportedPlaceholders = $analysis['unsupported'];

        if ($this->unsupportedPlaceholders !== []) {
            $this->addError(
                'html_content',
                'Unsupported placeholders found: '.implode(', ', $this->unsupportedPlaceholders)
            );

            return;
        }

        $payload = [
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'subject' => $validated['subject'],
            'preheader' => $validated['preheader'] ?: null,
            'html_content' => $validated['html_content'],
            'text_content' => $validated['text_content'] ?: null,
            'editor' => $validated['editor'],
            'status' => $validated['status'],
            'version_notes' => $validated['version_notes'] ?: null,
            'is_active' => $validated['is_active'],
            'builder_meta' => [
                'placeholders' => $analysis['placeholders'],
            ],
        ];

        $template = $this->template
            ? tap($this->template)->update($payload)
            : Template::query()->create($payload);

        app(ActivityLogService::class)->log(
            $this->template ? 'template.updated' : 'template.created',
            $template,
            ['name' => $template->name]
        );

        session()->flash('toast', [
            'type' => 'success',
            'message' => $this->template ? 'Template updated successfully.' : 'Template created successfully.',
        ]);

        $this->redirectRoute('sendportal.workspace.templates.index', navigate: true);
    }

    protected function refreshPlaceholderState(TemplatePlaceholderService $placeholderService): void
    {
        $analysis = $placeholderService->validateContent($this->html_content, $this->text_content);
        $this->detectedPlaceholders = $analysis['placeholders'];
        $this->unsupportedPlaceholders = $analysis['unsupported'];
    }

    public function render()
    {
        return view('livewire.sendportal.templates.form', [
            'supportedPlaceholders' => app(TemplatePlaceholderService::class)->supported(),
        ])->layout(config('sendportal-integration.layout'));
    }
}