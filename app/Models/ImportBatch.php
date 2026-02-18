<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportBatch extends Model
{
    protected $fillable = [
        'user_id',
        'category_id',
        'source_type',
        'original_filename',
        'total_rows',
        'valid_rows',
        'invalid_rows',
        'duplicate_rows',
        'inserted_rows',
        'status',
        'error_message',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function items(): HasMany
{
    return $this->hasMany(\App\Models\ImportItem::class);
}

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}