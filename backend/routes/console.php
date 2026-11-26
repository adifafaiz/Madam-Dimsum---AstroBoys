<?php

use App\Services\BackupService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * Pencadangan data DimsumKu.
 * - php artisan backup:buat            -> cadangan manual sekarang juga
 * - php artisan backup:otomatis        -> cadangan otomatis bila sudah jatuh tempo (jadwal di menu Pencadangan)
 * Scheduler memeriksa jadwal tiap menit; jalankan `php artisan schedule:work` (lokal)
 * atau cron `* * * * * php artisan schedule:run` (server).
 */
Artisan::command('backup:buat', function (BackupService $service) {
    $backup = $service->buat('manual');
    $backup->status === 'berhasil'
        ? $this->info("Cadangan dibuat: {$backup->file_path} ({$backup->ukuran_kb} KB)")
        : $this->error('Pencadangan gagal, lihat storage/logs/laravel.log');
})->purpose('Buat cadangan data DimsumKu sekarang');

Artisan::command('backup:otomatis', function (BackupService $service) {
    $backup = $service->jalankanOtomatisBilaJatuhTempo();
    if (! $backup) {
        $this->line('Belum jatuh tempo. Jadwal berikutnya: ' . $service->jadwalBerikutnya()->format('d M Y H:i'));
        return;
    }
    $backup->status === 'berhasil'
        ? $this->info("Cadangan otomatis dibuat: {$backup->file_path}")
        : $this->error('Cadangan otomatis gagal, lihat storage/logs/laravel.log');
})->purpose('Jalankan cadangan otomatis sesuai jadwal');

Schedule::command('backup:otomatis')->everyMinute()->withoutOverlapping();
