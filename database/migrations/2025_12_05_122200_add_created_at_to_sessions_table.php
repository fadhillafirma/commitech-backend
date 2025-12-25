<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  
    public function up()
    {
        Schema::table('sessions', function (Blueprint $table) {
         
            $table->integer('created_at')->nullable()->after('last_activity');
        });
        
    
        DB::table('sessions')->update([
            'created_at' => DB::raw('last_activity')
        ]);
    }


    public function down()
    {
        Schema::table('sessions', function (Blueprint $table) {
            $table->dropColumn('created_at');
        });
    }
};
