<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // -------------------------------------------------------
        // Categoriesss
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

        foreach ($categoriesData as $categoryName => $subcategories) {
            $category = Category::create(['title' => $categoryName]);

            foreach ($subcategories as $subcategoryName) {
                Subcategory::create([
                    'category_id' => $category->id,
                    'title' => $subcategoryName,
                ]);
            }
        }
    }
}
