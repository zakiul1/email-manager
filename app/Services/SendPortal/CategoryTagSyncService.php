<?php

namespace App\Services\SendPortal;

use App\Models\Category;
use App\Models\SendPortal\CategoryTagLink;
use Illuminate\Support\Facades\DB;

class CategoryTagSyncService
{
    public function __construct(
        protected SubscriberUpsertService $subscriberUpsertService
    ) {
    }

    public function ensureLink(Category $category): CategoryTagLink
    {
        return CategoryTagLink::query()->updateOrCreate(
            ['category_id' => $category->id],
            []
        );
    }

    public function syncCategory(Category $category): array
    {
        $link = $this->ensureLink($category);

        if (! $link->sync_enabled) {
            return [
                'total' => 0,
                'subscribed' => 0,
                'suppressed' => 0,
            ];
        }

        $total = 0;
        $subscribed = 0;
        $suppressed = 0;

        DB::table('category_email')
            ->join('email_addresses', 'email_addresses.id', '=', 'category_email.email_address_id')
            ->where('category_email.category_id', $category->id)
            ->select('email_addresses.id')
            ->orderBy('email_addresses.id')
            ->chunk(500, function ($rows) use (&$total, &$subscribed, &$suppressed) {
                $emailAddressIds = collect($rows)->pluck('id')->all();

                $emailAddresses = \App\Models\EmailAddress::query()
                    ->whereIn('id', $emailAddressIds)
                    ->get();

                foreach ($emailAddresses as $emailAddress) {
                    $subscriber = $this->subscriberUpsertService->fromEmailAddress($emailAddress);

                    $total++;

                    if ($subscriber->is_suppressed) {
                        $suppressed++;
                    } else {
                        $subscribed++;
                    }
                }
            });

        $link->update([
            'last_synced_at' => now(),
            'last_synced_total' => $total,
            'last_synced_subscribed' => $subscribed,
            'last_synced_suppressed' => $suppressed,
        ]);

        return [
            'total' => $total,
            'subscribed' => $subscribed,
            'suppressed' => $suppressed,
        ];
    }

    public function syncAllEnabled(): array
    {
        $results = [];

        $categories = Category::query()
            ->whereIn(
                'id',
                CategoryTagLink::query()
                    ->where('sync_enabled', true)
                    ->pluck('category_id')
            )
            ->get();

        foreach ($categories as $category) {
            $results[$category->id] = $this->syncCategory($category);
        }

        return $results;
    }
}