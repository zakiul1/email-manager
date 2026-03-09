<?php

namespace App\Services\SendPortal;

use App\Models\SendPortal\SmtpAccount;

class SmtpHealthService
{
    public function markSuccess(SmtpAccount $account): void
    {
        $account->update([
            'success_count' => (int) $account->success_count + 1,
            'failure_count' => 0,
            'cooldown_until' => null,
            'status' => 'active',
        ]);
    }

    public function markFailure(SmtpAccount $account, string $reason = ''): void
    {
        $failureCount = (int) $account->failure_count + 1;

        $payload = [
            'failure_count' => $failureCount,
            'last_test_status' => 'failed',
            'last_test_message' => $reason !== '' ? $reason : $account->last_test_message,
        ];

        if ($failureCount >= 3) {
            $payload['status'] = 'failing';
            $payload['cooldown_until'] = now()->addMinutes(30);
        }

        if ($failureCount >= 5) {
            $payload['status'] = 'paused';
            $payload['cooldown_until'] = now()->addHours(6);
        }

        $account->update($payload);
    }
}