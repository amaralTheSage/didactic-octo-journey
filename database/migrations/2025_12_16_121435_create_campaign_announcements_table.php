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
        Schema::create('campaign_announcements', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('agency_cut')->after('name');

            $table->decimal('budget', 14, 2);

            $table->foreignId('product_id')->constrained();
            $table->foreignId('company_id')->constrained('users');
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();

            $table->string('location')->nullable();

            $table->integer('n_reels')->default(0);
            $table->integer('n_stories')->default(0);
            $table->integer('n_carrousels')->default(0);

            $table->enum('announcement_status', ['open', 'paused', 'finished'])->default('open');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_announcement');
    }
};
