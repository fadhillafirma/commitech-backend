<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal_rekrutmen', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->string('tanggal_mulai');
            $table->string('tanggal_selesai');
            $table->string('waktu_mulai');
            $table->string('waktu_selesai');
            $table->string('pewawancara')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_rekrutmen');
    }
};

