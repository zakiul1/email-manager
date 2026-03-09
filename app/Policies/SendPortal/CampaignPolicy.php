<?php

namespace App\Policies\SendPortal;

use App\Models\User;
use App\Models\SendPortal\Campaign;

class CampaignPolicy
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
        return $this->isAllowed($user, 'sendportal.campaigns.view');
    }

    public function view(User $user, Campaign $campaign): bool
    {
        return $this->isAllowed($user, 'sendportal.campaigns.view');
    }

    public function create(User $user): bool
    {
        return $this->isAllowed($user, 'sendportal.campaigns.create');
    }

    public function update(User $user, Campaign $campaign): bool
    {
        return $this->isAllowed($user, 'sendportal.campaigns.update');
    }

    public function delete(User $user, Campaign $campaign): bool
    {
        return $this->isAllowed($user, 'sendportal.campaigns.delete');
    }

    public function dispatch(User $user, Campaign $campaign): bool
    {
        return $this->isAllowed($user, 'sendportal.campaigns.dispatch');
    }

    public function report(User $user, Campaign $campaign): bool
    {
        return $this->isAllowed($user, 'sendportal.reports.view');
    }
}