{{-- =========================================================================
     6. ESPACIO PUBLICITARIO DINÁMICO & EXPLORADOR DE SECCIONES
     ========================================================================= --}}
@php
  $bannerMiddle = null;
  if (isset($banners)) {
    $bannerMiddle = ($banners['portada_middle'] ?? collect())->first() 
                 ?? ($banners['footer'] ?? collect())->first()
                 ?? ($banners['home_middle'] ?? collect())->first();
  }
@endphp
<div class="ad-banner-strip-block">
  @if($bannerMiddle)
    ESPACIO PUBLICITARIO • {{ strtoupper($bannerMiddle->title) }}
    <div style="margin-top:8px;">
      <a href="{{ $bannerMiddle->link ?: '#' }}" @if($bannerMiddle->link) target="_blank" @endif style="display:inline-block; max-width:970px; width:100%;">
        <img src="{{ asset($bannerMiddle->image_path) }}" alt="{{ $bannerMiddle->title }}"
             style="width:100%; max-height:180px; object-fit:contain; border-radius:2px; box-shadow:0 2px 8px rgba(0,0,0,0.08);" loading="lazy">
      </a>
    </div>
  @else
    ESPACIO PUBLICITARIO • 970 x 90
    <div style="margin-top:8px;">
      <a href="https://radiobetania.com/" target="_blank" style="display:inline-block; max-width:800px; width:100%;">
        <img src="{{ asset('images/betania.jpg') }}" alt="Publicidad Betania"
             style="width:100%; border-radius:2px; box-shadow:0 2px 8px rgba(0,0,0,0.08);" loading="lazy">
      </a>
    </div>
  @endif
</div>

{{-- =========================================================================
     7. NEWSLETTER HIGHLIGHT BANNER (PORTADA)
     ========================================================================= --}}
<div style="background:linear-gradient(135deg, var(--color-navy) 0%, var(--color-navy-dark) 100%); border-radius:4px; border-left:5px solid var(--color-red); padding:32px 28px; margin-bottom:32px; box-shadow:0 4px 12px rgba(0,0,0,0.08); color:#fff;">
  <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:24px;">
    <div style="flex:1; min-width:280px;">
      <div style="display:inline-flex; align-items:center; gap:8px; background:rgba(215,25,32,0.2); border:1px solid rgba(215,25,32,0.4); padding:4px 10px; border-radius:2px; font-family:var(--font-title-montserrat); font-weight:800; font-size:0.68rem; color:#fff; text-transform:uppercase; margin-bottom:10px;">
        <i class="fas fa-paper-plane text-danger" style="color:var(--color-red);"></i> Boletín Exclusivo
      </div>
      <h3 style="font-family:var(--font-title-bebas); font-size:2.2rem; color:#fff; line-height:1; margin-bottom:6px;">
        SUSCRÍBETE A LAS NOTICIAS SIN RUIDO
      </h3>
      <p style="font-size:0.85rem; color:#94A3B8; margin:0; max-width:600px; line-height:1.5;">
        Recibe cada mañana nuestro resumen informativo de Santa Cruz, Bolivia y el mundo seleccionado por periodistas independientes.
      </p>
    </div>

    <div style="flex:1; min-width:300px; max-width:480px;">
      <form onsubmit="handleNewsletterSubmit(event, 'Portada')" data-msg-target="portada-newsletter-msg" style="display:flex; gap:8px; flex-wrap:nowrap;">
        <input type="email" required placeholder="Ingresa tu correo electrónico..." style="flex:1; padding:12px 14px; background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.25); color:#fff; border-radius:2px; font-size:0.88rem; outline:none;">
        <button type="submit" style="background:var(--color-red); color:#fff; font-family:var(--font-title-montserrat); font-weight:800; font-size:0.75rem; text-transform:uppercase; padding:12px 20px; border:none; border-radius:2px; cursor:pointer; white-space:nowrap; transition:background 0.2s;" onmouseover="this.style.background='var(--color-red-dark)'" onmouseout="this.style.background='var(--color-red)'">
          Suscribirme
        </button>
      </form>
      <div id="portada-newsletter-msg" style="display:none; margin-top:10px; font-size:0.82rem; padding:8px 12px; border-radius:2px;"></div>
    </div>
  </div>
</div>

{{-- Explorador de todas las categorías en píldoras --}}
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
