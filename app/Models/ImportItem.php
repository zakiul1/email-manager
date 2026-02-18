<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportItem extends Model
{
    protected $fillable = [
        'import_batch_id',
        'row_number',
        'raw_email',
        'email',
        'domain',
        'status',
        'reason',
        'email_address_id',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class, 'import_batch_id');
    }

    public function emailAddress(): BelongsTo
    {
        return $this->belongsTo(EmailAddress::class);
    }
}