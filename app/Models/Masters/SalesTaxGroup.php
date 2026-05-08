<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesTaxGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'tax_group_id',
        'tax_group_name',
        'created_by',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Company::class);
    }
}
