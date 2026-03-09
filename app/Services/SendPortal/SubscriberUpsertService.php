<?php

namespace App\Services\SendPortal;

use App\Contracts\SendPortal\ChecksSuppressedEmails;
use App\Models\EmailAddress;
use App\Models\SendPortal\Subscriber;

class SubscriberUpsertService
{
    public function __construct(
        protected ChecksSuppressedEmails $suppressionChecker
    ) {
    }

    public function fromEmailAddress(EmailAddress $emailAddress): Subscriber
    {
        $email = mb_strtolower(trim((string) $emailAddress->email));
        $isSuppressed = $this->suppressionChecker->isSuppressed($email);

        $subscriber = Subscriber::query()->firstOrNew([
            'email' => $email,
        ]);

        $subscriber->fill([
            'email_address_id' => $emailAddress->id,
            'status' => $isSuppressed ? 'suppressed' : 'subscribed',
            'is_suppressed' => $isSuppressed,
            'source' => 'email_manager',
            'last_synced_at' => now(),
            'meta' => array_merge($subscriber->meta ?? [], [
                'synced_from' => 'email_addresses',
            ]),
        ]);

        if ($isSuppressed) {
            $subscriber->unsubscribed_at ??= now();
        } else {
            $subscriber->subscribed_at ??= now();
            $subscriber->unsubscribed_at = null;
        }

        $subscriber->save();

        return $subscriber->refresh();
    }
}