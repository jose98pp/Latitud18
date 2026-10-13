<?php

namespace Database\Seeders;

use App\Models\PeriodicoPlantilla;
use App\Models\PeriodicoPlantillaEdicion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Latitud18EditorialV2Seeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $files = glob(database_path('data/latitud18-v2/*.latitud-template'));
            if (count($files) !== 12) throw new \RuntimeException('El catálogo editorial v2 debe contener 12 páginas.');
            foreach ($files as $file) {
                $t = json_decode(file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
                PeriodicoPlantilla::firstOrCreate(['id' => $t['id']], [
                    'nombre' => $t['name'], 'slug' => $t['id'], 'categoria' => $t['category'],
                    'descripcion' => $t['description'], 'preview_color' => $t['preview_color'],
                    'is_custom' => false, 'frames' => $t['frames'], 'configuracion' => $t['configuracion'],
                ]);
            }
            $mapping = json_decode(file_get_contents(database_path('data/latitud18-v2/edicion-completa.json')), true, 512, JSON_THROW_ON_ERROR);
            PeriodicoPlantillaEdicion::firstOrCreate(['nombre' => $mapping['nombre']], [
                'descripcion' => $mapping['descripcion'], 'mapping' => $mapping['mapping'],
            ]);
        });
    }
}
