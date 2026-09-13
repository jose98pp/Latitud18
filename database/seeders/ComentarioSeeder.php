<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Noticia;
use App\Models\Comentario;

class ComentarioSeeder extends Seeder
{
    public function run(): void
    {
        $noticias = Noticia::where('publicada', true)->take(3)->get();

        $comentariosData = [
            [
                'nombre' => 'Carlos Mendoza',
                'email' => 'carlos.mendoza@gmail.com',
                'contenido' => 'Excelente reporte e investigación. Muy importante mantener informado al país con datos verídicos y sin sensacionalismo.',
                'aprobado' => true,
                'ip_address' => '127.0.0.1',
            ],
            [
                'nombre' => 'Dra. Valeria Salinas',
                'email' => 'v.salinas@salud.bo',
                'contenido' => 'Un análisis muy acertado de la coyuntura regional. Felicidades al equipo de redacción de Latitud 18 por la rigurosidad.',
                'aprobado' => true,
                'ip_address' => '127.0.0.1',
            ],
            [
                'nombre' => 'Gonzalo Arnez',
                'email' => 'garnez@cainco.org.bo',
                'contenido' => 'Comentario pendiente de revisión por el moderador.',
                'aprobado' => false,
                'ip_address' => '127.0.0.1',
            ]
        ];

        foreach ($noticias as $i => $noticia) {
            if (isset($comentariosData[$i])) {
                Comentario::create(array_merge($comentariosData[$i], ['noticia_id' => $noticia->id]));
            }
        }
    }
}
