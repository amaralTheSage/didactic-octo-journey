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
        Schema::table('proposal_user', function (Blueprint $table) {
            $table->decimal('reels_price', 11, 2)->nullable();
            $table->decimal('stories_price', 11, 2)->nullable();
            $table->decimal('carrousel_price', 11, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proposal_user', function (Blueprint $table) {
            //
        });
    }
};
