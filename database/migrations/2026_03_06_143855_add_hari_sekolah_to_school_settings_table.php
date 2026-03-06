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
        DB::table('school_settings')->insert([
            'key' => 'hari_sekolah',
            'value' => 'senin,selasa,rabu,kamis,jumat',
            'description' => 'Hari-hari aktif sekolah (comma separated)',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('school_settings')->where('key', 'hari_sekolah')->delete();
    }
};
