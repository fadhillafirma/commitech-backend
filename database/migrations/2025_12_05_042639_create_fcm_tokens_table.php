<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('fcm_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('fcm_token', 255);
            $table->string('device_type', 50)->default('android');
            $table->string('device_name', 100)->nullable();
            $table->timestamps();

 
            $table->unique(['user_id', 'fcm_token']);
            
         
            $table->index('user_id');
        });
    }

  
    public function down(): void
    {
        Schema::dropIfExists('fcm_tokens');
    }
};
