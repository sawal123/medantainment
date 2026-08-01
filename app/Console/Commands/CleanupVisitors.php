<?php

namespace App\Console\Commands;

use App\Models\Visitor;
use Illuminate\Console\Command;

class CleanupVisitors extends Command
{
    /**
     * Nama dan opsi parameter perintah konsol.
     */
    protected $signature = 'visitors:cleanup {--older-than=90 : Jumlah hari batas masa simpan log visitor}';

    /**
     * Deskripsi fungsi perintah konsol.
     */
    protected $description = 'Membersihkan data riwayat kunjungan visitor lawas guna menjaga kesehatan performa database';

    /**
     * Eksekusi logika pembersihan.
     */
    public function handle(): int
    {
        $days = (int) $this->option('older-than');
        $thresholdDate = now()->subDays($days);

        $deletedCount = Visitor::where('created_at', '<', $thresholdDate)->delete();

        $this->info("Berhasil membersihkan {$deletedCount} catatan visitor yang lebih lama dari {$days} hari.");

        return Command::SUCCESS;
    }
}
