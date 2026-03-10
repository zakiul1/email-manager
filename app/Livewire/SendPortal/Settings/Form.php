<?php

namespace App\Livewire\SendPortal\Settings;

use App\Models\SendPortal\SmtpPool;
use App\Services\SendPortal\SettingsService;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Form extends Component
{
    public string $activeTab = 'general';

    public string $default_from_name = '';
    public string $default_from_email = '';
    public string $default_reply_to_name = '';
    public string $default_reply_to_email = '';
    public ?int $default_smtp_pool_id = null;
    public string $default_timezone = 'UTC';

    public bool $tracking_opens_enabled = true;
    public bool $tracking_clicks_enabled = true;
    public bool $unsubscribe_footer_enabled = true;
    public string $public_base_url = '';
    public bool $signed_public_routes_enabled = false;
    public string $webhook_secret = '';

    public int $retry_delay_minutes = 15;
    public int $max_retry_attempts = 3;
    public int $dispatch_chunk_size = 200;
    public bool $allow_laravel_mailer_fallback = false;

    public string $default_footer_text = '';
    public string $default_unsubscribe_text = 'Unsubscribe';
    public string $default_editor_mode = 'code';

    public string $default_report_range = '30_days';

    public string $mail_mailer = 'smtp';
    public string $mail_host = '';
    public ?int $mail_port = 587;
    public string $mail_username = '';
    public string $mail_password = '';
    public string $mail_encryption = 'tls';
    public string $mail_from_address = '';
    public string $mail_from_name = '';

    public function mount(SettingsService $settingsService): void
    {
        abort_unless(auth()->check(), 403);

        $general = $settingsService->getGroup('general');
        $tracking = $settingsService->getGroup('tracking');
        $queue = $settingsService->getGroup('queue');
        $templates = $settingsService->getGroup('templates');
        $reporting = $settingsService->getGroup('reporting');
        $security = $settingsService->getGroup('security');
        $mail = $settingsService->getGroup('mail');

        $this->default_from_name = (string) ($general['default_from_name'] ?? '');
        $this->default_from_email = (string) ($general['default_from_email'] ?? '');
        $this->default_reply_to_name = (string) ($general['default_reply_to_name'] ?? '');
        $this->default_reply_to_email = (string) ($general['default_reply_to_email'] ?? '');
        $this->default_smtp_pool_id = isset($general['default_smtp_pool_id']) ? (int) $general['default_smtp_pool_id'] : null;
        $this->default_timezone = (string) ($general['default_timezone'] ?? 'UTC');

        $this->tracking_opens_enabled = (bool) ($tracking['tracking_opens_enabled'] ?? true);
        $this->tracking_clicks_enabled = (bool) ($tracking['tracking_clicks_enabled'] ?? true);
        $this->unsubscribe_footer_enabled = (bool) ($tracking['unsubscribe_footer_enabled'] ?? true);
        $this->public_base_url = (string) ($tracking['public_base_url'] ?? '');

        $this->retry_delay_minutes = (int) ($queue['retry_delay_minutes'] ?? 15);
        $this->max_retry_attempts = (int) ($queue['max_retry_attempts'] ?? 3);
        $this->dispatch_chunk_size = (int) ($queue['dispatch_chunk_size'] ?? 200);
        $this->allow_laravel_mailer_fallback = (bool) ($queue['allow_laravel_mailer_fallback'] ?? false);

        $this->default_footer_text = (string) ($templates['default_footer_text'] ?? '');
        $this->default_unsubscribe_text = (string) ($templates['default_unsubscribe_text'] ?? 'Unsubscribe');
        $this->default_editor_mode = (string) ($templates['default_editor_mode'] ?? 'code');

        $this->default_report_range = (string) ($reporting['default_report_range'] ?? '30_days');

        $this->signed_public_routes_enabled = (bool) ($security['signed_public_routes_enabled'] ?? false);
        $this->webhook_secret = (string) ($security['webhook_secret'] ?? '');

        $this->mail_mailer = (string) ($mail['mail_mailer'] ?? 'smtp');
        $this->mail_host = (string) ($mail['mail_host'] ?? '');
        $this->mail_port = isset($mail['mail_port']) ? (int) ($mail['mail_port']) : 587;
        $this->mail_username = (string) ($mail['mail_username'] ?? '');
        $this->mail_password = '';
        $this->mail_encryption = (string) ($mail['mail_encryption'] ?? 'tls');
        $this->mail_from_address = (string) ($mail['mail_from_address'] ?? '');
        $this->mail_from_name = (string) ($mail['mail_from_name'] ?? '');
    }

    public function save(SettingsService $settingsService): void
    {
        abort_unless(auth()->check(), 403);

        $validated = $this->validate([
            'default_from_name' => ['nullable', 'string', 'max:255'],
            'default_from_email' => ['nullable', 'email', 'max:255'],
            'default_reply_to_name' => ['nullable', 'string', 'max:255'],
            'default_reply_to_email' => ['nullable', 'email', 'max:255'],
            'default_smtp_pool_id' => ['nullable', 'integer', 'exists:sp_smtp_pools,id'],
            'default_timezone' => ['required', 'string', 'max:100'],

            'tracking_opens_enabled' => ['boolean'],
            'tracking_clicks_enabled' => ['boolean'],
            'unsubscribe_footer_enabled' => ['boolean'],
            'public_base_url' => ['nullable', 'url', 'max:500'],

            'retry_delay_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'max_retry_attempts' => ['required', 'integer', 'min:1', 'max:20'],
            'dispatch_chunk_size' => ['required', 'integer', 'min:1', 'max:5000'],
            'allow_laravel_mailer_fallback' => ['boolean'],

            'default_footer_text' => ['nullable', 'string', 'max:2000'],
            'default_unsubscribe_text' => ['required', 'string', 'max:255'],
            'default_editor_mode' => ['required', Rule::in(['code', 'builder'])],

            'default_report_range' => ['required', Rule::in(['7_days', '30_days', '90_days'])],

            'signed_public_routes_enabled' => ['boolean'],
            'webhook_secret' => ['nullable', 'string', 'max:500'],

            'mail_mailer' => ['required', Rule::in(['smtp', 'sendmail', 'log', 'array', 'failover'])],
            'mail_host' => ['nullable', 'string', 'max:255'],
            'mail_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'mail_username' => ['nullable', 'string', 'max:255'],
            'mail_password' => ['nullable', 'string', 'max:500'],
            'mail_encryption' => ['nullable', Rule::in(['tls', 'ssl', ''])],
            'mail_from_address' => ['nullable', 'email', 'max:255'],
            'mail_from_name' => ['nullable', 'string', 'max:255'],
        ]);

        $settingsService->putMany('general', [
            'default_from_name' => $validated['default_from_name'] ?: null,
            'default_from_email' => $validated['default_from_email'] ?: null,
            'default_reply_to_name' => $validated['default_reply_to_name'] ?: null,
            'default_reply_to_email' => $validated['default_reply_to_email'] ?: null,
            'default_smtp_pool_id' => $validated['default_smtp_pool_id'],
            'default_timezone' => $validated['default_timezone'],
        ]);

        $settingsService->putMany('tracking', [
            'tracking_opens_enabled' => $validated['tracking_opens_enabled'],
            'tracking_clicks_enabled' => $validated['tracking_clicks_enabled'],
            'unsubscribe_footer_enabled' => $validated['unsubscribe_footer_enabled'],
            'public_base_url' => $validated['public_base_url'] ?: null,
        ]);

        $settingsService->putMany('queue', [
            'retry_delay_minutes' => $validated['retry_delay_minutes'],
            'max_retry_attempts' => $validated['max_retry_attempts'],
            'dispatch_chunk_size' => $validated['dispatch_chunk_size'],
            'allow_laravel_mailer_fallback' => $validated['allow_laravel_mailer_fallback'],
        ]);

        $settingsService->putMany('templates', [
            'default_footer_text' => $validated['default_footer_text'] ?: null,
            'default_unsubscribe_text' => $validated['default_unsubscribe_text'],
            'default_editor_mode' => $validated['default_editor_mode'],
        ]);

        $settingsService->putMany('reporting', [
            'default_report_range' => $validated['default_report_range'],
        ]);

        $settingsService->putMany('security', [
            'signed_public_routes_enabled' => $validated['signed_public_routes_enabled'],
            'webhook_secret' => $validated['webhook_secret'] ?: null,
        ], ['webhook_secret']);

        $mailPayload = [
            'mail_mailer' => $validated['mail_mailer'],
            'mail_host' => $validated['mail_host'] ?: null,
            'mail_port' => $validated['mail_port'],
            'mail_username' => $validated['mail_username'] ?: null,
            'mail_encryption' => $validated['mail_encryption'] ?: null,
            'mail_from_address' => $validated['mail_from_address'] ?: null,
            'mail_from_name' => $validated['mail_from_name'] ?: null,
        ];

        if (filled($validated['mail_password'])) {
            $mailPayload['mail_password'] = $validated['mail_password'];
            $settingsService->putMany('mail', $mailPayload, ['mail_password']);
        } else {
            $settingsService->putMany('mail', $mailPayload);
        }

        $this->dispatch('toast', type: 'success', message: 'SendPortal settings updated successfully.');
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['general', 'tracking', 'queue', 'templates', 'reporting', 'security', 'mail'], true)) {
            $this->activeTab = $tab;
        }
    }

    public function render()
    {
        return view('livewire.sendportal.settings.form', [
            'smtpPools' => SmtpPool::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
        ])->layout(config('sendportal-integration.layout'));
    }
}