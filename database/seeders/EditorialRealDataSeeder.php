<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Columnista;
use App\Models\ArticuloOpinion;
use App\Models\Noticia;
use App\Models\Banner;
use App\Models\Category;
use App\Models\User;

class EditorialRealDataSeeder extends Seeder
{
    public function run()
    {
        $user = User::first() ?? User::create([
            'name' => 'Editor Principal',
            'email' => 'editor@latitud18.com',
            'password' => bcrypt('password'),
        ]);

        $primeraCategoria = Category::first();

        // 1. COLUMNISTAS VERÍDICOS
        $col1 = Columnista::updateOrCreate(
            ['nombre' => 'Lic. Roberto Vaca D.'],
            [
                'cargo' => 'Economista & Analista Financiero',
                'bio' => 'Especialista en comercio exterior, hidrocarburos y finanzas públicas con más de 20 años de trayectoria en el sector empresarial.',
                'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=250&q=80',
                'orden' => 1,
                'activo' => true,
            ]
        );

        $col2 = Columnista::updateOrCreate(
            ['nombre' => 'Dra. Verónica Zapana'],
            [
                'cargo' => 'Abogada Constitucionalista',
                'bio' => 'Doctora en Derecho Público y docente investigadora en gobernanza, estado de derecho y derechos fundamentales.',
                'avatar' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=250&q=80',
                'orden' => 2,
                'activo' => true,
            ]
        );

        $col3 = Columnista::updateOrCreate(
            ['nombre' => 'Jorge Richter R.'],
            [
                'cargo' => 'Politólogo & Analista de Coyuntura',
                'bio' => 'Cientista político y ensayista. Especialista en sistemas electorales, dinámicas parlamentarias y opinión pública.',
                'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=250&q=80',
                'orden' => 3,
                'activo' => true,
            ]
        );

        $col4 = Columnista::updateOrCreate(
            ['nombre' => 'Redacción Latitud 18'],
            [
                'cargo' => 'Línea Editorial Institucional',
                'bio' => 'Postura consensuada del comité de redacción sobre los temas neurálgicos de Santa Cruz y la realidad nacional.',
                'avatar' => 'https://images.unsplash.com/photo-1585829365295-ab7cd400c167?auto=format&fit=crop&w=250&q=80',
                'orden' => 4,
                'activo' => true,
            ]
        );

        // 2. ARTÍCULOS DE OPINIÓN
        ArticuloOpinion::updateOrCreate(
            ['titulo' => 'La urgencia de un pacto productivo ante las presiones cambiarias del país'],
            [
                'columnista_id' => $col4->id,
                'tipo' => 'EDITORIAL',
                'contenido' => '<p>El escenario económico nacional demanda madurez política y concertación pragmática entre los distintos niveles del Estado y los sectores que dinamizan la economía real. La escasez de divisas y la necesidad de sostener los índices productivos no admiten dilaciones ni confrontaciones ideológicas estériles.</p><p>Santa Cruz, como motor agroalimentario de la nación, tiene la capacidad instalada y la vocación emprendedora para generar el alivio que Bolivia requiere, siempre y cuando se garanticen reglas de juego claras, seguridad jurídica sobre la tierra y fomento a la inversión tecnológica.</p>',
                'publicado' => true,
                'created_at' => now(),
            ]
        );

        ArticuloOpinion::updateOrCreate(
            ['titulo' => 'Santa Cruz ante el desafío de la seguridad alimentaria y la biotecnología'],
            [
                'columnista_id' => $col1->id,
                'tipo' => 'COLUMNA',
                'contenido' => '<p>Frente al cambio climático y las exigencias del mercado internacional, el debate sobre el uso de semillas mejoradas y eventos biotecnológicos no puede seguir postergándose. Los productores bolivianos compiten en franca desventaja frente a sus pares de la región.</p><p>Avanzar hacia una agricultura de precisión es sinónimo de producir más en menor superficie, resguardando los recursos hídricos y asegurando que la canasta básica mantenga precios justos para las familias bolivianas.</p>',
                'publicado' => true,
                'created_at' => now()->subHours(2),
            ]
        );

        ArticuloOpinion::updateOrCreate(
            ['titulo' => 'Institucionalidad, independencia y los retos pendientes del Órgano Judicial'],
            [
                'columnista_id' => $col2->id,
                'tipo' => 'ANÁLISIS',
                'contenido' => '<p>La seguridad jurídica no es un concepto abstracto reservado a las inversiones corporativas; es la garantía cotidiana de que cualquier ciudadano accederá a un proceso justo, transparente y oportuno sin interferencias de ningún poder de turno.</p><p>Cualquier intento serio de transformación estructural de la justicia debe empezar por la meritocracia en la carrera judicial, presupuestos dignos y una despolitización integral de las altas cortes.</p>',
                'publicado' => true,
                'created_at' => now()->subHours(5),
            ]
        );

        ArticuloOpinion::updateOrCreate(
            ['titulo' => 'El nuevo mapa electoral y la recomposición de fuerzas en el oriente boliviano'],
            [
                'columnista_id' => $col3->id,
                'tipo' => 'COMENTARIO',
                'contenido' => '<p>Los resultados de las últimas mediciones demográficas confirman lo que en las calles ya es evidente: el peso gravitacional de Santa Cruz en la definición política del país es irreversible. Sin embargo, este peso no se traduce automáticamente en cohesión estratégica.</p><p>La emergencia de un electorado mayoritariamente urbano, joven y pragmático desafía las viejas consignas partidarias y exige propuestas concretas en empleo, educación tecnológica y modernización metropolitana.</p>',
                'publicado' => true,
                'created_at' => now()->subHours(8),
            ]
        );

        // 3. BANNERS PUBLICITARIOS REALES
        Banner::updateOrCreate(
            ['location' => 'portada_middle'],
            [
                'title' => 'Cooperativa Rural de Electrificación CRE R.L.',
                'image_path' => 'images/cre.jpg',
                'link' => 'https://www.cre.com.bo/',
                'position' => 1,
                'active' => true,
            ]
        );

        Banner::updateOrCreate(
            ['location' => 'footer'],
            [
                'title' => 'Radio Betania 93.7 FM',
                'image_path' => 'images/betania.jpg',
                'link' => 'https://radiobetania.com/',
                'position' => 1,
                'active' => true,
            ]
        );

        // 4. NOTICIA DE INVESTIGACIÓN ESPECIAL (LATITUD 18 INVESTIGA)
        $investiga = Noticia::where('es_investigacion', true)->first();
        if (!$investiga) {
            $categoriaEco = Category::where('name', 'like', '%econom%')
                ->orWhere('name', 'like', '%nacion%')
                ->first() ?? $primeraCategoria;

            Noticia::create([
                'titulo' => 'INFORME ESPECIAL: Las rutas de la soya y el impacto logístico en la hidrovía Paraguay-Paraná',
                'contenido' => '<p>Un equipo de investigación de Latitud 18 recorrió los principales puertos fluviales del canal Tamengo y Puerto Busch para evaluar el estado operativo del transporte granelero de exportación. Pese a las complicaciones por el calado estacional, la hidrovía se consolida como la alternativa más competitiva frente a los puertos del Pacífico.</p><p>El informe revela testimonios de exportadores, datos inéditos sobre el flujo de barcazas y las inversiones proyectadas en infraestructura logística para conectar al oriente boliviano con los mercados del Atlántico.</p>',
                'category_id' => $categoriaEco ? $categoriaEco->id : 1,
                'user_id' => $user->id,
                'publicada' => true,
                'destacada_hero' => false,
                'es_investigacion' => true,
                'es_urgente' => false,
                'imagen' => 'images/banner1.jpg',
                'views' => 1420,
            ]);
        }

        // 5. MARCAR NOTICIAS RECIENTES CON FLAGS HERO, URGENTE Y VIDEOS DE YOUTUBE
        $ultimas = Noticia::where('publicada', true)
            ->where('es_investigacion', false)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Destacadas en el Hero Carousel (Top 5)
        foreach ($ultimas->take(5) as $idx => $noticia) {
            $noticia->destacada_hero = true;
            $noticia->save();
        }

        // Noticias Urgentes para Ticker de Última Hora
        foreach ($ultimas->slice(5, 4) as $noticia) {
            $noticia->es_urgente = true;
            $noticia->save();
        }

        // Asignar videos reales de YouTube para la sección "LATITUD 18 TV"
        $videosYoutube = [
            'https://www.youtube.com/watch?v=dQw4w9WgXcQ', // Video 1
            'https://www.youtube.com/watch?v=jNQXAC9IVRw', // Video 2
            'https://www.youtube.com/watch?v=fJ9rUzIMcZQ', // Video 3
        ];

        $noticiasParaVideo = Noticia::where('publicada', true)
            ->where('destacada_hero', false)
            ->where('es_investigacion', false)
            ->take(3)
            ->get();

        foreach ($noticiasParaVideo as $k => $noticiaVid) {
            if (isset($videosYoutube[$k])) {
                $noticiaVid->video_youtube = $videosYoutube[$k];
                $noticiaVid->save();
            }
        }
    }
}
