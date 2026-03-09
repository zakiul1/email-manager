<?php

namespace App\Policies\SendPortal;

use App\Models\User;
use App\Models\SendPortal\SmtpAccount;

class SmtpAccountPolicy
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
        return $this->isAllowed($user, 'sendportal.smtp.view');
    }

    public function view(User $user, SmtpAccount $account): bool
    {
        return $this->isAllowed($user, 'sendportal.smtp.view');
    }

    public function create(User $user): bool
    {
        return $this->isAllowed($user, 'sendportal.smtp.create');
    }

    public function update(User $user, SmtpAccount $account): bool
    {
        return $this->isAllowed($user, 'sendportal.smtp.update');
    }

    public function delete(User $user, SmtpAccount $account): bool
    {
        return $this->isAllowed($user, 'sendportal.smtp.delete');
    }

    public function viewSecrets(User $user, SmtpAccount $account): bool
    {
        return $this->isAllowed($user, 'sendportal.smtp.secrets');
    }
}