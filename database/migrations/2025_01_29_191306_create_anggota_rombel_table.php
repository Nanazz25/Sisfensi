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
        Schema::create('anggota_rombel', function (Blueprint $table) {
            $table->id();

            $table->foreignId('rombongan_belajar_id')
                ->constrained('rombongan_belajar')
                ->cascadeOnDelete();

            $table->foreignId('peserta_didik_id')
                ->constrained('peserta_didik')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anggota_rombel');
    }
};
