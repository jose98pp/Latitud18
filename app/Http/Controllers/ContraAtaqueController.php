<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Noticia;
use App\Models\Category;
use App\Models\Banner;

class ContraAtaqueController extends Controller
{
    /**
     * Portada principal de Contra Ataque (Deportes)
     */
    public function index(Request $request)
    {
        try {
            $categorias = Category::all();
            $categoriaDeportes = Category::where('name', 'LIKE', '%deporte%')
                ->orWhere('name', 'LIKE', '%futbol%')
                ->orWhere('name', 'LIKE', '%contra%')
                ->first();

            // Obtener noticias de deportes de la BD
            $noticiasDeportesQuery = Noticia::where('publicada', true);
            if ($categoriaDeportes) {
                $noticiasDeportesQuery->where(function($q) use ($categoriaDeportes) {
                    $q->where('category_id', $categoriaDeportes->id)
                      ->orWhere('titulo', 'LIKE', '%fútbol%')
                      ->orWhere('titulo', 'LIKE', '%futbol%')
                      ->orWhere('titulo', 'LIKE', '%deporte%')
                      ->orWhere('titulo', 'LIKE', '%liga%')
                      ->orWhere('titulo', 'LIKE', '%copa%')
                      ->orWhere('titulo', 'LIKE', '%bolívar%')
                      ->orWhere('titulo', 'LIKE', '%oriente%')
                      ->orWhere('titulo', 'LIKE', '%blooming%')
                      ->orWhere('titulo', 'LIKE', '%strongest%');
                });
            }

            $noticiasDB = $noticiasDeportesQuery->with('category')->orderBy('created_at', 'desc')->take(12)->get();
            $banners = Banner::where('active', true)->orderBy('position')->get()->groupBy('location');
        } catch (\Throwable $e) {
            $categorias = collect([]);
            $categoriaDeportes = null;
            $noticiasDB = collect([]);
            $banners = collect([]);
        }

        // Si hay pocas noticias en BD, complementamos con contenido deportivo de alta calidad de Bolivia e Internacional
        $noticiasFallback = $this->getFallbackSportsNews();

        $heroNews = $noticiasDB->first() ?? $noticiasFallback[0];
        $destacadas = $noticiasDB->slice(1, 4)->values();
        if ($destacadas->count() < 4) {
            $destacadas = collect(array_slice($noticiasFallback, 1, 4));
        }

        $masNoticias = $noticiasDB->slice(5)->values();
        if ($masNoticias->count() < 4) {
            $masNoticias = collect(array_slice($noticiasFallback, 5));
        }

        // Partidos de la Fecha / Live Scores (División Profesional de Bolivia)
        $partidosVivo = $this->getLiveMatches();

        // Tabla de Posiciones División Profesional de Bolivia 2026
        $tablaPosiciones = $this->getLeagueTable();

        // Videos y Jugadas
        $videosDestacados = $this->getVideoHighlights();

        // Columnistas de Opinión Deportiva
        $columnistasDeportes = $this->getSportsColumnists();

        return view('contraataque.index', compact(
            'categorias',
            'categoriaDeportes',
            'heroNews',
            'destacadas',
            'masNoticias',
            'partidosVivo',
            'tablaPosiciones',
            'videosDestacados',
            'columnistasDeportes',
            'banners'
        ));
    }

    /**
     * Ver noticia en el portal Contra Ataque
     */
    public function show($id)
    {
        $noticia = Noticia::with(['category', 'galeria', 'comentarios'])->find($id);

        if (!$noticia) {
            // Buscar en fallback
            $fallbackList = $this->getFallbackSportsNews();
            foreach ($fallbackList as $fn) {
                if ($fn['id'] == $id) {
                    $noticia = (object) $fn;
                    break;
                }
            }
        }

        if (!$noticia) {
            return redirect()->route('contraataque.index')->with('error', 'Noticia no encontrada.');
        }

        // Incrementar visitas si es modelo
        if ($noticia instanceof Noticia) {
            $noticia->increment('views');
        }

        $categorias = Category::all();
        $partidosVivo = $this->getLiveMatches();
        $tablaPosiciones = $this->getLeagueTable();
        $relacionadas = Noticia::where('publicada', true)
            ->where('id', '!=', $id)
            ->orderBy('created_at', 'desc')
            ->take(4)
            ->get();

        if ($relacionadas->count() < 4) {
            $relacionadas = collect(array_slice($this->getFallbackSportsNews(), 1, 4));
        }

        $banners = Banner::where('active', true)->orderBy('position')->get()->groupBy('location');

        return view('contraataque.show', compact(
            'noticia',
            'categorias',
            'partidosVivo',
            'tablaPosiciones',
            'relacionadas',
            'banners'
        ));
    }

