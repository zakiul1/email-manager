<?php

namespace App\Models\SendPortal;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tag extends Model
{
    protected $table = 'sendportal_tags';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function subscribers(): BelongsToMany
    {
        return $this->belongsToMany(Subscriber::class, 'sendportal_subscriber_tag')
            ->withTimestamps();
    }

    public function categoryLink(): HasOne
    {
        return $this->hasOne(CategoryTagLink::class, 'tag_id');
    }
}