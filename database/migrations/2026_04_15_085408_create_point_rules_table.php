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
        Schema::create('point_rules', function (Blueprint $table) {
            $table->id();
            $table->string('rule_name');
            $table->string('target_role');
            $table->string('trigger_type')->default('attendance');
            $table->string('basis_type')->default('fixed');
            $table->string('reference_key')->nullable();
            $table->integer('offset_minutes')->default(0);
            $table->string('condition_operator')->nullable();
            $table->string('condition_value')->nullable();
            $table->integer('point_modifier');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_rules');
    }
};
