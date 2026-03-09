<?php

namespace App\Models\SendPortal;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Template extends Model
{
    protected $table = 'sendportal_templates';

    protected $fillable = [
        'name',
        'slug',
        'subject',
        'preheader',
        'html_content',
        'text_content',
        'editor',
        'status',
        'usage_count',
        'version_notes',
        'builder_meta',
        'last_test_sent_at',
        'is_active',
        'meta',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'builder_meta' => 'array',
        'meta' => 'array',
        'last_test_sent_at' => 'datetime',
        'usage_count' => 'integer',
    ];

    public function tests(): HasMany
    {
        return $this->hasMany(TemplateTest::class, 'template_id');
    }
}