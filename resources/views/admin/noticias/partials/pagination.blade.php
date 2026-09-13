@if($news->hasPages())
    <div class="enhanced-pagination-wrapper">
        <div class="pagination-info">
            <span class="pagination-text">
                Mostrando {{ $news->firstItem() }} - {{ $news->lastItem() }} de {{ $news->total() }} noticias
            </span>
        </div>
        
        <nav class="enhanced-pagination" aria-label="Navegación de páginas">
            <ul class="pagination pagination-lg justify-content-center">
                {{-- First Page Link --}}
                @if ($news->currentPage() > 3)
                    <li class="page-item">
                        <a class="page-link" href="{{ $news->url(1) }}" aria-label="Primera página" title="Primera página">
                            <i class="fas fa-angle-double-left"></i>
                            <span class="d-none d-sm-inline">Primera</span>
                        </a>
                    </li>
                @endif

                {{-- Previous Page Link --}}
                @if ($news->onFirstPage())
                    <li class="page-item disabled">
                        <span class="page-link" aria-label="Página anterior">
                            <i class="fas fa-angle-left"></i>
                            <span class="d-none d-sm-inline">Anterior</span>
                        </span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $news->previousPageUrl() }}" aria-label="Página anterior" title="Página anterior">
                            <i class="fas fa-angle-left"></i>
                            <span class="d-none d-sm-inline">Anterior</span>
                        </a>
                    </li>
                @endif

                {{-- Pagination Elements --}}
                @php
                    $start = max(1, $news->currentPage() - 2);
                    $end = min($news->lastPage(), $news->currentPage() + 2);
                    
                    // Ensure we always show at least 5 pages when possible
                    if ($end - $start < 4) {
                        if ($start == 1) {
                            $end = min($news->lastPage(), $start + 4);
                        } else {
                            $start = max(1, $end - 4);
                        }
                    }
                @endphp

                {{-- Show dots if there's a gap at the beginning --}}
                @if ($start > 1)
                    <li class="page-item">
                        <a class="page-link" href="{{ $news->url(1) }}">1</a>
                    </li>
                    @if ($start > 2)
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                    @endif
                @endif

                {{-- Page Number Links --}}
                @for ($page = $start; $page <= $end; $page++)
                    @if ($page == $news->currentPage())
                        <li class="page-item active" aria-current="page">
                            <span class="page-link">
                                {{ $page }}
                                <span class="sr-only">(página actual)</span>
                            </span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $news->url($page) }}" title="Ir a página {{ $page }}">{{ $page }}</a>
                        </li>
                    @endif
                @endfor

                {{-- Show dots if there's a gap at the end --}}
                @if ($end < $news->lastPage())
                    @if ($end < $news->lastPage() - 1)
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                    @endif
                    <li class="page-item">
                        <a class="page-link" href="{{ $news->url($news->lastPage()) }}">{{ $news->lastPage() }}</a>
                    </li>
                @endif

                {{-- Next Page Link --}}
                @if ($news->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="{{ $news->nextPageUrl() }}" aria-label="Página siguiente" title="Página siguiente">
                            <span class="d-none d-sm-inline">Siguiente</span>
                            <i class="fas fa-angle-right"></i>
                        </a>
                    </li>
                @else
                    <li class="page-item disabled">
                        <span class="page-link" aria-label="Página siguiente">
                            <span class="d-none d-sm-inline">Siguiente</span>
                            <i class="fas fa-angle-right"></i>
                        </span>
                    </li>
                @endif

                {{-- Last Page Link --}}
                @if ($news->currentPage() < $news->lastPage() - 2)
                    <li class="page-item">
                        <a class="page-link" href="{{ $news->url($news->lastPage()) }}" aria-label="Última página" title="Última página">
                            <span class="d-none d-sm-inline">Última</span>
                            <i class="fas fa-angle-double-right"></i>
                        </a>
                    </li>
                @endif
            </ul>
        </nav>

        {{-- Page Size Selector --}}
        <div class="pagination-controls">
            <div class="page-size-selector">
                <label for="page-size" class="form-label">Mostrar:</label>
                <select id="page-size" class="form-select form-select-sm" onchange="changePageSize(this.value)">
                    <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                </select>
                <span class="form-text">por página</span>
            </div>

            {{-- Quick Jump --}}
            <div class="page-jump">
                <label for="page-jump-input" class="form-label">Ir a página:</label>
                <div class="input-group input-group-sm">
                    <input type="number" 
                           id="page-jump-input" 
                           class="form-control" 
                           min="1" 
                           max="{{ $news->lastPage() }}" 
                           placeholder="{{ $news->currentPage() }}"
                           style="width: 80px;">
                    <button class="btn btn-outline-primary" type="button" onclick="jumpToPage()">
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Styles are loaded from enhanced-pagination.css --}}

    {{-- Pagination functionality is handled by simple-pagination.js --}}
@endif