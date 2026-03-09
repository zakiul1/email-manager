<?php

namespace App\Services\SendPortal;

use App\Contracts\SendPortal\ResolvesCategoryRecipients;
use App\Models\EmailAddress;
use Illuminate\Database\Eloquent\Builder;

class CategoryRecipientResolver implements ResolvesCategoryRecipients
{
    public function forCategory(int $categoryId): Builder
    {
        return EmailAddress::query()
            ->select('email_addresses.*')
            ->join('category_email', 'category_email.email_address_id', '=', 'email_addresses.id')
            ->where('category_email.category_id', $categoryId)
            ->distinct('email_addresses.id');
    }
}