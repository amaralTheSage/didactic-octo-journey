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
        Schema::table('campaign_announcements', function (Blueprint $table) {
            $table->timestamp('verified_at')->nullable()->after('budget');
            $table->string('payment_id')->nullable()->after('verified_at');
            $table->enum('payment_status', ['pending', 'paid', 'expired', 'cancelled'])->default('pending')->after('payment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaign_announcements', function (Blueprint $table) {
            $table->dropColumn(['verified_at', 'payment_id', 'payment_status']);
        });
    }
};
