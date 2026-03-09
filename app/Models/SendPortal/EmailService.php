<?php

namespace App\Models\SendPortal;

use Illuminate\Database\Eloquent\Model;

class EmailService extends Model
{
    protected $table = 'sendportal_email_services';

    protected $fillable = [
        'name',
        'driver',
        'mailer',
        'from_name',
        'from_email',
        'reply_to_name',
        'reply_to_email',
        'is_active',
        'is_default',
        'settings',
        'daily_limit',
        'hourly_limit',
        'messages_sent_today',
        'messages_sent_hour',
        'last_used_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'settings' => 'array',
        'last_used_at' => 'datetime',
    ];
}