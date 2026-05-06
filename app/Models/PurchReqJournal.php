<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchReqJournal extends Model
{
    protected $fillable = [
        'request_id',
        'pr_no',
        'company',
        'buying_legal_entity',
        'pr_date',
        'warehouse',
        'pool_id',
        'contact_name',
        'remarks',
        'department',
        'lines',
        'attachments',
        'd365_response',
        'posted_by',
    ];

    protected $casts = [
        'lines'         => 'array',
        'attachments'   => 'array',
        'd365_response' => 'array',
        'pr_date'       => 'date',
    ];

    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'posted_by');
    }

    /**
     * Whether the user may edit or delete this journal (others may view only).
     */
    public function canBeManagedBy(?Authenticatable $user): bool
    {
        if ($user === null) {
            return false;
        }

        if ($this->posted_by === null) {
            return false;
        }

        return (int) $this->posted_by === (int) $user->getAuthIdentifier();
    }
}
