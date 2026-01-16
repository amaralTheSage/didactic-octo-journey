<?php

namespace Database\Seeders;

use App\Enums\UserRoles;
use App\Models\AttributeValue;
use App\Models\CampaignAnnouncement;
use App\Models\Category;
use App\Models\InfluencerInfo;
use App\Models\Product;
use App\Models\Subcategory;
use App\Models\User;
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
            $filename = Str::random(10) . '.jpg';
            $url = 'https://picsum.photos/300/300?random=' . Str::random(10);
            $imageData = file_get_contents($url);
            Storage::disk('public')->put("avatars/{$filename}", $imageData);

            return "avatars/{$filename}";
        };

        $this->call([
            // ChatSeeder::class,
            AttributeSeeder::class,
        ]);

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
        foreach (range(1, 5) as $i) {
            $companies->push(
                User::create([
                    'name' => fake()->company(),
                    'email' => "company{$i}@gmail.com",
                    'bio' => fake()->paragraph(),
                    'avatar' => $createAvatar(),
                    'password' => Hash::make('senha123'),
                    'role' => UserRoles::Company,
                    'email_verified_at' => now(),
                ])
            );
        }

        // -------------------------------------------------------
        // CURATORS
        // -------------------------------------------------------
        $curators = collect();
        foreach (range(1, 5) as $i) {
            $curators->push(
                User::create([
                    'name' => fake()->company(),
                    'email' => "curator{$i}@gmail.com",
                    'bio' => fake()->paragraph(),
                    'avatar' => $createAvatar(),
                    'password' => Hash::make('senha123'),
                    'role' => UserRoles::Curator,
                    'email_verified_at' => now(),
                ])
            );
        }

        // -------------------------------------------------------
        // AGENCIES
        // -------------------------------------------------------
        $agencies = collect();
        foreach (range(1, 5) as $i) {
            $agencies->push(
                User::create([
                    'name' => fake()->company(),
                    'email' => "agency{$i}@gmail.com",
                    'bio' => fake()->paragraph(),
                    'avatar' => $createAvatar(),
                    'password' => Hash::make('senha123'),
                    'role' => UserRoles::Agency,
                    'email_verified_at' => now(),
                ])
            );
        }

        // -------------------------------------------------------
        // Categories + Subcategories
        // -------------------------------------------------------
        $categoriesData = [
            'Beleza e Moda' => [
                'Maquiagem',
                'Cuidados com a Pele',
                'Cabelos',
                'Tendências de Moda',
                'Estilo de Vida Fitness',
            ],
            'Tecnologia e Jogos' => [
                'Reviews de Gadgets',
                'Mobile Gaming',
                'Consoles e PC Gaming',
                'Desenvolvimento de Software',
                'Inteligência Artificial',
            ],
            'Viagens e Turismo' => [
                'Viagens Nacionais',
                'Intercâmbios',
                'Gastronomia de Viagem',
                'Mochilão e Aventura',
                'Dicas de Hospedagem',
            ],
            'Alimentação e Culinária' => [
                'Receitas Veganas',
                'Culinária Internacional',
                'Bebidas e Coquetéis',
                'Dieta e Nutrição',
                'Restaurantes e Críticas',
            ],
            'Finanças e Negócios' => [
                'Investimentos',
                'Empreendedorismo',
                'Educação Financeira',
                'Marketing Digital',
                'Carreira e Produtividade',
            ],
            'Entretenimento e Cultura' => [
                'Críticas de Cinema e Séries',
                'Música e Shows',
                'Livros e Literatura',
                'Arte e Design',
                'Comédia e Humor',
            ],
        ];

        $categories = collect();
        foreach ($categoriesData as $categoryName => $subcategories) {
            $category = Category::create(['title' => $categoryName]);

            foreach ($subcategories as $subcategoryName) {
                Subcategory::create([
                    'category_id' => $category->id,
                    'title' => $subcategoryName,
                ]);
            }

            $categories->push($category);
        }

        // -------------------------------------------------------
        // INFLUENCERS
        // -------------------------------------------------------
        $allSubcategories = Subcategory::all();

        $influencers = collect();
        foreach (range(1, 20) as $i) {
            $user = User::create([
                'name' => fake()->name(),
                'email' => "influencer{$i}@gmail.com",
                'bio' => fake()->paragraph(),
                'avatar' => $createAvatar(),
                'password' => Hash::make('senha123'),
                'role' => UserRoles::Influencer,
                'email_verified_at' => now(),
            ]);

            // attach random subcategories
            $user->subcategories()->attach(
                $allSubcategories->random(rand(1, 3))->pluck('id')
            );

            $attributeValues = AttributeValue::all();

            $user->attribute_values()->attach(
                $attributeValues->random(rand(3, 6))->pluck('id')
                    ->toArray()
            );

            // influencer info
            InfluencerInfo::create([
                'user_id' => $user->id,
                'agency_id' => $agencies->random()->id,

                'location' => 'BR|RS|Pelotas',

                'instagram' => fake()->userName(),
                'instagram_followers' => rand(1000, 100000),
                'association_status' => collect(['approved', 'pending'])->random(),

                'reels_price' => rand(500, 5000),
                'stories_price' => rand(200, 2000),
                'carrousel_price' => rand(300, 3000),
                'commission_cut' => rand(10, 50),
            ]);

            $influencers->push($user);
        }

        // -------------------------------------------------------
        // Products (per company)
        // -------------------------------------------------------
        $companies->each(function ($company) use ($categories) {
            foreach (range(1, 3) as $i) {
                Product::create([
                    'name' => fake()->colorName() . ' ' . fake()->streetName,
                    'description' => "Description for product {$i} from {$company->name}.",
                    'price' => rand(10, 500),
                    'category_id' => $categories->random()->id,
                    'company_id' => $company->id,
                ]);
            }
        });

        // -------------------------------------------------------
        // MANUAL TEST USERS
        // -------------------------------------------------------
        $testCompany = User::create([
            'name' => 'Aa Empresa',
            'email' => 'empresa@gmail.com',
            'avatar' => $createAvatar(),
            'bio' => fake()->paragraph(),
            'password' => Hash::make('senha123'),
            'role' => UserRoles::Company,
            'email_verified_at' => now(),
        ]);

        $curadoria = User::create([
            'name' => 'Aa Curadoria',
            'email' => 'curadoria@gmail.com',
            'avatar' => $createAvatar(),
            'bio' => fake()->paragraph(),
            'password' => Hash::make('senha123'),
            'role' => UserRoles::Curator,
            'email_verified_at' => now(),
        ]);

        $agenciaA = User::create([
            'name' => 'Aa Agência',
            'email' => 'agencia@gmail.com',
            'avatar' => $createAvatar(),
            'bio' => fake()->paragraph(),
            'password' => Hash::make('senha123'),
            'role' => UserRoles::Agency,
            'email_verified_at' => now(),
        ]);

        $gabriel = User::create([
            'name' => 'Gabriel Amaral',
            'email' => 'gabriel@gmail.com',
            'avatar' => $createAvatar(),
            'bio' => fake()->paragraph(),
            'password' => Hash::make('senha123'),
            'role' => UserRoles::Influencer,
            'email_verified_at' => now(),
        ]);

        $attributeValues = AttributeValue::all();

        $gabriel->attribute_values()->attach(
            $attributeValues->random(rand(5, 10))->pluck('id')
                ->toArray()
        );

        $gabriel->subcategories()->attach(
            Subcategory::all()->random(rand(1, 3))->pluck('id')
        );

        InfluencerInfo::create([
            'user_id' => $gabriel->id,
            'agency_id' => $agenciaA->id,
            'association_status' => 'approved',
            'location' => 'BR|RS|Pelotas',
            'instagram' => fake()->word() . '_ig',
            'twitter' => fake()->word() . '_tw',
            'facebook' => fake()->word() . '_fb',
            'youtube' => fake()->word() . '_yt',
            'tiktok' => fake()->word() . '_tt',
            'instagram_followers' => rand(5000, 50000),
            'twitter_followers' => rand(5000, 50000),
            'facebook_followers' => rand(5000, 50000),
            'youtube_followers' => rand(5000, 50000),
            'tiktok_followers' => rand(5000, 50000),
            'reels_price' => rand(500, 5000),
            'stories_price' => rand(200, 2000),
            'carrousel_price' => rand(300, 3000),
            'commission_cut' => rand(10, 50),

        ]);

        $influencerA1 = User::create([
            'name' => 'Aa Influenciador',
            'email' => 'influencer@gmail.com',
            'avatar' => $createAvatar(),
            'bio' => fake()->paragraph(),
            'password' => Hash::make('senha123'),
            'role' => UserRoles::Influencer,
            'email_verified_at' => now(),
        ]);

        $influencerA1->subcategories()->attach(
            Subcategory::all()->random(rand(1, 3))->pluck('id')
        );

        InfluencerInfo::create([
            'user_id' => $influencerA1->id,
            'agency_id' => $agenciaA->id,
            'association_status' => 'approved',
            'location' => 'MX||',
            'reels_price' => rand(500, 5000),
            'stories_price' => rand(200, 2000),
            'carrousel_price' => rand(300, 3000),
            'commission_cut' => rand(10, 50),

        ]);

        $influencerA2 = User::create([
            'name' => 'Ab Influenciador',
            'email' => 'influencera2@gmail.com',
            'avatar' => $createAvatar(),
            'bio' => fake()->paragraph(),
            'password' => Hash::make('senha123'),
            'role' => UserRoles::Influencer,
            'email_verified_at' => now(),
        ]);

        $influencerA2->subcategories()->attach(
            Subcategory::all()->random(rand(1, 3))->pluck('id')
        );

        InfluencerInfo::create([
            'user_id' => $influencerA2->id,
            'agency_id' => $agenciaA->id,
            'association_status' => 'pending',
            'location' => 'AR||',
            'reels_price' => rand(500, 5000),
            'stories_price' => rand(200, 2000),
            'carrousel_price' => rand(300, 3000),
            'commission_cut' => rand(10, 50),
        ]);

        // -------------------------------------------------------
        // Products for Test Company
        // -------------------------------------------------------
        $testProducts = collect();
        foreach (range(1, 5) as $i) {
            $testProducts->push(
                Product::create([
                    'name' => fake()->colorName() . ' ' . fake()->streetName,
                    'description' => "Test product {$i} from 1 Empresa.",
                    'price' => rand(50, 1000),
                    'company_id' => $testCompany->id,
                    'category_id' => $categories->random()->id,
                ])
            );
        }

        // -------------------------------------------------------
        // Campaign Announcements for Test Company
        // -------------------------------------------------------
        $campaignNames = [
            'Campanha de Verão 2024',
            'Lançamento Exclusivo',
            'Black Friday Especial',
            'Campanha de Natal',
            'Promoção de Aniversário',
            'Campanha de Volta às Aulas',
            'Edição Limitada',
            'Mega Promoção',
        ];

        foreach ($campaignNames as $index => $campaignName) {
            $campaign = CampaignAnnouncement::create([
                'name' => $campaignName,
                'description' => fake()->paragraph(3),
                'agency_cut' => rand(10, 30),
                'budget' => rand(5000, 50000),
                'product_id' => $testProducts->random()->id,
                'company_id' => $testCompany->id,
                'category_id' => $categories->random()->id,
                'n_reels' => rand(1, 5),
                'n_stories' => rand(1, 5),
                'n_carrousels' => rand(1, 5),
            ]);

            $attributeValues = AttributeValue::all();

            $campaign->attribute_values()->attach(
                $attributeValues->random(rand(3, 8))->pluck('id')
                    ->toArray()
            );


            $campaign->subcategories()->attach(
                Subcategory::all()->random(rand(1, 3))->pluck('id')
            );
        }

        // -------------------------------------------------------
        // Proposals
        // -------------------------------------------------------
        $campaignAnnouncements = CampaignAnnouncement::all();

        $campaignAnnouncements->each(function ($announcement) use ($agencies, $influencers) {
            $numberOfProposals = rand(2, 2);
            $selectedAgencies = $agencies->random(min($numberOfProposals, $agencies->count()));

            $selectedAgencies->each(function ($agency) use ($announcement, $influencers) {
                $agencyInfluencers = $influencers->filter(function ($influencer) use ($agency) {
                    return $influencer->influencer_info->agency_id === $agency->id
                        && $influencer->influencer_info->association_status === 'approved';
                });

                $proposal = \App\Models\Proposal::create([
                    'message' => fake()->paragraph(2),
                    'proposed_agency_cut' => rand(5, 30),
                    'campaign_announcement_id' => $announcement->id,
                    'agency_id' => $agency->id,
                    'agency_approval' => collect(['pending', 'approved', 'rejected'])->random(),
                    'company_approval' => collect(['pending', 'approved', 'rejected'])->random(),
                    'status' => collect(['draft', 'approved', 'cancelled', 'finished'])->random(),
                ]);

                if ($agencyInfluencers->isNotEmpty()) {
                    $attachPayload = $agencyInfluencers
                        ->random(rand(1, min(3, $agencyInfluencers->count())))
                        ->mapWithKeys(function ($inf) {
                            $info = $inf->influencer_info;

                            return [
                                $inf->id => [
                                    'reels_price' => $info->reels_price ?? null,
                                    'stories_price' => $info->stories_price ?? null,
                                    'carrousel_price' => $info->carrousel_price ?? null,
                                    'influencer_approval' => 'pending',
                                ],
                            ];
                        })->all();

                    $proposal->influencers()->attach($attachPayload);
                }
            });
        });

        $this->call([
            // ChatSeeder::class,
            AttributeSeeder::class,
        ]);
    }
}
