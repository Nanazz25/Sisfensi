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
        Schema::create('rombongan_belajar', function (Blueprint $table) {
            $table->id();
            $table->string('nama_rombel');
            $table->foreignId('jurusan_id')
                ->constrained('jurusan')
                ->cascadeOnDelete();

            $table->foreignId('tahun_ajar_id')
                ->constrained('tahun_ajar')
                ->cascadeOnDelete();

            $table->foreignId('wali_kelas_id')
                ->nullable()
                ->constrained('teachers')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rombongan_belajar');
    }
};
