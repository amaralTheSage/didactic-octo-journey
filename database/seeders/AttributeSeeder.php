<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Database\Seeder;

class AttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        /*
        |--------------------------------------------------
        | Público-alvo (faixa etária)
        |--------------------------------------------------
        */
        $publicoAlvo = Attribute::create([
            'title' => 'Público-alvo (faixa etária)',
            'multiple_values' => true,
        ]);

        foreach (
            [
                '18-24',
                '25-34',
                '35-44',
                '45+',
                'Outro',
            ] as $item
        ) {
            AttributeValue::create([
                'attribute_id' => $publicoAlvo->id,
                'title' => $item,
                'editable' => false,
            ]);
        }

        /*
        |--------------------------------------------------
        | Etnia do influenciador
        |--------------------------------------------------
        */
        $etnia = Attribute::create([
            'title' => 'Etnia do influenciador',
            'multiple_values' => true,
        ]);

        foreach (
            [
                ['Negro', false],
                ['Branco', false],
                ['Pardo', false],
                ['Outro', true],
            ] as [$title, $editable]
        ) {
            AttributeValue::create([
                'attribute_id' => $etnia->id,
                'title' => $title,
                'editable' => $editable,
            ]);
        }

        /*
        |--------------------------------------------------
        | Opção sexual
        |--------------------------------------------------
        */
        $opcaoSexual = Attribute::create([
            'title' => 'Opção sexual',
            'multiple_values' => true,
        ]);

        foreach (
            [
                ['Heterossexual', false],
                ['Homossexual', false],
                ['Bissexual', false],
                ['Outro', true],
                ['Prefiro não informar', false],
            ] as [$title, $editable]
        ) {
            AttributeValue::create([
                'attribute_id' => $opcaoSexual->id,
                'title' => $title,
                'editable' => $editable,
            ]);
        }

        /*
        |--------------------------------------------------
        | Quantidade de influenciadores
        |--------------------------------------------------
        */
        $quantidade = Attribute::create([
            'title' => 'Quantidade de influenciadores',
            'multiple_values' => false,
        ]);

        foreach (
            [
                '30',
                '50',
                '100',
                '200',
                'Outro',
            ] as $item
        ) {
            AttributeValue::create([
                'attribute_id' => $quantidade->id,
                'title' => $item,
                'editable' => false,
            ]);
        }

        /*
        |--------------------------------------------------
        | Duração da campanha
        |--------------------------------------------------
        */
        $duracao = Attribute::create([
            'title' => 'Duração da campanha',
            'multiple_values' => false,
        ]);

        foreach (
            [
                '7 dias',
                '15 dias',
                '30 dias',
                'Outro',
            ] as $item
        ) {
            AttributeValue::create([
                'attribute_id' => $duracao->id,
                'title' => $item,
                'editable' => $item === 'Outro',
            ]);
        }

        /*
        |--------------------------------------------------
        | Acessibilidade
        |--------------------------------------------------
        */
        $acessibilidade = Attribute::create([
            'title' => 'Acessibilidade',
            'multiple_values' => true,
        ]);

        foreach (
            [
                'Fisíca',
                'Intelectual',
                'Auditiva',
                'Outro',
            ] as $item
        ) {
            AttributeValue::create([
                'attribute_id' => $acessibilidade->id,
                'title' => $item,
                'editable' => $item === 'Outro',
            ]);
        }
    }
}
