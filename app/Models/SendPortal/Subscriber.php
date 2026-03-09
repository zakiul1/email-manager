<?php

namespace App\Models\SendPortal;

use App\Models\EmailAddress;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscriber extends Model
{
    protected $table = 'sendportal_subscribers';

    protected $fillable = [
        'email_address_id',
        'email',
        'first_name',
        'last_name',
        'status',
        'is_suppressed',
        'subscribed_at',
        'unsubscribed_at',
        'last_synced_at',
        'source',
        'meta',
    ];

    protected $casts = [
        'is_suppressed' => 'boolean',
        'subscribed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
        'last_synced_at' => 'datetime',
        'meta' => 'array',
    ];

    public function emailAddress(): BelongsTo
    {
        return $this->belongsTo(EmailAddress::class);
    }
}