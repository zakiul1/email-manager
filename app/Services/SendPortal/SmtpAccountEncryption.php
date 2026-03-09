<?php

namespace App\Services\SendPortal;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

class SmtpAccountEncryption
{
    public function encrypt(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        return Crypt::encryptString($value);
    }

    public function decrypt(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (DecryptException) {
            return null;
        }
    }
}