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
        Schema::table('point_rules', function (Blueprint $table) {
            $table->string('attendance_type')->default('all')->after('trigger_type');
        });

        // Set existing attendance rules to 'masuk' by default
        \DB::table('point_rules')
            ->where('trigger_type', 'attendance')
            ->update(['attendance_type' => 'masuk']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('point_rules', function (Blueprint $table) {
            $table->dropColumn('attendance_type');
        });
    }
};
