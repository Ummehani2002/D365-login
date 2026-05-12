<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pool extends Model
{
    use HasFactory;

    protected $fillable = [
        'pool_id',
        'name',
        'uses_project',
        'uses_warehouse',
        'has_attachment',
        'has_item_category',
        'has_item_id',
        'project',
        'warehouse',
        'warehouse_company_id',
        'project_warehouse',
        'attachment',
        'item_category',
        'item_id',
        'category_item',
        'company_id',
    ];

    protected function casts(): array
    {
        return [
            'uses_project' => 'boolean',
            'uses_warehouse' => 'boolean',
            'has_attachment' => 'boolean',
            'has_item_category' => 'boolean',
            'has_item_id' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Company::class, 'company_id', 'd365_id');
    }
}
