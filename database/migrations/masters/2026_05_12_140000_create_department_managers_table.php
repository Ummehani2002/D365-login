<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('department_managers', function (Blueprint $table) {
            $table->id();
            $table->string('employee_name', 255);
            $table->string('department', 255);
            $table->string('company_id', 100);
            $table->timestamps();

            $table->unique(['company_id', 'employee_name', 'department'], 'dept_mgr_company_employee_department_unique');
            $table->index(['company_id', 'employee_name'], 'dept_mgr_company_employee_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('department_managers');
    }
};
