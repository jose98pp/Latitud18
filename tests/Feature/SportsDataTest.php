<?php

namespace Tests\Feature;

use App\Services\SportsDataService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SportsDataTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['cache.default' => 'array', 'services.sports.provider' => 'espn', 'services.sports.api_key' => null]);
        Cache::flush();
        Http::preventStrayRequests();
        $this->travelTo(\Carbon\Carbon::parse('2026-10-15 18:00:00', 'America/La_Paz'));
    }

    protected function tearDown(): void
    {
        $this->travelBack();
        parent::tearDown();
    }

    private function event(string $state = 'pre', string $name = 'STATUS_SCHEDULED'): array
    {
        return ['id' => '123', 'date' => '2026-10-16T00:30:00Z', 'status' => ['type' => ['state' => $state, 'name' => $name, 'shortDetail' => "45' +2"]],
            'competitions' => [['competitors' => [
                ['homeAway' => 'away', 'score' => '0', 'team' => ['displayName' => 'Visitante']],
                ['homeAway' => 'home', 'score' => '1', 'team' => ['displayName' => 'Local']],
            ]]]];
    }

    public function test_empty_day_is_empty_and_outage_never_invents_bolivian_matches_or_standings(): void
    {
        Http::fake(['*scoreboard*' => Http::response(['events' => []]), '*standings*' => Http::response([], 503)]);
        $service = app(SportsDataService::class);
        $this->assertEquals('empty', $service->getMatchFeed()['status']);
        $this->assertSame([], $service->getMatches());
        $this->assertEquals('unavailable', $service->getStandingsFeed()['status']);
        $this->assertSame([], $service->getBolivianStandings());
        Http::assertSentCount(3);
    }

    public function test_midnight_utc_fixture_is_on_the_correct_bolivian_day_and_scheduled_scores_are_blank(): void
    {
        Http::fake(['*' => Http::response(['events' => [$this->event()]])]);
        $match = app(SportsDataService::class)->getMatches('bolivia', '2026-10-15')[0];
        $this->assertEquals('Local', $match['local']);
        $this->assertEquals('15/10 20:30', $match['estado']);
        $this->assertSame('-', $match['goles_local']);
        Http::assertSent(fn ($r) => $r['dates'] === '20261015');
        Http::assertSent(fn ($r) => $r['dates'] === '20261016');
    }

    public function test_live_scores_go_stale_without_a_live_badge_and_successful_empty_refresh_clears_them(): void
    {
        Http::fake(['*' => Http::sequence()->push(['events' => [$this->event('in')]])->push(['events' => []])->push([], 503)->push([], 503)->push(['events' => []])->push(['events' => []])]);
        $service = app(SportsDataService::class);
        $first = $service->getMatchFeed();
        $this->assertTrue($first['matches'][0]['is_live']);
        $this->assertEquals("45' +2", $first['matches'][0]['minuto']);
        $this->travel(181)->seconds();
        $stale = $service->getMatchFeed();
        $this->assertEquals('stale', $stale['status']);
        $this->assertEquals($first['updated_at'], $stale['updated_at']);
        $this->assertFalse($stale['matches'][0]['is_live']);
        $this->assertEquals('SIN ACTUALIZAR', $stale['matches'][0]['estado']);
        $this->travel(31)->seconds();
        $this->assertEquals('empty', $service->getMatchFeed()['status']);
    }

    public function test_postponed_fixture_is_not_a_finished_match(): void
    {
        Http::fake(['*' => Http::response(['events' => [$this->event('post', 'STATUS_POSTPONED')]])]);
        $match = app(SportsDataService::class)->getMatches()[0];
        $this->assertEquals('APLAZADO', $match['estado']);
        $this->assertFalse($match['is_finished']);
        $this->assertSame('-', $match['goles_local']);
    }

    public function test_api_validates_league_and_date_before_network_requests(): void
    {
        Http::fake();
        $this->getJson('/contraataque/api/partidos?liga=no-existe')->assertUnprocessable();
        $this->getJson('/contraataque/api/partidos?fecha=2026-99-32')->assertUnprocessable();
        Http::assertNothingSent();
    }

    public function test_api_returns_escaped_partials_and_changes_standings_to_the_selected_league(): void
    {
        $event = $this->event('post');
        $event['competitions'][0]['competitors'][1]['team']['displayName'] = '<script>alert(1)</script>';
        Http::fake(['*scoreboard*' => Http::response(['events' => [$event]]), '*standings*' => Http::response(['children' => []])]);
        $data = $this->getJson('/contraataque/api/partidos?liga=premier&fecha=2026-10-15')->assertOk()->json();
        $this->assertEquals('premier', $data['liga']);
        $this->assertStringContainsString('&lt;script&gt;', $data['matches_html']);
        $this->assertStringNotContainsString('<script>', $data['matches_html']);
        $this->assertStringContainsString('No hay posiciones', $data['standings_html']);
        Http::assertSent(fn ($request) => str_contains($request->url(), 'eng.1/standings'));
    }

    public function test_api_football_resolves_current_season_uses_server_key_and_real_positions(): void
    {
        config(['services.sports.provider' => 'api-football', 'services.sports.api_key' => 'testing-private-key']);
        Http::fake([
            '*leagues*' => Http::response(['errors' => [], 'response' => [['league' => ['id' => 999, 'name' => 'Primera División'], 'seasons' => [['year' => 2026, 'start' => '2026-01-01', 'end' => '2026-12-31']]]]]),
            '*fixtures*' => Http::response(['response' => [['fixture' => ['id' => 51, 'date' => '2026-10-16T00:30:00Z', 'status' => ['short' => 'HT', 'elapsed' => 45]], 'teams' => ['home' => ['name' => 'Local'], 'away' => ['name' => 'Visitante']], 'goals' => ['home' => 2, 'away' => 0]]]]),
            '*standings*' => Http::response(['response' => [['league' => ['standings' => [[['rank' => 1, 'team' => ['name' => 'Local'], 'points' => 9, 'group' => 'Grupo A', 'all' => ['played' => 3, 'win' => 3, 'goals' => ['for' => 6, 'against' => 1]]]]]]]]]),
        ]);
        $data = $this->getJson('/contraataque/api/partidos?liga=bolivia&fecha=2026-10-15')->assertOk()->json();
        $this->assertEquals('API-Football', $data['feed']['source']);
        $this->assertEquals('DESCANSO', $data['matches'][0]['minuto']);
        $this->assertEquals(2, $data['matches'][0]['goles_local']);
        $this->assertStringContainsString('Local', $data['standings_html']);
        $this->assertStringNotContainsString('testing-private-key', json_encode($data));
        Http::assertSent(fn ($r) => $r->hasHeader('x-apisports-key', 'testing-private-key') && str_contains($r->url(), '/fixtures') && $r['league'] === 999 && $r['season'] === 2026 && $r['timezone'] === 'America/La_Paz');
        Http::assertSentCount(3);
    }

    public function test_quota_errors_and_missing_credentials_are_unavailable_not_empty_successes(): void
    {
        config(['services.sports.provider' => 'api-football']);
        Http::fake(['*' => Http::response(['errors' => ['requests' => 'Quota exceeded'], 'response' => []])]);
        $service = app(SportsDataService::class);
        $this->assertEquals('unavailable', $service->getMatchFeed()['status']);
        Http::assertNothingSent();
        config(['services.sports.api_key' => 'testing-key']);
        $this->assertEquals('unavailable', $service->getMatchFeed()['status']);
        Http::assertSentCount(1);
    }

    public function test_daily_request_budget_prevents_additional_provider_calls(): void
    {
        config(['services.sports.provider' => 'api-football', 'services.sports.api_key' => 'budget-test', 'services.sports.daily_budget' => 1]);
        Http::fake(['*' => Http::response(['errors' => [], 'response' => []])]);
        $service = app(SportsDataService::class);
        $this->assertEquals('unavailable', $service->getMatchFeed('bolivia')['status']);
        $this->assertEquals('unavailable', $service->getMatchFeed('premier')['status']);
        Http::assertSentCount(1);
    }
}
