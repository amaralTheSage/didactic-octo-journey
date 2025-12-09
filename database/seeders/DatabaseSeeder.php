<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Filament\Support\Colors\Color;
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
        // COMPANIES
        // -------------------------------------------------------
        $companies = collect();
        foreach (range(1, 10) as $i) {
            $companies->push(
                User::create([
                    'name' => fake()->company(),
                    'email' => "company$i@gmail.com",
                    'avatar' => $createAvatar("Company $i"),
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
                    'avatar' => $createAvatar("Agency $i"),
                    'password' => Hash::make('senha123'),
                    'role' => 'agency',
                    'email_verified_at' => now(),
                ])
            );
        }

        // -------------------------------------------------------
        // INFLUENCERS
        // -------------------------------------------------------
        foreach (range(1, 30) as $i) {
            User::create([
                'name' => fake()->name(),
                'email' => "influencer$i@gmail.com",
                'avatar' => $createAvatar("Influencer $i"),
                'password' => Hash::make('senha123'),
                'role' => 'influencer',
                'agency_id' => $agencies->random()->id,
                'email_verified_at' => now(),
            ]);
        }


        // -------------------------------------------------------
        // Products
        // -------------------------------------------------------
        $companies->each(function ($company) {
            foreach (range(1, 5) as $i) {
                Product::create([
                    'name' => fake()->colorName() . " " . fake()->streetName,
                    'description' => "Description for product {$i} from {$company->name}.",
                    'price' => rand(10, 500),
                    'company_id' => $company->id,
                ]);
            }
        });

        // -------------------------------------------------------
        // -------------------------------------------------------
        User::create([
            'name' => 'Empresa A',
            'email' => 'empresa@gmail.com',
            'avatar' => $createAvatar("Empresa A"),
            'password' => Hash::make('senha123'),
            'role' => 'company',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Agência A',
            'email' => 'agencia@gmail.com',
            'avatar' => $createAvatar("Agência A"),
            'password' => Hash::make('senha123'),
            'role' => 'agency',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Influencer A1',
            'email' => 'influencer@gmail.com',
            'avatar' => $createAvatar("Influencer A1"),
            'password' => Hash::make('senha123'),
            'role' => 'influencer',
            'agency_id' => 2,
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Influencer A2',
            'email' => 'influencera2@gmail.com',
            'avatar' => $createAvatar("Influencer A2"),
            'password' => Hash::make('senha123'),
            'role' => 'influencer',
            'agency_id' => 2,
            'email_verified_at' => now(),
        ]);
    }
}
