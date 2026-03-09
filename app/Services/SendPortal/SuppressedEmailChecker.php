<?php

namespace App\Services\SendPortal;

use App\Contracts\SendPortal\ChecksSuppressedEmails;
use App\Models\DomainUnsubscribe;
use App\Models\EmailAddress;

class SuppressedEmailChecker implements ChecksSuppressedEmails
{
    public function isSuppressed(string $email): bool
    {
        $normalized = mb_strtolower(trim($email));

        if ($normalized === '' || ! str_contains($normalized, '@')) {
            return true;
        }

        [$localPart, $domain] = explode('@', $normalized, 2);

        if ($localPart === '' || $domain === '') {
            return true;
        }

        $emailAddress = EmailAddress::query()
            ->where('email', $normalized)
            ->first();

        if ($emailAddress?->suppressions()->exists()) {
            return true;
        }

        return DomainUnsubscribe::isBlockedDomain($domain)
            || DomainUnsubscribe::isBlockedUser($localPart);
    }
}