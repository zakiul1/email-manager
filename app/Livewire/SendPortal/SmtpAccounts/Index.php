<?php

namespace App\Livewire\SendPortal\SmtpAccounts;

use App\Models\SendPortal\SmtpAccount;
use App\Services\SendPortal\ActivityLogService;
use App\Services\SendPortal\SmtpConnectionTestService;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function toggleStatus(int $accountId): void
    {
        $account = SmtpAccount::query()->findOrFail($accountId);

        $newStatus = $account->status->value === 'active' ? 'paused' : 'active';

        $account->update([
            'status' => $newStatus,
        ]);

        app(ActivityLogService::class)->log('smtp_account.status_toggled', $account, [
            'new_status' => $newStatus,
        ]);

        $this->dispatch('toast', type: 'success', message: 'SMTP account status updated.');
    }

    public function deleteAccount(int $accountId): void
    {
        $account = SmtpAccount::query()->findOrFail($accountId);

        app(ActivityLogService::class)->log('smtp_account.deleted', $account, [
            'name' => $account->name,
        ]);

        $account->delete();

        $this->dispatch('toast', type: 'success', message: 'SMTP account deleted.');
    }

    public function testConnection(int $accountId, SmtpConnectionTestService $testService): void
    {
        $account = SmtpAccount::query()->findOrFail($accountId);

        $result = $testService->test($account);

        app(ActivityLogService::class)->log('smtp_account.tested', $account, [
            'ok' => $result['ok'],
            'message' => $result['message'],
        ]);

        $this->dispatch(
            'toast',
            type: $result['ok'] ? 'success' : 'error',
            message: $result['message']
        );
    }

    public function render()
    {
        $accounts = SmtpAccount::query()
            ->when($this->search !== '', function ($query) {
                $query->where(function ($inner) {
                    $inner->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('provider_label', 'like', '%'.$this->search.'%')
                        ->orWhere('from_email', 'like', '%'.$this->search.'%')
                        ->orWhere('host', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->status !== '', function ($query) {
                $query->where('status', $this->status);
            })
            ->latest('id')
            ->paginate(15);

        return view('livewire.sendportal.smtp-accounts.index', [
            'accounts' => $accounts,
            'stats' => [
                'total' => SmtpAccount::query()->count(),
                'active' => SmtpAccount::query()->where('status', 'active')->count(),
                'paused' => SmtpAccount::query()->where('status', 'paused')->count(),
                'default' => SmtpAccount::query()->where('is_default', true)->count(),
            ],
        ])->layout(config('sendportal-integration.layout'));
    }
}