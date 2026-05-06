<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuPermissionMatch extends Model
{
    protected $fillable = [
        'menu_key',
        'menu_label',
        'route_name',
        'permission_id',
    ];

    protected function casts(): array
    {
        return [
            'permission_id' => 'integer',
        ];
    }

    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class);
    }
}

