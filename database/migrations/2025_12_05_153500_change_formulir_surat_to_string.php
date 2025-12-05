<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * PERBAIKAN: Ubah formulir_pendaftaran dan surat_komitmen dari boolean ke string
     * agar bisa menyimpan link Google Drive
     */
    public function up(): void
    {
        Schema::table('peserta', function (Blueprint $table) {
            // Drop kolom boolean lama
            $table->dropColumn(['formulir_pendaftaran', 'surat_komitmen']);
        });
        
        // Tambah ulang sebagai string/text untuk menyimpan URL
        Schema::table('peserta', function (Blueprint $table) {
            $table->text('formulir_pendaftaran')->nullable()->after('krs_terakhir');
            $table->text('surat_komitmen')->nullable()->after('formulir_pendaftaran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('peserta', function (Blueprint $table) {
            // Drop kolom string
            $table->dropColumn(['formulir_pendaftaran', 'surat_komitmen']);
        });
        
        // Kembalikan ke boolean
        Schema::table('peserta', function (Blueprint $table) {
            $table->boolean('formulir_pendaftaran')->default(false)->after('krs_terakhir');
            $table->boolean('surat_komitmen')->default(false)->after('formulir_pendaftaran');
        });
    }
};

