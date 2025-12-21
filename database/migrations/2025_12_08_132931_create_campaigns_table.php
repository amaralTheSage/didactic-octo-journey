<?php

use App\CampaignStatus;
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

            $table->decimal('budget', 14, 2);
            $table->decimal('agency_cut', 5, 2);

            $table->foreignId('product_id')->constrained();
            $table->foreignId('influencer_id')->nullable()->constrained('users');
            $table->foreignId('company_id')->constrained('users');
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('agency_id')->nullable()->constrained('users');

            $table->enum('status_agency', [CampaignStatus::PENDING_APPROVAL, CampaignStatus::APPROVED, CampaignStatus::FINISHED, CampaignStatus::REJECTED])->default('pending_approval');

            $table->enum('status_influencer', ['pending_approval', 'approved', 'finished', 'rejected'])->default('pending_approval');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
