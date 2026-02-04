<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Illuminate\Support\Str;
use App\Models\RombonganBelajar;
use App\Models\PesertaDidik;
use App\Models\AnggotaRombel;
use App\Models\Jurusan;
use App\Models\TahunAjar;

class SyncZieAnggotaRombel extends Command
{
    protected $signature = 'sync:zie-anggota-rombel {tahun=2025}';
    protected $description = 'Sync data anggota rombel dari Zie API';

    public function handle()
    {
        $tahun = $this->argument('tahun');
        $this->info("Ambil anggota rombel tahun {$tahun}...");

        $response = Http::withoutVerifying()->get(
            'https://zieapi.zielabs.id/api/getsiswa',
            ['tahun' => $tahun]
        );

        if (!$response->successful()) {
            $this->error('Gagal ambil data anggota rombel');
            return Command::FAILURE;
        }

        $data = $response->json('data');

        if (empty($data)) {
            $this->warn('Data anggota rombel kosong');
            return Command::SUCCESS;
        }

        foreach ($data as $item) {
            $noInduk = trim($item['no_induk'] ?? '');
            $nama = trim($item['nama'] ?? '');

            if (!$noInduk || !$nama) {
                $this->warn("Skip data: No Induk atau Nama kosong.");
                continue;
            }

            $nisn = trim($item['nisn'] ?? '');
            $nik = trim($item['nik'] ?? '');
            if (empty($nik) || in_array($nik, ['0', '-', '.']))
                $nik = null;
            if (empty($nisn) || in_array($nisn, ['0', '-', '.']))
                $nisn = null;

            // 0. Cari atau buat User untuk Siswa
            $pesertaDidik = PesertaDidik::where('no_induk', $noInduk)
                ->when($nisn, fn($q) => $q->orWhere('nisn', $nisn))
                ->when($nik, fn($q) => $q->orWhere('nik', $nik))
                ->first();

            if ($pesertaDidik && $pesertaDidik->user_id) {
                $userId = $pesertaDidik->user_id;
            } else {
                $email = ($item['email'] ?? null) ?: Str::slug($nama) . '.' . $noInduk . '@siswa.local';
                $user = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'name' => $nama,
                        'password' => bcrypt('password'),
                        'role' => 'siswa'
                    ]
                );
                $userId = $user->id;
            }

            if (!$userId) {
                $this->error("Gagal mendapatkan User ID untuk {$nama}");
                continue;
            }

            // 1. Update atau buat peserta didik
            $studentData = [
                'nama_lengkap' => $nama,
                'no_induk' => $noInduk,
                'nisn' => $nisn,
                'nik' => $nik,
                'jenis_kelamin' => ($item['jenis_kelamin'] ?? null) ?: 'L',
                'tempat_lahir' => ($item['tempat_lahir'] ?? null) ?: '-',
                'tanggal_lahir' => (!empty($item['tanggal_lahir']) && $item['tanggal_lahir'] !== '0000-00-00' ? $item['tanggal_lahir'] : null),
                'user_id' => $userId,
            ];

            if ($pesertaDidik) {
                $pesertaDidik->update($studentData);
                $siswa = $pesertaDidik;
            } else {
                $siswa = PesertaDidik::create($studentData);
            }

            // 2. Update atau buat rombel
            $rombelName = trim($item['nama_rombel'] ?? '');
            if (!$rombelName) {
                $this->warn("Skip Rombel: Nama rombel kosong untuk {$nama}");
                continue;
            }

            $rombel = RombonganBelajar::where('nama_rombel', $rombelName)->first();

            if (!$rombel) {
                // Ambil atau buat Jurusan
                $namaJurusan = $item['jurusan'] ?? 'Umum';
                $jurusan = Jurusan::firstOrCreate(
                    ['nama_jurusan' => $namaJurusan],
                    ['kode_jurusan' => strtoupper(Str::slug($namaJurusan))]
                );

                // Ambil Tahun Ajar
                $tahunAjar = TahunAjar::where('nama', 'LIKE', "%$tahun%")->first();
                if (!$tahunAjar) {
                    $tahunAjar = TahunAjar::where('is_active', true)->first();
                }

                if (!$tahunAjar) {
                    $this->warn("Tahun ajar tidak ditemukan, skip rombel {$rombelName}");
                    continue;
                }

                $rombel = RombonganBelajar::create([
                    'nama_rombel' => $rombelName,
                    'jurusan_id' => $jurusan->id,
                    'tahun_ajar_id' => $tahunAjar->id,
                ]);
            }

            // 3. Mapping peserta didik ke rombel
            AnggotaRombel::updateOrCreate(
                [
                    'rombongan_belajar_id' => $rombel->id,
                    'peserta_didik_id' => $siswa->id,
                ]
            );
        }

        $this->info('Sync anggota rombel selesai');
        return Command::SUCCESS;
    }
}
