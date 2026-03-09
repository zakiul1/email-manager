<?php

namespace App\Services\SendPortal;

use App\Models\Category;
use App\Models\SendPortal\Campaign;
use App\Models\SendPortal\Subscriber;
use Illuminate\Support\Collection;

class CampaignAudienceResolver
{
    public function __construct(
        protected SubscriberUpsertService $subscriberUpsertService
    ) {
    }

    public function resolve(Campaign $campaign): Collection
    {
        $campaign->loadMissing('audiences');

        $ids = collect();

        foreach ($campaign->audiences as $audience) {
            $category = Category::query()->find($audience->source_id);

            if (! $category) {
                continue;
            }

            $emailAddresses = $category->emailAddresses()->get();

            foreach ($emailAddresses as $emailAddress) {
                $subscriber = $this->subscriberUpsertService->fromEmailAddress($emailAddress);
                $ids->push($subscriber->id);
            }
        }

        $subscriberIds = $ids->unique()->values();

        return Subscriber::query()
            ->whereIn('id', $subscriberIds)
            ->get();
    }

    public function stats(Campaign $campaign): array
    {
        $subscribers = $this->resolve($campaign);

        return [
            'total' => $subscribers->count(),
            'active' => $subscribers->where('is_suppressed', false)->count(),
            'suppressed' => $subscribers->where('is_suppressed', true)->count(),
        ];
    }
}