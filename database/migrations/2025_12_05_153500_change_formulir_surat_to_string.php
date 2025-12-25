<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   
    public function up(): void
    {
        Schema::table('peserta', function (Blueprint $table) {
         
            $table->dropColumn(['formulir_pendaftaran', 'surat_komitmen']);
        });
        
     
        Schema::table('peserta', function (Blueprint $table) {
            $table->text('formulir_pendaftaran')->nullable()->after('krs_terakhir');
            $table->text('surat_komitmen')->nullable()->after('formulir_pendaftaran');
        });
    }


    public function down(): void
    {
        Schema::table('peserta', function (Blueprint $table) {
        
            $table->dropColumn(['formulir_pendaftaran', 'surat_komitmen']);
        });
        
      
        Schema::table('peserta', function (Blueprint $table) {
            $table->boolean('formulir_pendaftaran')->default(false)->after('krs_terakhir');
            $table->boolean('surat_komitmen')->default(false)->after('formulir_pendaftaran');
        });
    }
};

