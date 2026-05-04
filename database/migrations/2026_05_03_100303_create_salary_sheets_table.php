<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_sheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('month'); // Format: YYYY-MM
            $table->decimal('total_hajira', 5, 2)->default(0);
            $table->decimal('total_overtime_hours', 8, 2)->default(0);
            $table->integer('absent_days')->default(0);
            $table->decimal('basic_amount', 12, 2)->default(0);
            $table->decimal('overtime_amount', 12, 2)->default(0);
            $table->decimal('advance_deducted', 12, 2)->default(0);
            $table->decimal('adjustment_amount', 12, 2)->default(0);
            $table->decimal('net_salary', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('due_amount', 12, 2)->default(0);
            $table->enum('status', ['draft', 'locked', 'partial', 'paid'])->default('draft');
            $table->timestamp('locked_at')->nullable();
            $table->timestamps();
            
            $table->unique(['employee_id', 'month']);
            $table->index('status');
            $table->index('month');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_sheets');
    }
};
