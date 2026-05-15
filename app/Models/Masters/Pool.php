<?php

namespace App\Models\Masters;

use App\Support\DataAreaId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

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
        'has_fd_location',
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
            'has_fd_location' => 'boolean',
        ];
    }

    public function setCompanyIdAttribute(mixed $value): void
    {
        $this->attributes['company_id'] = $value === null || $value === '' ? null : DataAreaId::normalize((string) $value);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Company::class, 'company_id', 'd365_id');
    }

    /**
     * Columns needed for PR pool lookups (avoids SQL errors if migrations are pending).
     *
     * @return list<string>
     */
    public static function purchReqSelectColumns(): array
    {
        $columns = [
            'id',
            'pool_id',
            'name',
            'company_id',
            'uses_project',
            'uses_warehouse',
            'has_attachment',
            'has_item_category',
            'has_item_id',
            'item_id',
            'category_item',
        ];

        if (Schema::hasColumn((new static)->getTable(), 'has_fd_location')) {
            $columns[] = 'has_fd_location';
        }

        return $columns;
    }
}
