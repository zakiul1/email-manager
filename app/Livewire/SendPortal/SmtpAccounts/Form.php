<?php

namespace App\Livewire\SendPortal\SmtpAccounts;

use App\Enums\SendPortal\SmtpAccountStatus;
use App\Models\SendPortal\SmtpAccount;
use App\Services\SendPortal\ActivityLogService;
use App\Services\SendPortal\SmtpAccountEncryption;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Form extends Component
{
    public ?SmtpAccount $account = null;

    public string $name = '';
    public string $provider_label = '';
    public string $driver_type = 'smtp';
    public string $mailer_name = 'smtp';
    public string $host = '';
    public ?int $port = 587;
    public string $username = '';
    public string $password = '';
    public string $encryption = 'tls';
    public string $from_name = '';
    public string $from_email = '';
    public string $reply_to_name = '';
    public string $reply_to_email = '';
    public ?int $daily_limit = null;
    public ?int $hourly_limit = null;
    public ?int $warmup_limit = null;
    public int $priority = 100;
    public string $status = 'active';
    public bool $is_default = false;
    public string $notes = '';

    public function mount(?SmtpAccount $account = null, SmtpAccountEncryption $encryption): void
    {
        $this->account = $account && $account->exists ? $account : null;

        abort_unless(
            auth()->user()?->can(
                $this->account ? 'update' : 'create',
                $this->account ?? SmtpAccount::class
            ),
            403
        );

        if (! $this->account) {
            return;
        }

        $this->name = (string) $this->account->name;
        $this->provider_label = (string) ($this->account->provider_label ?? '');
        $this->driver_type = (string) $this->account->driver_type;
        $this->mailer_name = (string) $this->account->mailer_name;
        $this->host = (string) ($this->account->host ?? '');
        $this->port = $this->account->port;
        $this->username = (string) ($this->account->username ?? '');
        $this->password = (string) ($encryption->decrypt($this->account->encrypted_password) ?? '');
        $this->encryption = (string) ($this->account->encryption ?? '');
        $this->from_name = (string) ($this->account->from_name ?? '');
        $this->from_email = (string) ($this->account->from_email ?? '');
        $this->reply_to_name = (string) ($this->account->reply_to_name ?? '');
        $this->reply_to_email = (string) ($this->account->reply_to_email ?? '');
        $this->daily_limit = $this->account->daily_limit;
        $this->hourly_limit = $this->account->hourly_limit;
        $this->warmup_limit = $this->account->warmup_limit;
        $this->priority = (int) $this->account->priority;
        $this->status = $this->account->status->value;
        $this->is_default = (bool) $this->account->is_default;
        $this->notes = (string) ($this->account->notes ?? '');
    }

    public function save(SmtpAccountEncryption $encryption): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'provider_label' => ['nullable', 'string', 'max:255'],
            'driver_type' => ['required', 'string', 'max:30'],
            'mailer_name' => ['required', 'string', 'max:50'],
            'host' => ['required_if:driver_type,smtp', 'nullable', 'string', 'max:255'],
            'port' => ['required_if:driver_type,smtp', 'nullable', 'integer', 'min:1', 'max:65535'],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => [$this->account ? 'nullable' : 'required', 'string', 'max:500'],
            'encryption' => ['nullable', 'string', 'max:20'],
            'from_name' => ['nullable', 'string', 'max:255'],
            'from_email' => ['nullable', 'email', 'max:255'],
            'reply_to_name' => ['nullable', 'string', 'max:255'],
            'reply_to_email' => ['nullable', 'email', 'max:255'],
            'daily_limit' => ['nullable', 'integer', 'min:1'],
            'hourly_limit' => ['nullable', 'integer', 'min:1'],
            'warmup_limit' => ['nullable', 'integer', 'min:1'],
            'priority' => ['required', 'integer', 'min:1', 'max:100000'],
            'status' => ['required', Rule::in(array_column(SmtpAccountStatus::options(), 'value'))],
            'is_default' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        if ($this->is_default) {
            SmtpAccount::query()->update(['is_default' => false]);
        }

        $payload = [
            'name' => $validated['name'],
            'provider_label' => $validated['provider_label'] ?: null,
            'driver_type' => $validated['driver_type'],
            'mailer_name' => $validated['mailer_name'],
            'host' => $validated['host'] ?: null,
            'port' => $validated['port'],
            'username' => $validated['username'] ?: null,
            'encryption' => $validated['encryption'] ?: null,
            'from_name' => $validated['from_name'] ?: null,
            'from_email' => $validated['from_email'] ?: null,
            'reply_to_name' => $validated['reply_to_name'] ?: null,
            'reply_to_email' => $validated['reply_to_email'] ?: null,
            'daily_limit' => $validated['daily_limit'],
            'hourly_limit' => $validated['hourly_limit'],
            'warmup_limit' => $validated['warmup_limit'],
            'priority' => $validated['priority'],
            'status' => $validated['status'],
            'is_default' => $validated['is_default'],
            'notes' => $validated['notes'] ?: null,
        ];

        if (($validated['password'] ?? '') !== '') {
            $payload['encrypted_password'] = $encryption->encrypt($validated['password']);
        }

        $account = $this->account
            ? tap($this->account)->update($payload)
            : SmtpAccount::query()->create($payload);

        app(ActivityLogService::class)->log(
            $this->account ? 'smtp_account.updated' : 'smtp_account.created',
            $account,
            ['name' => $account->name]
        );

        session()->flash('toast', [
            'type' => 'success',
            'message' => $this->account ? 'SMTP account updated successfully.' : 'SMTP account created successfully.',
        ]);

        $this->redirectRoute('sendportal.workspace.smtp-accounts.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.sendportal.smtp-accounts.form', [
            'statusOptions' => SmtpAccountStatus::options(),
        ])->layout(config('sendportal-integration.layout'));
    }
}