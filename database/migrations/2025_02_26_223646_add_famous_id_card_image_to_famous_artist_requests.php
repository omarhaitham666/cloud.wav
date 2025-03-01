<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        if (!Schema::hasTable('famous_artist_requests')) {
        Schema::table('famous_artist_requests', function (Blueprint $table) {
            //
            $table->string('famous_id_card_image')->after('famous_profile_image');
        });
    }
    
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('famous_artist_requests', function (Blueprint $table) {
            //
        });
    }
};