    /**
     * Subsecciones de Contra Ataque (Fútbol Boliviano, La Verde, Internacional, Motores, Polideportivo)
     */
    public function seccion($seccion)
    {
        $categorias = Category::all();
        $partidosVivo = $this->getLiveMatches();
        $tablaPosiciones = $this->getLeagueTable();
        $noticiasFallback = $this->getFallbackSportsNews();

        $seccionTitulo = match($seccion) {
            'futbol-boliviano' => 'Fútbol Boliviano — División Profesional',
            'la-verde' => 'La Verde — Selección Boliviana',
            'internacional' => 'Fútbol Internacional & Champions',
            'motores' => 'Motores & Rally Dakar',
            'polideportivo' => 'Polideportivo & Básquetbol',
            'opinion' => 'El Ojo de Contraataque — Opinión',
            default => 'Noticias de ' . ucfirst($seccion)
        };

        $noticias = collect($noticiasFallback);
        $banners = Banner::where('active', true)->orderBy('position')->get()->groupBy('location');

        return view('contraataque.seccion', compact(
            'seccion',
            'seccionTitulo',
            'noticias',
            'categorias',
            'partidosVivo',
            'tablaPosiciones',
            'banners'
        ));
    }

    /**
     * Datos simulados/enriquecidos de Partidos de la División Profesional
     */
    private function getLiveMatches(): array
    {
        return [
            [
                'id' => 'm1',
                'estado' => 'EN VIVO',
                'minuto' => "68'",
                'torneo' => 'División Profesional — Fecha 18',
                'local' => 'Oriente Petrolero',
                'local_code' => 'ORI',
                'local_color' => '#15803d',
                'goles_local' => 2,
                'visitante' => 'Blooming',
                'visitante_code' => 'BLO',
                'visitante_color' => '#0284c7',
                'goles_visitante' => 1,
                'estadio' => 'Tahuichi Aguilera (Santa Cruz)',
                'destacado' => true
            ],
            [
                'id' => 'm2',
                'estado' => 'FINAL',
                'minuto' => 'FT',
                'torneo' => 'División Profesional — Fecha 18',
                'local' => 'The Strongest',
                'local_code' => 'STR',
                'local_color' => '#ca8a04',
                'goles_local' => 3,
                'visitante' => 'Bolívar',
                'visitante_code' => 'BOL',
                'visitante_color' => '#38bdf8',
                'goles_visitante' => 2,
                'estadio' => 'Hernando Siles (La Paz)',
                'destacado' => false
            ],
            [
                'id' => 'm3',
                'estado' => 'HOY 19:30',
                'minuto' => 'Próximo',
                'torneo' => 'División Profesional — Fecha 18',
                'local' => 'Always Ready',
                'local_code' => 'ALW',
                'local_color' => '#dc2626',
                'goles_local' => '-',
                'visitante' => 'Wilstermann',
                'visitante_code' => 'WIL',
                'visitante_color' => '#991b1b',
                'goles_visitante' => '-',
                'estadio' => 'Villa Ingenio (El Alto)',
                'destacado' => false
            ],
            [
                'id' => 'm4',
                'estado' => 'HOY 20:30',
                'minuto' => 'Próximo',
                'torneo' => 'Copa Libertadores',
                'local' => 'Real Madrid',
                'local_code' => 'RMA',
                'local_color' => '#0f172a',
                'goles_local' => '-',
                'visitante' => 'Manchester City',
                'visitante_code' => 'MCI',
                'visitante_color' => '#0ea5e9',
                'goles_visitante' => '-',
                'estadio' => 'Santiago Bernabéu',
                'destacado' => false
            ]
        ];
    }

