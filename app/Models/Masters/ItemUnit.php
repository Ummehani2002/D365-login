<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'item_id',
        'unit_id',
        'unit_name',
        'definition',
        'created_by',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Item::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Company::class);
    }
}
