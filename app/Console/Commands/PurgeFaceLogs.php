<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FaceLog;
use App\Models\SchoolSetting;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class PurgeFaceLogs extends Command
{
    protected $signature = 'facelog:purge';
    protected $description = 'Hapus face log FAILURE berdasarkan retention days';

    public function handle()
    {
        $days = SchoolSetting::where('key', 'facelog_retention_days')->value('value') ?? 7;
        $cutoff = Carbon::now()->subDays((int) $days);

        $logs = FaceLog::where('result', '!=', 'match')
            ->where('created_at', '<', $cutoff)
            ->get();

        $deleted = 0;

        foreach ($logs as $log) {
            if ($log->image_path && Storage::exists($log->image_path)) {
                Storage::delete($log->image_path);
            }

            $log->delete();
            $deleted++;
        }

        $this->info("🧹 {$deleted} face log lama berhasil dihapus.");
    }
}