    /**
     * Tabla de posiciones actualizada de la División Profesional
     */
    private function getLeagueTable(): array
    {
        return [
            ['pos' => 1, 'club' => 'Bolívar', 'pj' => 18, 'g' => 13, 'e' => 2, 'p' => 3, 'gf' => 42, 'gc' => 16, 'dg' => '+26', 'pts' => 41, 'zona' => 'libertadores'],
            ['pos' => 2, 'club' => 'The Strongest', 'pj' => 18, 'g' => 12, 'e' => 3, 'p' => 3, 'gf' => 38, 'gc' => 19, 'dg' => '+19', 'pts' => 39, 'zona' => 'libertadores'],
            ['pos' => 3, 'club' => 'Always Ready', 'pj' => 17, 'g' => 10, 'e' => 4, 'p' => 3, 'gf' => 31, 'gc' => 17, 'dg' => '+14', 'pts' => 34, 'zona' => 'libertadores'],
            ['pos' => 4, 'club' => 'Oriente Petrolero', 'pj' => 18, 'g' => 9, 'e' => 4, 'p' => 5, 'gf' => 29, 'gc' => 22, 'dg' => '+7', 'pts' => 31, 'zona' => 'sudamericana'],
            ['pos' => 5, 'club' => 'Blooming', 'pj' => 18, 'g' => 8, 'e' => 5, 'p' => 5, 'gf' => 27, 'gc' => 24, 'dg' => '+3', 'pts' => 29, 'zona' => 'sudamericana'],
            ['pos' => 6, 'club' => 'Aurora', 'pj' => 17, 'g' => 8, 'e' => 4, 'p' => 5, 'gf' => 26, 'gc' => 23, 'dg' => '+3', 'pts' => 28, 'zona' => 'sudamericana'],
            ['pos' => 7, 'club' => 'San Antonio Bulo Bulo', 'pj' => 18, 'g' => 7, 'e' => 5, 'p' => 6, 'gf' => 24, 'gc' => 22, 'dg' => '+2', 'pts' => 26, 'zona' => 'sudamericana'],
            ['pos' => 8, 'club' => 'Wilstermann', 'pj' => 17, 'g' => 6, 'e' => 6, 'p' => 5, 'gf' => 22, 'gc' => 20, 'dg' => '+2', 'pts' => 24, 'zona' => 'neutro'],
            ['pos' => 9, 'club' => 'Real Tomayapo', 'pj' => 18, 'g' => 6, 'e' => 4, 'p' => 8, 'gf' => 20, 'gc' => 26, 'dg' => '-6', 'pts' => 22, 'zona' => 'neutro'],
            ['pos' => 10, 'club' => 'Guabirá', 'pj' => 18, 'g' => 5, 'e' => 4, 'p' => 9, 'gf' => 19, 'gc' => 28, 'dg' => '-9', 'pts' => 19, 'zona' => 'neutro'],
        ];
    }

    /**
     * Videos y Jugadas destacadas
     */
    private function getVideoHighlights(): array
    {
        return [
            [
                'id' => 1,
                'titulo' => 'Los 5 mejores goles del Clásico Cruceño en el Tahuichi',
                'duracion' => '04:12',
                'imagen' => 'https://images.unsplash.com/photo-1508098682722-e99c43a406b2?w=800&q=80',
                'categoria' => 'CLÁSICO CRUCEÑO',
                'vistas' => '24.8K'
            ],
            [
                'id' => 2,
                'titulo' => 'La atajada milagrosa en el minuto 94 que salvó la punta',
                'duracion' => '01:45',
                'imagen' => 'https://images.unsplash.com/photo-1574629810360-7efbbe195018?w=800&q=80',
                'categoria' => 'JUGADA DE LA FECHA',
                'vistas' => '18.2K'
            ],
            [
                'id' => 3,
                'titulo' => 'Resumen y análisis táctico: La Verde se alista para las Eliminatorias',
                'duracion' => '08:30',
                'imagen' => 'https://images.unsplash.com/photo-1517466787929-bc90951d0974?w=800&q=80',
                'categoria' => 'SELECCIÓN BOLIVIANA',
                'vistas' => '31.5K'
            ]
        ];
    }

