<?php

namespace App\Models\SendPortal;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignAudience extends Model
{
    protected $table = 'sendportal_campaign_audiences';

    protected $fillable = [
        'campaign_id',
        'source_type',
        'source_id',
        'filters',
    ];

    protected $casts = [
        'filters' => 'array',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }
}