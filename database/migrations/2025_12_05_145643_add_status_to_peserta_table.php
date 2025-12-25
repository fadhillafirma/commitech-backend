<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::table('peserta', function (Blueprint $table) {
       
            $table->enum('status_seleksi_berkas', ['belum_direview', 'lulus', 'tidak_lulus'])
                  ->default('belum_direview')
                  ->after('pindah_divisi');
        });
    }


    public function down(): void
    {
        Schema::table('peserta', function (Blueprint $table) {
            $table->dropColumn('status_seleksi_berkas');
        });
    }
};
