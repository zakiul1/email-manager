<?php

namespace App\Http\Controllers\SendPortal;

use App\Http\Controllers\Controller;
use App\Services\SendPortal\CampaignWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicWebhookController extends Controller
{
    public function __invoke(Request $request, CampaignWebhookService $webhookService): JsonResponse
    {
        $payload = $request->all();

        $result = $webhookService->process($payload);

        return response()->json($result, $result['ok'] ? 200 : 404);
    }
}