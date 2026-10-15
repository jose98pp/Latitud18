@php
    $state = $feed['status'] ?? 'unavailable';
    $stamp = !empty($feed['updated_at']) ? \Carbon\Carbon::parse($feed['updated_at'])->timezone('America/La_Paz')->format('d/m H:i') : null;
@endphp
<p class="small mb-2 {{ in_array($state, ['stale', 'unavailable']) ? 'text-warning' : 'text-muted' }}" role="status">
    @if($state === 'unavailable')
        Datos temporalmente no disponibles. Inténtalo de nuevo más tarde.
    @elseif($state === 'stale')
        No fue posible actualizar la fuente. Se muestra la última información recibida, sin actualización en directo.
    @else
        Fuente: {{ $feed['source'] ?? '' }}.
    @endif
    @if($stamp) <span>Actualizado: {{ $stamp }} · Bolivia.</span> @endif
</p>
