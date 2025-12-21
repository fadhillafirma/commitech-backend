<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Peserta;
use App\Models\JadwalRekrutmen;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Migrasi data dari tanggal_jadwal dan waktu_jadwal ke jadwal_rekrutmen_id
     */
    public function up(): void
    {
        // Ambil semua peserta yang memiliki tanggal_jadwal
        $pesertaDenganJadwal = Peserta::whereNotNull('tanggal_jadwal')
            ->where('tanggal_jadwal', '!=', '')
            ->get();

        foreach ($pesertaDenganJadwal as $peserta) {
            // Cari jadwal rekrutmen yang sesuai dengan tanggal_jadwal
            // Cocokkan berdasarkan tanggal_mulai
            $jadwal = JadwalRekrutmen::where('tanggal_mulai', $peserta->tanggal_jadwal)
                ->first();

            if ($jadwal) {
                // Update peserta dengan jadwal_rekrutmen_id
                $peserta->update([
                    'jadwal_rekrutmen_id' => $jadwal->id
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     * Migrasi kembali dari jadwal_rekrutmen_id ke tanggal_jadwal dan waktu_jadwal
     */
    public function down(): void
    {
        // Ambil semua peserta yang memiliki jadwal_rekrutmen_id
        $pesertaDenganJadwal = Peserta::whereNotNull('jadwal_rekrutmen_id')->get();

        foreach ($pesertaDenganJadwal as $peserta) {
            $jadwal = JadwalRekrutmen::find($peserta->jadwal_rekrutmen_id);

            if ($jadwal) {
                // Update peserta dengan tanggal_jadwal dan waktu_jadwal
                // Gunakan waktu_mulai sebagai waktu_jadwal default
                $peserta->update([
                    'tanggal_jadwal' => $jadwal->tanggal_mulai,
                    'waktu_jadwal' => $jadwal->waktu_mulai ?? '00:00 WIB',
                ]);
            }
        }
    }
};
