<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('peserta', function (Blueprint $table) {
            $table->string('jurusan')->nullable()->after('nim');
            $table->string('angkatan')->nullable()->after('jurusan');
            $table->string('pilihan_divisi_3')->nullable()->after('pilihan_divisi_2');
            $table->text('alasan_3')->nullable()->after('alasan_2');
            $table->string('krs_terakhir')->nullable()->after('alasan_3');
            $table->boolean('formulir_pendaftaran')->default(false)->after('krs_terakhir');
            $table->boolean('surat_komitmen')->default(false)->after('formulir_pendaftaran');
            $table->boolean('pindah_divisi')->default(false)->after('surat_komitmen');
        });
    }

    public function down(): void
    {
        Schema::table('peserta', function (Blueprint $table) {
            $table->dropColumn([
                'jurusan',
                'angkatan',
                'pilihan_divisi_3',
                'alasan_3',
                'krs_terakhir',
                'formulir_pendaftaran',
                'surat_komitmen',
                'pindah_divisi'
            ]);
        });
    }
};

