<?php

namespace App\Models;

use App\Models\EmailAddress;
use App\Models\SendPortal\CategoryTagLink;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'notes',
    ];

    public function emails(): BelongsToMany
    {
        return $this->belongsToMany(EmailAddress::class, 'category_email')
            ->withPivot(['times_added', 'import_batch_id'])
            ->withTimestamps();
    }

    public function emailAddresses(): BelongsToMany
    {
        return $this->belongsToMany(EmailAddress::class, 'category_email')
            ->withPivot(['times_added', 'import_batch_id'])
            ->withTimestamps();
    }

    public function sendPortalTagLink(): HasOne
    {
        return $this->hasOne(CategoryTagLink::class);
    }
}