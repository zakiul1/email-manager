<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Export extends Model
{
    protected $fillable = [
        'user_id','category_id','format','status','error_message',
        'filters','total_rows','exported_rows','started_at','completed_at'
    ];

    protected $casts = [
        'filters' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function file(): HasOne
    {
        return $this->hasOne(ExportFile::class);
    }
}