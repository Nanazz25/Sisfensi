<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Teacher;

class SyncZieGuru extends Command
{
    protected $signature = 'sync:zie-guru {tahun=2025}';
    protected $description = 'Sync data guru dari Zie API';

    public function handle()
    {
        $tahun = $this->argument('tahun');

        $this->info("Ambil data guru tahun {$tahun}...");

        $response = Http::withoutVerifying()->get(
            'https://zieapi.zielabs.id/api/getguru',
            ['tahun' => $tahun]
        );

        if (!$response->successful()) {
            $this->error('Gagal ambil data API');
            return;
        }

        $gurus = $response->json();

        if (empty($gurus)) {
            $this->warn('Data kosong');
            return;
        }

        foreach ($gurus as $guru) {
            $jk = strtoupper(trim($guru['jenis_kelamin'] ?? ''));
            if (!in_array($jk, ['L', 'P'])) {
                $jk = 'L';
            }

            $user = User::updateOrCreate(
                ['email' => $guru['email']],
                [
                    'name' => $guru['nama'],
                    'password' => Hash::make('password'),
                    'role' => 'guru',
                ]
            );

            Teacher::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nama_lengkap' => $guru['nama'],
                    'nip' => trim($guru['nip'] ?? '') ?: null,
                    'nuptk' => trim($guru['nuptk'] ?? '') ?: null,
                    'nik' => trim($guru['nik'] ?? '') ?: null,
                    'jenis_kelamin' => $jk,
                    'tempat_lahir' => $guru['tempat_lahir'] ?? null,
                    'tanggal_lahir' => $guru['tanggal_lahir'] ?? null,
                ]
            );
        }

        $this->info('Sync guru selesai');
    }
}
