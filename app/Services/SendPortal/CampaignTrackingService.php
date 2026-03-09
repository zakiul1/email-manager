<?php

namespace App\Services\SendPortal;

use App\Contracts\SendPortal\ChecksSuppressedEmails;
use App\Models\SendPortal\CampaignMessage;
use Illuminate\Http\RedirectResponse;

class CampaignTrackingService
{
    public function __construct(
        protected ChecksSuppressedEmails $suppressionChecker
    ) {
    }

    public function markOpen(CampaignMessage $message): void
    {
        $message->update([
            'opened_at' => $message->opened_at ?: now(),
            'open_count' => (int) $message->open_count + 1,
        ]);
    }

    public function markClick(CampaignMessage $message): void
    {
        $message->update([
            'clicked_at' => $message->clicked_at ?: now(),
            'click_count' => (int) $message->click_count + 1,
        ]);
    }

    public function unsubscribe(CampaignMessage $message): bool
    {
        $email = mb_strtolower(trim((string) $message->recipient_email));

        $ok = $this->suppressionChecker->suppress($email, 'campaign_unsubscribe');

        if ($ok) {
            $message->update([
                'unsubscribed_at' => now(),
            ]);

            if ($message->subscriber) {
                $message->subscriber->update([
                    'status' => 'suppressed',
                    'is_suppressed' => true,
                    'unsubscribed_at' => $message->subscriber->unsubscribed_at ?: now(),
                ]);
            }
        }

        return $ok;
    }

    public function resolveByToken(string $token): ?CampaignMessage
    {
        return CampaignMessage::query()
            ->with('subscriber')
            ->where('tracking_token', $token)
            ->first();
    }
}