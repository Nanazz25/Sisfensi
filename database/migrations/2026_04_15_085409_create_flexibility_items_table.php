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
        Schema::create('flexibility_items', function (Blueprint $table) {
            $table->id();
            $table->string('item_name');
            $table->string('item_type')->default('voucher');
            $table->integer('effect_value')->nullable();
            $table->text('description')->nullable();
            $table->integer('point_cost');
            $table->integer('purchase_limit')->nullable();
            $table->enum('purchase_period', ['none', 'daily', 'weekly', 'monthly'])->default('none');
            $table->integer('stock_limit')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flexibility_items');
    }
};
