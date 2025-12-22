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
        Schema::create('influencer_info', function (Blueprint $table) {
            $table->id();

            $table->string('instagram')->nullable();
            $table->string('twitter')->nullable();
            $table->string('facebook')->nullable();
            $table->string('youtube')->nullable();
            $table->string('tiktok')->nullable();

            $table->integer('instagram_followers')->nullable();
            $table->integer('twitter_followers')->nullable();
            $table->integer('facebook_followers')->nullable();
            $table->integer('youtube_followers')->nullable();
            $table->integer('tiktok_followers')->nullable();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('agency_id')->nullable()->constrained('users')->nullOnDelete();

            $table->decimal('reels_price', 11, 2)->nullable();
            $table->decimal('stories_price', 11, 2)->nullable();
            $table->decimal('carrousel_price', 11, 2)->nullable();

            $table->enum('association_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('influencer_info', function (Blueprint $table) {});
    }
};
