<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\PeriodicoPlantilla;
use App\Models\PeriodicoPlantillaEdicion;

class Latitud18ReferenciaSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            foreach (glob(database_path('data/latitud18/*.latitud-template')) as $path) {
                $t=json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
                // No sobrescribir una plantilla que el usuario ya haya editado.
                PeriodicoPlantilla::firstOrCreate(['id'=>$t['id']], [
                    'nombre'=>$t['name'], 'slug'=>$t['id'], 'categoria'=>$t['category'],
                    'descripcion'=>$t['description'], 'preview_color'=>$t['preview_color'],
                    'is_custom'=>true, 'frames'=>$t['frames'], 'configuracion'=>$t['configuracion'],
                ]);
            }
            $e=json_decode(file_get_contents(database_path('data/latitud18/edicion-completa.json')),true,512,JSON_THROW_ON_ERROR);
            PeriodicoPlantillaEdicion::firstOrCreate(['nombre'=>$e['nombre']],['descripcion'=>$e['descripcion'],'mapping'=>$e['mapping']]);
        });
    }
}
