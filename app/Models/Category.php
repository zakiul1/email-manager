<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'notes'];

    public function emails(): BelongsToMany
    {
        return $this->belongsToMany(EmailAddress::class, 'category_email')
            ->withPivot(['times_added', 'import_batch_id'])
            ->withTimestamps();
    }
}