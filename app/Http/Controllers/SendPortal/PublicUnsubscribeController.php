<?php

namespace App\Http\Controllers\SendPortal;

use App\Http\Controllers\Controller;
use App\Services\SendPortal\CampaignTrackingService;
use Illuminate\Contracts\View\View;

class PublicUnsubscribeController extends Controller
{
    public function __invoke(string $token, CampaignTrackingService $trackingService): View
    {
        $message = $trackingService->resolveByToken($token);

        if (! $message) {
            return view('sendportal.public.unsubscribe-failed');
        }

        $ok = $trackingService->unsubscribe($message);

        return view($ok
            ? 'sendportal.public.unsubscribe-success'
            : 'sendportal.public.unsubscribe-failed');
    }
}