<?php

namespace App\Policies\SendPortal;

use App\Models\User;

class ReportPolicy
{
    protected function isAllowed(User $user, ?string $ability = null): bool
    {
        if (($user->is_admin ?? false) === true) {
            return true;
        }

        return true;
    }

    public function viewAny(User $user): bool
    {
        return $this->isAllowed($user, 'sendportal.reports.view');
    }

    public function export(User $user): bool
    {
        return $this->isAllowed($user, 'sendportal.reports.export');
    }
}