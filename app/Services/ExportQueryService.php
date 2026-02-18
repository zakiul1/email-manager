<?php

namespace App\Services;

use App\Models\EmailAddress;
use Illuminate\Database\Eloquent\Builder;

class ExportQueryService
{
    public function build(array $filters): Builder
    {
        $categoryId = (int)($filters['category_id'] ?? 0);

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

        // Exclude domain unsubscribes
        if (!empty($filters['exclude_domain_unsubscribes'])) {
            $q->whereNotExists(function ($sub) {
                $sub->selectRaw(1)
                    ->from('domain_unsubscribes')
                    ->whereColumn('domain_unsubscribes.domain', 'email_addresses.domain');
            });
        }

        return $q->orderBy('email_addresses.id', 'desc');
    }
}