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
        Schema::table('artist_requests', function (Blueprint $table) {
            //
            $table->string('division')->after('social_links')->default('rap');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('artist_requests', function (Blueprint $table) {
            //
            if (Schema::hasColumn('artist_requests', 'division')) {
                $table->dropColumn('division');
            }
        });
    }
};
