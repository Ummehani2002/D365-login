<?php

namespace App\Models\Masters;

use App\Support\DataAreaId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetResourceCode extends Model
{
    protected $fillable = [
        'company_id',
        'project',
        'resource_code',
        'description',
        'unit',
        'quantity',
        'rate',
        'amount',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'rate' => 'decimal:2',
            'amount' => 'decimal:2',
        ];
    }

    public function setCompanyIdAttribute(mixed $value): void
    {
        $this->attributes['company_id'] = $value === null || $value === '' ? null : DataAreaId::normalize((string) $value);
    }

    public function setResourceCodeAttribute(mixed $value): void
    {
        $this->attributes['resource_code'] = $value === null || $value === '' ? null : trim((string) $value);
    }

    public function setProjectAttribute(mixed $value): void
    {
        $this->attributes['project'] = $value === null || $value === '' ? null : trim((string) $value);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
}
