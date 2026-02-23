<?php

namespace App\Livewire\EmailManager\Suppression;

use App\Models\EmailAddress;
use App\Models\SuppressionEntry;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class GlobalList extends Component
{
    use WithPagination;

    // ✅ Now supports multiple emails
    public string $emails = ''; // textarea input: newline/comma/semicolon separated
    public ?string $reason = null;

    // ✅ Show result summary on page
    public array $result = [
        'total' => 0,
        'added' => 0,
        'already' => 0,
        'invalid' => 0,
    ];

    public array $invalidPreview = []; // optional preview

    public function add(): void
    {
        // Reset previous result
        $this->result = [
            'total' => 0,
            'added' => 0,
            'already' => 0,
            'invalid' => 0,
        ];
        $this->invalidPreview = [];

        $this->validate([
            'emails' => 'required|string',
            'reason' => 'nullable|string|max:255',
        ]);

        // Parse many emails
        $rows = $this->parseEmails($this->emails);
        $this->result['total'] = count($rows);

        if (count($rows) === 0) {
            // Nothing to do
            return;
        }

        // Normalize + de-dupe within the submission
        $normalized = [];
        foreach ($rows as $raw) {
            $raw = trim((string) $raw);
            if ($raw === '')
                continue;

            $email = mb_strtolower($raw);
            $normalized[] = $email;
        }
        $normalized = array_values(array_unique($normalized));

        DB::transaction(function () use ($normalized) {
            foreach ($normalized as $email) {
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $this->result['invalid']++;
                    $this->pushInvalidPreview($email, 'Invalid format');
                    continue;
                }

                [$local, $domain] = explode('@', $email, 2);
                $local = trim($local);
                $domain = trim($domain);

                if ($local === '' || $domain === '') {
                    $this->result['invalid']++;
                    $this->pushInvalidPreview($email, 'Invalid parts');
                    continue;
                }

                $emailAddress = EmailAddress::firstOrCreate(
                    ['email' => $email],
                    [
                        'local_part' => $local,
                        'domain' => $domain,
                        'is_valid' => true,
                        'invalid_reason' => null,
                    ]
                );

                // Check if already suppressed
                $exists = SuppressionEntry::query()
                    ->where('scope', 'global')
                    ->where('email_address_id', $emailAddress->id)
                    ->exists();

                if ($exists) {
                    $this->result['already']++;
                    continue;
                }

                SuppressionEntry::create([
                    'scope' => 'global',
                    'email_address_id' => $emailAddress->id,
                    'reason' => $this->reason,
                    'user_id' => auth()->id(),
                ]);

                $this->result['added']++;
            }
        });

        // Clear inputs
        $this->reset(['emails', 'reason']);
        $this->resetPage();
    }

    public function remove(int $id): void
    {
        SuppressionEntry::where('id', $id)
            ->where('scope', 'global')
            ->delete();

        $this->resetPage();
    }

    private function parseEmails(string $text): array
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $chunks = preg_split('/[\n,;]+/', $text) ?: [];

        return array_values(array_filter(array_map('trim', $chunks), fn($v) => $v !== ''));
    }

    private function pushInvalidPreview(string $email, string $reason): void
    {
        if (count($this->invalidPreview) >= 50) {
            return;
        }

        $this->invalidPreview[] = [
            'email' => $email,
            'reason' => $reason,
        ];
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