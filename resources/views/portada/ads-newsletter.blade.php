{{-- =========================================================================
     6. ESPACIO PUBLICITARIO DINÃMICO & EXPLORADOR DE SECCIONES
     ========================================================================= --}}
@php
  $bannerMiddle = null;
  if (isset($banners)) {
    $bannerMiddle = ($banners['portada_middle'] ?? collect())->first()
                 ?? ($banners['footer'] ?? collect())->first()
                 ?? ($banners['home_middle'] ?? collect())->first();
  }
@endphp
@if($bannerMiddle)
  <x-ad-slot location="portada_middle" :max-width="970" label="Espacio Publicitario" :banners="$banners ?? null" />
@else
<div class="ad-banner-strip-block">
  ESPACIO PUBLICITARIO â€¢ 970 x 90
  <div style="margin-top:8px;">
    <a href="https://radiobetania.com/" target="_blank" rel="noopener sponsored" style="display:inline-block; max-width:800px; width:100%;">
      <img src="{{ asset('images/betania.jpg') }}" alt="Publicidad Betania"
           style="width:100%; border-radius:2px; box-shadow:0 2px 8px rgba(0,0,0,0.08);" loading="lazy">
    </a>
  </div>
</div>
@endif

{{-- Explorador de todas las categorÃ­as en pÃ­ldoras --}}
<div style="text-align:center; padding: 20px 0 36px; border-top: 1px solid var(--color-border); margin-bottom: 20px;">
  <h3 style="font-family:var(--font-title-montserrat); font-weight:900; font-size:0.82rem; text-transform:uppercase; letter-spacing:1.5px; color:var(--color-navy); margin-bottom:14px;">
    Explora Todas las Secciones
  </h3>
  <div style="display:flex; flex-wrap:wrap; justify-content:center; gap:8px;">
    @foreach($categorias as $cat)
      <a href="{{ route('categoria.noticias', $cat->slug) }}"
         style="font-family:var(--font-title-montserrat); font-weight:700; font-size:0.72rem; text-transform:uppercase; letter-spacing:0.5px; color:var(--color-navy); background:var(--color-navy-subtle); padding:6px 16px; border-radius:2px; text-decoration:none; border:1px solid var(--color-border); transition:all 0.2s;"
         onmouseover="this.style.background='var(--color-red)';this.style.color='#fff';this.style.borderColor='var(--color-red)'"
         onmouseout="this.style.background='var(--color-navy-subtle)';this.style.color='var(--color-navy)';this.style.borderColor='var(--color-border)'">
        {{ $cat->name }}
      </a>
    @endforeach
  </div>
</div>
