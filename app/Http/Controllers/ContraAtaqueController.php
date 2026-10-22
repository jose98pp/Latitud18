<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Noticia;
use App\Models\Category;
use App\Models\Banner;
use App\Models\ArticuloOpinion;
use App\Services\SportsDataService;

class ContraAtaqueController extends Controller
{
    protected SportsDataService $sportsService;

    public function __construct(SportsDataService $sportsService)
    {
        $this->sportsService = $sportsService;
    }

    /**
     * Obtener categorías deportivas por su nombre, sin depender de un ID fijo.
     */
    private function getSportsCategoryIds()
    {
        return Category::deportivas()->pluck('id');
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
            $categoriaDeportes = Category::deportivas()->first();
            $sportsCatIds = $this->getSportsCategoryIds();
            $noticiasDB = $this->getSportsNewsQuery()->with(['category', 'galeria'])
                ->orderByDesc('created_at')->orderByDesc('id')->take(24)->get();

            $banners = Banner::where('active', true)->orderBy('position')->get()->groupBy('location');
        } catch (\Throwable $e) {
            $categorias = collect([]);
            $categoriaDeportes = null;
            $noticiasDB = collect([]);
            $banners = collect([]);
            $sportsCatIds = collect();
        }

        $heroNews = $noticiasDB->first();
        $destacadas = $noticiasDB->slice(1, 4)->values();
        $masNoticias = $noticiasDB->slice(5, 12)->values();

        // Ligas disponibles para el Scoreboard Multi-Liga
        $ligasDisponibles = $this->sportsService->getLeagues();
        $ligaActiva = 'bolivia';

        // Partidos en tiempo real (iniciando con la Liga Boliviana)
        $matchFeed = $this->sportsService->getMatchFeed($ligaActiva);
        $partidosVivo = $matchFeed['matches'];

        // Tabla de Posiciones Oficial en Tiempo Real (División Profesional)
        $standingsFeed = $this->sportsService->getStandingsFeed($ligaActiva);
        $tablaPosiciones = $standingsFeed['standings'];

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
            'matchFeed',
            'standingsFeed',
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
        $request->validate([
            'liga' => ['sometimes', 'string', \Illuminate\Validation\Rule::in(array_keys(SportsDataService::LEAGUES))],
            'fecha' => ['sometimes', 'date_format:Y-m-d'],
        ]);
        $liga = $request->query('liga', 'bolivia');
        $leagues = $this->sportsService->getLeagues();
        $feed = $this->sportsService->getMatchFeed($liga, $request->query('fecha'));
        $table = $this->sportsService->getStandingsFeed($liga);
        $matches = $feed['matches'];

