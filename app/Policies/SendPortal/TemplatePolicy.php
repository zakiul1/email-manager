<?php

namespace App\Policies\SendPortal;

use App\Models\User;
use App\Models\SendPortal\Template;

class TemplatePolicy
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
        return $this->isAllowed($user, 'sendportal.templates.view');
    }

    public function view(User $user, Template $template): bool
    {
        return $this->isAllowed($user, 'sendportal.templates.view');
    }

    public function create(User $user): bool
    {
        return $this->isAllowed($user, 'sendportal.templates.create');
    }

    public function update(User $user, Template $template): bool
    {
        return $this->isAllowed($user, 'sendportal.templates.update');
    }

    public function delete(User $user, Template $template): bool
    {
        return $this->isAllowed($user, 'sendportal.templates.delete');
    }

    public function testSend(User $user, Template $template): bool
    {
        return $this->isAllowed($user, 'sendportal.templates.test-send');
    }
}