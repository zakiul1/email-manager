<?php

namespace App\Models\SendPortal;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'sendportal_settings';

    protected $fillable = [
        'group',
        'key',
        'value_json',
        'is_encrypted',
    ];

    protected $casts = [
        'value_json' => 'array',
        'is_encrypted' => 'boolean',
    ];
}