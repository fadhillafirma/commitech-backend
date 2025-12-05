<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * INSTAGRAM-STYLE SESSION:
     * Add created_at field untuk track kapan session dibuat
     * Session expire 7 days dari created_at, bukan last_activity
     */
    public function up()
    {
        Schema::table('sessions', function (Blueprint $table) {
            // Add created_at field (timestamp)
            // Default to current last_activity untuk existing sessions
            $table->integer('created_at')->nullable()->after('last_activity');
        });
        
        // Update existing sessions: set created_at = last_activity
        DB::table('sessions')->update([
            'created_at' => DB::raw('last_activity')
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('sessions', function (Blueprint $table) {
            $table->dropColumn('created_at');
        });
    }
};
