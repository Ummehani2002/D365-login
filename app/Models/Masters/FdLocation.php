<?php

namespace App\Models\Masters;

use App\Support\DataAreaId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FdLocation extends Model
{
    protected $fillable = [
        'company_id',
        'fd_location_id',
        'description',
        'created_by',
    ];

    public function setCompanyIdAttribute(mixed $value): void
    {
        $this->attributes['company_id'] = $value === null || $value === '' ? null : DataAreaId::normalize((string) $value);
    }

    public function setFdLocationIdAttribute(mixed $value): void
    {
        $this->attributes['fd_location_id'] = $value === null || $value === '' ? null : trim((string) $value);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
}
