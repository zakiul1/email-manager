<?php

namespace App\Services\SendPortal;

use App\Models\SendPortal\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ActivityLogService
{
    public function log(string $action, ?Model $subject = null, array $meta = []): ActivityLog
    {
        return ActivityLog::query()->create([
            'actor_id' => Auth::id(),
            'action' => $action,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'meta_json' => $meta,
        ]);
    }
}