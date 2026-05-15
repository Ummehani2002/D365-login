<?php

namespace App\Models\Modules\Procurement;

use App\Support\DataAreaId;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
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
        'project_id',
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

    public function setCompanyAttribute(mixed $value): void
    {
        $this->attributes['company'] = $value === null || $value === '' ? null : DataAreaId::normalize((string) $value);
    }

    public function setBuyingLegalEntityAttribute(mixed $value): void
    {
        $this->attributes['buying_legal_entity'] = $value === null || $value === '' ? null : DataAreaId::normalize((string) $value);
    }

    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'posted_by');
    }

    public function canBeManagedBy(?Authenticatable $user): bool
    {
        if ($user === null || $this->posted_by === null) {
            return false;
        }

        return (int) $this->posted_by === (int) $user->getAuthIdentifier();
    }

    /**
     * List view: omit lines, attachments (base64), and d365_response to avoid memory exhaustion.
     */
    public function scopeForList(Builder $query): Builder
    {
        $table = $query->getModel()->getTable();
        $prefix = "{$table}.";

        $query->select([
            "{$prefix}id",
            "{$prefix}request_id",
            "{$prefix}pr_no",
            "{$prefix}company",
            "{$prefix}pr_date",
            "{$prefix}warehouse",
            "{$prefix}project_id",
            "{$prefix}pool_id",
            "{$prefix}contact_name",
            "{$prefix}department",
            "{$prefix}posted_by",
            "{$prefix}created_at",
        ]);

        $driver = $query->getConnection()->getDriverName();
        if ($driver === 'mysql') {
            $query->selectRaw("COALESCE(JSON_LENGTH({$prefix}lines), 0) as lines_count");
            $query->selectRaw("COALESCE(JSON_LENGTH({$prefix}attachments), 0) as attachments_count");
        } elseif ($driver === 'sqlite') {
            $query->selectRaw("COALESCE(json_array_length({$prefix}lines), 0) as lines_count");
            $query->selectRaw("COALESCE(json_array_length({$prefix}attachments), 0) as attachments_count");
        } else {
            $query->selectRaw('0 as lines_count')->selectRaw('0 as attachments_count');
        }

        return $query;
    }
}
