<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  
    public function up(): void
    {
        Schema::table('peserta', function (Blueprint $table) {
            $table->enum('status_wawancara', ['pending', 'diterima', 'ditolak'])
                ->default('pending')
                ->after('status_seleksi_berkas')
                ->comment('Status seleksi wawancara: pending, diterima, atau ditolak');
        });
    }

   
    public function down(): void
    {
        Schema::table('peserta', function (Blueprint $table) {
            $table->dropColumn('status_wawancara');
        });
    }
};

