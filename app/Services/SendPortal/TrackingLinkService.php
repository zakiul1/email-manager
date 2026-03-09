<?php

namespace App\Services\SendPortal;

use App\Models\SendPortal\CampaignMessage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class TrackingLinkService
{
    public function ensureToken(CampaignMessage $message): CampaignMessage
    {
        if (! $message->tracking_token) {
            $message->update([
                'tracking_token' => Str::uuid()->toString(),
            ]);
        }

        return $message->refresh();
    }

    public function openPixelUrl(CampaignMessage $message): string
    {
        $message = $this->ensureToken($message);

        return URL::route('sendportal.public.track.open', [
            'token' => $message->tracking_token,
        ]);
    }

    public function unsubscribeUrl(CampaignMessage $message): string
    {
        $message = $this->ensureToken($message);

        return URL::route('sendportal.public.unsubscribe', [
            'token' => $message->tracking_token,
        ]);
    }

    public function rewriteHtmlLinks(CampaignMessage $message, string $html): string
    {
        $message = $this->ensureToken($message);

        return preg_replace_callback(
            '/href=["\'](https?:\/\/[^"\']+)["\']/i',
            function ($matches) use ($message) {
                $target = $matches[1];

                $tracked = URL::route('sendportal.public.track.click', [
                    'token' => $message->tracking_token,
                    'url' => base64_encode($target),
                ]);

                return 'href="'.$tracked.'"';
            },
            $html
        ) ?? $html;
    }

    public function appendOpenPixel(CampaignMessage $message, string $html): string
    {
        $pixel = '<img src="'.$this->openPixelUrl($message).'" alt="" width="1" height="1" style="display:none;">';

        if (str_contains($html, '</body>')) {
            return str_replace('</body>', $pixel.'</body>', $html);
        }

        return $html.$pixel;
    }
}