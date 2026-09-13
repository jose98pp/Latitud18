<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Política',
            'Deportes',
            'Economía',
            'Cultura',
            'Tecnología',
            'Salud',
            'Educación',
            'Internacional',
            'Entretenimiento',
            'Sociedad'
        ];

        foreach ($categories as $categoryName) {
            \App\Models\Category::firstOrCreate([
                'name' => $categoryName
            ]);
        }

        $this->command->info('✅ Categorías creadas exitosamente');
    }
}
