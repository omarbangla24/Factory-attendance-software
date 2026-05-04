<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            $table->string('event')->nullable()->after('log_name');
            $table->json('attribute_changes')->nullable()->after('properties');
            $table->uuid('batch_uuid')->nullable()->after('attribute_changes');
        });
    }

    public function down(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            $table->dropColumn(['event', 'attribute_changes', 'batch_uuid']);
        });
    }
};
