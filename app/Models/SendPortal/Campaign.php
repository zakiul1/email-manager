<?php

namespace App\Models\SendPortal;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    protected $table = 'sendportal_campaigns';

    protected $fillable = [
        'name',
        'subject',
        'preheader',
        'status',
        'delivery_mode',
        'template_id',
        'email_service_id',
        'smtp_pool_id',
        'from_name',
        'from_email',
        'reply_to_name',
        'reply_to_email',
        'html_content',
        'text_content',
        'audience_type',
        'audience_reference',
        'recipient_count',
        'sent_count',
        'failed_count',
        'scheduled_at',
        'queued_at',
        'sent_at',
        'meta',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'queued_at' => 'datetime',
        'sent_at' => 'datetime',
        'meta' => 'array',
        'recipient_count' => 'integer',
        'sent_count' => 'integer',
        'failed_count' => 'integer',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class, 'template_id');
    }

    public function emailService(): BelongsTo
    {
        return $this->belongsTo(EmailService::class, 'email_service_id');
    }

    public function smtpPool(): BelongsTo
    {
        return $this->belongsTo(SmtpPool::class, 'smtp_pool_id');
    }

    public function audiences(): HasMany
    {
        return $this->hasMany(CampaignAudience::class, 'campaign_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(CampaignMessage::class, 'campaign_id');
    }
}