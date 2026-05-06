<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'd365_id',
        'name',
        'created_by',
    ];

    protected $appends = [
        'company_id',
    ];

    public function getCompanyIdAttribute(): ?string
    {
        return $this->attributes['d365_id'] ?? null;
    }

    public function setCompanyIdAttribute(mixed $value): void
    {
        $this->attributes['d365_id'] = $value === null ? null : trim((string) $value);
    }

    public static function resolveFromMixed(mixed $value): ?self
    {
        if ($value === null) {
            return null;
        }

        $candidate = trim((string) $value);
        if ($candidate === '') {
            return null;
        }

        if (ctype_digit($candidate)) {
            $byId = static::query()->find((int) $candidate);
            if ($byId) {
                return $byId;
            }
        }

        return static::query()
            ->whereRaw('UPPER(d365_id) = ?', [strtoupper($candidate)])
            ->first();
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(CompanyMembership::class);
    }

    public function roles(): Builder
    {
        return Role::query()->whereHas(
            'companyMemberships',
            fn (Builder $query) => $query->where('company_memberships.company_id', $this->id)
        );
    }
}
