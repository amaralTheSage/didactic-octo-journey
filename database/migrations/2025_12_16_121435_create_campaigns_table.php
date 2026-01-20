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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('agency_cut')->after('name');

            $table->decimal('budget', 14, 2);

            $table->foreignId('product_id')->constrained();
            $table->foreignId('company_id')->constrained('users');
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();

            $table->string('location')->nullable();

            $table->integer('n_reels')->nullable();
            $table->integer('n_stories')->nullable();
            $table->integer('n_carrousels')->nullable();

            $table->integer('n_influencers')->nullable();
            $table->integer('duration')->nullable();

            $table->enum('campaign_status', ['open', 'paused', 'finished'])->default('open');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign');
    }
};
