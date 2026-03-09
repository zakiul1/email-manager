<?php

namespace App\Http\Controllers\SendPortal;

use App\Http\Controllers\Controller;
use App\Services\SendPortal\CampaignTrackingService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PublicTrackingController extends Controller
{
    public function open(string $token, CampaignTrackingService $trackingService): Response
    {
        $message = $trackingService->resolveByToken($token);

        if ($message) {
            $trackingService->markOpen($message);
        }

        $pixel = base64_decode('R0lGODlhAQABAPAAAAAAAAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==');

        return response($pixel, 200, [
            'Content-Type' => 'image/gif',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }

    public function click(string $token, Request $request, CampaignTrackingService $trackingService)
    {
        $message = $trackingService->resolveByToken($token);

        $target = $request->query('url');
        $decoded = $target ? base64_decode($target, true) : false;

        if ($message) {
            $trackingService->markClick($message);
        }

        if (! $decoded || ! filter_var($decoded, FILTER_VALIDATE_URL)) {
            abort(404);
        }

        return redirect()->away($decoded);
    }
}