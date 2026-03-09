<?php

namespace App\Providers;

use App\Models\SendPortal\Campaign;
use App\Models\SendPortal\SmtpAccount;
use App\Models\SendPortal\Template;
use App\Policies\SendPortal\CampaignPolicy;
use App\Policies\SendPortal\SmtpAccountPolicy;
use App\Policies\SendPortal\TemplatePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class SendPortalPolicyServiceProvider extends ServiceProvider
{
    protected $policies = [
        Campaign::class => CampaignPolicy::class,
        Template::class => TemplatePolicy::class,
        SmtpAccount::class => SmtpAccountPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('sendportal.reports.view', fn ($user) => ($user->is_admin ?? false) || true);
        Gate::define('sendportal.reports.export', fn ($user) => ($user->is_admin ?? false) || true);
    }
}