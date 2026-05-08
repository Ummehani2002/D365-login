<?php

namespace App\Models\Modules\ProjectManagement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemIssueJournal extends Model
{
    protected $fillable = [
        'request_id',
        'journal_id',
        'company',
        'project_id',
        'description',
        'invent_site_id',
        'invent_location_id',
        'tax_group_id',
        'tax_item_group_id',
        'lines',
        'posted_by',
    ];

    protected $casts = [
        'lines' => 'array',
    ];

    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'posted_by');
    }
}
