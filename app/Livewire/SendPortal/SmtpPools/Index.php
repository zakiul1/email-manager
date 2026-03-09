<?php

namespace App\Livewire\SendPortal\SmtpPools;

use App\Models\SendPortal\SmtpPool;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function toggleStatus(int $poolId): void
    {
        $pool = SmtpPool::query()->findOrFail($poolId);

        $pool->update([
            'is_active' => ! $pool->is_active,
        ]);

        $this->dispatch('toast', type: 'success', message: 'SMTP pool status updated.');
    }

    public function deletePool(int $poolId): void
    {
        $pool = SmtpPool::query()->findOrFail($poolId);

        $pool->delete();

        $this->dispatch('toast', type: 'success', message: 'SMTP pool deleted.');
    }

    public function render()
    {
        $pools = SmtpPool::query()
            ->withCount('accounts')
            ->when($this->search !== '', function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%');
            })
            ->latest('id')
            ->paginate(12);

        return view('livewire.sendportal.smtp-pools.index', [
            'pools' => $pools,
            'stats' => [
                'total' => SmtpPool::query()->count(),
                'active' => SmtpPool::query()->where('is_active', true)->count(),
                'inactive' => SmtpPool::query()->where('is_active', false)->count(),
                'accounts_linked' => \App\Models\SendPortal\SmtpPoolAccount::query()->count(),
            ],
        ])->layout(config('sendportal-integration.layout'));
    }
}