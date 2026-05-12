<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentManager extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_name',
        'department',
        'company_id',
    ];
}
