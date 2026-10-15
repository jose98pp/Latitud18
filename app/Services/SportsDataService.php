<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SportsDataService
{
    public const LEAGUES = [
        'bolivia' => [
            'id' => 'bolivia',
            'espn_slug' => 'bol.1',
            'name' => 'División Profesional',
            'country' => 'Bolivia',
            'flag' => '🇧🇴',
            'badge' => 'LIGA BOLIVIANA',
            'short' => 'Bolivia',
        ],
        'premier' => [
            'id' => 'premier',
            'espn_slug' => 'eng.1',
            'name' => 'Premier League',
            'country' => 'Inglaterra',
            'flag' => '🇬🇧',
            'badge' => 'PREMIER LEAGUE',
            'short' => 'Premier',
        ],
        'laliga' => [
            'id' => 'laliga',
            'espn_slug' => 'esp.1',
            'name' => 'LaLiga EA Sports',
            'country' => 'España',
            'flag' => '🇪🇸',
            'badge' => 'LA LIGA',
            'short' => 'LaLiga',
        ],
        'serie_a' => [
            'id' => 'serie_a',
            'espn_slug' => 'ita.1',
            'name' => 'Serie A',
            'country' => 'Italia',
            'flag' => '🇮🇹',
            'badge' => 'SERIE A',
            'short' => 'Serie A',
        ],
        'bundesliga' => [
            'id' => 'bundesliga',
            'espn_slug' => 'ger.1',
            'name' => 'Bundesliga',
            'country' => 'Alemania',
            'flag' => '🇩🇪',
            'badge' => 'BUNDESLIGA',
            'short' => 'Bundesliga',
        ],
        'champions' => [
            'id' => 'champions',
            'espn_slug' => 'uefa.champions',
            'name' => 'UEFA Champions League',
            'country' => 'Europa',
            'flag' => '🏆',
            'badge' => 'CHAMPIONS LEAGUE',
            'short' => 'Champions',
        ],
    ];

    public function getLeagues(): array { return self::LEAGUES; }

    public function getMatches(string $leagueKey = 'bolivia', ?string $date = null): array
    {
        return $this->getMatchFeed($leagueKey, $date)['matches'];
    }

    public function getBolivianStandings(): array
    {
        return $this->getStandingsFeed('bolivia')['standings'];
    }

    public function getMatchFeed(string $leagueKey = 'bolivia', ?string $date = null): array
    {
        $date ??= now('America/La_Paz')->format('Y-m-d');
        return $this->feed('matches', $leagueKey, $date);
    }

    public function getStandingsFeed(string $leagueKey = 'bolivia', ?string $date = null): array
    {
        $date ??= now('America/La_Paz')->format('Y-m-d');
        return $this->feed('standings', $leagueKey, $date);
    }

    private function feed(string $kind, string $leagueKey, string $date): array
    {
        if (!isset(self::LEAGUES[$leagueKey])) throw new \InvalidArgumentException('Liga no válida.');
        CarbonImmutable::createFromFormat('!Y-m-d', $date, 'America/La_Paz');
        $provider = config('services.sports.provider', 'espn');
        $identity = substr(hash('sha256', (string) config('services.sports.api_key')), 0, 12);
        $key = "sports:v3:{$provider}:{$identity}:{$kind}:{$leagueKey}:{$date}";
        if ($cached = Cache::get($key)) return $cached;
        $league = self::LEAGUES[$leagueKey];
        $source = $provider === 'api-football' ? 'API-Football' : 'ESPN';
        try {
            if ($provider === 'api-football') {
                $items = $this->apiFootball($kind, $leagueKey, $date);
            } elseif ($provider === 'espn') {
                $items = $this->espn($kind, $league, $date);
            } else {
                throw new \RuntimeException('Proveedor deportivo no válido.');
            }
            $result = [$kind => $items, 'status' => $items ? 'ok' : 'empty', 'updated_at' => now()->toIso8601String(), 'source' => $source, 'date' => $date];
            Cache::put($key.':last', $result, 21600);
            Cache::put($key, $result, $kind === 'matches' ? max(60, (int)config('services.sports.refresh_seconds', 180)) : 900);
            return $result;
        } catch (\Throwable $e) {
            // Never log credentials, headers or provider payloads.
            Log::warning('Datos deportivos temporalmente no disponibles.', ['provider' => $provider, 'league' => $leagueKey, 'kind' => $kind]);
            $last = Cache::get($key.':last');
            $result = $last ? array_replace($last, ['status' => 'stale']) : [$kind => [], 'status' => 'unavailable', 'updated_at' => null, 'source' => $source, 'date' => $date];
            if ($kind === 'matches' && $last) {
                // A cached live score must not keep claiming to be live during an outage.
                $result[$kind] = array_map(fn ($m) => array_replace($m, ['is_live' => false, 'estado' => 'SIN ACTUALIZAR', 'minuto' => 'Último dato recibido']), $result[$kind]);
            }
            Cache::put($key, $result, 30);
            return $result;
        }
    }

    private function json(string $url, array $query = [], array $headers = []): array
    {
        $response = Http::acceptJson()->withHeaders(['User-Agent' => 'curl/8.4.0'] + $headers)->connectTimeout(3)->timeout(6)->get($url, $query);
        if (!$response->successful() || !is_array($data = $response->json())) throw new \RuntimeException('Respuesta deportiva inválida.');
        return $data;
    }

    private function espn(string $kind, array $league, string $date): array
    {
        if ($kind === 'matches') {
            // Soccer scoreboards reject date ranges. Query the two UTC days in parallel,
            // then filter to Bolivia's day and deduplicate overlapping events.
            $days = [str_replace('-', '', $date), CarbonImmutable::parse($date)->addDay()->format('Ymd')];
            $url = "https://site.api.espn.com/apis/site/v2/sports/soccer/{$league['espn_slug']}/scoreboard";
            $responses = Http::pool(fn ($pool) => array_map(fn ($day) => $pool->acceptJson()->withHeaders(['User-Agent' => 'curl/8.4.0'])->connectTimeout(3)->timeout(6)->get($url, ['dates' => $day, 'limit' => 100]), $days));
            $events = [];
            foreach ($responses as $response) {
                if ($response instanceof \Throwable || !$response->successful() || !is_array($response->json('events'))) throw new \RuntimeException('Sin estructura de partidos.');
                $events = array_merge($events, $response->json('events'));
            }
            $events = collect($events)->unique('id')->values();
            $matches = [];
            foreach ($events as $event) {
                $comp = $event['competitions'][0] ?? [];
                $teams = collect($comp['competitors'] ?? []);
                $home = $teams->firstWhere('homeAway', 'home'); $away = $teams->firstWhere('homeAway', 'away');
                if (!$home || !$away || empty($event['id']) || empty($event['date'])) throw new \RuntimeException('Partido incompleto.');
                $status = $comp['status'] ?? $event['status'] ?? [];
                $state = $status['type']['state'] ?? 'pre';
                $name = $status['type']['name'] ?? '';
                $special = ['STATUS_POSTPONED' => 'APLAZADO', 'STATUS_CANCELED' => 'CANCELADO', 'STATUS_SUSPENDED' => 'SUSPENDIDO', 'STATUS_DELAYED' => 'DEMORADO'];
                $live = $state === 'in' && !isset($special[$name]);
                $finished = $state === 'post' && !isset($special[$name]);
                $time = CarbonImmutable::parse($event['date'])->setTimezone('America/La_Paz');
                // ESPN dates are queried with a buffer; the visible day is always Bolivia's day.
                if ($time->format('Y-m-d') !== $date) continue;
                $matches[] = $this->match($league, $event['id'], $time,
                    $home['team']['shortDisplayName'] ?? $home['team']['displayName'] ?? 'Local',
                    $away['team']['shortDisplayName'] ?? $away['team']['displayName'] ?? 'Visitante',
                    $home['team']['logo'] ?? null, $away['team']['logo'] ?? null,
                    $home['score'] ?? null, $away['score'] ?? null, $live, $finished,
                    $special[$name] ?? ($live ? 'EN VIVO' : ($finished ? 'FINAL' : $time->format('d/m H:i'))),
                    $live ? ($status['type']['shortDetail'] ?? 'En juego') : ($finished ? 'FT' : ''), $comp['venue']['fullName'] ?? '');
            }
            return $matches;
        }
        $data = $this->json("https://site.api.espn.com/apis/v2/sports/soccer/{$league['espn_slug']}/standings");
        $groups = $data['children'] ?? [];
        if (isset($data['standings'])) $groups = [$data];
        if (!is_array($groups) || (!$groups && !array_key_exists('children', $data))) throw new \RuntimeException('Sin estructura de posiciones.');
        $rows = [];
        foreach ($groups as $group) foreach ($group['standings']['entries'] ?? [] as $i => $entry) {
            $stats = collect($entry['stats'] ?? []);
            $stat = fn ($name) => (int) ($stats->firstWhere('name', $name)['value'] ?? 0);
            $rows[] = $this->standing($entry['team']['shortDisplayName'] ?? $entry['team']['displayName'] ?? 'Club', $entry['team']['logos'][0]['href'] ?? null,
                $stat('rank') ?: $i+1, $stat('gamesPlayed'), $stat('wins'), $stat('ties'), $stat('losses'), $stat('pointsFor'), $stat('pointsAgainst'), $stat('points'), $group['name'] ?? '');
        }
        return $rows;
    }

    private function apiFootball(string $kind, string $key, string $date): array
    {
        $token = (string) config('services.sports.api_key');
        if ($token === '') throw new \RuntimeException('Falta configurar la clave.');
        $names = ['bolivia' => ['Primera Division', 'Primera División', 'División Profesional'], 'premier' => ['Premier League'], 'laliga' => ['La Liga'], 'serie_a' => ['Serie A'], 'bundesliga' => ['Bundesliga'], 'champions' => ['UEFA Champions League']];
        $countries = ['bolivia' => 'Bolivia', 'premier' => 'England', 'laliga' => 'Spain', 'serie_a' => 'Italy', 'bundesliga' => 'Germany'];
        $request = function ($path, $query) use ($token) {
            $day = now('UTC');
            $budgetKey = 'sports:budget:'.substr(hash('sha256', $token), 0, 12).':'.$day->format('Y-m-d');
            Cache::add($budgetKey, 0, $day->copy()->endOfDay()->addMinute());
            if (Cache::increment($budgetKey) > (int) config('services.sports.daily_budget', 90)) {
                throw new \RuntimeException('Presupuesto diario de consultas alcanzado.');
            }
            $data = $this->json('https://v3.football.api-sports.io/'.$path, $query, ['x-apisports-key' => $token]);
            if (!empty($data['errors']) || !isset($data['response']) || !is_array($data['response'])) throw new \RuntimeException('Proveedor sin datos autorizados.');
            return $data['response'];
        };
        $leagueKey = 'sports:leagues:'.substr(hash('sha256', $token),0,12).':'.$key;
        $competitions = Cache::remember($leagueKey, 86400, fn () => $request('leagues', array_filter(['country' => $countries[$key] ?? null, 'search' => Str::ascii($names[$key][0])])));
        $normalize = fn ($s) => strtolower(Str::ascii($s));
        $competition = collect($competitions)->first(fn ($c) => in_array($normalize($c['league']['name'] ?? ''), array_map($normalize, $names[$key]), true));
        $season = collect($competition['seasons'] ?? [])->first(fn ($s) => $date >= ($s['start'] ?? '') && $date <= ($s['end'] ?? ''));
        if (!$competition || !$season) throw new \RuntimeException('Sin cobertura de liga o temporada.');
        $league = self::LEAGUES[$key];
        $query = ['league' => $competition['league']['id'], 'season' => $season['year']];
        if ($kind === 'matches') {
            $data = $request('fixtures', $query + ['date' => $date, 'timezone' => 'America/La_Paz']);
            $matches = [];
            foreach ($data as $item) {
                if (empty($item['fixture']['id']) || empty($item['fixture']['date']) || empty($item['teams']['home']['name']) || empty($item['teams']['away']['name'])) throw new \RuntimeException('Partido incompleto.');
                $status = $item['fixture']['status']['short'] ?? 'NS';
                $live = in_array($status, ['1H','HT','2H','ET','BT','P','LIVE'], true);
                $finished = in_array($status, ['FT','AET','PEN'], true);
                $special = ['PST' => 'APLAZADO', 'CANC' => 'CANCELADO', 'SUSP' => 'SUSPENDIDO', 'INT' => 'INTERRUMPIDO', 'ABD' => 'ABANDONADO', 'AWD' => 'DECISIÓN OFICIAL', 'WO' => 'NO PRESENTADO', 'TBD' => 'FECHA POR CONFIRMAR'];
                $time = CarbonImmutable::parse($item['fixture']['date'])->setTimezone('America/La_Paz');
                $matches[] = $this->match($league, $item['fixture']['id'], $time, $item['teams']['home']['name'], $item['teams']['away']['name'],
                    $item['teams']['home']['logo'] ?? null, $item['teams']['away']['logo'] ?? null, $item['goals']['home'] ?? null, $item['goals']['away'] ?? null, $live, $finished,
                    $special[$status] ?? ($live ? 'EN VIVO' : ($finished ? 'FINAL' : $time->format('d/m H:i'))),
                    $live ? ($status === 'HT' ? 'DESCANSO' : (($item['fixture']['status']['elapsed'] ?? '')."'")) : ($finished ? $status : ''), $item['fixture']['venue']['name'] ?? '');
            }
            return $matches;
        }
        $data = $request('standings', $query); $rows = [];
        foreach ($data as $competition) foreach ($competition['league']['standings'] ?? [] as $group) foreach ($group as $r) {
            $all = $r['all'] ?? [];
            $rows[] = $this->standing($r['team']['name'] ?? 'Club', $r['team']['logo'] ?? null, (int)($r['rank'] ?? 0), (int)($all['played'] ?? 0), (int)($all['win'] ?? 0), (int)($all['draw'] ?? 0), (int)($all['lose'] ?? 0), (int)($all['goals']['for'] ?? 0), (int)($all['goals']['against'] ?? 0), (int)($r['points'] ?? 0), $r['group'] ?? '');
        }
        return $rows;
    }

    private function match(array $league, $id, CarbonImmutable $time, string $home, string $away, $homeLogo, $awayLogo, $homeScore, $awayScore, bool $live, bool $finished, string $state, string $minute, string $venue): array
    {
        return ['id' => (string)$id, 'torneo' => $league['name'], 'torneo_badge' => $league['badge'], 'flag' => $league['flag'],
            'estado' => $state, 'is_live' => $live, 'is_finished' => $finished, 'minuto' => $minute, 'date' => $time->toIso8601String(),
            'local' => $home, 'local_full' => $home, 'local_code' => mb_strtoupper(mb_substr($home,0,3)), 'local_logo' => $this->imageUrl($homeLogo), 'local_color' => '#00FF87',
            'visitante' => $away, 'visitante_full' => $away, 'visitante_code' => mb_strtoupper(mb_substr($away,0,3)), 'visitante_logo' => $this->imageUrl($awayLogo), 'visitante_color' => '#FFFFFF',
            'goles_local' => ($live || $finished) && is_numeric($homeScore) ? (int)$homeScore : '-', 'goles_visitante' => ($live || $finished) && is_numeric($awayScore) ? (int)$awayScore : '-', 'estadio' => $venue];
    }

    private function standing(string $club, $logo, int $rank, int $played, int $wins, int $draws, int $losses, int $for, int $against, int $points, string $group): array
    {
        return ['pos' => $rank, 'club' => $club, 'club_full' => $club, 'logo' => $this->imageUrl($logo), 'pj' => $played, 'g' => $wins, 'e' => $draws, 'p' => $losses, 'gf' => $for, 'gc' => $against, 'dg' => sprintf('%+d', $for-$against), 'pts' => $points, 'zona' => 'neutro', 'group' => $group];
    }

    private function imageUrl($url): ?string
    {
        return is_string($url) && filter_var($url, FILTER_VALIDATE_URL) && parse_url($url, PHP_URL_SCHEME) === 'https' ? $url : null;
    }
}
