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
        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anggota_rombel_id')->constrained('anggota_rombel')->onDelete('cascade');
            $table->foreignId('schedule_id')->constrained('schedules')->onDelete('cascade');
            $table->date('tanggal');
            $table->dateTime('waktu_absen');
            $table->enum('jenis_absensi', ['masuk', 'pelajaran', 'pulang']);
            $table->enum('status', ['hadir', 'izin', 'sakit', 'alpha']);
            $table->enum('metode', ['wajah', 'manual']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};
