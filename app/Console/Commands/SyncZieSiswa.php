<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Models\PesertaDidik;
use Illuminate\Support\Str;

class SyncZieSiswa extends Command
{
    protected $signature = 'sync:zie-siswa {tahun=2025}';
    protected $description = 'Sync data peserta didik dari ZIE API';

    public function handle()
    {
        $tahun = $this->argument('tahun');

        $this->info("Ambil data siswa tahun {$tahun}...");

        $response = Http::withoutVerifying()
            ->timeout(60)
            ->get("https://zieapi.zielabs.id/api/getsiswa", [
                'tahun' => $tahun
            ]);

        if (!$response->ok()) {
            $this->error('Gagal mengambil data API');
            return;
        }

        $data = $response->json()['data'] ?? [];

        if (count($data) === 0) {
            $this->warn('Data kosong');
            return;
        }

        foreach ($data as $siswa) {

            $noInduk = trim($siswa['no_induk'] ?? '')
                ?: trim($siswa['nisn'] ?? '')
                ?: null;

            if (!$noInduk) {
                $this->warn("Siswa {$siswa['nama']} tidak punya No Induk/NISN, skip.");
                continue;
            }

            $email = $siswa['email'] ?? null;
            if (!$email) {
                $email = Str::slug($siswa['nama']) . '.' . $noInduk . '@siswa.id';
            }

            $jk = strtoupper(trim($siswa['jenis_kelamin'] ?? ''));
            if (!in_array($jk, ['L', 'P'])) {
                $jk = 'L';
            }

            $tanggalLahir = $siswa['tanggal_lahir'] ?? null;
            if (
                !$tanggalLahir ||
                $tanggalLahir === '0000-00-00' ||
                $tanggalLahir === '0000-00-00 00:00:00'
            ) {
                $tanggalLahir = null;
            }

            $nisn = trim($siswa['nisn'] ?? '');
            $nik = trim($siswa['nik'] ?? '');
            if (empty($nik) || in_array($nik, ['-', '.', '0']))
                $nik = null;

            $pesertaDidik = PesertaDidik::where('no_induk', $noInduk)
                ->when($nisn, fn($q) => $q->orWhere('nisn', $nisn))
                ->when($nik, fn($q) => $q->orWhere('nik', $nik))
                ->first();

            if ($pesertaDidik) {
                // Update User terkait
                /** @var \App\Models\User $userRelation */
                $userRelation = $pesertaDidik->user;
                $userRelation->update([
                    'name' => $siswa['nama'],
                ]);

                // Update Data Peserta Didik
                /** @var PesertaDidik $pesertaDidik */
                $pesertaDidik->update([
                    'nama_lengkap' => $siswa['nama'],
                    'no_induk' => $noInduk,
                    'nisn' => $nisn ?: null,
                    'nik' => $nik ?: null,
                    'jenis_kelamin' => $jk,
                    'tempat_lahir' => $siswa['tempat_lahir'] ?: null,
                    'tanggal_lahir' => $tanggalLahir,
                ]);
            } else {
                // Buat User baru (atau gunakan yang sudah ada jika email sama)
                $plainPassword = Str::random(8); // Generate random password

                $user = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'name' => $siswa['nama'],
                        'password' => bcrypt($plainPassword),
                        'initial_password' => $plainPassword,
                        'password_changed' => false,
                        'role' => 'siswa'
                    ]
                );

                // Buat Data Peserta Didik baru
                PesertaDidik::create([
                    'user_id' => $user->id,
                    'nama_lengkap' => $siswa['nama'],
                    'no_induk' => $noInduk,
                    'nisn' => $nisn ?: null,
                    'nik' => $nik ?: null,
                    'jenis_kelamin' => $jk,
                    'tempat_lahir' => $siswa['tempat_lahir'] ?: null,
                    'tanggal_lahir' => $tanggalLahir,
                ]);
            }
        }

        $this->info('Sync peserta didik selesai');
    }
}
