<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Hapus kolom tanggal_jadwal dan waktu_jadwal setelah migrasi ke jadwal_rekrutmen_id
     */
    public function up(): void
    {
        Schema::table('peserta', function (Blueprint $table) {
            $table->dropColumn(['tanggal_jadwal', 'waktu_jadwal']);
        });
    }

    /**
     * Reverse the migrations.
     * Tambahkan kembali kolom tanggal_jadwal dan waktu_jadwal
     */
    public function down(): void
    {
        Schema::table('peserta', function (Blueprint $table) {
            $table->string('tanggal_jadwal')->nullable()->after('lokasi');
            $table->string('waktu_jadwal')->nullable()->after('tanggal_jadwal');
        });
    }
};