    /**
     * Columnistas de opinión deportiva
     */
    private function getSportsColumnists(): array
    {
        return [
            [
                'autor' => 'Marco "El Mariscal" Roca',
                'cargo' => 'Jefe de Deportes Contra Ataque',
                'foto' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=200&q=80',
                'titulo' => 'El replanteo táctico que definió el liderazgo en el Clausura',
                'extracto' => 'La presión alta en los primeros 25 minutos ahogó por completo la salida del rival y evidenció la falta de recambio...'
            ],
            [
                'autor' => 'Lic. Pamela Soruco',
                'cargo' => 'Especialista en Fútbol Internacional',
                'foto' => 'https://images.unsplash.com/photo-1580489944761-15a19d654956?w=200&q=80',
                'titulo' => 'Bolivia rumbo al repechaje: La calculadora de la ilusión',
                'extracto' => 'Con 6 puntos en juego en condición de local, el margen de error es cero pero la fortaleza en la altura sigue siendo el mayor activo...'
            ]
        ];
    }

    /**
     * Noticias de reserva en caso de base de datos vacía
     */
    private function getFallbackSportsNews(): array
    {
        return [
            [
                'id' => 101,
                'titulo' => '¡Clásico Caliente en el Tahuichi! Oriente y Blooming disputan el liderazgo de Santa Cruz con estadio repleto',
                'slug' => 'clasico-caliente-oriente-blooming-tahuichi-liderazgo',
                'bajada' => 'Con más de 32.000 hinchas en las tribunas, la fecha 18 de la División Profesional promete un choque vibrante con formaciones confirmadas y ambos técnicos apostando al ataque desde el primer minuto.',
                'contenido' => '<p>La pasión cruceña se paraliza una vez más con la disputa del clásico más convocante del oriente boliviano. Oriente Petrolero y Blooming llegan en un momento estelar, separados por apenas dos unidades en la tabla acumulada y buscando sellar su clasificación directa a torneos internacionales.</p><p>El técnico refinero confirmó la vuelta de sus dos atacantes titulares tras superar molestias musculares, mientras que en la vereda celeste la táctica pasará por el contragolpe veloz y el dominio del balón en la mitad de la cancha.</p>',
                'imagen' => 'https://images.unsplash.com/photo-1508098682722-e99c43a406b2?w=1200&q=80',
                'foto' => 'https://images.unsplash.com/photo-1508098682722-e99c43a406b2?w=1200&q=80',
                'category' => (object)['id' => 1, 'name' => 'FÚTBOL BOLIVIANO'],
                'autor' => 'Redacción Contra Ataque',
                'created_at' => now()->subHours(2),
                'tiempo_lectura' => '4 min',
                'visitas' => 1420
            ],
            [
                'id' => 102,
                'titulo' => 'La Verde inicia microciclo en La Paz con 26 convocados pensando en las Eliminatorias',
                'slug' => 'la-verde-inicia-microciclo-la-paz-convocados-eliminatorias',
                'bajada' => 'El cuerpo técnico nacional presentó la nómina con varias sorpresas juveniles de la División Profesional y los legionarios que llegarán el fin de semana.',
                'contenido' => '<p>La Selección Boliviana de Fútbol comenzó sus trabajos en el estadio Hernando Siles con el objetivo de afinar el sistema defensivo y la velocidad en transición ofensiva para la doble fecha de eliminatorias sudamericanas.</p>',
                'imagen' => 'https://images.unsplash.com/photo-1574629810360-7efbbe195018?w=800&q=80',
                'foto' => 'https://images.unsplash.com/photo-1574629810360-7efbbe195018?w=800&q=80',
                'category' => (object)['id' => 1, 'name' => 'SELECCIÓN LA VERDE'],
                'autor' => 'Contra Ataque Deportes',
                'created_at' => now()->subHours(4),
                'tiempo_lectura' => '3 min',
                'visitas' => 980
            ],
            [
                'id' => 103,
                'titulo' => 'Champions League: El sorteo de cuartos de final deja cruces electrizantes',
                'slug' => 'champions-league-sorteo-cuartos-final-cruces',
                'bajada' => 'Los gigantes europeos conocen su destino en la carrera a la gran final en Wembley. Conoce los horarios y el fixture completo.',
                'contenido' => '<p>La UEFA Champions League entra en su fase más apasionante con duelos que reeditarán finales históricas y pondrán a prueba a las grandes estrellas del fútbol mundial.</p>',
                'imagen' => 'https://images.unsplash.com/photo-1517466787929-bc90951d0974?w=800&q=80',
                'foto' => 'https://images.unsplash.com/photo-1517466787929-bc90951d0974?w=800&q=80',
                'category' => (object)['id' => 1, 'name' => 'INTERNACIONAL'],
                'autor' => 'Contra Ataque Internacional',
                'created_at' => now()->subHours(6),
                'tiempo_lectura' => '5 min',
                'visitas' => 840
            ],
            [
                'id' => 104,
                'titulo' => 'Dakar 2027: Pilotos cruceños intensifican entrenamientos en las dunas de Santa Cruz',
                'slug' => 'dakar-pilotos-crucenos-entrenamientos-dunas',
                'bajada' => 'La delegación boliviana prepara sus máquinas en terreno pesado con el sueño de subir al podio en las categorías motos y cuadriciclos.',
                'contenido' => '<p>La resistencia mecánica y la navegación en dunas altas formaron parte del exigente test que los pilotos nacionales completaron durante el fin de semana.</p>',
                'imagen' => 'https://images.unsplash.com/photo-1568605117036-5fe5e7bab0b7?w=800&q=80',
                'foto' => 'https://images.unsplash.com/photo-1568605117036-5fe5e7bab0b7?w=800&q=80',
                'category' => (object)['id' => 1, 'name' => 'MOTORES'],
                'autor' => 'Sección Tuerca',
                'created_at' => now()->subHours(8),
                'tiempo_lectura' => '3 min',
                'visitas' => 610
            ],
            [
                'id' => 105,
                'titulo' => 'Bolívar golea en El Alto y mantiene la presión en la cima de la tabla',
                'slug' => 'bolivar-golea-mantiene-presion-cima',
                'bajada' => 'Con un triplete de su delantero estrella, la academia celeste sumó tres puntos de oro en condición de visitante.',
                'contenido' => '<p>Un partido impecable en definición le permitió a Bolívar consolidar su racha positiva en el torneo local.</p>',
                'imagen' => 'https://images.unsplash.com/photo-1511886929837-354d827aae26?w=800&q=80',
                'foto' => 'https://images.unsplash.com/photo-1511886929837-354d827aae26?w=800&q=80',
                'category' => (object)['id' => 1, 'name' => 'FÚTBOL BOLIVIANO'],
                'autor' => 'Contra Ataque La Paz',
                'created_at' => now()->subHours(10),
                'tiempo_lectura' => '2 min',
                'visitas' => 1120
            ],
            [
                'id' => 106,
                'titulo' => 'Libobásquet: El quinteto cruceño clasifica invicto al Final Four nacional',
                'slug' => 'libobasquet-quinteto-cruceno-clasifica-invicto-final-four',
                'bajada' => 'En un desenlace dramático en los últimos segundos, sellaron la victoria que los coloca como favoritos al título.',
                'contenido' => '<p>Gran ambiente en el coliseo Gilberto Pareja con un público que vibró con los triples decisivos del último cuarto.</p>',
                'imagen' => 'https://images.unsplash.com/photo-1546519638-68e109498ffc?w=800&q=80',
                'foto' => 'https://images.unsplash.com/photo-1546519638-68e109498ffc?w=800&q=80',
                'category' => (object)['id' => 1, 'name' => 'POLIDEPORTIVO'],
                'autor' => 'Contra Ataque Polideportivo',
                'created_at' => now()->subHours(12),
                'tiempo_lectura' => '3 min',
                'visitas' => 520
            ]
        ];
    }
}
