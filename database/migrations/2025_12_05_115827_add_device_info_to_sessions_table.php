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
    Schema::table('sessions', function (Blueprint $table) {
        $table->string('device_name')->nullable()->after('user_id');
        $table->string('device_type', 50)->nullable()->after('device_name');
        $table->string('device_id')->nullable()->after('device_type');
        $table->string('location')->nullable()->after('user_agent');
    });
}

public function down()
{
    Schema::table('sessions', function (Blueprint $table) {
        $table->dropColumn(['device_name', 'device_type', 'device_id', 'location']);
    });
}
};
