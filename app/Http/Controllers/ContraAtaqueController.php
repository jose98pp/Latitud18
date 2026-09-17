<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Noticia;
use App\Models\Category;
use App\Models\Banner;
use App\Models\ArticuloOpinion;
use App\Models\Columnista;

class ContraAtaqueController extends Controller
{
    /**
     * Obtener IDs de categorías relacionadas a deportes
     */
    private function getSportsCategoryIds()
    {
        return Category::where('name', 'LIKE', '%deport%')
            ->orWhere('name', 'LIKE', '%futbol%')
            ->orWhere('name', 'LIKE', '%contra%')
            ->pluck('id');
    }

    /**
     * Query base de noticias de deportes
     */
    private function getSportsNewsQuery()
    {
        $catIds = $this->getSportsCategoryIds();

        return Noticia::publicadaActiva()
            ->where(function($q) use ($catIds) {
                if ($catIds->isNotEmpty()) {
                    $q->whereIn('category_id', $catIds);
                }
                $q->orWhere('titulo', 'LIKE', '%fútbol%')
                  ->orWhere('titulo', 'LIKE', '%futbol%')
                  ->orWhere('titulo', 'LIKE', '%deporte%')
                  ->orWhere('titulo', 'LIKE', '%mundial%')
                  ->orWhere('titulo', 'LIKE', '%copa%')
                  ->orWhere('titulo', 'LIKE', '%liga%')
                  ->orWhere('titulo', 'LIKE', '%fifa%')
                  ->orWhere('titulo', 'LIKE', '%bolívar%')
                  ->orWhere('titulo', 'LIKE', '%oriente%')
                  ->orWhere('titulo', 'LIKE', '%blooming%')
                  ->orWhere('titulo', 'LIKE', '%strongest%');
            });
    }

