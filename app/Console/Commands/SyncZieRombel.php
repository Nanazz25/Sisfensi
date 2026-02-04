<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\RombonganBelajar;
use App\Models\Jurusan;
use App\Models\TahunAjar;
use App\Models\Teacher;

class SyncZieRombel extends Command
{
    protected $signature = 'sync:zie-rombel {tahun=2025}';
    protected $description = 'Sync data rombel dari Zie API';

    public function handle()
    {
        $tahun = $this->argument('tahun');

        $this->info("Ambil data rombel tahun {$tahun}...");

        $response = Http::withoutVerifying()->get(
            'https://zieapi.zielabs.id/api/getkelas',
            ['tahun' => $tahun]
        );

        if (!$response->successful()) {
            $this->error('Gagal ambil data API');
            return;
        }

        $rombelResponse = $response->json();

        if (!isset($rombelResponse['data']) || empty($rombelResponse['data'])) {
            $this->warn('Data kosong atau format API tidak sesuai');
            return;
        }

        foreach ($rombelResponse['data'] as $r) {
            // 1. Ambil atau Buat Jurusan otomatis
            $jurusan = Jurusan::firstOrCreate(
                ['nama_jurusan' => $r['jurusan']],
                ['kode_jurusan' => $r['jurusan']] // Fallback kode sama dengan nama
            );

            // 2. Ambil atau Buat Tahun Ajar secara otomatis jika belum ada
            $tahunTarget = $r['tahun_ajar'] ?? $tahun;
            $tahunAjar = TahunAjar::where('nama', 'LIKE', "%$tahunTarget%")->first();

            if (!$tahunAjar) {
                $tahunAjar = TahunAjar::create([
                    'nama' => $tahunTarget,
                    'semester' => 'Ganjil', // Default
                    'tanggal_mulai' => "$tahunTarget-07-01",
                    'tanggal_selesai' => ($tahunTarget + 1) . "-06-30",
                    'is_active' => true,
                ]);
            }

            $waliKelas = Teacher::where('nama_lengkap', $r['wali_kelas'] ?? null)->first();

            RombonganBelajar::updateOrCreate(
                ['nama_rombel' => $r['nama'] ?? $r['rombel']],
                [
                    'jurusan_id' => $jurusan->id,
                    'tahun_ajar_id' => $tahunAjar->id,
                    'wali_kelas_id' => $waliKelas->id ?? null,
                ]
            );
        }

        $this->info('Sync rombel selesai');
    }
}
