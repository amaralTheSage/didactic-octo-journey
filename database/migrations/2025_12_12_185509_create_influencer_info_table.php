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

            $table->string('instagram');
            $table->string('twitter');
            $table->string('facebook');
            $table->string('youtube');
            $table->string('tiktok');

            $table->integer('instagram_followers');
            $table->integer('twitter_followers');
            $table->integer('facebook_followers');
            $table->integer('youtube_followers');
            $table->integer('tiktok_followers');

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('agency_id')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('association_status', ['pending', 'approved', 'rejected'])->default('pending');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('influencer_info', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['agency_id']);

            $table->dropColumn([
                'instagram',
                'twitter',
                'facebook',
                'youtube',
                'tiktok',
                'instagram_followers',
                'twitter_followers',
                'facebook_followers',
                'youtube_followers',
                'tiktok_followers',
                'user_id',
                'agency_id',
                'association_status',
            ]);
        });
    }
};
