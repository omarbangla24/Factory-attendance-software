<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advance_deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advance_id')->constrained('advances')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('salary_sheet_id')->constrained('salary_sheets')->onDelete('cascade');
            $table->decimal('amount', 12, 2);
            $table->timestamps();
            
            $table->index('employee_id');
            $table->index('salary_sheet_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advance_deductions');
    }
};
