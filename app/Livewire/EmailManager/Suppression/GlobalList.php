<?php

namespace App\Livewire\EmailManager\Suppression;

use App\Models\EmailAddress;
use App\Models\SuppressionEntry;
use Livewire\Component;
use Livewire\WithPagination;

class GlobalList extends Component
{
    use WithPagination;

    public string $email = '';
    public ?string $reason = null;

    public function add(): void
    {
        $this->validate([
            'email' => 'required|email',
            'reason' => 'nullable|string|max:255',
        ]);

        $normalized = mb_strtolower(trim($this->email));
        $domain = explode('@', $normalized)[1] ?? '';
        $local = explode('@', $normalized)[0] ?? '';

        $emailAddress = EmailAddress::firstOrCreate(
            ['email' => $normalized],
            ['local_part' => $local, 'domain' => $domain, 'is_valid' => true]
        );

        SuppressionEntry::firstOrCreate(
            ['scope' => 'global', 'email_address_id' => $emailAddress->id],
            ['reason' => $this->reason, 'user_id' => auth()->id()]
        );

        $this->reset(['email', 'reason']);
        $this->resetPage();
    }

    public function remove(int $id): void
    {
        SuppressionEntry::where('id', $id)
            ->where('scope', 'global')
            ->delete();

        $this->resetPage();
    }

    public function render()
    {
        $items = SuppressionEntry::query()
            ->with('emailAddress')
            ->where('scope', 'global')
            ->latest('id')
            ->paginate(15);

        return view('livewire.email-manager.suppression.global-list', [
            'items' => $items,
        ])->layout('layouts.app');
    }
}