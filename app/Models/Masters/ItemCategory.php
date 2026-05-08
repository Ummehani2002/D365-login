<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;

class ItemCategory extends Model
{
    protected $fillable = [
        'company_id',
        'item_category_id',
        'd365_id',
        'name',
    ];

    protected $appends = ['item_category_id'];

    public function getItemCategoryIdAttribute(): ?string
    {
        return $this->attributes['d365_id'] ?? null;
    }

    public function setItemCategoryIdAttribute(mixed $value): void
    {
        $this->attributes['d365_id'] = $value === null ? null : trim((string) $value);
    }
}
