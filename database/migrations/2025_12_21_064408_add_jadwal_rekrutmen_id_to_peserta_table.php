<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('peserta', function (Blueprint $table) {
      
            $table->foreignId('jadwal_rekrutmen_id')
                ->nullable()
                ->after('lokasi')
                ->constrained('jadwal_rekrutmen')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('peserta', function (Blueprint $table) {
            $table->dropForeign(['jadwal_rekrutmen_id']);
            $table->dropColumn('jadwal_rekrutmen_id');
        });
    }
};
