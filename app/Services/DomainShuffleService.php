<?php

namespace App\Services;

use Illuminate\Support\Collection;

class DomainShuffleService
{
    public function shuffle(Collection $rows): Collection
    {
        $groups = $rows->groupBy(function ($row) {
            return strtolower($row->domain ?? '');
        });

        // shuffle each group internally
        $groups = $groups->map(function ($items) {
            return $items->shuffle()->values();
        });

        // shuffle domain order
        $domainKeys = $groups->keys()->shuffle()->values();

        $result = collect();

        while ($groups->isNotEmpty()) {
            foreach ($domainKeys as $key) {
                if (!isset($groups[$key]) || $groups[$key]->isEmpty()) {
                    continue;
                }

                $result->push($groups[$key]->shift());

                if ($groups[$key]->isEmpty()) {
                    $groups->forget($key);
                }
            }

            $domainKeys = $groups->keys()->shuffle()->values();
        }

        return $result->values();
    }
}