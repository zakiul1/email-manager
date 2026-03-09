<?php

namespace App\Models\SendPortal;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignMessage extends Model
{
    protected $table = 'sendportal_campaign_messages';

    protected $fillable = [
        'campaign_id',
        'subscriber_id',
        'template_id',
        'smtp_account_id',
        'smtp_pool_id',
        'status',
        'attempt_count',
        'recipient_email',
        'tracking_token',
        'provider_message_id',
        'provider_event',
        'subject',
        'html_body',
        'text_body',
        'queued_at',
        'sent_at',
        'delivered_at',
        'opened_at',
        'open_count',
        'clicked_at',
        'click_count',
        'bounced_at',
        'complained_at',
        'unsubscribed_at',
        'failed_at',
        'retry_at',
        'failure_reason',
        'meta',
        'provider_payload',
    ];

    protected $casts = [
        'queued_at' => 'datetime',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'opened_at' => 'datetime',
        'clicked_at' => 'datetime',
        'bounced_at' => 'datetime',
        'complained_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
        'failed_at' => 'datetime',
        'retry_at' => 'datetime',
        'meta' => 'array',
        'provider_payload' => 'array',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(Subscriber::class, 'subscriber_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class, 'template_id');
    }

    public function smtpAccount(): BelongsTo
    {
        return $this->belongsTo(SmtpAccount::class, 'smtp_account_id');
    }

    public function smtpPool(): BelongsTo
    {
        return $this->belongsTo(SmtpPool::class, 'smtp_pool_id');
    }
}