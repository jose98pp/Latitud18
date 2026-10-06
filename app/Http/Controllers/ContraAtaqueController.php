<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Noticia;
use App\Models\Category;
use App\Models\Banner;
use App\Models\ArticuloOpinion;
use App\Models\Columnista;
use App\Services\SportsDataService;

class ContraAtaqueController extends Controller
{
    /**
     * Palabras clave por subseccion deportiva.
     * 'incluir' = debe coincidir alguna. 'excluir' = descarta falsos positivos
     * (terminos genericos que tambien aparecen en otra disciplina).
     */
    public const SUBSECCIONES = [
        'futbol-boliviano' => [
            'incluir' => [
                'división profesional', 'liga boliviana', 'clausura', 'apertura',
                'oriente Petrolero', 'blooming', 'bolívar', 'the strongest', 'strongest',
                'always ready', 'wilstermann', 'aurora', 'blooming', 'petrolero',
                'independiente', 'real potosí', 'real potosi', 'guabirá', 'guabira',
                'banguato', 'san josé', 'san jose', 'bulo bulo', 'universidad',
                'tahuichi', 'municipal de yacuiba',
            ],
            'excluir' => ['selección', 'seleccion', 'mundial', 'champions', 'libertadores', 'sudamericana', 'copa simón', 'copa simon'],
        ],
        'la-verde' => [
            'incluir' => [
                'la verde', 'selección boliviana', 'seleccion boliviana', 'eliminatorias',
                'copa américa', 'copa america', 'preliminar', 'fbf', 'villegas',
                'félix ramírez', 'felix ramirez', 'martins', 'robín', 'robin',
                'cuauhtémoc', 'cuauhtemoc', 'blooming', 'entrenador de la verde',
            ],
            'excluir' => [],
        ],
        'internacional' => [
            'incluir' => [
                'champions', 'libertadores', 'sudamericana', 'conmebol', 'fifa',
                'premier league', 'la liga', 'laliga', 'serie a', 'bundesliga',
                'real madrid', 'barcelona', 'atlético', 'atletico', 'manchester',
                'messi', 'cristiano ronaldo', 'cr7', 'mbappé', 'mbappe', 'haaland',
                'mundial de fútbol', 'copa del mundo', 'mundial 2026',
            ],
            'excluir' => [],
        ],
        'motores' => [
            'incluir' => [
                'dakar', 'rally', 'fórmula 1', 'formula 1', 'f1', 'motogp',
                'moto gp', 'nascar', 'automovilismo', 'automotriz', 'volkswagen',
                'toyota', 'ferrari', 'bmx', 'motocross', 'vehículo', 'vehiculo',
                'autódromo', 'autodromo', 'monoplaza', 'camoto', 'autoes',
            ],
            'excluir' => [],
        ],
        'polideportivo' => [
            'incluir' => [
                'básquet', 'basquet', 'basketball', 'vóley', 'voleibol', 'handball',
                'atletismo', 'tenis', 'natación', 'natacion', 'swimming', 'ciclis',
                'ciclismo', 'maratón', 'maraton', 'gimnasia', 'boxeo', 'karate',
                'judo', 'taekwondo', 'ajedrez', 'golf', 'surf', 'escalada',
                'olímpicos', 'panamericanos', 'medallas',
            ],
            // 'campamento' y 'amputadas' suelen ser de fútbol; 'olímpico' también
            // aparece en nombres de estadios (Estadio Olímpico Patria).
            'excluir' => ['fútbol', 'futbol', 'selección', 'seleccion', 'liga profesional'],
        ],
    ];

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
        // Cacheado: la taxonomia cambia muy rara vez y se consulta en cada request
        // de la portada, secciones y detalle.
        return Cache::remember('contraataque.deportes.cat_ids', 600, function () {
            $catIds = Category::where(function ($q) {
                $q->where('id', 9)
                  ->orWhere('name', 'LIKE', '%deport%')
                  ->orWhere('name', 'LIKE', '%futbol%')
                  ->orWhere('name', 'LIKE', '%contra%');
            })->pluck('id');

            return $catIds->isEmpty() ? collect([9]) : $catIds;
        });
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

        $deportesCatIds = $sportsCatIds;

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
            'ligaActiva',
            'deportesCatIds'
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

        // Filtrar según la subsección deportiva.
        // 'excluir' evita que un término genérico capture noticias de otra disciplina
        // (ej: "campamento" de fútbol no debe caer en polideportivo).
        $filtros = self::SUBSECCIONES[$seccion] ?? null;
        $usadoFiltro = false;

        if ($filtros) {
            $query->where(function ($q) use ($filtros) {
                $q->where(function ($sub) use ($filtros) {
                    foreach ($filtros['incluir'] as $k) {
                        $sub->orWhere('titulo', 'LIKE', "%{$k}%");
                    }
                });
                if (!empty($filtros['excluir'])) {
                    $q->whereNot(function ($sub) use ($filtros) {
                        foreach ($filtros['excluir'] as $k) {
                            $sub->orWhere('titulo', 'LIKE', "%{$k}%");
                        }
                    });
                }
            });
            $usadoFiltro = true;
        }

        $noticias = $query->orderBy('created_at', 'desc')->paginate(12);

        // Si el filtro especifico no arroja resultados, mostrar Ultimas de deportes
        // pero avisar en la UI que no hay contenido especifico de esa disciplina.
        $filtroSinResultados = $usadoFiltro && $noticias->isEmpty();
        if ($filtroSinResultados) {
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
            'ligaActiva',
            'filtroSinResultados'
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
                    'imagen' => $img ?: ($v->youtube_thumbnail ?: '/images/default-news.svg'),
                    'categoria' => strtoupper($v->category->name ?? 'DEPORTES'),
                    // Antes inventaba "150 vistas" cuando no habia dato real
                    'vistas' => $v->views > 0 ? number_format($v->views) . ' vistas' : '',
                    'youtube_id' => $v->youtube_id,
                ];
            })->toArray();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Columnistas de opinión deportiva reales
     *
     * Solo devuelve articulos que existen en la BD. Antes, si no habia Opinion,
     * retornaba dos columnistas y dos notas INVENTADAS que parecian reales.
     */
    private function getRealSportsColumnists(): array
    {
        try {
            $articulosDB = ArticuloOpinion::with('columnista')
                ->publicado()
                ->take(2)
                ->get();

            if ($articulosDB->isNotEmpty()) {
                return $articulosDB->map(function ($a) {
                    $nombre = $a->columnista->nombre ?? 'Línea Editorial';
                    return [
                        'autor' => $nombre,
                        'cargo' => $a->columnista->cargo ?? 'Contra Ataque Opinión',
                        // La columna real es 'avatar' (no 'avatar_url')
                        'foto' => $a->columnista->avatar
                            ?: 'https://ui-avatars.com/api/?name=' . rawurlencode(mb_substr($nombre, 0, 2)) . '&background=0B1F3A&color=ffffff&bold=true',
                        'titulo' => $a->titulo,
                        'extracto' => \Illuminate\Support\Str::limit(strip_tags($a->contenido ?? ''), 140),
                    ];
                })->toArray();
            }
        } catch (\Throwable $e) {
            return [];
        }

        // Sin articulos de opinion publicados: no inventar contenido.
        return [];
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
