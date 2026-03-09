<?php

namespace App\Enums\SendPortal;

enum SmtpAccountStatus: string
{
    case Active = 'active';
    case Paused = 'paused';
    case Failing = 'failing';
    case Disabled = 'disabled';
    case Testing = 'testing';

    public static function options(): array
    {
        return array_map(
            fn (self $case) => ['value' => $case->value, 'label' => ucfirst($case->value)],
            self::cases()
        );
    }
}