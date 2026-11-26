<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Backup;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Pencadangan & pemulihan data DimsumKu.
 *
 * Cadangan berupa file JSON berisi seluruh tabel bisnis, disimpan di disk "local"
 * (storage/app/private/backups). Format JSON dipilih supaya tidak bergantung pada
 * jenis database (SQLite/MySQL) dan bisa dipulihkan dalam satu transaksi.
 *
 * Tabel backups sendiri, sesi login, cache, dan token sengaja tidak ikut dicadangkan,
 * supaya riwayat cadangan tetap utuh dan pengguna yang sedang login tidak terlempar.
 */
class BackupService
{
    public const FORMAT = 'dimsumku-backup';
    public const VERSI = 1;
    public const FOLDER = 'backups';

    /** Urutan sesuai ketergantungan foreign key: induk dulu, anak belakangan. */
    public const TABEL = [
        'settings',
        'suppliers',
        'users',
        'products',
        'orders',
        'order_items',
        'stock_movements',
        'activity_logs',
    ];

    public const FREKUENSI = ['Harian', 'Mingguan', 'Bulanan'];

    protected function disk()
    {
        return Storage::disk('local');
    }

    /**
     * Buat cadangan baru. Selalu menghasilkan baris di tabel backups:
     * status "berhasil" bila file tersimpan, "gagal" bila terjadi error.
     */
    public function buat(string $jenis = 'manual'): Backup
    {
        $waktu = Carbon::now();
        $path = self::FOLDER . '/backup_' . $waktu->format('Y_m_d_His') . '_' . $jenis . '.json';

        try {
            $isi = [
                'format' => self::FORMAT,
                'versi' => self::VERSI,
                'dibuat' => $waktu->toIso8601String(),
                'tabel' => [],
            ];
            foreach (self::TABEL as $tabel) {
                $isi['tabel'][$tabel] = DB::table($tabel)->orderBy('id')->get()
                    ->map(fn ($baris) => (array) $baris)->all();
            }

            $json = json_encode($isi, JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR);
            if (! $this->disk()->put($path, $json)) {
                throw new RuntimeException('File cadangan tidak dapat ditulis.');
            }

            return Backup::create([
                'waktu' => $waktu,
                'ukuran_kb' => (int) max(1, ceil(strlen($json) / 1024)),
                'jenis' => $jenis,
                'status' => 'berhasil',
                'file_path' => $path,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return Backup::create([
                'waktu' => $waktu,
                'ukuran_kb' => 0,
                'jenis' => $jenis,
                'status' => 'gagal',
                'file_path' => $path,
            ]);
        }
    }

    public function fileAda(Backup $backup): bool
    {
        return $backup->status === 'berhasil'
            && $backup->file_path
            && $this->disk()->exists($backup->file_path);
    }

    public function pathAbsolut(Backup $backup): string
    {
        return $this->disk()->path($backup->file_path);
    }

    /**
     * Baca & periksa isi file cadangan. Melempar RuntimeException dengan pesan
     * Bahasa Indonesia yang aman ditampilkan ke pengguna.
     */
    public function baca(Backup $backup): array
    {
        if (! $this->fileAda($backup)) {
            throw new RuntimeException('File cadangan tidak ditemukan.');
        }

        $isi = json_decode((string) $this->disk()->get($backup->file_path), true);
        if (! is_array($isi) || ($isi['format'] ?? null) !== self::FORMAT || ! is_array($isi['tabel'] ?? null)) {
            throw new RuntimeException('File cadangan rusak atau bukan cadangan DimsumKu.');
        }
        foreach (self::TABEL as $tabel) {
            if (! array_key_exists($tabel, $isi['tabel']) || ! is_array($isi['tabel'][$tabel])) {
                throw new RuntimeException('File cadangan tidak lengkap.');
            }
        }

        return $isi;
    }

    /**
     * Pulihkan seluruh data bisnis dari cadangan dalam SATU transaksi.
     * Sebelum memulihkan, kondisi saat ini dicadangkan dulu supaya bisa dikembalikan.
     *
     * @return Backup cadangan pengaman yang dibuat sebelum pemulihan
     */
    public function pulihkan(Backup $backup, ?int $userId = null): Backup
    {
        $isi = $this->baca($backup);

        $pengaman = $this->buat('manual');
        if ($pengaman->status !== 'berhasil') {
            throw new RuntimeException('Gagal membuat cadangan pengaman, pemulihan dibatalkan.');
        }

        DB::transaction(function () use ($isi) {
            foreach (array_reverse(self::TABEL) as $tabel) {
                DB::table($tabel)->delete();
            }
            foreach (self::TABEL as $tabel) {
                foreach (array_chunk($isi['tabel'][$tabel], 100) as $potongan) {
                    DB::table($tabel)->insert($potongan);
                }
            }
        });

        // BR-11: catat pemulihan (hanya bila user pelaku ada di data hasil pemulihan)
        if ($userId && DB::table('users')->where('id', $userId)->exists()) {
            ActivityLog::create([
                'user_id' => $userId,
                'aksi' => 'pulihkan_cadangan',
                'tabel' => 'backups',
                'data_lama' => ['cadangan_pengaman' => $pengaman->file_path],
                'data_baru' => ['dipulihkan_dari' => $backup->file_path, 'waktu_cadangan' => $backup->waktu?->toDateTimeString()],
                'waktu' => Carbon::now(),
            ]);
        }

        return $pengaman;
    }

    /* ---------- Jadwal otomatis ---------- */

    public function jadwal(): array
    {
        $frekuensi = Setting::where('kunci', 'backup_frekuensi')->value('nilai') ?? 'Harian';
        $jam = Setting::where('kunci', 'backup_jam')->value('nilai') ?? '02:00';

        return [
            in_array($frekuensi, self::FREKUENSI, true) ? $frekuensi : 'Harian',
            preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $jam) ? $jam : '02:00',
        ];
    }

    /**
     * Waktu jadwal terakhir yang sudah lewat (<= $sekarang).
     * Harian: tiap hari; Mingguan: tiap Senin; Bulanan: tiap tanggal 1.
     */
    public function jadwalTerakhir(?Carbon $sekarang = null): Carbon
    {
        $sekarang ??= Carbon::now();
        [$frekuensi, $jam] = $this->jadwal();
        [$h, $m] = array_map('intval', explode(':', $jam));

        $kandidat = match ($frekuensi) {
            'Mingguan' => $sekarang->copy()->startOfWeek(Carbon::MONDAY)->setTime($h, $m),
            'Bulanan' => $sekarang->copy()->startOfMonth()->setTime($h, $m),
            default => $sekarang->copy()->setTime($h, $m),
        };

        if ($kandidat->gt($sekarang)) {
            $kandidat = match ($frekuensi) {
                'Mingguan' => $kandidat->subWeek(),
                'Bulanan' => $kandidat->subMonthNoOverflow(),
                default => $kandidat->subDay(),
            };
        }

        return $kandidat;
    }

    /** Waktu jadwal berikutnya, untuk ditampilkan ke owner. */
    public function jadwalBerikutnya(?Carbon $sekarang = null): Carbon
    {
        [$frekuensi] = $this->jadwal();
        $terakhir = $this->jadwalTerakhir($sekarang);

        return match ($frekuensi) {
            'Mingguan' => $terakhir->copy()->addWeek(),
            'Bulanan' => $terakhir->copy()->addMonthNoOverflow(),
            default => $terakhir->copy()->addDay(),
        };
    }

    /**
     * Jalankan cadangan otomatis bila jadwal terakhir belum dikerjakan.
     * Aman dipanggil tiap menit oleh scheduler; cadangan yang terlewat (server mati)
     * dikejar sekali saat scheduler berjalan lagi.
     */
    public function jalankanOtomatisBilaJatuhTempo(?Carbon $sekarang = null): ?Backup
    {
        $sekarang ??= Carbon::now();
        $jatuhTempo = $this->jadwalTerakhir($sekarang);

        $sudahBerhasil = Backup::where('jenis', 'otomatis')
            ->where('status', 'berhasil')
            ->where('waktu', '>=', $jatuhTempo)
            ->exists();

        // Bila percobaan gagal, coba lagi paling cepat 1 jam kemudian (tidak membanjiri riwayat).
        $baruDicoba = Backup::where('jenis', 'otomatis')
            ->where('waktu', '>=', $jatuhTempo)
            ->where('waktu', '>', $sekarang->copy()->subHour())
            ->exists();

        return ($sudahBerhasil || $baruDicoba) ? null : $this->buat('otomatis');
    }
}
