<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'project_id',
        'd365_id',
        'name',
        'created_by',
    ];

    protected $appends = ['project_id'];

    public function getProjectIdAttribute(): ?string
    {
        return $this->attributes['d365_id'] ?? null;
    }

    public function setProjectIdAttribute(mixed $value): void
    {
        $this->attributes['d365_id'] = $value === null ? null : trim((string) $value);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Company::class);
    }
}
