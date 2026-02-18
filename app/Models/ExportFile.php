<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExportFile extends Model
{
    protected $fillable = ['export_id','disk','path','filename','size_bytes'];

    public function export(): BelongsTo
    {
        return $this->belongsTo(Export::class);
    }
}