<?php

namespace Database\Factories;

use App\Models\Campaign;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Proposal>
 */
class ProposalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'campaign_id' => Campaign::factory(),
            'agency_id' => User::factory()->create(['role' => 'agency']),
            'message' => $this->faker->sentence(),
            'proposed_agency_cut' => 30,

            // approvals / status
            'agency_approval' => 'pending',
            'company_approval' => 'pending',
            'status' => 'draft',

            // deliverables (will be overridden by model boot logic)
            'n_reels' => 0,
            'n_stories' => 0,
            'n_carrousels' => 0,
        ];
    }
}
