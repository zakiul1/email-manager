<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DomainUnsubscribe extends Model
{
    protected $fillable = [
        'domain',
        'reason',
        'user_id',
    ];
}