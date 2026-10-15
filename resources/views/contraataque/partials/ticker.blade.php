<strong style="color:var(--ca-volt);white-space:nowrap;">PARTIDOS Y RESULTADOS</strong>
@if(in_array($feed['status'] ?? '', ['stale', 'unavailable']))
    <span class="text-warning small" style="white-space:nowrap;">{{ ($feed['status'] ?? '') === 'stale' ? 'Sin actualizar · Última información recibida' : 'Datos temporalmente no disponibles' }}</span>
@endif
@forelse($partidos as $p)
    <div class="ca-match-chip">
        @if($p['is_live']) <span class="ca-badge-live">VIVO {{ $p['minuto'] }}</span>
        @elseif($p['is_finished']) <span class="ca-badge-ft">FINAL</span>
        @else <span style="color:var(--ca-gold);">{{ $p['estado'] }}</span> @endif
        <strong>{{ $p['local'] }}</strong>
        <span style="color:var(--ca-volt);">{{ $p['goles_local'] }} - {{ $p['goles_visitante'] }}</span>
        <strong>{{ $p['visitante'] }}</strong>
    </div>
@empty
    <span class="small text-muted" style="white-space:nowrap;">{{ ($feed['status'] ?? '') === 'empty' ? 'Sin partidos para esta fecha' : 'Marcadores no disponibles' }}</span>
@endforelse
