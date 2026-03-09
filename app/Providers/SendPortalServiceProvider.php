<?php

namespace App\Providers;

use App\Contracts\SendPortal\ChecksSuppressedEmails;
use App\Contracts\SendPortal\ResolvesCategoryRecipients;
use App\Services\SendPortal\CategoryRecipientResolver;
use App\Services\SendPortal\SuppressedEmailChecker;
use Illuminate\Support\ServiceProvider;

class SendPortalServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ChecksSuppressedEmails::class, SuppressedEmailChecker::class);
        $this->app->singleton(ResolvesCategoryRecipients::class, CategoryRecipientResolver::class);
    }

    public function boot(): void
    {
        //
    }
}