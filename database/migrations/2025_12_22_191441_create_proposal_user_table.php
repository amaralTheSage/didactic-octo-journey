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
        Schema::create('proposal_user', function (Blueprint $table) {
            $table->id();

            $table->foreignId('proposal_id')->constrained()->cascadeOnDelete();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->enum('influencer_approval', ['pending', 'approved', 'rejected'])->default('pending');

            $table->decimal('reels_price', 11, 2)->nullable();
            $table->decimal('stories_price', 11, 2)->nullable();
            $table->decimal('carrousel_price', 11, 2)->nullable();
            $table->decimal('commission_cut', 5, 2)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proposal_user');
    }
};
