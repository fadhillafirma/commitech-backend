<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Peserta;
use App\Models\JadwalRekrutmen;

return new class extends Migration
{

    public function up(): void
    {
       
        $pesertaDenganJadwal = Peserta::whereNotNull('tanggal_jadwal')
            ->where('tanggal_jadwal', '!=', '')
            ->get();

        foreach ($pesertaDenganJadwal as $peserta) {
           
            $jadwal = JadwalRekrutmen::where('tanggal_mulai', $peserta->tanggal_jadwal)
                ->first();

            if ($jadwal) {
              
                $peserta->update([
                    'jadwal_rekrutmen_id' => $jadwal->id
                ]);
            }
        }
    }

    
    public function down(): void
    {
     
        $pesertaDenganJadwal = Peserta::whereNotNull('jadwal_rekrutmen_id')->get();

        foreach ($pesertaDenganJadwal as $peserta) {
            $jadwal = JadwalRekrutmen::find($peserta->jadwal_rekrutmen_id);

            if ($jadwal) {
               
                $peserta->update([
                    'tanggal_jadwal' => $jadwal->tanggal_mulai,
                    'waktu_jadwal' => $jadwal->waktu_mulai ?? '00:00 WIB',
                ]);
            }
        }
    }
};
