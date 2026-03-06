<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('face_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peserta_didik_id')->nullable()->constrained('peserta_didik')->onDelete('cascade');
            $table->float('confidence')->nullable();
            $table->enum('result', [
                'unrecognized',
                'low_confidence',
                'spoof',
            ]);
            $table->string('image_path')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('face_logs');
    }
};
