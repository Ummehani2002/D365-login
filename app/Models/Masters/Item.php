<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    protected $fillable = [
        'company_id',
        'item_id',
        'd365_id',
        'd365_item_id',
        'item_name',
        'type',
        'item_category_id',
    ];

    protected $appends = ['item_id'];

    public function getItemIdAttribute(): ?string
    {
        return $this->attributes['d365_id']
            ?? $this->attributes['d365_item_id']
            ?? null;
    }

    public function setItemIdAttribute(mixed $value): void
    {
        $resolved = $value === null ? null : trim((string) $value);
        $this->attributes['d365_id']      = $resolved;
        $this->attributes['d365_item_id'] = $resolved;
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Company::class, 'company_id');
    }

    public function itemUnits(): HasMany
    {
        return $this->hasMany(\App\Models\ItemUnit::class);
    }
}
