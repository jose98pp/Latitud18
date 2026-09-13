@if($news->count() > 0)
    <div class="news-cards-grid">
        @foreach($news as $noticia)
            <div class="news-card bg-white rounded-xl shadow-md overflow-hidden transition-all duration-300 hover:shadow-xl hover:transform hover:scale-105">
                <!-- Card Image -->
                <div class="news-card-image relative h-48 bg-gray-200">
                    @if(isset($noticia->image_info) && $noticia->image_info['exists'])
                        <img data-src="{{ $noticia->image_info['url'] }}" 
                             src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='300'%3E%3Crect width='100%25' height='100%25' fill='%23f3f4f6'/%3E%3C/svg%3E"
                             alt="{{ $noticia->titulo }}" 
                             class="lazy-image w-full h-full object-cover transition-opacity duration-300"
                             loading="lazy"
                             onerror="this.onerror=null; this.src='{{ asset('images/default-news.svg') }}'; this.parentElement.classList.add('bg-gray-200');">
                        @if($noticia->image_info['size'])
                            <div class="absolute top-2 left-2 bg-green-500 text-white text-xs px-2 py-1 rounded-full" title="Imagen válida">
                                <i class="fas fa-check mr-1"></i>{{ number_format($noticia->image_info['size'] / 1024, 1) }}KB
                            </div>
                        @endif
                    @else
                        <div class="w-full h-full flex items-center justify-center bg-gray-200">
                            <div class="lazy-placeholder">
                                <i class="fas fa-image text-gray-400 text-4xl"></i>
                            </div>
                        </div>
                        @if($noticia->imagen && !$noticia->has_valid_image)
                            <div class="absolute top-2 left-2 bg-red-500 text-white text-xs px-2 py-1 rounded-full" title="Imagen no encontrada">
                                <i class="fas fa-exclamation-triangle mr-1"></i>Error
                            </div>
                        @endif
                    @endif
                    
                    <!-- Status Badge -->
                    <div class="absolute top-2 right-2">
                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ $noticia->publicada ? 'bg-green-500 text-white' : 'bg-yellow-500 text-white' }}">
                            {{ $noticia->publicada ? 'Publicada' : 'Borrador' }}
                        </span>
                    </div>
                </div>

                <!-- Card Content -->
                <div class="news-card-content p-4">
                    <!-- Card Header -->
                    <div class="news-card-header flex items-center justify-between mb-2">
                        <span class="category-badge px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                            {{ $noticia->category->name ?? 'Sin Categoría' }}
                        </span>
                        <small class="text-gray-500 text-xs">
                            {{ $noticia->created_at->format('d/m/Y') }}
                        </small>
                    </div>

                    <!-- Card Title -->
                    <h5 class="news-card-title text-lg font-semibold text-gray-900 mb-2 line-clamp-2">
                        {{ $noticia->titulo }}
                    </h5>

                    <!-- Card Description -->
                    <p class="news-card-description text-sm text-gray-600 mb-4 line-clamp-3">
                        {{ Str::limit(strip_tags($noticia->contenido), 120) }}
                    </p>

                    <!-- Card Footer -->
                    <div class="news-card-footer flex items-center justify-between">
                        <div class="text-xs text-gray-500">
                            ID: {{ $noticia->id }}
                        </div>
                        
                        <!-- Actions Dropdown -->
                        <div class="actions-dropdown relative">
                            <button class="actions-trigger p-2 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100 transition-colors" 
                                    onclick="toggleDropdown({{ $noticia->id }})">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div id="dropdown-{{ $noticia->id }}" class="actions-menu absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10 hidden">
                                <div class="py-1">
                                    @if($noticia->publicada)
                                        <a href="{{ route('show', $noticia->id) }}" 
                                           target="_blank"
                                           class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <i class="fas fa-eye mr-3 text-blue-500"></i>
                                            Ver noticia
                                        </a>
                                    @endif
                                    <a href="{{ route('admin.noticias.edit', $noticia->id) }}" 
                                       class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-edit mr-3 text-indigo-500"></i>
                                        Editar
                                    </a>
                                    <form action="{{ route('admin.noticias.destroy', $noticia->id) }}" 
                                          method="POST" 
                                          class="inline w-full"
                                          onsubmit="return confirm('¿Estás seguro de eliminar esta noticia?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <i class="fas fa-trash mr-3 text-red-500"></i>
                                            Eliminar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="text-center py-12">
        <i class="fas fa-search text-gray-400 text-6xl mb-4"></i>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No se encontraron noticias</h3>
        <p class="text-gray-500 mb-6">Intenta ajustar los filtros de búsqueda.</p>
        <button onclick="clearAllFilters()" 
                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition">
            <i class="fas fa-times-circle mr-2"></i>
            Limpiar Filtros
        </button>
    </div>
@endif