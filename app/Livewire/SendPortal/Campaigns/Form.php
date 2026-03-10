<?php

namespace App\Livewire\SendPortal\Campaigns;

use App\Models\Category;
use App\Models\SendPortal\Campaign;
use App\Models\SendPortal\CampaignAudience;
use App\Models\SendPortal\SmtpPool;
use App\Models\SendPortal\Template;
use App\Services\SendPortal\ActivityLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Form extends Component
{
    public ?Campaign $campaign = null;

    public string $name = '';
    public string $subject = '';
    public string $preheader = '';
    public string $status = 'draft';
    public string $delivery_mode = 'draft';
    public ?int $template_id = null;
    public ?int $smtp_pool_id = null;
    public string $from_name = '';
    public string $from_email = '';
    public string $reply_to_name = '';
    public string $reply_to_email = '';
    public string $html_content = '';
    public string $text_content = '';
    public array $audience_ids = [];
    public ?string $scheduled_at = null;
    public string $notes = '';
    public string $category_search = '';

    public function mount(?Campaign $campaign = null): void
    {
        $this->campaign = $campaign && $campaign->exists ? $campaign : null;

        abort_unless(
            auth()->user()?->can(
                $this->campaign ? 'update' : 'create',
                $this->campaign ?? Campaign::class
            ),
            403
        );

        if (! $this->campaign) {
            return;
        }

        $this->name = (string) $this->campaign->name;
        $this->subject = (string) $this->campaign->subject;
        $this->preheader = (string) ($this->campaign->preheader ?? '');
        $this->status = (string) $this->campaign->status;
        $this->delivery_mode = (string) $this->campaign->delivery_mode;
        $this->template_id = $this->campaign->template_id;
        $this->smtp_pool_id = $this->campaign->smtp_pool_id;
        $this->from_name = (string) ($this->campaign->from_name ?? '');
        $this->from_email = (string) ($this->campaign->from_email ?? '');
        $this->reply_to_name = (string) ($this->campaign->reply_to_name ?? '');
        $this->reply_to_email = (string) ($this->campaign->reply_to_email ?? '');
        $this->html_content = (string) ($this->campaign->html_content ?? '');
        $this->text_content = (string) ($this->campaign->text_content ?? '');
        $this->scheduled_at = $this->campaign->scheduled_at?->format('Y-m-d\TH:i');

        $this->audience_ids = $this->campaign->audiences()
            ->pluck('source_id')
            ->map(fn ($id) => (string) $id)
            ->values()
            ->all();

        $this->notes = (string) ($this->campaign->meta['notes'] ?? '');
    }

    public function updatedTemplateId(): void
    {
        if (! $this->template_id) {
            return;
        }

        $template = Template::query()->find($this->template_id);

        if (! $template) {
            return;
        }

        $this->subject = $this->subject !== '' ? $this->subject : (string) ($template->subject ?? '');
        $this->preheader = $this->preheader !== '' ? $this->preheader : (string) ($template->preheader ?? '');
        $this->html_content = $this->html_content !== '' ? $this->html_content : (string) ($template->html_content ?? '');
        $this->text_content = $this->text_content !== '' ? $this->text_content : (string) ($template->text_content ?? '');
    }

    public function toggleAudience(string|int $categoryId): void
    {
        $categoryId = (string) $categoryId;

        if (in_array($categoryId, $this->audience_ids, true)) {
            $this->audience_ids = array_values(array_filter(
                $this->audience_ids,
                fn ($id) => (string) $id !== $categoryId
            ));

            return;
        }

        $this->audience_ids[] = $categoryId;
        $this->audience_ids = array_values(array_unique(array_map('strval', $this->audience_ids)));
    }

    public function save(ActivityLogService $activityLogService): void
    {
        $normalizedAudienceIds = collect($this->audience_ids)
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $this->audience_ids = array_map('strval', $normalizedAudienceIds);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'preheader' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['draft', 'scheduled', 'active', 'paused', 'completed', 'cancelled', 'failed'])],
            'delivery_mode' => ['required', Rule::in(['draft', 'schedule', 'manual'])],
            'template_id' => ['nullable', 'integer', 'exists:sendportal_templates,id'],
            'smtp_pool_id' => ['nullable', 'integer', 'exists:sp_smtp_pools,id'],
            'from_name' => ['nullable', 'string', 'max:255'],
            'from_email' => ['nullable', 'email', 'max:255'],
            'reply_to_name' => ['nullable', 'string', 'max:255'],
            'reply_to_email' => ['nullable', 'email', 'max:255'],
            'html_content' => ['nullable', 'string'],
            'text_content' => ['nullable', 'string'],
            'audience_ids' => ['required', 'array', 'min:1'],
            'audience_ids.*' => ['required', 'integer', 'exists:categories,id'],
            'scheduled_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        if ($validated['delivery_mode'] === 'schedule' && blank($validated['scheduled_at'])) {
            $this->addError('scheduled_at', 'Scheduled date and time is required when delivery mode is schedule.');

            return;
        }

        DB::transaction(function () use ($validated, $activityLogService) {
            $payload = [
                'name' => $validated['name'],
                'subject' => $validated['subject'],
                'preheader' => $validated['preheader'] ?: null,
                'status' => $validated['status'],
                'delivery_mode' => $validated['delivery_mode'],
                'template_id' => $validated['template_id'],
                'smtp_pool_id' => $validated['smtp_pool_id'],
                'from_name' => $validated['from_name'] ?: null,
                'from_email' => $validated['from_email'] ?: null,
                'reply_to_name' => $validated['reply_to_name'] ?: null,
                'reply_to_email' => $validated['reply_to_email'] ?: null,
                'html_content' => $validated['html_content'] ?: null,
                'text_content' => $validated['text_content'] ?: null,
                'audience_type' => 'category',
                'audience_reference' => implode(',', $validated['audience_ids']),
                'scheduled_at' => $validated['scheduled_at'] ?: null,
                'meta' => [
                    'notes' => $validated['notes'] ?: null,
                ],
            ];

            $campaign = $this->campaign
                ? tap($this->campaign)->update($payload)
                : Campaign::query()->create($payload);

            CampaignAudience::query()
                ->where('campaign_id', $campaign->id)
                ->delete();

            foreach ($validated['audience_ids'] as $sourceId) {
                CampaignAudience::query()->create([
                    'campaign_id' => $campaign->id,
                    'source_type' => 'category',
                    'source_id' => $sourceId,
                    'filters' => null,
                ]);
            }

            $activityLogService->log(
                $this->campaign ? 'campaign.updated' : 'campaign.created',
                $campaign,
                [
                    'name' => $campaign->name,
                    'audience_type' => 'category',
                    'audience_count' => count($validated['audience_ids']),
                ]
            );
        });

        session()->flash('toast', [
            'type' => 'success',
            'message' => $this->campaign ? 'Campaign updated successfully.' : 'Campaign created successfully.',
        ]);

        $this->redirectRoute('sendportal.workspace.campaigns.index', navigate: true);
    }

    public function render()
    {
        $categories = Category::query()
            ->when($this->category_search !== '', function ($query) {
                $query->where('name', 'like', '%' . $this->category_search . '%');
            })
            ->orderBy('name')
            ->get();

        return view('livewire.sendportal.campaigns.form', [
            'templates' => Template::query()->orderBy('name')->get(),
            'smtpPools' => SmtpPool::query()->where('is_active', true)->orderBy('name')->get(),
            'categories' => $categories,
        ])->layout(config('sendportal-integration.layout'));
    }
}