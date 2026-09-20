{!! '<' . '?xml version="1.0" encoding="UTF-8"?' . '>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:news="http://www.google.com/schemas/sitemap-news/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">

    {{-- Portada Principal --}}
    <url>
        <loc>{{ route('portada') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
        <changefreq>always</changefreq>
        <priority>1.0</priority>
    </url>

    {{-- Periódico Digital --}}
    <url>
        <loc>{{ route('periodico.public.index') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.9</priority>
    </url>

    {{-- Portal Deportivo Contra Ataque --}}
    <url>
        <loc>{{ route('contraataque.index') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
        <changefreq>hourly</changefreq>
        <priority>0.9</priority>
    </url>

    {{-- Sección de Opinión --}}
    <url>
        <loc>{{ route('opinion.index') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>0.8</priority>
    </url>

    {{-- Categorías --}}
    @foreach($categorias as $categoria)
        <url>
            <loc>{{ route('categoria.noticias', $categoria->slug) }}</loc>
            <lastmod>{{ $categoria->updated_at ? $categoria->updated_at->toAtomString() : now()->toAtomString() }}</lastmod>
            <changefreq>hourly</changefreq>
            <priority>0.8</priority>
        </url>
    @endforeach

    {{-- Columnistas --}}
    @foreach($columnistas as $columnista)
        <url>
            <loc>{{ route('opinion.columnista', $columnista->id) }}</loc>
            <lastmod>{{ $columnista->updated_at ? $columnista->updated_at->toAtomString() : now()->toAtomString() }}</lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.7</priority>
        </url>
    @endforeach

    {{-- Artículos de Opinión --}}
    @foreach($articulosOpinion as $articulo)
        <url>
            <loc>{{ route('opinion.articulo', $articulo->id) }}</loc>
            <lastmod>{{ $articulo->updated_at ? $articulo->updated_at->toAtomString() : now()->toAtomString() }}</lastmod>
            <changefreq>monthly</changefreq>
            <priority>0.7</priority>
        </url>
    @endforeach

    {{-- Noticias Publicadas con Formato Google News --}}
    @foreach($noticias as $noticia)
        <url>
            <loc>{{ $noticia->url }}</loc>
            <lastmod>{{ $noticia->updated_at ? $noticia->updated_at->toAtomString() : now()->toAtomString() }}</lastmod>
            <changefreq>daily</changefreq>
            <priority>0.9</priority>
            @if($noticia->has_valid_image)
                <image:image>
                    <image:loc>{{ $noticia->imagenUrl }}</image:loc>
                    <image:title>{{ $noticia->titulo }}</image:title>
                </image:image>
            @endif
        </url>
    @endforeach

</urlset>
