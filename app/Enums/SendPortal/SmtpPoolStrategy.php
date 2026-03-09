<?php

namespace App\Enums\SendPortal;

enum SmtpPoolStrategy: string
{
    case WeightedRandom = 'weighted_random';
    case RoundRobin = 'round_robin';
    case LeastUsedToday = 'least_used_today';
    case FailoverChain = 'failover_chain';

    public static function options(): array
    {
        return array_map(
            fn (self $case) => [
                'value' => $case->value,
                'label' => str($case->value)->replace('_', ' ')->title()->toString(),
            ],
            self::cases()
        );
    }
}