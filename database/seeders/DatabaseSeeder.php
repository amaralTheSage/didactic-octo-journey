<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\InfluencerInfo;
use App\Models\Product;
use App\Models\Subcategory;
use App\Models\User;
use App\UserRoles;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Storage::disk('public')->makeDirectory('avatars');

        $createAvatar = function () {
            $filename = Str::random(20) . '.jpg';

            $url = 'https://picsum.photos/300/300?random=' . Str::random(10);

            $imageData = file_get_contents($url);

            Storage::disk('public')->put("avatars/{$filename}", $imageData);

            return "avatars/{$filename}";
        };

        // -------------------------------------------------------
        // ADMIN
        // -------------------------------------------------------
        User::create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'bio' => 'admin role during development',
            'avatar' => $createAvatar(),
            'password' => Hash::make('senha123'),
            'role' => UserRoles::Admin,
            'email_verified_at' => now(),
        ]);

        // -------------------------------------------------------
        // COMPANIES
        // -------------------------------------------------------
        $companies = collect();
        foreach (range(1, 10) as $i) {
            $companies->push(
                User::create([
                    'name' => fake()->company(),
                    'email' => "company$i@gmail.com",
                    'bio' => fake()->paragraph(),
                    'avatar' => $createAvatar(),
                    'password' => Hash::make('senha123'),
                    'role' => 'company',
                    'email_verified_at' => now(),
                ])
            );
        }

        // -------------------------------------------------------
        // AGENCIES
        // -------------------------------------------------------
        $agencies = collect();
        foreach (range(1, 10) as $i) {
            $agencies->push(
                User::create([
                    'name' => fake()->company(),
                    'email' => "agency$i@gmail.com",
                    'bio' => fake()->paragraph(),
                    'avatar' => $createAvatar(),
                    'password' => Hash::make('senha123'),
                    'role' => 'agency',
                    'email_verified_at' => now(),
                ])
            );
        }

        // -------------------------------------------------------
        // INFLUENCERS
        // -------------------------------------------------------
        $influencers = collect();
        foreach (range(1, 30) as $i) {
            $user = User::create([
                'name' => fake()->name(),
                'email' => "influencer$i@gmail.com",
                'bio' => fake()->paragraph(),
                'avatar' => $createAvatar(),
                'password' => Hash::make('senha123'),
                'role' => 'influencer',
                'email_verified_at' => now(),
            ]);

            InfluencerInfo::create([
                'user_id' => $user->id,
                'agency_id' => $agencies->random()->id,
                'instagram' => fake()->userName(),
                'instagram_followers' => rand(1000, 100000),
                'association_status' => collect(['approved', 'pending'])->random(),
            ]);
            $influencers->push($user);
        }

        // -------------------------------------------------------
        // Products
        // -------------------------------------------------------
        $companies->each(function ($company) {
            foreach (range(1, 5) as $i) {
                Product::create([
                    'name' => fake()->colorName() . ' ' . fake()->streetName,
                    'description' => "Description for product {$i} from {$company->name}.",
                    'price' => rand(10, 500),
                    'company_id' => $company->id,
                ]);
            }
        });

      

        // -------------------------------------------------------
        // MANUAL TEST USERS
        // -------------------------------------------------------
        User::create([
            'name' => '1 Empresa',
            'email' => 'empresa@gmail.com',
            'avatar' => $createAvatar(),
            'bio' => fake()->paragraph(),
            'password' => Hash::make('senha123'),
            'role' => 'company',
            'email_verified_at' => now(),
        ]);

        $agenciaA = User::create([
            'name' => '1 Agência',
            'email' => 'agencia@gmail.com',
            'avatar' => $createAvatar(),
            'bio' => fake()->paragraph(),
            'password' => Hash::make('senha123'),
            'role' => 'agency',
            'email_verified_at' => now(),
        ]);

        $influencerA1 = User::create([
            'name' => '1 Influencer',
            'email' => 'influencer@gmail.com',
            'avatar' => $createAvatar(),
            'bio' => fake()->paragraph(),
            'password' => Hash::make('senha123'),
            'role' => 'influencer',
            'email_verified_at' => now(),
        ]);
        InfluencerInfo::create([
            'user_id' => $influencerA1->id,
            'agency_id' => $agenciaA->id,
            'association_status' => 'approved',
        ]);

        $influencerA2 = User::create([
            'name' => '1 Influencer',
            'email' => 'influencera2@gmail.com',
            'avatar' => $createAvatar(),
            'bio' => fake()->paragraph(),
            'password' => Hash::make('senha123'),
            'role' => 'influencer',
            'email_verified_at' => now(),
        ]);
        InfluencerInfo::create([
            'user_id' => $influencerA2->id,
            'agency_id' => $agenciaA->id,
            'association_status' => 'pending',
        ]);
    }
}
