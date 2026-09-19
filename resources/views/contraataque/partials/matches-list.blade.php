@forelse($partidos ?? [] as $p)
    <div class="p-2 rounded ca-match-item" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07); transition: all 0.2s ease;">
        <div class="d-flex justify-content-between align-items-center mb-1 pb-1 border-bottom border-secondary border-opacity-10" style="font-size: 0.68rem;">
            <span class="text-truncate me-2" style="color: #94A3B8; font-weight: 600;">
                <span class="me-1">{{ $p['flag'] ?? '⚽' }}</span> {{ $p['torneo'] ?? 'Fútbol' }}
            </span>
            @if(!empty($p['is_live']))
                <span class="ca-badge-live"><i class="fas fa-circle ca-blink" style="font-size: 5px;"></i> VIVO {{ $p['minuto'] }}</span>
            @elseif(!empty($p['is_finished']))
                <span class="ca-badge-ft">FINAL</span>
            @else
                <span style="color: var(--ca-gold); font-weight: 700;"><i class="far fa-clock me-1"></i>{{ $p['estado'] }}</span>
            @endif
        </div>

        <div class="d-flex align-items-center justify-content-between py-1">
            <!-- Equipo Local -->
            <div class="d-flex align-items-center gap-2" style="width: 40%; overflow: hidden;">
                @if(!empty($p['local_logo']))
                    <img src="{{ $p['local_logo'] }}" alt="{{ $p['local'] }}" class="ca-team-logo" onerror="this.style.display='none'">
                @else
                    <span style="width: 10px; height: 10px; border-radius: 50%; background: {{ $p['local_color'] ?? '#00FF87' }}; display: inline-block; flex-shrink: 0;"></span>
                @endif
                <span class="fw-bold text-white text-truncate" style="font-size: 0.84rem;" title="{{ $p['local_full'] ?? $p['local'] }}">
                    {{ $p['local'] }}
                </span>
            </div>

            <!-- Marcador Digital -->
            <div class="text-center px-2 py-1 rounded ca-score-box" style="background: #000; font-family: var(--ca-font-display); font-weight: 900; font-size: 1.05rem; color: var(--ca-volt); min-width: 54px; letter-spacing: 1px; border: 1px solid rgba(0,255,135,0.25);">
                {{ $p['goles_local'] }} : {{ $p['goles_visitante'] }}
            </div>

            <!-- Equipo Visitante -->
            <div class="d-flex align-items-center justify-content-end gap-2" style="width: 40%; overflow: hidden;">
                <span class="fw-bold text-white text-truncate text-end" style="font-size: 0.84rem;" title="{{ $p['visitante_full'] ?? $p['visitante'] }}">
                    {{ $p['visitante'] }}
                </span>
                @if(!empty($p['visitante_logo']))
                    <img src="{{ $p['visitante_logo'] }}" alt="{{ $p['visitante'] }}" class="ca-team-logo" onerror="this.style.display='none'">
                @else
                    <span style="width: 10px; height: 10px; border-radius: 50%; background: {{ $p['visitante_color'] ?? '#FFFFFF' }}; display: inline-block; flex-shrink: 0;"></span>
                @endif
            </div>
        </div>

        @if(!empty($p['estadio']))
            <div class="d-flex align-items-center mt-1 pt-1 border-top border-secondary border-opacity-10 text-muted" style="font-size: 0.66rem;">
                <span class="text-truncate"><i class="fas fa-location-dot me-1 text-danger"></i>{{ $p['estadio'] }}</span>
            </div>
        @endif
    </div>
@empty
    <div class="p-4 text-center rounded" style="background: rgba(255,255,255,0.02); border: 1px dashed rgba(255,255,255,0.1);">
        <i class="fas fa-futbol text-muted mb-2 fs-3"></i>
        <p class="small text-muted mb-0">No hay partidos programados en vivo para esta fecha.</p>
    </div>
@endforelse
