<?php

namespace App\Livewire\EmailManager\Suppression;

use App\Models\DomainUnsubscribe;
use Livewire\Component;
use Livewire\WithPagination;

class DomainList extends Component
{
    use WithPagination;

    public string $domain = '';
    public ?string $reason = null;

    public function add(): void
    {
        $this->validate([
            'domain' => 'required|string|max:255',
            'reason' => 'nullable|string|max:255',
        ]);

        $d = mb_strtolower(trim($this->domain));
        $d = ltrim($d, '@');

        DomainUnsubscribe::firstOrCreate(
            ['domain' => $d],
            ['reason' => $this->reason, 'user_id' => auth()->id()]
        );

        $this->reset(['domain', 'reason']);
        $this->resetPage();
    }

    public function remove(int $id): void
    {
        DomainUnsubscribe::where('id', $id)->delete();
        $this->resetPage();
    }

    public function render()
    {
        $items = DomainUnsubscribe::query()
            ->latest('id')
            ->paginate(15);

        return view('livewire.email-manager.suppression.domain-list', [
            'items' => $items,
        ])->layout('layouts.app');
    }
}