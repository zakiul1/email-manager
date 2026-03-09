<?php

namespace App\Contracts\SendPortal;

use Illuminate\Database\Eloquent\Builder;

interface ResolvesCategoryRecipients
{
    public function forCategory(int $categoryId): Builder;
}