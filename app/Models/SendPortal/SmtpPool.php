<?php

namespace App\Models\SendPortal;

use App\Enums\SendPortal\SmtpPoolStrategy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SmtpPool extends Model
{
    protected $table = 'sp_smtp_pools';

    protected $fillable = [
        'name',
        'strategy',
        'is_active',
        'notes',
        'meta',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'meta' => 'array',
        'strategy' => SmtpPoolStrategy::class,
    ];

    public function accounts(): BelongsToMany
    {
        return $this->belongsToMany(SmtpAccount::class, 'sp_smtp_pool_accounts', 'smtp_pool_id', 'smtp_account_id')
            ->withPivot(['weight', 'max_percent', 'is_active'])
            ->withTimestamps();
    }
}