        return response()->json([
            'status' => 'success',
            'liga' => $liga,
            'liga_meta' => $leagues[$liga] ?? $leagues['bolivia'],
            'matches' => $matches,
            'count' => count($matches),
            'feed' => \Illuminate\Support\Arr::except($feed, 'matches'),
            'standings_feed' => \Illuminate\Support\Arr::except($table, 'standings'),
            'matches_html' => view('contraataque.partials.matches-list', ['partidos' => $matches, 'feed' => $feed])->render(),
            'ticker_html' => view('contraataque.partials.ticker', ['partidos' => $matches, 'feed' => $feed])->render(),
            'standings_html' => view('contraataque.partials.standings', ['tablaPosiciones' => $table['standings'], 'standingsFeed' => $table])->render(),
        ]);
    }

    /**
     * Ver noticia en el portal Contra Ataque
     */
    public function show($id, $slug = null)
    {
        $noticia = Noticia::publicadaActiva()->with(['category', 'galeria', 'comentariosAprobados', 'reacciones'])->find($id);

        if (!$noticia) {
            return redirect()->route('contraataque.index')->with('error', 'La noticia deportiva solicitada no fue encontrada o ya no está disponible.');
        }

        // Preserve old links that were wrongly routed here by the category-ID heuristic.
        if (!$noticia->category?->es_deportiva) return redirect()->to($noticia->url, 301);

        $expectedSlug = \Illuminate\Support\Str::slug($noticia->titulo) ?: 'noticia';

        // Redirección canónica SEO 301 si falta el slug en la URL o no coincide
        if ($slug !== $expectedSlug && request()->isMethod('GET') && !request()->ajax()) {
            return redirect()->route('contraataque.show', ['id' => $id, 'slug' => $expectedSlug], 301);
        }

        // Incrementar contador de visitas
        $noticia->increment('views');

        $categorias = Category::all();
        $esSeccionFutbol = !in_array($noticia->seccion_deportiva, ['motores', 'polideportivo'], true);
        $matchFeed = $esSeccionFutbol ? $this->sportsService->getMatchFeed('bolivia') : [];
        $standingsFeed = $esSeccionFutbol ? $this->sportsService->getStandingsFeed('bolivia') : [];
        $partidosVivo = $matchFeed['matches'] ?? [];
        $tablaPosiciones = $standingsFeed['standings'] ?? [];
        $ligasDisponibles = $this->sportsService->getLeagues();
        $ligaActiva = 'bolivia';

        $catIds = $this->getSportsCategoryIds();

        // Noticias relacionadas estrictamente de la categoría Deportes
        $relacionadas = Noticia::publicadaActiva()
            ->where('id', '!=', $id)
            ->when($noticia->seccion_deportiva, fn ($q) => $q->where('seccion_deportiva', $noticia->seccion_deportiva))
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
            'esSeccionFutbol',
            'matchFeed',
            'standingsFeed',
            'banners',
            'ligasDisponibles',
            'ligaActiva'
        ));
    }

    /**
     * Subsecciones de Contra Ataque (Fútbol Boliviano, La Verde, Internacional, Motores, Polideportivo)
     */
    public function seccion(Request $request, string $seccion)
    {
        if ($seccion === 'deportes') return redirect()->route('contraataque.index', [], 301);
        $labels = \App\Support\SportsSections::LABELS;
        abort_unless(isset($labels[$seccion]) || $seccion === 'opinion', 404);
        $filters = $request->validate(['q' => 'nullable|string|max:150']);
        $search = trim($filters['q'] ?? '');
        $categorias = Category::all();
        $ligasDisponibles = $this->sportsService->getLeagues();
        $ligaActiva = 'bolivia';
        $esSeccionFutbol = in_array($seccion, ['futbol-boliviano', 'la-verde', 'internacional'], true);
        $matchFeed = $esSeccionFutbol ? $this->sportsService->getMatchFeed($ligaActiva) : [];
        $standingsFeed = $esSeccionFutbol ? $this->sportsService->getStandingsFeed($ligaActiva) : [];
        $partidosVivo = $matchFeed['matches'] ?? [];
        $tablaPosiciones = $standingsFeed['standings'] ?? [];
        $banners = Banner::where('active', true)->orderBy('position')->get()->groupBy('location');
        $seccionTitulo = $labels[$seccion] ?? 'El Ojo de Contraataque';
        if ($seccion === 'opinion') {
            $query = ArticuloOpinion::with('columnista')->where('publicado', true)->where('es_deportiva', true)
                ->whereHas('columnista', fn ($q) => $q->where('activo', true));
        } else {
            $query = $this->getSportsNewsQuery()->where('seccion_deportiva', $seccion)->with('category');
        }
        if ($search !== '') $query->where(fn ($q) => $q->where('titulo', 'LIKE', '%'.$search.'%')->orWhere('contenido', 'LIKE', '%'.$search.'%'));
        $noticias = $query->orderByDesc('created_at')->orderByDesc('id')->paginate(12)->withQueryString();
        return view('contraataque.seccion', compact('seccion', 'seccionTitulo', 'noticias', 'categorias', 'partidosVivo',
            'tablaPosiciones', 'banners', 'ligasDisponibles', 'ligaActiva', 'esSeccionFutbol', 'matchFeed', 'standingsFeed', 'search'));
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
                ->where('es_deportiva', true)
                ->whereHas('columnista', fn ($q) => $q->where('activo', true))
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
                        'url' => route('opinion.articulo', $a->id),
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
