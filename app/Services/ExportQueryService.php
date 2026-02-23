<?php

namespace App\Services;

use App\Models\DomainUnsubscribe;
use App\Models\EmailAddress;
use Illuminate\Database\Eloquent\Builder;

class ExportQueryService
{
    public function build(array $filters): Builder
    {
        $categoryId = (int) ($filters['category_id'] ?? 0);

        $q = EmailAddress::query()->select('email_addresses.*');

        if ($categoryId > 0) {
            $q->join('category_email', 'category_email.email_address_id', '=', 'email_addresses.id')
                ->where('category_email.category_id', $categoryId);
        }

        if (!empty($filters['domain'])) {
            $q->where('email_addresses.domain', mb_strtolower(trim($filters['domain'])));
        }

        if (($filters['valid'] ?? 'all') === 'valid') {
            $q->where('email_addresses.is_valid', true);
        } elseif (($filters['valid'] ?? 'all') === 'invalid') {
            $q->where('email_addresses.is_valid', false);
        }

        // Exclude suppressed
        if (!empty($filters['exclude_global_suppression'])) {
            $q->whereNotExists(function ($sub) {
                $sub->selectRaw(1)
                    ->from('suppression_entries')
                    ->where('suppression_entries.scope', 'global')
                    ->whereColumn('suppression_entries.email_address_id', 'email_addresses.id');
            });
        }

        /**
         * Exclude domain unsubscribes (NEW):
         * - type=domain: exact match (value == email_addresses.domain)
         * - type=extension: suffix match (email_addresses.domain ends with value)
         */
        if (!empty($filters['exclude_domain_unsubscribes'])) {
            // exact domains
            $q->whereNotExists(function ($sub) {
                $sub->selectRaw(1)
                    ->from('domain_unsubscribes')
                    ->where('domain_unsubscribes.type', 'domain')
                    ->whereColumn('domain_unsubscribes.value', 'email_addresses.domain');
            });

            // extensions (suffix)
            // NOTE: MySQL syntax with CONCAT + LIKE. Works for common MySQL/MariaDB setups.
            $q->whereNotExists(function ($sub) {
                $sub->selectRaw(1)
                    ->from('domain_unsubscribes')
                    ->where('domain_unsubscribes.type', 'extension')
                    ->whereRaw("LOWER(email_addresses.domain) LIKE CONCAT('%', LOWER(domain_unsubscribes.value))");
            });
        }

        return $q->orderBy('email_addresses.id', 'desc');
    }
}