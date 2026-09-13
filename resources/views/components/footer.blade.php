<!-- FOOTER -->
<footer class="site-main-footer" style="background:var(--color-navy);border-top:4px solid var(--color-red);color:#94A3B8;padding:48px 0 24px;transition:background .3s">
  <div class="container">
    <div style="display:grid;grid-template-columns:1.8fr 1.2fr 1.2fr 1fr 1.5fr;gap:32px">
      <div>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px">
          <div><span style="font-family:var(--font-title-anton);font-size:1.5rem;color:#fff">{{ strtoupper(setting('site_name', 'LATITUD 18')) }}</span></div>
        </div>
        <p style="font-size:.82rem;line-height:1.6;margin-bottom:16px;color:#94A3B8">
          {{ setting('footer_about', 'Portal de noticias 24/7. Información sin ruido para Bolivia y el mundo.') }}
        </p>
        <div style="display:flex;gap:8px">
          @if(setting('social_facebook'))
            <a href="{{ setting('social_facebook') }}" target="_blank" style="width:34px;height:34px;background:rgba(255,255,255,.08);border-radius:4px;display:flex;align-items:center;justify-content:center;color:#94A3B8;text-decoration:none;transition:all .2s" onmouseover="this.style.background='var(--color-red)';this.style.color='#fff'" onmouseout="this.style.background='rgba(255,255,255,.08)';this.style.color='#94A3B8'"><i class="fab fa-facebook-f" style="font-size:.8rem"></i></a>
          @endif
          @if(setting('social_twitter'))
            <a href="{{ setting('social_twitter') }}" target="_blank" style="width:34px;height:34px;background:rgba(255,255,255,.08);border-radius:4px;display:flex;align-items:center;justify-content:center;color:#94A3B8;text-decoration:none;transition:all .2s" onmouseover="this.style.background='var(--color-red)';this.style.color='#fff'" onmouseout="this.style.background='rgba(255,255,255,.08)';this.style.color='#94A3B8'"><i class="fab fa-x-twitter" style="font-size:.8rem"></i></a>
          @endif
          @if(setting('social_instagram'))
            <a href="{{ setting('social_instagram') }}" target="_blank" style="width:34px;height:34px;background:rgba(255,255,255,.08);border-radius:4px;display:flex;align-items:center;justify-content:center;color:#94A3B8;text-decoration:none;transition:all .2s" onmouseover="this.style.background='var(--color-red)';this.style.color='#fff'" onmouseout="this.style.background='rgba(255,255,255,.08)';this.style.color='#94A3B8'"><i class="fab fa-instagram" style="font-size:.8rem"></i></a>
          @endif
          @if(setting('social_youtube'))
            <a href="{{ setting('social_youtube') }}" target="_blank" style="width:34px;height:34px;background:rgba(255,255,255,.08);border-radius:4px;display:flex;align-items:center;justify-content:center;color:#94A3B8;text-decoration:none;transition:all .2s" onmouseover="this.style.background='var(--color-red)';this.style.color='#fff'" onmouseout="this.style.background='rgba(255,255,255,.08)';this.style.color='#94A3B8'"><i class="fab fa-youtube" style="font-size:.8rem"></i></a>
          @endif
          @if(setting('whatsapp_phone'))
            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', setting('whatsapp_phone')) }}?text={{ urlencode(setting('whatsapp_message', 'Hola')) }}" target="_blank" style="width:34px;height:34px;background:rgba(255,255,255,.08);border-radius:4px;display:flex;align-items:center;justify-content:center;color:#25D366;text-decoration:none;transition:all .2s" onmouseover="this.style.background='#25D366';this.style.color='#fff'" onmouseout="this.style.background='rgba(255,255,255,.08)';this.style.color='#25D366'"><i class="fab fa-whatsapp" style="font-size:.85rem"></i></a>
          @endif
        </div>
      </div>
      <div>
        <h4 style="font-family:var(--font-title-montserrat);font-weight:900;color:#fff;font-size:.75rem;text-transform:uppercase;letter-spacing:1.5px;margin-bottom:16px">Secciones</h4>
        <ul style="list-style:none;padding:0;margin:0">
          @forelse($categorias ?? [] as $categoria)
            <li style="margin-bottom:8px"><a href="{{ route('categoria.noticias', $categoria->id) }}" style="color:#94A3B8;text-decoration:none;font-size:.82rem;transition:color .2s" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#94A3B8'">{{ $categoria->name }}</a></li>
          @empty
            @foreach(['Política','País','Santa Cruz','Economía','Deportes'] as $cat)
              <li style="margin-bottom:8px"><a href="#" style="color:#94A3B8;text-decoration:none;font-size:.82rem">{{ $cat }}</a></li>
            @endforeach
          @endforelse
        </ul>
      </div>
      <div>
        <h4 style="font-family:var(--font-title-montserrat);font-weight:900;color:#fff;font-size:.75rem;text-transform:uppercase;letter-spacing:1.5px;margin-bottom:16px">Editorial & Especiales</h4>
        <ul style="list-style:none;padding:0;margin:0">
          <li style="margin-bottom:8px"><a href="{{ route('contraataque.index') }}" style="color:#00FF87;font-weight:700;text-decoration:none;font-size:.82rem;transition:color .2s" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#00FF87'"><i class="fas fa-bolt" style="color:#FF3B30;margin-right:4px"></i> Contra Ataque (Deportes)</a></li>
          <li style="margin-bottom:8px"><a href="{{ route('opinion.index') }}" style="color:#94A3B8;text-decoration:none;font-size:.82rem" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#94A3B8'">Opinión y Columnistas</a></li>
          <li style="margin-bottom:8px"><a href="{{ route('periodico.public.index') }}" style="color:#94A3B8;text-decoration:none;font-size:.82rem" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#94A3B8'">Periódico Semanal</a></li>
          <li style="margin-bottom:8px"><a href="{{ route('search') }}" style="color:#94A3B8;text-decoration:none;font-size:.82rem" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#94A3B8'">Buscador de Archivo</a></li>
        </ul>
      </div>
      <div>
        <h4 style="font-family:var(--font-title-montserrat);font-weight:900;color:#fff;font-size:.75rem;text-transform:uppercase;letter-spacing:1.5px;margin-bottom:16px">En Vivo</h4>
        <button onclick="openLiveModal()" style="display:inline-flex;align-items:center;gap:6px;background:var(--color-red);color:#fff;font-family:var(--font-title-montserrat);font-weight:800;font-size:.7rem;text-transform:uppercase;padding:8px 16px;border-radius:2px;border:none;cursor:pointer;margin-bottom:12px"><span style="width:6px;height:6px;background:#fff;border-radius:50%;animation:pulse 1.5s infinite"></span> EN VIVO</button>
        <p style="font-size:.78rem;color:#64748B;line-height:1.5">Transmisión multimedia digital 24/7.</p>
      </div>
      <div>
        <h4 style="font-family:var(--font-title-montserrat);font-weight:900;color:#fff;font-size:.75rem;text-transform:uppercase;letter-spacing:1.5px;margin-bottom:16px">Contacto & Redacción</h4>
        <div style="font-size:.82rem;line-height:1.8">
          <p><i class="fas fa-map-marker-alt" style="color:var(--color-red);margin-right:6px"></i> {{ setting('contact_address', 'Santa Cruz de la Sierra, Bolivia') }}</p>
          <p><i class="fas fa-envelope" style="color:var(--color-red);margin-right:6px"></i> {{ setting('contact_email', 'info@latitud18.com') }}</p>
          <p><i class="fas fa-phone" style="color:var(--color-red);margin-right:6px"></i> {{ setting('contact_phone', '+591 (2) 211-4500') }}</p>
        </div>
      </div>
    </div>

    {{-- Newsletter en Footer --}}
    @include('components.newsletter')

    <div style="border-top:1px solid rgba(255,255,255,.08);margin-top:24px;padding-top:16px;text-align:center;font-size:.75rem;color:#64748B">
      {{ setting('copyright_text', '© ' . date('Y') . ' LATITUD 18 / UHTV Bolivia. Todos los derechos reservados.') }}
    </div>
  </div>
</footer>
