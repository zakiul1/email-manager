<?php

namespace App\Livewire\SendPortal\SmtpPools;

use App\Enums\SendPortal\SmtpPoolStrategy;
use App\Models\SendPortal\SmtpAccount;
use App\Models\SendPortal\SmtpPool;
use App\Models\SendPortal\SmtpPoolAccount;
use App\Services\SendPortal\ActivityLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Form extends Component
{
    public ?SmtpPool $pool = null;

    public string $name = '';
    public string $strategy = 'weighted_random';
    public bool $is_active = true;
    public string $notes = '';

    /**
     * @var array<int, array{smtp_account_id:int|string, weight:int|string|null, max_percent:int|string|null, is_active:bool}>
     */
    public array $memberships = [];

    public function mount(?SmtpPool $pool = null): void
    {
        if (! $pool || ! $pool->exists) {
            $this->memberships = [
                $this->emptyMembershipRow(),
            ];

            return;
        }

        $this->pool = $pool;
        $this->name = (string) $pool->name;
        $this->strategy = $pool->strategy->value;
        $this->is_active = (bool) $pool->is_active;
        $this->notes = (string) ($pool->notes ?? '');

        $this->memberships = $pool->accounts()
            ->get()
            ->map(fn ($account) => [
                'smtp_account_id' => $account->id,
                'weight' => (int) ($account->pivot->weight ?? 100),
                'max_percent' => $account->pivot->max_percent,
                'is_active' => (bool) ($account->pivot->is_active ?? true),
            ])
            ->values()
            ->all();

        if ($this->memberships === []) {
            $this->memberships = [
                $this->emptyMembershipRow(),
            ];
        }
    }

    public function addMembership(): void
    {
        $this->memberships[] = $this->emptyMembershipRow();
    }

    public function removeMembership(int $index): void
    {
        unset($this->memberships[$index]);
        $this->memberships = array_values($this->memberships);

        if ($this->memberships === []) {
            $this->memberships[] = $this->emptyMembershipRow();
        }
    }

    public function save(ActivityLogService $activityLogService): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'strategy' => ['required', Rule::in(array_column(SmtpPoolStrategy::options(), 'value'))],
            'is_active' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'memberships' => ['array', 'min:1'],
            'memberships.*.smtp_account_id' => ['required', 'integer', 'exists:sp_smtp_accounts,id'],
            'memberships.*.weight' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'memberships.*.max_percent' => ['nullable', 'integer', 'min:1', 'max:100'],
            'memberships.*.is_active' => ['boolean'],
        ]);

        $accountIds = collect($validated['memberships'])
            ->pluck('smtp_account_id')
            ->filter()
            ->values();

        if ($accountIds->count() !== $accountIds->unique()->count()) {
            $this->addError('memberships', 'The same SMTP account cannot be added more than once to the same pool.');

            return;
        }

        DB::transaction(function () use ($validated, $activityLogService) {
            $pool = $this->pool
                ? tap($this->pool)->update([
                    'name' => $validated['name'],
                    'strategy' => $validated['strategy'],
                    'is_active' => $validated['is_active'],
                    'notes' => $validated['notes'] ?: null,
                ])
                : SmtpPool::query()->create([
                    'name' => $validated['name'],
                    'strategy' => $validated['strategy'],
                    'is_active' => $validated['is_active'],
                    'notes' => $validated['notes'] ?: null,
                ]);

            SmtpPoolAccount::query()
                ->where('smtp_pool_id', $pool->id)
                ->delete();

            foreach ($validated['memberships'] as $member) {
                SmtpPoolAccount::query()->create([
                    'smtp_pool_id' => $pool->id,
                    'smtp_account_id' => $member['smtp_account_id'],
                    'weight' => $member['weight'] ?: 100,
                    'max_percent' => $member['max_percent'] ?: null,
                    'is_active' => $member['is_active'] ?? true,
                ]);
            }

            $activityLogService->log(
                $this->pool ? 'smtp_pool.updated' : 'smtp_pool.created',
                $pool,
                [
                    'name' => $pool->name,
                    'memberships_count' => count($validated['memberships']),
                ]
            );
        });

        session()->flash('toast', [
            'type' => 'success',
            'message' => $this->pool ? 'SMTP pool updated successfully.' : 'SMTP pool created successfully.',
        ]);

        $this->redirectRoute('sendportal.workspace.smtp-pools.index', navigate: true);
    }

    public function getAvailableAccountsProperty()
    {
        return SmtpAccount::query()
            ->orderBy('name')
            ->get();
    }

    protected function emptyMembershipRow(): array
    {
        return [
            'smtp_account_id' => '',
            'weight' => 100,
            'max_percent' => null,
            'is_active' => true,
        ];
    }

    public function render()
    {
        return view('livewire.sendportal.smtp-pools.form', [
            'strategyOptions' => SmtpPoolStrategy::options(),
            'availableAccounts' => $this->availableAccounts,
        ])->layout(config('sendportal-integration.layout'));
    }
}