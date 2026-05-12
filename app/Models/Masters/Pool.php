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

    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Company::class, 'company_id', 'd365_id');
    }
}