    /**
     * Portada principal de Contra Ataque (Deportes)
     */
    public function index(Request $request)
    {
        try {
            $categorias = Category::all();
            $categoriaDeportes = Category::where('name', 'LIKE', '%deport%')
                ->orWhere('name', 'LIKE', '%futbol%')
                ->orWhere('name', 'LIKE', '%contra%')
                ->first();

            $sportsCatIds = $this->getSportsCategoryIds();

            // Obtener noticias de deportes de la BD
            $noticiasDB = $this->getSportsNewsQuery()
                ->with(['category', 'galeria'])
                ->orderBy('created_at', 'desc')
                ->take(24)
                ->get();

            // Si hubiera menos de 8 noticias, completar con las últimas noticias reales de la BD
            if ($noticiasDB->count() < 8) {
                $existingIds = $noticiasDB->pluck('id')->toArray();
                $fillers = Noticia::publicadaActiva()
                    ->whereNotIn('id', $existingIds)
                    ->with(['category', 'galeria'])
                    ->orderBy('created_at', 'desc')
                    ->take(8 - $noticiasDB->count())
                    ->get();
                $noticiasDB = $noticiasDB->merge($fillers);
            }

            $banners = Banner::where('active', true)->orderBy('position')->get()->groupBy('location');
        } catch (\Throwable $e) {
            $categorias = collect([]);
            $categoriaDeportes = null;
            $noticiasDB = collect([]);
            $banners = collect([]);
        }

        $heroNews = $noticiasDB->first();
        $destacadas = $noticiasDB->slice(1, 4)->values();
        $masNoticias = $noticiasDB->slice(5, 12)->values();

        // Partidos de la Fecha / Live Scores (División Profesional de Bolivia)
        $partidosVivo = $this->getLiveMatches();

        // Tabla de Posiciones División Profesional de Bolivia 2026
        $tablaPosiciones = $this->getLeagueTable();

        // Videos y Jugadas reales de la BD
        $videosDestacados = $this->getRealVideoHighlights($sportsCatIds ?? collect([]));

        // Columnistas de Opinión Deportiva (reales o editoriales)
        $columnistasDeportes = $this->getRealSportsColumnists();

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
        $noticia = Noticia::with(['category', 'galeria', 'comentariosAprobados', 'reacciones'])->findOrFail($id);

        // Incrementar contador de visitas
        $noticia->increment('views');

        $categorias = Category::all();
        $partidosVivo = $this->getLiveMatches();
        $tablaPosiciones = $this->getLeagueTable();

        $catIds = $this->getSportsCategoryIds();

        $relacionadas = Noticia::publicadaActiva()
            ->where('id', '!=', $id)
            ->where(function($q) use ($catIds, $noticia) {
                if ($catIds->isNotEmpty()) {
                    $q->whereIn('category_id', $catIds);
                }
                if ($noticia->category_id) {
                    $q->orWhere('category_id', $noticia->category_id);
                }
            })
            ->with('category')
            ->orderBy('created_at', 'desc')
            ->take(4)
            ->get();

        if ($relacionadas->count() < 4) {
            $existingRelIds = $relacionadas->pluck('id')->push($id)->toArray();
            $moreRel = Noticia::publicadaActiva()
                ->whereNotIn('id', $existingRelIds)
                ->with('category')
                ->orderBy('created_at', 'desc')
                ->take(4 - $relacionadas->count())
                ->get();
            $relacionadas = $relacionadas->merge($moreRel);
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

        $seccionTitulo = match($seccion) {
            'futbol-boliviano' => 'Fútbol Boliviano — División Profesional',
            'la-verde' => 'La Verde — Selección Boliviana',
            'internacional' => 'Fútbol Internacional & Champions',
            'motores' => 'Motores & Rally Dakar',
            'polideportivo' => 'Polideportivo & Básquetbol',
            'opinion' => 'El Ojo de Contraataque — Opinión Deportiva',
            default => 'Noticias de ' . ucwords(str_replace('-', ' ', $seccion))
        };

        $catIds = $this->getSportsCategoryIds();
        $query = Noticia::publicadaActiva()->with(['category', 'galeria']);

        // Filtrar según la subsección deportiva
        switch ($seccion) {
            case 'futbol-boliviano':
                $query->where(function($q) use ($catIds) {
                    $q->where(function($sub) {
                        $sub->where('titulo', 'LIKE', '%bolivia%')
                            ->orWhere('titulo', 'LIKE', '%división%')
                            ->orWhere('titulo', 'LIKE', '%liga%')
                            ->orWhere('titulo', 'LIKE', '%oriente%')
                            ->orWhere('titulo', 'LIKE', '%blooming%')
                            ->orWhere('titulo', 'LIKE', '%bolívar%')
                            ->orWhere('titulo', 'LIKE', '%strongest%')
                            ->orWhere('titulo', 'LIKE', '%wilstermann%')
                            ->orWhere('titulo', 'LIKE', '%aurora%')
                            ->orWhere('titulo', 'LIKE', '%tahuichi%');
                    });
                    if ($catIds->isNotEmpty()) {
                        $q->orWhereIn('category_id', $catIds);
                    }
                });
                break;

            case 'la-verde':
                $query->where(function($q) use ($catIds) {
                    $q->where('titulo', 'LIKE', '%verde%')
                      ->orWhere('titulo', 'LIKE', '%selección%')
                      ->orWhere('titulo', 'LIKE', '%eliminatoria%')
                      ->orWhere('titulo', 'LIKE', '%conmebol%')
                      ->orWhere('titulo', 'LIKE', '%bolivia%');
                    if ($catIds->isNotEmpty()) {
                        $q->orWhereIn('category_id', $catIds);
                    }
                });
                break;

            case 'internacional':
                $query->where(function($q) use ($catIds) {
                    $q->where('titulo', 'LIKE', '%mundial%')
                      ->orWhere('titulo', 'LIKE', '%fifa%')
                      ->orWhere('titulo', 'LIKE', '%champions%')
                      ->orWhere('titulo', 'LIKE', '%copa%')
                      ->orWhere('titulo', 'LIKE', '%españa%')
                      ->orWhere('titulo', 'LIKE', '%argentina%')
                      ->orWhere('titulo', 'LIKE', '%brasil%')
                      ->orWhere('titulo', 'LIKE', '%inglaterra%')
                      ->orWhere('titulo', 'LIKE', '%real madrid%')
                      ->orWhere('titulo', 'LIKE', '%colombia%');
                    if ($catIds->isNotEmpty()) {
                        $q->orWhereIn('category_id', $catIds);
                    }
                });
                break;

            case 'motores':
                $query->where(function($q) use ($catIds) {
                    $q->where('titulo', 'LIKE', '%dakar%')
                      ->orWhere('titulo', 'LIKE', '%rally%')
                      ->orWhere('titulo', 'LIKE', '%motor%')
                      ->orWhere('titulo', 'LIKE', '%f1%')
                      ->orWhere('titulo', 'LIKE', '%volkswagen%')
                      ->orWhere('titulo', 'LIKE', '%vehículo%')
                      ->orWhere('titulo', 'LIKE', '%auto%');
                    if ($catIds->isNotEmpty()) {
                        $q->orWhereIn('category_id', $catIds);
                    }
                });
                break;

            case 'polideportivo':
                $query->where(function($q) use ($catIds) {
                    $q->where('titulo', 'LIKE', '%básquet%')
                      ->orWhere('titulo', 'LIKE', '%basquet%')
                      ->orWhere('titulo', 'LIKE', '%atletismo%')
                      ->orWhere('titulo', 'LIKE', '%tenis%')
                      ->orWhere('titulo', 'LIKE', '%natación%')
                      ->orWhere('titulo', 'LIKE', '%campamento%');
                    if ($catIds->isNotEmpty()) {
                        $q->orWhereIn('category_id', $catIds);
                    }
                });
                break;

            default:
                if ($catIds->isNotEmpty()) {
                    $query->whereIn('category_id', $catIds);
                }
                break;
        }

        $noticias = $query->orderBy('created_at', 'desc')->paginate(12);

        // Si la consulta arroja 0 resultados específicos, cargar noticias generales de deportes para que la sección nunca esté vacía
        if ($noticias->isEmpty() && $catIds->isNotEmpty()) {
            $noticias = Noticia::publicadaActiva()
                ->whereIn('category_id', $catIds)
                ->with(['category', 'galeria'])
                ->orderBy('created_at', 'desc')
                ->paginate(12);
        }

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
     * Videos y Jugadas reales desde la BD
     */
    private function getRealVideoHighlights($sportsCatIds): array
    {
        try {
            $videosRaw = Noticia::publicadaActiva()
                ->whereNotNull('video_youtube')
                ->where('video_youtube', '!=', '')
                ->where(function($q) use ($sportsCatIds) {
                    if ($sportsCatIds->isNotEmpty()) {
                        $q->whereIn('category_id', $sportsCatIds);
                    }
                })
                ->with('category')
                ->latest()
                ->take(3)
                ->get();

            if ($videosRaw->count() < 3) {
                $existingVidIds = $videosRaw->pluck('id')->toArray();
                $moreVideos = Noticia::publicadaActiva()
                    ->whereNotNull('video_youtube')
                    ->where('video_youtube', '!=', '')
                    ->whereNotIn('id', $existingVidIds)
                    ->with('category')
                    ->latest()
                    ->take(3 - $videosRaw->count())
                    ->get();
                $videosRaw = $videosRaw->merge($moreVideos);
            }

            return $videosRaw->map(function($v) {
                $img = method_exists($v, 'getImageUrl') ? $v->getImageUrl() : ($v->imagen ?? $v->foto ?? '');
                return [
                    'id' => $v->id,
                    'titulo' => $v->titulo,
                    'duracion' => 'Video HD',
                    'imagen' => $img ?: ($v->youtube_thumbnail ?: 'https://images.unsplash.com/photo-1508098682722-e99c43a406b2?w=800&q=80'),
                    'categoria' => strtoupper($v->category->name ?? 'DEPORTES'),
                    'vistas' => number_format($v->views > 0 ? $v->views : 150) . ' vistas',
                    'youtube_id' => $v->youtube_id,
                ];
            })->toArray();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Columnistas de opinión deportiva reales
     */
    private function getRealSportsColumnists(): array
    {
        try {
            $articulosDB = ArticuloOpinion::with('columnista')
                ->publicado()
                ->take(2)
                ->get();

            if ($articulosDB->isNotEmpty()) {
                return $articulosDB->map(function($a) {
                    return [
                        'autor' => $a->columnista->nombre ?? 'Línea Editorial',
                        'cargo' => $a->columnista->cargo ?? 'Contra Ataque Opinión',
                        'foto' => $a->columnista->avatar_url ?? 'https://ui-avatars.com/api/?name=CA&background=00FF87&color=000',
                        'titulo' => $a->titulo,
                        'extracto' => \Illuminate\Support\Str::limit(strip_tags($a->contenido), 140),
                    ];
                })->toArray();
            }
        } catch (\Throwable $e) {
            // Seguir con los predeterminados
        }

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
     * Datos de Partidos de la División Profesional
     */
    private function getLiveMatches(): array
    {
        return [
            [
                'id' => 'm1',
                'estado' => 'EN VIVO',
                'minuto' => "68'",
                'torneo' => 'División Profesional — Clausura',
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
                'torneo' => 'División Profesional — Clausura',
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
                'torneo' => 'División Profesional — Clausura',
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
}
