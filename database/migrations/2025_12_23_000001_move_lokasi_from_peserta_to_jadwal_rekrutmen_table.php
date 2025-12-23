<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwal_rekrutmen', function (Blueprint $table) {
            $table->string('lokasi')->nullable()->after('pewawancara');
        });

        $pairs = DB::table('peserta')
            ->select('jadwal_rekrutmen_id', 'lokasi')
            ->whereNotNull('jadwal_rekrutmen_id')
            ->whereNotNull('lokasi')
            ->where('lokasi', '!=', '')
            ->orderBy('jadwal_rekrutmen_id')
            ->get();

        $jadwalLokasi = [];
        foreach ($pairs as $row) {
            if ($row->jadwal_rekrutmen_id === null) {
                continue;
            }
            if (!array_key_exists($row->jadwal_rekrutmen_id, $jadwalLokasi)) {
                $jadwalLokasi[$row->jadwal_rekrutmen_id] = $row->lokasi;
            }
        }

        foreach ($jadwalLokasi as $jadwalId => $lokasi) {
            DB::table('jadwal_rekrutmen')
                ->where('id', $jadwalId)
                ->where(function ($q) {
                    $q->whereNull('lokasi')->orWhere('lokasi', '=', '');
                })
                ->update(['lokasi' => $lokasi]);
        }

        Schema::table('peserta', function (Blueprint $table) {
            $table->dropColumn('lokasi');
        });
    }

    public function down(): void
    {
        Schema::table('peserta', function (Blueprint $table) {
            $table->string('lokasi')->nullable()->after('jadwal_rekrutmen_id');
        });

        $jadwalRows = DB::table('jadwal_rekrutmen')
            ->select('id', 'lokasi')
            ->whereNotNull('lokasi')
            ->where('lokasi', '!=', '')
            ->get();

        foreach ($jadwalRows as $jadwal) {
            DB::table('peserta')
                ->where('jadwal_rekrutmen_id', $jadwal->id)
                ->update(['lokasi' => $jadwal->lokasi]);
        }

        Schema::table('jadwal_rekrutmen', function (Blueprint $table) {
            $table->dropColumn('lokasi');
        });
    }
};
