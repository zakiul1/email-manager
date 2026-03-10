<?php

namespace App\Livewire\SendPortal\SmtpPools;

use App\Models\SendPortal\SmtpPool;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $deleteId = null;
    public ?string $deleteName = null;

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

    public function confirmDelete(int $poolId, string $name): void
    {
        $this->deleteId = $poolId;
        $this->deleteName = $name;
    }

    public function cancelDelete(): void
    {
        $this->resetDeleteState();
    }

    public function deleteConfirmed(): void
    {
        if (! $this->deleteId) {
            return;
        }

        $pool = SmtpPool::query()->findOrFail($this->deleteId);

        $pool->delete();

        $this->resetDeleteState();

        $this->dispatch('toast', type: 'success', message: 'SMTP pool deleted.');
        $this->dispatch('close-modal', name: 'delete-smtp-pool');
    }

    protected function resetDeleteState(): void
    {
        $this->deleteId = null;
        $this->deleteName = null;
    }

    public function render()
    {
        $pools = SmtpPool::query()
            ->withCount('accounts')
            ->when($this->search !== '', function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
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