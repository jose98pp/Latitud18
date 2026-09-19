@extends('layouts.admin')

@section('title', 'Configuración General y Streaming - Panel de Administración')
@section('page-title', 'Configuración del Medio y Streaming')

@section('content')
<div class="container-fluid py-4">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
            <i class="fas fa-check-circle me-2 fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('admin.configuracion.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-4">
            {{-- BLOQUE 1: STREAMING EN VIVO (TV & RADIO) --}}
            <div class="col-lg-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                        <h5 class="mb-0 fw-bold text-dark d-flex align-items-center">
                            <i class="fas fa-satellite-dish text-danger me-2"></i> Streaming En Vivo
                        </h5>
                        <span class="badge bg-danger text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.5px;">Live Broadcast</span>
                    </div>
                    <div class="card-body p-4">

                        {{-- Señal TV Online --}}
                        <div class="p-3 mb-4 rounded border bg-light">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <label class="form-label fw-bold mb-0 text-dark d-flex align-items-center">
                                    <i class="fas fa-tv text-primary me-2"></i> Televisión En Vivo (YouTube Live / Video)
                                </label>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" role="switch" id="streaming_tv_active" 
                                           name="streaming_tv_active" value="1" {{ ($settings['streaming_tv_active'] ?? '1') == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold small text-muted" for="streaming_tv_active">Activar Señal</label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="streaming_tv_title" class="form-label small fw-semibold text-muted">Título de la Emisión TV</label>
                                <input type="text" class="form-control" id="streaming_tv_title" name="streaming_tv_title" 
                                       value="{{ $settings['streaming_tv_title'] ?? 'UHTV En Vivo — Transmisión Digital 24/7' }}" 
                                       placeholder="Ej: Noticiero Central En Vivo">
                            </div>

                            <div class="mb-2">
                                <label for="streaming_tv_youtube_id" class="form-label small fw-semibold text-muted">
                                    ID o Enlace de YouTube Live
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fab fa-youtube text-danger"></i></span>
                                    <input type="text" class="form-control font-monospace" id="streaming_tv_youtube_id" name="streaming_tv_youtube_id" 
                                           value="{{ $settings['streaming_tv_youtube_id'] ?? '' }}" 
                                           placeholder="Ej: jfKfPfyJRdk o https://youtube.com/watch?v=...">
                                </div>
                                <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">
                                    Puedes ingresar solo el ID de 11 caracteres o la URL completa del directo en YouTube.
                                </small>
                            </div>
                        </div>

                        {{-- Señal Radio Online --}}
                        <div class="p-3 rounded border bg-light">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <label class="form-label fw-bold mb-0 text-dark d-flex align-items-center">
                                    <i class="fas fa-broadcast-tower text-success me-2"></i> Radio Online En Vivo
                                </label>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" role="switch" id="streaming_radio_active" 
                                           name="streaming_radio_active" value="1" {{ ($settings['streaming_radio_active'] ?? '1') == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold small text-muted" for="streaming_radio_active">Activar Radio</label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="streaming_radio_title" class="form-label small fw-semibold text-muted">Título de la Radio</label>
                                <input type="text" class="form-control" id="streaming_radio_title" name="streaming_radio_title" 
                                       value="{{ $settings['streaming_radio_title'] ?? 'Radio Latitud 18 FM — Señal Online' }}" 
                                       placeholder="Ej: Radio Latitud 18 FM 94.5">
                            </div>

                            <div class="mb-2">
                                <label for="streaming_radio_url" class="form-label small fw-semibold text-muted">URL del Stream de Audio (Icecast / Shoutcast / MP3 / AAC)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fas fa-volume-up text-success"></i></span>
                                    <input type="url" class="form-control font-monospace" id="streaming_radio_url" name="streaming_radio_url" 
                                           value="{{ $settings['streaming_radio_url'] ?? '' }}" 
                                           placeholder="https://stream.servidor.com/live.mp3">
                                </div>
                                <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">
                                    Enlace directo al flujo de audio HTTPS para reproducción continua en el reproductor web.
                                </small>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- BLOQUE 2: REDES SOCIALES Y WHATSAPP --}}
            <div class="col-lg-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 fw-bold text-dark d-flex align-items-center">
                            <i class="fas fa-share-nodes text-danger me-2"></i> Redes Sociales y WhatsApp
                        </h5>
                    </div>
                    <div class="card-body p-4">

                        <div class="mb-3">
                            <label for="social_facebook" class="form-label small fw-semibold text-muted">Página de Facebook</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white" style="width: 42px;"><i class="fab fa-facebook-f text-primary"></i></span>
                                <input type="url" class="form-control" id="social_facebook" name="social_facebook" 
                                       value="{{ $settings['social_facebook'] ?? '' }}" placeholder="https://facebook.com/tu-pagina">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="social_twitter" class="form-label small fw-semibold text-muted">Perfil de X / Twitter</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white" style="width: 42px;"><i class="fab fa-x-twitter text-dark"></i></span>
                                <input type="url" class="form-control" id="social_twitter" name="social_twitter" 
                                       value="{{ $settings['social_twitter'] ?? '' }}" placeholder="https://x.com/tu-cuenta">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="social_youtube" class="form-label small fw-semibold text-muted">Canal de YouTube</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white" style="width: 42px;"><i class="fab fa-youtube text-danger"></i></span>
                                <input type="url" class="form-control" id="social_youtube" name="social_youtube" 
                                       value="{{ $settings['social_youtube'] ?? '' }}" placeholder="https://youtube.com/@tu-canal">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="social_instagram" class="form-label small fw-semibold text-muted">Cuenta de Instagram</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white" style="width: 42px;"><i class="fab fa-instagram text-danger"></i></span>
                                <input type="url" class="form-control" id="social_instagram" name="social_instagram" 
                                       value="{{ $settings['social_instagram'] ?? '' }}" placeholder="https://instagram.com/tu-cuenta">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="social_tiktok" class="form-label small fw-semibold text-muted">Cuenta de TikTok</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white" style="width: 42px;"><i class="fab fa-tiktok text-dark"></i></span>
                                <input type="url" class="form-control" id="social_tiktok" name="social_tiktok" 
                                       value="{{ $settings['social_tiktok'] ?? '' }}" placeholder="https://tiktok.com/@tu-cuenta">
                            </div>
                        </div>

                        <div class="row g-2">
                            <div class="col-md-6">
                                <label for="whatsapp_phone" class="form-label small fw-semibold text-muted">WhatsApp de Denuncias / Prensa</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fab fa-whatsapp text-success"></i></span>
                                    <input type="text" class="form-control" id="whatsapp_phone" name="whatsapp_phone" 
                                           value="{{ $settings['whatsapp_phone'] ?? '' }}" placeholder="+591 70000000">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="whatsapp_message" class="form-label small fw-semibold text-muted">Mensaje Predeterminado</label>
                                <input type="text" class="form-control" id="whatsapp_message" name="whatsapp_message" 
                                       value="{{ $settings['whatsapp_message'] ?? '' }}" placeholder="Hola, quiero enviar...">
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- BLOQUE 3: CANAL OFICIAL DE YOUTUBE Y GALERÍA DE PORTADA --}}
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <h5 class="mb-0 fw-bold text-dark d-flex align-items-center">
                            <i class="fab fa-youtube text-danger me-2 fs-5"></i> Canal Oficial de YouTube y Galería de Portada
                        </h5>
                        <div class="d-flex align-items-center gap-3">
                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 text-uppercase px-2 py-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                                Galería Nativa Sin Marcas de Agua
                            </span>
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" role="switch" id="youtube_gallery_active" 
                                       name="youtube_gallery_active" value="1" {{ ($settings['youtube_gallery_active'] ?? '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold small text-muted" for="youtube_gallery_active">Mostrar en Portada</label>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="alert alert-info border-0 d-flex align-items-start mb-4" style="background-color: #f0f7ff;">
                            <i class="fas fa-circle-info text-primary me-3 mt-1 fs-5"></i>
                            <div class="small text-muted">
                                <strong class="text-dark">Configuración lista para cuando crees tu canal oficial:</strong>
                                Puedes ingresar el nombre, usuario y enlace directo de tu canal de YouTube aquí. Además, puedes destacar videos específicos o transmisiones. Si dejas la lista de videos vacía, el sistema mostrará automáticamente las últimas noticias con video y la señal en vivo configurada.
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="youtube_channel_name" class="form-label small fw-semibold text-muted">Nombre del Canal</label>
                                <input type="text" class="form-control" id="youtube_channel_name" name="youtube_channel_name" 
                                       value="{{ $settings['youtube_channel_name'] ?? 'Latitud 18 TV' }}" placeholder="Ej: Latitud 18 TV">
                            </div>

                            <div class="col-md-4">
                                <label for="youtube_channel_handle" class="form-label small fw-semibold text-muted">Handle / Usuario (@)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fas fa-at text-muted"></i></span>
                                    <input type="text" class="form-control" id="youtube_channel_handle" name="youtube_channel_handle" 
                                           value="{{ $settings['youtube_channel_handle'] ?? '@Latitud18TV' }}" placeholder="@Latitud18TV">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label for="youtube_channel_badge" class="form-label small fw-semibold text-muted">Texto del Badge / Distintivo</label>
                                <input type="text" class="form-control" id="youtube_channel_badge" name="youtube_channel_badge" 
                                       value="{{ $settings['youtube_channel_badge'] ?? 'Canal Oficial • Cobertura 24/7' }}" placeholder="Ej: Canal Oficial • Cobertura 24/7">
                            </div>

                            <div class="col-md-6">
                                <label for="youtube_channel_url" class="form-label small fw-semibold text-muted">Enlace al Canal de YouTube (Botón Suscribirse)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fab fa-youtube text-danger"></i></span>
                                    <input type="url" class="form-control font-monospace" id="youtube_channel_url" name="youtube_channel_url" 
                                           value="{{ $settings['youtube_channel_url'] ?? 'https://www.youtube.com/@UHTVBolivia' }}" placeholder="https://youtube.com/@tu-canal">
                                </div>
                                <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">
                                    Al hacer clic en "Suscribirse" o "Ver Canal", los usuarios irán a esta dirección.
                                </small>
                            </div>

                            <div class="col-md-6">
                                <label for="youtube_gallery_videos" class="form-label small fw-semibold text-muted">
                                    Videos Destacados del Canal (IDs o URLs de YouTube)
                                </label>
                                <textarea class="form-control font-monospace" id="youtube_gallery_videos" name="youtube_gallery_videos" rows="3" 
                                          placeholder="Ej: https://youtube.com/watch?v=VIDEO_ID_1, VIDEO_ID_2 (un ID o URL por línea o separados por comas)">{{ $settings['youtube_gallery_videos'] ?? '' }}</textarea>
                                <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">
                                    Opcional. Ingresa uno o varios IDs / enlaces de YouTube. Si lo dejas vacío, tomará automáticamente las noticias con video publicadas y la señal en vivo.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- BLOQUE 4: INFORMACIÓN INSTITUCIONAL, CONTACTO Y FOOTER --}}
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 fw-bold text-dark d-flex align-items-center">
                            <i class="fas fa-building text-danger me-2"></i> Información Institucional y Footer
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="site_name" class="form-label small fw-semibold text-muted">Nombre del Medio</label>
                                <input type="text" class="form-control fw-bold" id="site_name" name="site_name" 
                                       value="{{ $settings['site_name'] ?? 'Latitud 18' }}">
                            </div>
                            <div class="col-md-6">
                                <label for="site_slogan" class="form-label small fw-semibold text-muted">Eslogan / Subtítulo</label>
                                <input type="text" class="form-control" id="site_slogan" name="site_slogan" 
                                       value="{{ $settings['site_slogan'] ?? 'Periodismo Independiente, Multimedia e Investigación' }}">
                            </div>

                            <div class="col-md-4">
                                <label for="contact_email" class="form-label small fw-semibold text-muted">Correo Electrónico de Contacto</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fas fa-envelope text-muted"></i></span>
                                    <input type="email" class="form-control" id="contact_email" name="contact_email" 
                                           value="{{ $settings['contact_email'] ?? 'prensa@latitud18.bo' }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="contact_phone" class="form-label small fw-semibold text-muted">Teléfono de Redacción</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fas fa-phone text-muted"></i></span>
                                    <input type="text" class="form-control" id="contact_phone" name="contact_phone" 
                                           value="{{ $settings['contact_phone'] ?? '+591 (2) 211-4500' }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="contact_address" class="form-label small fw-semibold text-muted">Dirección Física / Oficina</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fas fa-location-dot text-muted"></i></span>
                                    <input type="text" class="form-control" id="contact_address" name="contact_address" 
                                           value="{{ $settings['contact_address'] ?? 'La Paz, Bolivia' }}">
                                </div>
                            </div>

                            <div class="col-md-8">
                                <label for="footer_about" class="form-label small fw-semibold text-muted">Texto Informativo "Acerca de Nosotros" (Footer)</label>
                                <textarea class="form-control" id="footer_about" name="footer_about" rows="3">{{ $settings['footer_about'] ?? '' }}</textarea>
                            </div>
                            <div class="col-md-4">
                                <label for="copyright_text" class="form-label small fw-semibold text-muted">Texto de Derechos de Autor (Copyright)</label>
                                <textarea class="form-control" id="copyright_text" name="copyright_text" rows="3">{{ $settings['copyright_text'] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-light py-3 d-flex justify-content-end gap-2">
                        <button type="submit" class="btn btn-danger px-4 py-2 fw-bold d-flex align-items-center">
                            <i class="fas fa-save me-2"></i> Guardar Todos los Ajustes
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

</div>
@endsection
