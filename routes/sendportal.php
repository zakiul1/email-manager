<?php

use App\Livewire\SendPortal\Campaigns\Audience as CampaignsAudience;
use App\Livewire\SendPortal\Campaigns\Form as CampaignsForm;
use App\Livewire\SendPortal\Campaigns\Index as CampaignsIndex;
use App\Livewire\SendPortal\Campaigns\Preview as CampaignsPreview;
use App\Livewire\SendPortal\Campaigns\Show as CampaignsShow;
use App\Livewire\SendPortal\CategoryBridge\Index as CategoryBridgeIndex;
use App\Livewire\SendPortal\Dashboard\Overview as SendPortalOverview;
use App\Livewire\SendPortal\Reports\CampaignDetail as ReportsCampaignDetail;
use App\Livewire\SendPortal\Reports\CategoryPerformance as ReportsCategoryPerformance;
use App\Livewire\SendPortal\Reports\Index as ReportsIndex;
use App\Livewire\SendPortal\Settings\Form as SettingsForm;
use App\Livewire\SendPortal\SmtpAccounts\Form as SmtpAccountsForm;
use App\Livewire\SendPortal\SmtpAccounts\Index as SmtpAccountsIndex;
use App\Livewire\SendPortal\SmtpPools\Form as SmtpPoolsForm;
use App\Livewire\SendPortal\SmtpPools\Index as SmtpPoolsIndex;
use App\Livewire\SendPortal\Subscribers\Index as SubscribersIndex;
use App\Livewire\SendPortal\Templates\Form as TemplatesForm;
use App\Livewire\SendPortal\Templates\Index as TemplatesIndex;
use App\Livewire\SendPortal\Templates\Preview as TemplatesPreview;
use App\Livewire\SendPortal\Templates\TestSend as TemplatesTestSend;
use Illuminate\Support\Facades\Route;

Route::middleware(config('sendportal-integration.middleware'))
    ->prefix(config('sendportal-integration.route_prefix'))
    ->name(config('sendportal-integration.workspace_route_name_prefix'))
    ->group(function () {
        Route::get('/', SendPortalOverview::class)->name('dashboard');

        Route::get('/campaigns', CampaignsIndex::class)->name('campaigns.index');
        Route::get('/campaigns/create', CampaignsForm::class)->name('campaigns.create');
        Route::get('/campaigns/{campaign}', CampaignsShow::class)->name('campaigns.show');
        Route::get('/campaigns/{campaign}/edit', CampaignsForm::class)->name('campaigns.edit');
        Route::get('/campaigns/{campaign}/preview', CampaignsPreview::class)->name('campaigns.preview');
        Route::get('/campaigns/{campaign}/audience', CampaignsAudience::class)->name('campaigns.audience');

        Route::get('/subscribers', SubscribersIndex::class)->name('subscribers.index');
        Route::get('/category-bridge', CategoryBridgeIndex::class)->name('category-bridge.index');

        Route::get('/templates', TemplatesIndex::class)->name('templates.index');
        Route::get('/templates/create', TemplatesForm::class)->name('templates.create');
        Route::get('/templates/{template}/edit', TemplatesForm::class)->name('templates.edit');
        Route::get('/templates/{template}/preview', TemplatesPreview::class)->name('templates.preview');
        Route::get('/templates/{template}/test-send', TemplatesTestSend::class)->name('templates.test-send');

        Route::get('/smtp-accounts', SmtpAccountsIndex::class)->name('smtp-accounts.index');
        Route::get('/smtp-accounts/create', SmtpAccountsForm::class)->name('smtp-accounts.create');
        Route::get('/smtp-accounts/{account}/edit', SmtpAccountsForm::class)->name('smtp-accounts.edit');

        Route::get('/smtp-pools', SmtpPoolsIndex::class)->name('smtp-pools.index');
        Route::get('/smtp-pools/create', SmtpPoolsForm::class)->name('smtp-pools.create');
        Route::get('/smtp-pools/{pool}/edit', SmtpPoolsForm::class)->name('smtp-pools.edit');

        Route::get('/reports', ReportsIndex::class)->name('reports.index');
        Route::get('/reports/campaigns/{campaign}', ReportsCampaignDetail::class)->name('reports.campaign-detail');
        Route::get('/reports/categories', ReportsCategoryPerformance::class)->name('reports.category-performance');

        Route::get('/settings', SettingsForm::class)->name('settings.index');
    });