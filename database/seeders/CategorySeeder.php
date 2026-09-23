<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * The main categories (Prosa/Poesía) and their child categories, with
     * real names and descriptions rather than random Faker data.
     *
     * @var array<string, array{description: string, children: array<string, string>}>
     */
    private const TAXONOMY = [
        'Prosa' => [
            'description' => 'Textos escritos en prosa, sin sujeción a una métrica o rima determinada.',
            'children' => [
                'Cuento' => 'Narración breve de ficción con una trama concisa y un desenlace definido.',
                'Novela' => 'Obra narrativa extensa que desarrolla personajes y tramas complejas.',
                'Microrrelato' => 'Relato extremadamente breve que condensa una historia completa en pocas líneas.',
                'Ensayo' => 'Texto reflexivo que analiza o argumenta sobre un tema desde una perspectiva personal.',
                'Crónica' => 'Relato periodístico o literario que narra hechos reales con estilo narrativo.',
                'Fábula' => 'Relato breve, protagonizado por animales u objetos personificados, que transmite una enseñanza moral.',
                'Relato autobiográfico' => 'Narración en la que el autor cuenta experiencias vividas en primera persona.',
            ],
        ],
        'Poesía' => [
            'description' => 'Textos escritos en verso, organizados en torno al ritmo, la métrica o la rima.',
            'children' => [
                'Soneto' => 'Composición de catorce versos endecasílabos organizados en dos cuartetos y dos tercetos.',
                'Haiku' => 'Poema breve de origen japonés compuesto por tres versos de 5-7-5 sílabas.',
                'Verso libre' => 'Poesía que no sigue métrica ni rima fija, priorizando el ritmo natural del lenguaje.',
                'Oda' => 'Poema de tono elevado que exalta o celebra a una persona, objeto o idea.',
                'Elegía' => 'Composición poética que expresa dolor o lamento, habitualmente ante una pérdida.',
                'Romance' => 'Poema narrativo tradicional compuesto en versos octosílabos con rima asonante en los pares.',
                'Acróstico' => 'Poema en el que las letras iniciales de cada verso forman una palabra o frase.',
            ],
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::TAXONOMY as $mainName => $main) {
            $mainCategory = Category::firstOrCreate(
                ['name' => $mainName],
                ['slug' => Str::slug($mainName), 'description' => $main['description'], 'parent_id' => null]
            );

            foreach ($main['children'] as $childName => $childDescription) {
                Category::firstOrCreate(
                    ['name' => $childName],
                    ['slug' => Str::slug($childName), 'description' => $childDescription, 'parent_id' => $mainCategory->id]
                );
            }
        }
    }
}
