<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuppressionEntry extends Model
{
    protected $fillable = [
        'scope',
        'domain',
        'email_address_id',
        'reason',
        'user_id',
    ];

    public function emailAddress(): BelongsTo
    {
        return $this->belongsTo(EmailAddress::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}