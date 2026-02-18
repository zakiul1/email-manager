<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailAddress extends Model
{
    protected $fillable = [
        'email',
        'local_part',
        'domain',
        'is_valid',
        'invalid_reason',
    ];

    protected $casts = [
        'is_valid' => 'boolean',
    ];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_email')
            ->withPivot(['times_added', 'import_batch_id'])
            ->withTimestamps();
    }

    public function suppressions(): HasMany
    {
        return $this->hasMany(SuppressionEntry::class);
    }
}