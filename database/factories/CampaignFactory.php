<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Campaign>
 */
class CampaignFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph,
            'product_id' => Product::factory(),
            'company_id' => User::factory()->create(['role' => UserRole::COMPANY]),
            'budget' => 10000,
            'agency_cut' => 30,
            'campaign_status' => 'open',

            'n_reels' => 0,
            'n_carrousels' => 0,
            'n_stories' => 0,

            'n_influencers' => 0,
            'duration' => 30,

            'location' => null,
            'validated_at' => null,
        ];
    }
}
