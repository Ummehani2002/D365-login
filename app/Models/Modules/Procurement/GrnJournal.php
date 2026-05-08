<?php

namespace App\Models\Modules\Procurement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrnJournal extends Model
{
    protected $fillable = [
        'request_id',
        'company',
        'purch_id',
        'project_id',
        'vendor_name',
        'packing_slip_id',
        'document_date',
        'lines',
        'd365_response',
        'posted_by',
    ];

    protected $casts = [
        'document_date' => 'date',
        'lines'         => 'array',
        'd365_response' => 'array',
    ];

    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'posted_by');
    }
}
