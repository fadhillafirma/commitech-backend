<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->string('device_name_custom')->nullable(); // Rename to avoid conflict if exists
            $table->string('device_type')->nullable();
            $table->string('device_id')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('location')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->dropColumn([
                'device_name_custom', 
                'device_type', 
                'device_id', 
                'ip_address', 
                'user_agent', 
                'location'
            ]);
        });
    }
};
