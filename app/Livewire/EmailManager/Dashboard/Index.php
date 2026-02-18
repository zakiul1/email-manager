<?php

namespace App\Livewire\EmailManager\Dashboard;

use App\Models\EmailAddress;
use App\Models\ImportBatch;
use Livewire\Component;

class Index extends Component
{
    public int $days = 30;

    public function mount(): void
    {
        // Send initial chart data to JS (so charts render even after Livewire navigation)
        $this->dispatch('dashboard-chart-data', chart: $this->getChartData());
    }

    public function updatedDays(): void
    {
        // When dropdown changes, send fresh chart data to JS
        $this->dispatch('dashboard-chart-data', chart: $this->getChartData());
    }

    public function getChartData(): array
    {
        $from = now()->subDays($this->days)->startOfDay();

        // 1) Emails added per day (based on email_addresses.created_at)
        $added = EmailAddress::query()
            ->selectRaw('DATE(created_at) as d, COUNT(*) as c')
            ->where('created_at', '>=', $from)
            ->groupBy('d')
            ->orderBy('d')
            ->get();

        $addedMap = $added->pluck('c', 'd')->toArray();

        $labels = [];
        $addedCounts = [];
        for ($i = $this->days - 1; $i >= 0; $i--) {
            $d = now()->subDays($i)->toDateString();
            $labels[] = $d;
            $addedCounts[] = (int)($addedMap[$d] ?? 0);
        }

        // 2) Top domains (overall)
        $domains = EmailAddress::query()
            ->selectRaw('domain, COUNT(*) as c')
            ->whereNotNull('domain')
            ->where('domain', '!=', '')
            ->groupBy('domain')
            ->orderByDesc('c')
            ->limit(10)
            ->get();

        // 3) Import outcomes last N days
        $imports = ImportBatch::query()
            ->selectRaw('
                SUM(inserted_rows) as inserted,
                SUM(invalid_rows) as invalid,
                SUM(duplicate_rows) as duplicate,
                SUM(COALESCE(suppressed_rows, 0)) as suppressed
            ')
            ->where('created_at', '>=', $from)
            ->first();

        $importSummary = [
            'inserted' => (int)($imports->inserted ?? 0),
            'invalid' => (int)($imports->invalid ?? 0),
            'duplicate' => (int)($imports->duplicate ?? 0),
            'suppressed' => (int)($imports->suppressed ?? 0),
        ];

        return [
            'labels' => $labels,
            'addedCounts' => $addedCounts,
            'topDomains' => [
                'labels' => $domains->pluck('domain')->toArray(),
                'counts' => $domains->pluck('c')->map(fn ($v) => (int) $v)->toArray(),
            ],
            'importSummary' => $importSummary,
        ];
    }

    public function render()
    {
        return view('livewire.email-manager.dashboard.index', [
            'chart' => $this->getChartData(),
        ])->layout('layouts.app');
    }
}