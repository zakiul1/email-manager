<?php

namespace App\Services\SendPortal;

use App\Models\SendPortal\SmtpAccount;
use App\Models\SendPortal\SmtpPool;
use Illuminate\Support\Collection;

class SmtpPoolSelectorService
{
    public function __construct(
        protected SmtpAccountLimitService $limitService
    ) {
    }

    public function select(?SmtpPool $pool, array $excludeAccountIds = []): ?SmtpAccount
    {
        if (! $pool || ! $pool->is_active) {
            return SmtpAccount::query()
                ->where('is_default', true)
                ->get()
                ->reject(fn ($account) => in_array($account->id, $excludeAccountIds, true))
                ->first(fn ($account) => $account->status->value === 'active'
                    && ! $account->inCooldown()
                    && $this->limitService->canSend($account));
        }

        $pool->loadMissing('accounts');

        $accounts = $pool->accounts
            ->reject(fn ($account) => in_array($account->id, $excludeAccountIds, true))
            ->filter(fn ($account) => $account->pivot->is_active)
            ->filter(fn ($account) => $account->status->value === 'active')
            ->filter(fn ($account) => ! $account->inCooldown())
            ->filter(fn ($account) => $this->limitService->canSend($account))
            ->values();

        if ($accounts->isEmpty()) {
            return SmtpAccount::query()
                ->where('is_default', true)
                ->get()
                ->reject(fn ($account) => in_array($account->id, $excludeAccountIds, true))
                ->first(fn ($account) => $account->status->value === 'active'
                    && ! $account->inCooldown()
                    && $this->limitService->canSend($account));
        }

        return match ($pool->strategy->value) {
            'round_robin' => $this->roundRobin($accounts),
            'least_used_today' => $this->leastUsedToday($accounts),
            'failover_chain' => $this->failoverChain($accounts),
            default => $this->weightedRandom($accounts),
        };
    }

    protected function weightedRandom(Collection $accounts): ?SmtpAccount
    {
        $expanded = collect();

        foreach ($accounts as $account) {
            $weight = max(1, (int) ($account->pivot->weight ?? 100));

            for ($i = 0; $i < $weight; $i++) {
                $expanded->push($account);
            }
        }

        return $expanded->isEmpty() ? null : $expanded->random();
    }

    protected function roundRobin(Collection $accounts): ?SmtpAccount
    {
        return $accounts
            ->sortBy(fn ($account) => [
                $account->last_used_at?->timestamp ?? 0,
                (int) $account->priority,
            ])
            ->values()
            ->first();
    }

    protected function leastUsedToday(Collection $accounts): ?SmtpAccount
    {
        return $accounts
            ->sortBy(fn ($account) => [
                (int) ($account->meta['sent_today'] ?? 0),
                (int) $account->priority,
            ])
            ->values()
            ->first();
    }

    protected function failoverChain(Collection $accounts): ?SmtpAccount
    {
        return $accounts->sortBy('priority')->values()->first();
    }
}