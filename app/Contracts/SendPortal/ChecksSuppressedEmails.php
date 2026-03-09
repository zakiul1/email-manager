<?php

namespace App\Contracts\SendPortal;

interface ChecksSuppressedEmails
{
    public function isSuppressed(string $email): bool;
}