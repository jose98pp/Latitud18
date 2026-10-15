@include('contraataque.partials.feed-status', ['feed' => $standingsFeed ?? []])
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-2" style="font-size: 0.82rem; vertical-align: middle;">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--ca-volt); font-family: var(--ca-font-display); font-size: 0.85rem; color: #94A3B8;">
                                <th style="width: 35px;">#</th>
                                <th>CLUB</th>
                                <th class="text-center">PJ</th>
                                <th class="text-center">G</th>
                                <th class="text-center">E</th>
                                <th class="text-center">P</th>
                                <th class="text-center">DG</th>
                                <th class="text-center" style="color: var(--ca-volt); font-size: 0.95rem;">PTS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tablaPosiciones ?? [] as $row)
                                <tr style="border-bottom: 1px solid rgba(255,255,255,0.06);">
                                    <td>
                                        @if($row['zona'] === 'libertadores')
                                            <span class="badge" style="background: #16A34A; width: 22px; padding: 3px 0;">{{ $row['pos'] }}</span>
                                        @elseif($row['zona'] === 'sudamericana')
                                            <span class="badge" style="background: #0284C7; width: 22px; padding: 3px 0;">{{ $row['pos'] }}</span>
                                        @else
                                            <span class="text-muted fw-bold ps-1">{{ $row['pos'] }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if(!empty($row['logo']))
                                                <img loading="lazy" decoding="async" src="{{ $row['logo'] }}" alt="{{ $row['club'] }}" style="width: 20px; height: 20px; object-fit: contain;" class="me-2" onerror="this.style.display='none'">
                                            @endif
                                            <strong class="text-white">{{ $row['club'] }}</strong>
                                            @if(!empty($row['group'])) <small class="text-muted ms-1">{{ $row['group'] }}</small> @endif
                                        </div>
                                    </td>
                                    <td class="text-center">{{ $row['pj'] }}</td>
                                    <td class="text-center text-muted">{{ $row['g'] }}</td>
                                    <td class="text-center text-muted">{{ $row['e'] }}</td>
                                    <td class="text-center text-muted">{{ $row['p'] }}</td>
                                    <td class="text-center fw-bold {{ str_contains($row['dg'], '+') ? 'text-success' : 'text-danger' }}">{{ $row['dg'] }}</td>
                                    <td class="text-center fw-bold" style="color: var(--ca-volt); font-size: 1rem;">{{ $row['pts'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-muted py-4">No hay posiciones disponibles para esta liga.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

