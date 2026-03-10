<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('school_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('value');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Seed data awal
        DB::table('school_settings')->insert([
            ['key' => 'jam_masuk', 'value' => '07:00', 'description' => 'Batas awal jam masuk sekolah'],
            ['key' => 'jam_masuk_toleransi', 'value' => '07:30', 'description' => 'Batas akhir jam masuk sebelum dianggap terlambat'],
            ['key' => 'jam_pulang', 'value' => '15:00', 'description' => 'Jam resmi pulang sekolah'],
            [
                'key' => 'facelog_retention_days',
                'value' => '14',
                'description' => 'Jumlah hari penyimpanan face log (gagal verifikasi)'
            ],
            [
                'key' => 'hari_sekolah',
                'value' => 'senin,selasa,rabu,kamis,jumat',
                'description' => 'Hari-hari aktif sekolah (comma separated)'
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('school_settings');
    }
};
