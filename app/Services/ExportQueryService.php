<?php

namespace App\Services;

use App\Models\EmailAddress;
use Illuminate\Database\Eloquent\Builder;

class ExportQueryService
{
    public function build(array $filters): Builder
    {
        $categoryId = (int)($filters['category_id'] ?? 0);

        $domain = trim((string)($filters['domain'] ?? ''));
        $domain = $domain !== '' ? mb_strtolower(ltrim($domain, '@')) : null;

        $valid = (string)($filters['valid'] ?? 'all');

        $excludeGlobalSuppression = $this->toBool($filters['exclude_global_suppression'] ?? false);
        $excludeDomainUnsubscribes = $this->toBool($filters['exclude_domain_unsubscribes'] ?? false);

        // Base query
        $q = EmailAddress::query()
            ->select('email_addresses.*');

        /**
         * Category filter
         * Use join (fast) but ensure duplicates don’t happen by using DISTINCT.
         */
        if ($categoryId > 0) {
            $q->join('category_email', 'category_email.email_address_id', '=', 'email_addresses.id')
                ->where('category_email.category_id', $categoryId)
                ->distinct('email_addresses.id');
        }

        // Domain filter
        if ($domain !== null) {
            $q->where('email_addresses.domain', $domain);
        }

        // Validity filter
        if ($valid === 'valid') {
            $q->where('email_addresses.is_valid', true);
        } elseif ($valid === 'invalid') {
            $q->where('email_addresses.is_valid', false);
        }

        // Exclude suppressed (global)
        if ($excludeGlobalSuppression) {
            $q->whereNotExists(function ($sub) {
                $sub->selectRaw('1')
                    ->from('suppression_entries')
                    ->where('suppression_entries.scope', 'global')
                    ->whereColumn('suppression_entries.email_address_id', 'email_addresses.id');
            });
        }

        /**
         * Exclude unsubscribes:
         * - domain: exact match (value == email_addresses.domain)
         * - extension: suffix match (email_addresses.domain ends with value)
         * - user: local-part exact match (value == email_addresses.local_part)
         *
         * Keep the original flag name for backward compatibility:
         * exclude_domain_unsubscribes => excludes all 3 types (domain/extension/user)
         */
        if ($excludeDomainUnsubscribes) {
            // exact domains
            $q->whereNotExists(function ($sub) {
                $sub->selectRaw('1')
                    ->from('domain_unsubscribes')
                    ->where('domain_unsubscribes.type', 'domain')
                    ->whereColumn('domain_unsubscribes.value', 'email_addresses.domain');
            });

            // extensions (suffix)
            $q->whereNotExists(function ($sub) {
                $sub->selectRaw('1')
                    ->from('domain_unsubscribes')
                    ->where('domain_unsubscribes.type', 'extension')
                    ->whereRaw("LOWER(email_addresses.domain) LIKE CONCAT('%', LOWER(domain_unsubscribes.value))");
            });

            // users (local-part exact)
            $q->whereNotExists(function ($sub) {
                $sub->selectRaw('1')
                    ->from('domain_unsubscribes')
                    ->where('domain_unsubscribes.type', 'user')
                    ->whereRaw("LOWER(domain_unsubscribes.value) = LOWER(email_addresses.local_part)");
            });
        }

        return $q->orderByDesc('email_addresses.id');
    }

    private function toBool(mixed $value): bool
    {
        // handles true/false, 1/0, "1"/"0", "true"/"false"
        if (is_bool($value)) return $value;
        if (is_int($value)) return $value === 1;
        $v = mb_strtolower(trim((string)$value));
        return in_array($v, ['1', 'true', 'yes', 'on'], true);
    }
}