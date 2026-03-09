<?php

namespace App\Models\SendPortal;

use App\Enums\SendPortal\SmtpAccountStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SmtpAccount extends Model
{
    protected $table = 'sp_smtp_accounts';

    protected $fillable = [
        'name',
        'provider_label',
        'driver_type',
        'mailer_name',
        'host',
        'port',
        'username',
        'encrypted_password',
        'encryption',
        'from_name',
        'from_email',
        'reply_to_name',
        'reply_to_email',
        'daily_limit',
        'hourly_limit',
        'warmup_limit',
        'priority',
        'status',
        'is_default',
        'last_tested_at',
        'last_test_status',
        'last_test_message',
        'failure_count',
        'success_count',
        'cooldown_until',
        'notes',
        'meta',
    ];

    protected $casts = [
        'port' => 'integer',
        'daily_limit' => 'integer',
        'hourly_limit' => 'integer',
        'warmup_limit' => 'integer',
        'priority' => 'integer',
        'is_default' => 'boolean',
        'last_tested_at' => 'datetime',
        'cooldown_until' => 'datetime',
        'meta' => 'array',
        'status' => SmtpAccountStatus::class,
    ];

    public function pools(): BelongsToMany
    {
        return $this->belongsToMany(SmtpPool::class, 'sp_smtp_pool_accounts', 'smtp_account_id', 'smtp_pool_id')
            ->withPivot(['weight', 'max_percent', 'is_active'])
            ->withTimestamps();
    }

    public function isActive(): bool
    {
        return $this->status === SmtpAccountStatus::Active;
    }

    public function inCooldown(): bool
    {
        return $this->cooldown_until !== null && $this->cooldown_until->isFuture();
    }
}