<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peserta', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('nim')->nullable();
            $table->string('email')->nullable();
            $table->string('telepon')->nullable();
            $table->string('pilihan_divisi_1')->nullable();
            $table->string('pilihan_divisi_2')->nullable();
            $table->text('alasan_1')->nullable();
            $table->text('alasan_2')->nullable();
            $table->string('tanggal_jadwal')->nullable();
            $table->string('waktu_jadwal')->nullable();
            $table->string('lokasi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peserta');
    }
};
