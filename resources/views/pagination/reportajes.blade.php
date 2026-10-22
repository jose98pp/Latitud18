@if($paginator->hasPages())
<div class="reports-pagination" role="group" aria-label="Navegar páginas">
    @if($paginator->onFirstPage())<span aria-disabled="true">← Anterior</span>@else<a href="{{ $paginator->previousPageUrl() }}" rel="prev">← Anterior</a>@endif
    @foreach($elements as $element)
        @if(is_string($element))<span>{{ $element }}</span>@else
            @foreach($element as $page => $url)
                @if($page === $paginator->currentPage())<span aria-current="page">{{ $page }}</span>@else<a href="{{ $url }}" aria-label="Página {{ $page }}">{{ $page }}</a>@endif
            @endforeach
        @endif
    @endforeach
    @if($paginator->hasMorePages())<a href="{{ $paginator->nextPageUrl() }}" rel="next">Siguiente →</a>@else<span aria-disabled="true">Siguiente →</span>@endif
</div>
@endif
