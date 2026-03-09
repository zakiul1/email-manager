<?php

namespace App\Livewire\SendPortal\Subscribers;

use App\Models\SendPortal\Subscriber;
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

    public function render()
    {
        $subscribers = Subscriber::query()
            ->when($this->search !== '', function ($query) {
                $query->where('email', 'like', '%'.$this->search.'%');
            })
            ->when($this->status !== '', function ($query) {
                $query->where('status', $this->status);
            })
            ->latest('id')
            ->paginate(20);

        return view('livewire.sendportal.subscribers.index', [
            'subscribers' => $subscribers,
            'stats' => [
                'total' => Subscriber::query()->count(),
                'subscribed' => Subscriber::query()->where('status', 'subscribed')->count(),
                'suppressed' => Subscriber::query()->where('status', 'suppressed')->count(),
            ],
        ])->layout(config('sendportal-integration.layout'));
    }
}