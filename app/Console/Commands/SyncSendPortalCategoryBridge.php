<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Services\SendPortal\CategoryTagSyncService;
use Illuminate\Console\Command;

class SyncSendPortalCategoryBridge extends Command
{
    protected $signature = 'sendportal:sync-category-bridge {categoryId?}';

    protected $description = 'Sync category-based subscribers into SendPortal native tables and refresh category sync stats';

    public function handle(CategoryTagSyncService $syncService): int
    {
        $categoryId = $this->argument('categoryId');

        if ($categoryId) {
            $category = Category::query()->find($categoryId);

            if (! $category) {
                $this->error('Category not found.');

                return self::FAILURE;
            }

            $result = $syncService->syncCategory($category);

            $this->info(
                "Category synced. Total: {$result['total']}, Subscribed: {$result['subscribed']}, Suppressed: {$result['suppressed']}"
            );

            return self::SUCCESS;
        }

        $results = $syncService->syncAllEnabled();

        $this->info('Enabled category sync links refreshed.');

        foreach ($results as $id => $result) {
            $this->line(
                "Category {$id} => Total: {$result['total']}, Subscribed: {$result['subscribed']}, Suppressed: {$result['suppressed']}"
            );
        }

        return self::SUCCESS;
    }
}