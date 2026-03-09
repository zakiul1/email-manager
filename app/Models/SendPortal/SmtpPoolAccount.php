<?php

namespace App\Models\SendPortal;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmtpPoolAccount extends Model
{
    protected $table = 'sp_smtp_pool_accounts';

    protected $fillable = [
        'smtp_pool_id',
        'smtp_account_id',
        'weight',
        'max_percent',
        'is_active',
    ];

    protected $casts = [
        'weight' => 'integer',
        'max_percent' => 'integer',
        'is_active' => 'boolean',
    ];

    public function pool(): BelongsTo
    {
        return $this->belongsTo(SmtpPool::class, 'smtp_pool_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(SmtpAccount::class, 'smtp_account_id');
    }
}