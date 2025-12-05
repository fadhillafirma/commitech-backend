<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('peserta', function (Blueprint $table) {
            // Tambah kolom status dengan enum: belum_direview, lulus, tidak_lulus
            $table->enum('status_seleksi_berkas', ['belum_direview', 'lulus', 'tidak_lulus'])
                  ->default('belum_direview')
                  ->after('pindah_divisi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('peserta', function (Blueprint $table) {
            $table->dropColumn('status_seleksi_berkas');
        });
    }
};
