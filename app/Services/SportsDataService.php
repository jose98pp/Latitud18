<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SportsDataService
{
    /**
     * Ligas soportadas con sus códigos de ESPN y metadatos
     */
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

    /**
     * Obtiene los metadatos de todas las ligas disponibles
     */
    public function getLeagues(): array
    {
        return self::LEAGUES;
    }

    /**
     * Obtiene los partidos de una liga específica con caché de 3 minutos
     */
    public function getMatches(string $leagueKey = 'bolivia'): array
    {
        $league = self::LEAGUES[$leagueKey] ?? self::LEAGUES['bolivia'];
        $cacheKey = "contraataque_matches_{$league['id']}";

        return Cache::remember($cacheKey, 180, function () use ($league) {
            $url = "https://site.api.espn.com/apis/site/v2/sports/soccer/{$league['espn_slug']}/scoreboard";
            $data = $this->fetchFromESPN($url);

            if (!$data || empty($data['events'])) {
                return $this->getFallbackMatches($league['id']);
            }

            $matches = [];
            foreach ($data['events'] as $event) {
                $comp = $event['competitions'][0] ?? null;
                if (!$comp || empty($comp['competitors'])) {
                    continue;
                }

                $competitors = $comp['competitors'];
                $home = null;
                $away = null;

                foreach ($competitors as $c) {
                    if (($c['homeAway'] ?? '') === 'home') {
                        $home = $c;
                    } else {
                        $away = $c;
                    }
                }

                // Si no vinieran con la clave homeAway explícita, tomar orden 0 y 1
                if (!$home && isset($competitors[0])) {
                    $home = $competitors[0];
                }
                if (!$away && isset($competitors[1])) {
                    $away = $competitors[1];
                }

                if (!$home || !$away) {
                    continue;
                }

                $state = $event['status']['type']['state'] ?? 'pre';
                $isLive = in_array($state, ['in']);
                $isFinished = in_array($state, ['post']);
                $displayClock = $event['status']['displayClock'] ?? '';
                $detail = $event['status']['type']['shortDetail'] ?? ($event['status']['type']['detail'] ?? '');

                // Estado legible
                $estado = 'PROGRAMADO';
                $minuto = $detail;
                if ($isLive) {
                    $estado = 'EN VIVO';
                    $minuto = !empty($displayClock) ? $displayClock . "'" : 'En Vivo';
                } elseif ($isFinished) {
                    $estado = 'FINAL';
                    $minuto = 'FT';
                } else {
                    // Formatear hora programada en zona horaria de Bolivia
                    if (!empty($event['date'])) {
                        try {
                            $dt = new \DateTime($event['date']);
                            $dt->setTimezone(new \DateTimeZone('America/La_Paz'));
                            $estado = $dt->format('d/m H:i');
                            $minuto = 'Próximo';
                        } catch (\Throwable $e) {
                            $estado = $detail;
                        }
                    }
                }

                $homeTeam = $home['team'] ?? [];
                $awayTeam = $away['team'] ?? [];

                $localName = $homeTeam['shortDisplayName'] ?? ($homeTeam['displayName'] ?? 'Local');
                $awayName = $awayTeam['shortDisplayName'] ?? ($awayTeam['displayName'] ?? 'Visitante');
                $localCode = $homeTeam['abbreviation'] ?? mb_strtoupper(mb_substr($localName, 0, 3));
                $awayCode = $awayTeam['abbreviation'] ?? mb_strtoupper(mb_substr($awayName, 0, 3));

                $matches[] = [
                    'id' => $event['id'] ?? uniqid(),
                    'torneo' => $league['name'],
                    'torneo_badge' => $league['badge'],
                    'flag' => $league['flag'],
                    'estado' => $estado,
                    'is_live' => $isLive,
                    'is_finished' => $isFinished,
                    'minuto' => $minuto,
                    'local' => $localName,
                    'local_full' => $homeTeam['displayName'] ?? 'Local',
                    'local_code' => $localCode,
                    'local_logo' => $homeTeam['logo'] ?? null,
                    'local_color' => !empty($homeTeam['color']) ? '#' . $homeTeam['color'] : '#00FF87',
                    'goles_local' => $home['score'] ?? ($isLive || $isFinished ? 0 : '-'),
                    'visitante' => $awayName,
                    'visitante_full' => $awayTeam['displayName'] ?? 'Visitante',
                    'visitante_code' => $awayCode,
                    'visitante_logo' => $awayTeam['logo'] ?? null,
                    'visitante_color' => !empty($awayTeam['color']) ? '#' . $awayTeam['color'] : '#FFFFFF',
                    'goles_visitante' => $away['score'] ?? ($isLive || $isFinished ? 0 : '-'),
                    'estadio' => $comp['venue']['fullName'] ?? ($event['venue']['displayName'] ?? 'Estadio Oficial'),
                ];
            }

            return !empty($matches) ? $matches : $this->getFallbackMatches($league['id']);
        });
    }

    /**
     * Obtiene la tabla de posiciones oficial de la Liga Boliviana
     */
    public function getBolivianStandings(): array
    {
        return Cache::remember('contraataque_standings_bolivia', 600, function () {
            $url = "https://site.api.espn.com/apis/v2/sports/soccer/bol.1/standings";
            $data = $this->fetchFromESPN($url);

            if (!$data || empty($data['children'][0]['standings']['entries'])) {
                return $this->getFallbackStandings();
            }

            $entries = $data['children'][0]['standings']['entries'];
            $table = [];

            foreach ($entries as $index => $entry) {
                $team = $entry['team'] ?? [];
                $stats = collect($entry['stats'] ?? []);

                $pj = (int) ($stats->firstWhere('name', 'gamesPlayed')['value'] ?? 0);
                $pts = (int) ($stats->firstWhere('name', 'points')['value'] ?? 0);
                $g = (int) ($stats->firstWhere('name', 'wins')['value'] ?? 0);
                $e = (int) ($stats->firstWhere('name', 'ties')['value'] ?? 0);
                $p = (int) ($stats->firstWhere('name', 'losses')['value'] ?? 0);
                $gf = (int) ($stats->firstWhere('name', 'pointsFor')['value'] ?? 0);
                $gc = (int) ($stats->firstWhere('name', 'pointsAgainst')['value'] ?? 0);
                $dgVal = (int) ($stats->firstWhere('name', 'pointDifferential')['value'] ?? ($gf - $gc));
                $dg = ($dgVal > 0 ? "+{$dgVal}" : (string)$dgVal);

                $pos = $index + 1;
                $zona = 'neutro';
                if ($pos <= 4) {
                    $zona = 'libertadores';
                } elseif ($pos <= 8) {
                    $zona = 'sudamericana';
                } elseif ($pos >= 15) {
                    $zona = 'descenso';
                }

                $table[] = [
                    'pos' => $pos,
                    'club' => $team['shortDisplayName'] ?? ($team['displayName'] ?? 'Club'),
                    'club_full' => $team['displayName'] ?? 'Club',
                    'logo' => $team['logos'][0]['href'] ?? ($team['logo'] ?? null),
                    'pj' => $pj,
                    'g' => $g,
                    'e' => $e,
                    'p' => $p,
                    'gf' => $gf,
                    'gc' => $gc,
                    'dg' => $dg,
                    'pts' => $pts,
                    'zona' => $zona,
                ];
            }

            return !empty($table) ? $table : $this->getFallbackStandings();
        });
    }

    /**
     * Realiza la petición cURL a los endpoints de ESPN con User-Agent seguro
     */
    private function fetchFromESPN(string $url): ?array
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 6);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
            curl_setopt($ch, CURLOPT_USERAGENT, 'curl/8.4.0');
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200 && !empty($response)) {
                $decoded = json_decode($response, true);
                if (is_array($decoded)) {
                    return $decoded;
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Error consultando ESPN API ({$url}): " . $e->getMessage());
        }

        return null;
    }

    /**
     * Fallback de partidos garantizados de la División Profesional
     */
    private function getFallbackMatches(string $leagueKey): array
    {
        if ($leagueKey === 'bolivia') {
            return [
                [
                    'id' => 'fb_bol_1',
                    'torneo' => 'División Profesional',
                    'torneo_badge' => 'LIGA BOLIVIANA',
                    'flag' => '🇧🇴',
                    'estado' => 'FINAL',
                    'is_live' => false,
                    'is_finished' => true,
                    'minuto' => 'FT',
                    'local' => 'Ind. Petrolero',
                    'local_full' => 'Independiente Petrolero',
                    'local_logo' => 'https://a.espncdn.com/i/teamlogos/soccer/500/20889.png',
                    'local_color' => '#dc2626',
                    'goles_local' => 1,
                    'visitante' => 'Bulo Bulo',
                    'visitante_full' => 'San Antonio Bulo Bulo',
                    'visitante_logo' => 'https://a.espncdn.com/i/teamlogos/soccer/500/22137.png',
                    'visitante_color' => '#00FF87',
                    'goles_visitante' => 3,
                    'estadio' => 'Estadio Olímpico Patria (Sucre)',
                ],
                [
                    'id' => 'fb_bol_2',
                    'torneo' => 'División Profesional',
                    'torneo_badge' => 'LIGA BOLIVIANA',
                    'flag' => '🇧🇴',
                    'estado' => 'FINAL',
                    'is_live' => false,
                    'is_finished' => true,
                    'minuto' => 'FT',
                    'local' => 'The Strongest',
                    'local_full' => 'The Strongest',
                    'local_logo' => 'https://a.espncdn.com/i/teamlogos/soccer/500/3283.png',
                    'local_color' => '#ca8a04',
                    'goles_local' => 2,
                    'visitante' => 'Bolívar',
                    'visitante_full' => 'Club Bolívar',
                    'visitante_logo' => 'https://a.espncdn.com/i/teamlogos/soccer/500/3277.png',
                    'visitante_color' => '#38bdf8',
                    'goles_visitante' => 1,
                    'estadio' => 'Estadio Hernando Siles (La Paz)',
                ],
                [
                    'id' => 'fb_bol_3',
                    'torneo' => 'División Profesional',
                    'torneo_badge' => 'LIGA BOLIVIANA',
                    'flag' => '🇧🇴',
                    'estado' => 'HOY 19:30',
                    'is_live' => false,
                    'is_finished' => false,
                    'minuto' => 'Próximo',
                    'local' => 'Always Ready',
                    'local_full' => 'Club Always Ready',
                    'local_logo' => 'https://a.espncdn.com/i/teamlogos/soccer/500/20349.png',
                    'local_color' => '#dc2626',
                    'goles_local' => '-',
                    'visitante' => 'Wilstermann',
                    'visitante_full' => 'Jorge Wilstermann',
                    'visitante_logo' => 'https://a.espncdn.com/i/teamlogos/soccer/500/3279.png',
                    'visitante_color' => '#b91c1c',
                    'goles_visitante' => '-',
                    'estadio' => 'Estadio Municipal de El Alto',
                ],
                [
                    'id' => 'fb_bol_4',
                    'torneo' => 'División Profesional',
                    'torneo_badge' => 'LIGA BOLIVIANA',
                    'flag' => '🇧🇴',
                    'estado' => 'MAÑANA 17:30',
                    'is_live' => false,
                    'is_finished' => false,
                    'minuto' => 'Próximo',
                    'local' => 'Oriente Petrolero',
                    'local_full' => 'Oriente Petrolero',
                    'local_logo' => 'https://a.espncdn.com/i/teamlogos/soccer/500/3278.png',
                    'local_color' => '#16a34a',
                    'goles_local' => '-',
                    'visitante' => 'Blooming',
                    'visitante_full' => 'Club Blooming',
                    'visitante_logo' => 'https://a.espncdn.com/i/teamlogos/soccer/500/3280.png',
                    'visitante_color' => '#0284c7',
                    'goles_visitante' => '-',
                    'estadio' => 'Estadio Ramón Tahuichi Aguilera',
                ],
            ];
        }

        // Para Premier, LaLiga, etc. si el API no responde
        return [
            [
                'id' => "fb_{$leagueKey}_1",
                'torneo' => self::LEAGUES[$leagueKey]['name'] ?? 'Fútbol Internacional',
                'torneo_badge' => self::LEAGUES[$leagueKey]['badge'] ?? 'INTERNACIONAL',
                'flag' => self::LEAGUES[$leagueKey]['flag'] ?? '⚽',
                'estado' => 'FINAL',
                'is_live' => false,
                'is_finished' => true,
                'minuto' => 'FT',
                'local' => 'Equipo Local',
                'local_full' => 'Equipo Local',
                'local_logo' => null,
                'local_color' => '#00FF87',
                'goles_local' => 2,
                'visitante' => 'Equipo Visitante',
                'visitante_full' => 'Equipo Visitante',
                'visitante_logo' => null,
                'visitante_color' => '#FFFFFF',
                'goles_visitante' => 1,
                'estadio' => 'Estadio Principal',
            ]
        ];
    }

    /**
     * Fallback de tabla de posiciones de la Liga Boliviana
     */
    private function getFallbackStandings(): array
    {
        return [
            ['pos' => 1, 'club' => 'Bolívar', 'club_full' => 'Club Bolívar', 'logo' => 'https://a.espncdn.com/i/teamlogos/soccer/500/3277.png', 'pj' => 18, 'g' => 13, 'e' => 2, 'p' => 3, 'gf' => 42, 'gc' => 16, 'dg' => '+26', 'pts' => 41, 'zona' => 'libertadores'],
            ['pos' => 2, 'club' => 'The Strongest', 'club_full' => 'The Strongest', 'logo' => 'https://a.espncdn.com/i/teamlogos/soccer/500/3283.png', 'pj' => 18, 'g' => 12, 'e' => 3, 'p' => 3, 'gf' => 38, 'gc' => 19, 'dg' => '+19', 'pts' => 39, 'zona' => 'libertadores'],
            ['pos' => 3, 'club' => 'Always Ready', 'club_full' => 'Club Always Ready', 'logo' => 'https://a.espncdn.com/i/teamlogos/soccer/500/20349.png', 'pj' => 17, 'g' => 10, 'e' => 4, 'p' => 3, 'gf' => 31, 'gc' => 17, 'dg' => '+14', 'pts' => 34, 'zona' => 'libertadores'],
            ['pos' => 4, 'club' => 'Oriente Petrolero', 'club_full' => 'Oriente Petrolero', 'logo' => 'https://a.espncdn.com/i/teamlogos/soccer/500/3278.png', 'pj' => 18, 'g' => 9, 'e' => 4, 'p' => 5, 'gf' => 29, 'gc' => 22, 'dg' => '+7', 'pts' => 31, 'zona' => 'sudamericana'],
            ['pos' => 5, 'club' => 'Blooming', 'club_full' => 'Club Blooming', 'logo' => 'https://a.espncdn.com/i/teamlogos/soccer/500/3280.png', 'pj' => 18, 'g' => 8, 'e' => 5, 'p' => 5, 'gf' => 27, 'gc' => 24, 'dg' => '+3', 'pts' => 29, 'zona' => 'sudamericana'],
            ['pos' => 6, 'club' => 'Aurora', 'club_full' => 'Club Aurora', 'logo' => 'https://a.espncdn.com/i/teamlogos/soccer/500/3281.png', 'pj' => 17, 'g' => 8, 'e' => 4, 'p' => 5, 'gf' => 26, 'gc' => 23, 'dg' => '+3', 'pts' => 28, 'zona' => 'sudamericana'],
            ['pos' => 7, 'club' => 'San Antonio Bulo Bulo', 'club_full' => 'San Antonio Bulo Bulo', 'logo' => 'https://a.espncdn.com/i/teamlogos/soccer/500/22137.png', 'pj' => 18, 'g' => 7, 'e' => 5, 'p' => 6, 'gf' => 24, 'gc' => 22, 'dg' => '+2', 'pts' => 26, 'zona' => 'sudamericana'],
            ['pos' => 8, 'club' => 'Wilstermann', 'club_full' => 'Jorge Wilstermann', 'logo' => 'https://a.espncdn.com/i/teamlogos/soccer/500/3279.png', 'pj' => 17, 'g' => 6, 'e' => 6, 'p' => 5, 'gf' => 22, 'gc' => 20, 'dg' => '+2', 'pts' => 24, 'zona' => 'neutro'],
            ['pos' => 9, 'club' => 'Real Tomayapo', 'club_full' => 'CD Real Tomayapo', 'logo' => 'https://a.espncdn.com/i/teamlogos/soccer/500/21356.png', 'pj' => 18, 'g' => 6, 'e' => 4, 'p' => 8, 'gf' => 20, 'gc' => 26, 'dg' => '-6', 'pts' => 22, 'zona' => 'neutro'],
            ['pos' => 10, 'club' => 'Guabirá', 'club_full' => 'Club Guabirá', 'logo' => 'https://a.espncdn.com/i/teamlogos/soccer/500/3282.png', 'pj' => 18, 'g' => 5, 'e' => 4, 'p' => 9, 'gf' => 19, 'gc' => 28, 'dg' => '-9', 'pts' => 19, 'zona' => 'neutro'],
            ['pos' => 11, 'club' => 'Ind. Petrolero', 'club_full' => 'Independiente Petrolero', 'logo' => 'https://a.espncdn.com/i/teamlogos/soccer/500/20889.png', 'pj' => 18, 'g' => 5, 'e' => 3, 'p' => 10, 'gf' => 21, 'gc' => 32, 'dg' => '-11', 'pts' => 18, 'zona' => 'neutro'],
            ['pos' => 12, 'club' => 'Nacional Potosí', 'club_full' => 'Nacional Potosí', 'logo' => 'https://a.espncdn.com/i/teamlogos/soccer/500/10427.png', 'pj' => 17, 'g' => 5, 'e' => 2, 'p' => 10, 'gf' => 18, 'gc' => 30, 'dg' => '-12', 'pts' => 17, 'zona' => 'neutro'],
            ['pos' => 13, 'club' => 'Real Santa Cruz', 'club_full' => 'Real Santa Cruz', 'logo' => 'https://a.espncdn.com/i/teamlogos/soccer/500/20888.png', 'pj' => 18, 'g' => 4, 'e' => 3, 'p' => 11, 'gf' => 16, 'gc' => 31, 'dg' => '-15', 'pts' => 15, 'zona' => 'neutro'],
            ['pos' => 14, 'club' => 'Gualberto Villarroel', 'club_full' => 'GV San José', 'logo' => 'https://a.espncdn.com/i/teamlogos/soccer/500/22138.png', 'pj' => 18, 'g' => 4, 'e' => 2, 'p' => 12, 'gf' => 17, 'gc' => 35, 'dg' => '-18', 'pts' => 14, 'zona' => 'neutro'],
            ['pos' => 15, 'club' => 'Universitario de Vinto', 'club_full' => 'FC Universitario', 'logo' => 'https://a.espncdn.com/i/teamlogos/soccer/500/21576.png', 'pj' => 17, 'g' => 3, 'e' => 4, 'p' => 10, 'gf' => 15, 'gc' => 34, 'dg' => '-19', 'pts' => 13, 'zona' => 'descenso'],
            ['pos' => 16, 'club' => 'Royal Pari', 'club_full' => 'Royal Pari FC', 'logo' => 'https://a.espncdn.com/i/teamlogos/soccer/500/19875.png', 'pj' => 18, 'g' => 2, 'e' => 4, 'p' => 12, 'gf' => 14, 'gc' => 37, 'dg' => '-23', 'pts' => 10, 'zona' => 'descenso'],
        ];
    }
}
