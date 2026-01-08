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
        Schema::create('proposals', function (Blueprint $table) {
            $table->id();

            $table->text('message')->nullable();
            $table->integer('proposed_agency_cut')->nullable()->default('0');

            $table->foreignId('campaign_announcement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('agency_id')->constrained('users')->cascadeOnDelete();

            $table->enum('agency_approval', ['pending', 'approved', 'rejected'])->default('pending');

            $table->enum('company_approval', ['pending', 'approved', 'rejected'])->default('pending');

            $table->enum('status', ['draft', 'approved', 'cancelled', 'finished'])->default('draft');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proposals');
    }
};
