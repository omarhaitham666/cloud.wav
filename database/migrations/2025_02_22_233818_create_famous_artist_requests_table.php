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
        Schema::create('famous_artist_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('famous_profile_image');
            $table->string('famous_name');
            $table->string('famous_email');
            $table->string('famous_number');
            $table->string('famous_whatsapp_number');
            $table->string('famous_details');
            $table->string('famous_social_links');
            $table->string('famous_division');
            $table->enum('status',['pending','approved','rejected'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('famous_artist_requests');
    }
};
