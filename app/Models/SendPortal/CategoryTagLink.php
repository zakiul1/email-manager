<?php

namespace App\Models\SendPortal;

use App\Models\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CategoryTagLink extends Model
{
    protected $table = 'sendportal_category_tag_links';

    protected $fillable = [
        'category_id',
        'sync_enabled',
        'last_synced_at',
        'last_synced_total',
        'last_synced_subscribed',
        'last_synced_suppressed',
    ];

    protected $casts = [
        'sync_enabled' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}