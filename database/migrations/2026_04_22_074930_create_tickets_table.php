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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('operator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('category_id')->constrained('ticket_categories')->cascadeOnDelete();
            $table->string('subject');
            $table->text('description');
            $table->enum('priority', ['Low', 'Mid', 'High'])->default('Low');
            $table->enum('status', ['Open', 'In-Progress', 'Resolved', 'Closed'])->default('Open');
            
            // SLA tracking
            $table->timestamp('responded_at')->nullable(); // timestamp of first response
            $table->timestamp('resolved_at')->nullable();  // timestamp when status becomes resolved/closed
            
            $table->timestamps();

            // Full-text index for anti-duplication feature
            $table->fullText(['subject', 'description']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
