<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Noticia;
use App\Models\Category;
use App\Models\Banner;
use App\Models\ArticuloOpinion;
use App\Models\Columnista;
use App\Services\SportsDataService;

class ContraAtaqueController extends Controller
{
    protected SportsDataService $sportsService;

    public function __construct(SportsDataService $sportsService)
    {
        $this->sportsService = $sportsService;
    }

    /**
     * Obtener IDs de categorías relacionadas estrictamente a deportes (Categoría 9 Deportes)
     */
    private function getSportsCategoryIds()
    {
        $catIds = Category::where(function($q) {
            $q->where('id', 9)
              ->orWhere('name', 'LIKE', '%deport%')
              ->orWhere('name', 'LIKE', '%futbol%')
              ->orWhere('name', 'LIKE', '%contra%');
        })->pluck('id');

        if ($catIds->isEmpty()) {
            return collect([9]);
        }

        return $catIds;
    }

    /**
     * Query base de noticias de deportes (100% Exclusivo de Deportes)
     */
    private function getSportsNewsQuery()
    {
        $catIds = $this->getSportsCategoryIds();

        return Noticia::publicadaActiva()
            ->whereIn('category_id', $catIds);
    }

    /**
     * Portada principal de Contra Ataque (Deportes)
     */
    public function index(Request $request)
    {
        try {
            $categorias = Category::all();
            $categoriaDeportes = Category::where('id', 9)
                ->orWhere('name', 'LIKE', '%deport%')
                ->first();

            $sportsCatIds = $this->getSportsCategoryIds();

            // Obtener noticias de deportes de la BD (Exclusivamente de la categoría Deportes)
            $noticiasDB = $this->getSportsNewsQuery()
                ->with(['category', 'galeria'])
                ->orderBy('created_at', 'desc')
                ->take(24)
                ->get();

            // Respaldo de seguridad si aún no cargara
            if ($noticiasDB->isEmpty()) {
                $noticiasDB = Noticia::publicadaActiva()
                    ->where('category_id', 9)
                    ->with(['category', 'galeria'])
                    ->orderBy('created_at', 'desc')
                    ->take(24)
                    ->get();
            }

            $banners = Banner::where('active', true)->orderBy('position')->get()->groupBy('location');
        } catch (\Throwable $e) {
            $categorias = collect([]);
            $categoriaDeportes = null;
            $noticiasDB = collect([]);
            $banners = collect([]);
            $sportsCatIds = collect([9]);
        }

        $heroNews = $noticiasDB->first();
        $destacadas = $noticiasDB->slice(1, 4)->values();
        $masNoticias = $noticiasDB->slice(5, 12)->values();

        // Ligas disponibles para el Scoreboard Multi-Liga
        $ligasDisponibles = $this->sportsService->getLeagues();
        $ligaActiva = 'bolivia';

        // Partidos en tiempo real (iniciando con la Liga Boliviana)
        $partidosVivo = $this->sportsService->getMatches($ligaActiva);

        // Tabla de Posiciones Oficial en Tiempo Real (División Profesional)
        $tablaPosiciones = $this->sportsService->getBolivianStandings();

        // Videos y Jugadas reales de la BD
        $videosDestacados = $this->getRealVideoHighlights($sportsCatIds);

        // Columnistas de Opinión Deportiva
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
            'banners',
            'ligasDisponibles',
            'ligaActiva'
        ));
    }

    /**
     * Endpoint API AJAX para alternar partidos en tiempo real entre ligas
     */
    public function apiPartidos(Request $request)
    {
        $liga = $request->query('liga', 'bolivia');
        $leagues = $this->sportsService->getLeagues();
        $matches = $this->sportsService->getMatches($liga);

        return response()->json([
            'status' => 'success',
            'liga' => $liga,
            'liga_meta' => $leagues[$liga] ?? $leagues['bolivia'],
            'matches' => $matches,
            'count' => count($matches),
        ]);
    }

    /**
     * Ver noticia en el portal Contra Ataque
     */
    public function show($id, $slug = null)
    {
        $noticia = Noticia::with(['category', 'galeria', 'comentariosAprobados', 'reacciones'])->find($id);

        if (!$noticia) {
            return redirect()->route('contraataque.index')->with('error', 'La noticia deportiva solicitada no fue encontrada o ya no está disponible.');
        }

        $expectedSlug = \Illuminate\Support\Str::slug($noticia->titulo) ?: 'noticia';

        // Redirección canónica SEO 301 si falta el slug en la URL o no coincide
        if ($slug !== $expectedSlug && request()->isMethod('GET') && !request()->ajax()) {
            return redirect()->route('contraataque.show', ['id' => $id, 'slug' => $expectedSlug], 301);
        }

        // Incrementar contador de visitas
        $noticia->increment('views');

        $categorias = Category::all();
        $partidosVivo = $this->sportsService->getMatches('bolivia');
        $tablaPosiciones = $this->sportsService->getBolivianStandings();
        $ligasDisponibles = $this->sportsService->getLeagues();
        $ligaActiva = 'bolivia';

        $catIds = $this->getSportsCategoryIds();

        // Noticias relacionadas estrictamente de la categoría Deportes
        $relacionadas = Noticia::publicadaActiva()
            ->where('id', '!=', $id)
            ->whereIn('category_id', $catIds)
            ->with('category')
            ->orderBy('created_at', 'desc')
            ->take(4)
            ->get();

        $banners = Banner::where('active', true)->orderBy('position')->get()->groupBy('location');

        return view('contraataque.show', compact(
            'noticia',
            'categorias',
            'partidosVivo',
            'tablaPosiciones',
            'relacionadas',
            'banners',
            'ligasDisponibles',
            'ligaActiva'
        ));
    }

    /**
     * Subsecciones de Contra Ataque (Fútbol Boliviano, La Verde, Internacional, Motores, Polideportivo)
     */
    public function seccion($seccion)
    {
        $categorias = Category::all();
        $partidosVivo = $this->sportsService->getMatches('bolivia');
        $tablaPosiciones = $this->sportsService->getBolivianStandings();
        $ligasDisponibles = $this->sportsService->getLeagues();
        $ligaActiva = 'bolivia';

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
        $query = Noticia::publicadaActiva()
            ->whereIn('category_id', $catIds)
            ->with(['category', 'galeria']);

        // Filtrar según la subsección deportiva
        switch ($seccion) {
            case 'futbol-boliviano':
                $query->where(function($q) {
                    $q->where('titulo', 'LIKE', '%bolivia%')
                        ->orWhere('titulo', 'LIKE', '%división%')
                        ->orWhere('titulo', 'LIKE', '%liga%')
                        ->orWhere('titulo', 'LIKE', '%oriente%')
                        ->orWhere('titulo', 'LIKE', '%blooming%')
                        ->orWhere('titulo', 'LIKE', '%bolívar%')
                        ->orWhere('titulo', 'LIKE', '%strongest%')
                        ->orWhere('titulo', 'LIKE', '%always%')
                        ->orWhere('titulo', 'LIKE', '%wilstermann%')
                        ->orWhere('titulo', 'LIKE', '%aurora%')
                        ->orWhere('titulo', 'LIKE', '%clausura%')
                        ->orWhere('titulo', 'LIKE', '%apertura%');
                });
                break;

            case 'la-verde':
                $query->where(function($q) {
                    $q->where('titulo', 'LIKE', '%verde%')
                      ->orWhere('titulo', 'LIKE', '%selección%')
                      ->orWhere('titulo', 'LIKE', '%seleccion%')
                      ->orWhere('titulo', 'LIKE', '%eliminatorias%')
                      ->orWhere('titulo', 'LIKE', '%villegas%')
                      ->orWhere('titulo', 'LIKE', '%copa américa%')
                      ->orWhere('titulo', 'LIKE', '%fbf%');
                });
                break;

            case 'internacional':
                $query->where(function($q) {
                    $q->where('titulo', 'LIKE', '%champions%')
                      ->orWhere('titulo', 'LIKE', '%libertadores%')
                      ->orWhere('titulo', 'LIKE', '%sudamericana%')
                      ->orWhere('titulo', 'LIKE', '%messi%')
                      ->orWhere('titulo', 'LIKE', '%cr7%')
                      ->orWhere('titulo', 'LIKE', '%ronaldo%')
                      ->orWhere('titulo', 'LIKE', '%premier%')
                      ->orWhere('titulo', 'LIKE', '%madrid%')
                      ->orWhere('titulo', 'LIKE', '%barcelona%')
                      ->orWhere('titulo', 'LIKE', '%españa%')
                      ->orWhere('titulo', 'LIKE', '%argentina%')
                      ->orWhere('titulo', 'LIKE', '%brasil%')
                      ->orWhere('titulo', 'LIKE', '%inglaterra%')
                      ->orWhere('titulo', 'LIKE', '%real madrid%')
                      ->orWhere('titulo', 'LIKE', '%colombia%');
                });
                break;

            case 'motores':
                $query->where(function($q) {
                    $q->where('titulo', 'LIKE', '%dakar%')
                      ->orWhere('titulo', 'LIKE', '%rally%')
                      ->orWhere('titulo', 'LIKE', '%motor%')
                      ->orWhere('titulo', 'LIKE', '%f1%')
                      ->orWhere('titulo', 'LIKE', '%volkswagen%')
                      ->orWhere('titulo', 'LIKE', '%vehículo%')
                      ->orWhere('titulo', 'LIKE', '%auto%');
                });
                break;

            case 'polideportivo':
                $query->where(function($q) {
                    $q->where('titulo', 'LIKE', '%básquet%')
                      ->orWhere('titulo', 'LIKE', '%basquet%')
                      ->orWhere('titulo', 'LIKE', '%atletismo%')
                      ->orWhere('titulo', 'LIKE', '%tenis%')
                      ->orWhere('titulo', 'LIKE', '%natación%')
                      ->orWhere('titulo', 'LIKE', '%campamento%');
                });
                break;

            default:
                break;
        }

        $noticias = $query->orderBy('created_at', 'desc')->paginate(12);

        // Si la consulta arroja 0 resultados específicos, cargar las últimas noticias de deportes
        if ($noticias->isEmpty()) {
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
            'banners',
            'ligasDisponibles',
            'ligaActiva'
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
                ->whereIn('category_id', $sportsCatIds)
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
                    ->whereIn('category_id', $sportsCatIds)
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
     * Compatibilidad hacia atrás
     */
    private function getLiveMatches(): array
    {
        return $this->sportsService->getMatches('bolivia');
    }

    private function getLeagueTable(): array
    {
        return $this->sportsService->getBolivianStandings();
    }
}
