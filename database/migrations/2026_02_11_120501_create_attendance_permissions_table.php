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
        Schema::create('attendance_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anggota_rombel_id')->constrained('anggota_rombel')->onDelete('cascade');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->string('jenis'); // changed from enum to string for flexibility
            $table->text('keterangan');
            $table->string('lampiran')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->enum('jenis_absensi_manual', ['masuk', 'pulang', 'mapel'])->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_permissions');
    }
};
