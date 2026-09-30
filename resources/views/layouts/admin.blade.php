<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel Admin - Latitud18')</title>
    
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Fonts: Montserrat + Anton -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Bebas+Neue&family=Montserrat:wght@400;500;600;700;800&family=Source+Sans+3:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS personalizado -->
    <link href="{{ asset('css/optimized.css') }}" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Script de inicialización inmediata para modo oscuro -->
    <script>
        (function() {
            try {
                const DARK_MODE_KEY = 'latitud18-admin-theme';
                const savedTheme = localStorage.getItem(DARK_MODE_KEY);
                const systemPrefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                
                const shouldBeDark = savedTheme === 'dark' || (!savedTheme && systemPrefersDark);
                
                if (shouldBeDark) {
                    document.documentElement.classList.add('dark');
                    if (document.body) {
                        document.body.classList.add('dark');
                    }
                    document.documentElement.style.colorScheme = 'dark';
                    document.documentElement.setAttribute('data-bs-theme', 'dark');
                } else {
                    document.documentElement.classList.remove('dark');
                    if (document.body) {
                        document.body.classList.remove('dark');
                    }
                    document.documentElement.style.colorScheme = 'light';
                    document.documentElement.setAttribute('data-bs-theme', 'light');
                }
                
                window.darkModeImmediateInit = true;
                window.darkModeInitialState = shouldBeDark;
                
            } catch (e) {
                console.warn('Error in immediate dark mode initialization:', e);
            }
        })();
    </script>
    
    <style>
        :root {
            --lat-red: #D71920;
            --lat-navy: #0B1F3A;
            --lat-navy-dark: #071322;
            --lat-bg: #F0F2F5;
            --lat-bg-card: #FFFFFF;
            --lat-text: #1A1A2E;
            --lat-text-muted: #6B7280;
            --lat-border: #E5E7EB;
        }

        .sidebar-transition {
            transition: transform 0.3s ease-in-out;
        }
        
        .admin-sidebar {
            background: linear-gradient(180deg, var(--lat-navy) 0%, var(--lat-navy-dark) 100%);
            box-shadow: 4px 0 20px rgba(11, 31, 58, 0.15);
        }
        
        .admin-sidebar .logo-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--lat-red);
            color: #FFF;
            font-family: 'Anton', sans-serif;
            font-size: 1.1rem;
            letter-spacing: 1px;
            border-radius: 4px;
            padding: 2px 6px;
            line-height: 1;
        }
        
        .nav-link-admin {
            transition: all 0.2s ease;
            border-radius: 8px;
            margin: 2px 0;
            font-family: 'Montserrat', sans-serif;
            font-weight: 500;
            font-size: 0.9rem;
        }
        
        .nav-link-admin:hover {
            background: rgba(215, 25, 32, 0.15);
            color: #fff !important;
        }
        
        .nav-link-admin.active {
            background: rgba(215, 25, 32, 0.25);
            border-left: 3px solid var(--lat-red);
            font-weight: 600;
        }
        
        .admin-header {
            background: var(--lat-bg-card);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--lat-border);
        }
        
        .stat-card-mini {
            background: var(--lat-bg);
            border-radius: 10px;
            border: 1px solid var(--lat-border);
            transition: all 0.3s ease;
        }
        
        .stat-card-mini:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(11, 31, 58, 0.08);
        }
        
        .user-avatar {
            background: rgba(215, 25, 32, 0.15);
            border: 2px solid rgba(215, 25, 32, 0.3);
            color: var(--lat-red);
        }
        
        .btn-latitud {
            background: var(--lat-red);
            border: none;
            color: #FFF;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        
        .btn-latitud:hover {
            background: #b8141b;
            color: #FFF;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(215, 25, 32, 0.3);
        }
        
        .page-title {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            color: var(--lat-text);
        }

        /* Dark mode */
        html[data-bs-theme="dark"] {
            --lat-bg: #080E18;
            --lat-bg-card: #0D1726;
            --lat-text: #E8EAF0;
            --lat-text-muted: #94A3B8;
            --lat-border: #1E293B;
        }
        
        html[data-bs-theme="dark"] .admin-header {
            background: var(--lat-bg-card);
            border-bottom-color: var(--lat-border);
        }
        
        html[data-bs-theme="dark"] .stat-card-mini {
            background: var(--lat-bg);
            border-color: var(--lat-border);
        }

        @media (max-width: 768px) {
            .admin-sidebar {
                transform: translateX(-100%);
            }
            
            .admin-sidebar.show {
                transform: translateX(0);
            }
        }
    </style>
    
    @stack('styles')
</head>
<body class="bg-light">
    <div class="d-flex" style="min-height: 100vh;">
        <!-- Sidebar -->
        <div id="sidebar" class="admin-sidebar text-white sidebar-transition position-relative" style="min-width: 256px; min-height: 100vh; display: flex; flex-direction: column;">
            <!-- Logo -->
            <div class="p-4 border-bottom border-light border-opacity-25">
                <div class="d-flex align-items-center">
                    <div class="user-avatar rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
                        <span class="logo-badge" style="font-size: 1.4rem; padding: 4px 8px;">18</span>
                    </div>
                    <div>
                        <h1 class="h5 mb-0 fw-bold" style="font-family: 'Anton', sans-serif; letter-spacing: 1px;">
                            LATITUD<span class="logo-badge" style="font-size: 0.85rem; padding: 1px 5px; margin-left: 2px;">18</span>
                        </h1>
                        <p class="small mb-0 opacity-75" style="font-family: 'Montserrat', sans-serif;">Panel de Control</p>
                    </div>
                </div>
            </div>
            
            <!-- Navigation -->
            <nav class="flex-fill p-3">
                <ul class="list-unstyled">
                    <li>
                        <a href="{{ route('admin.dashboard') }}" 
                           class="nav-link-admin d-flex align-items-center text-white text-decoration-none p-3 {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <i class="fas fa-tachometer-alt me-3"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.noticias.index') }}" 
                           class="nav-link-admin d-flex align-items-center text-white text-decoration-none p-3 {{ request()->routeIs('admin.noticias.*') ? 'active' : '' }}">
                            <i class="fas fa-newspaper me-3"></i>
                            <span>Noticias</span>
                            @php
                                // \Throwable cubre Error (p.ej. user null) y Exception (DB/caixa)
                                try {
                                    $noticiasCount = auth()->user()?->noticias()->count() ?? 0;
                                } catch (\Throwable $e) {
                                    $noticiasCount = 0;
                                }
                            @endphp
                            @if($noticiasCount > 0)
                                <span class="badge bg-danger ms-auto">{{ $noticiasCount }}</span>
                            @endif
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.categorias.index') }}" 
                           class="nav-link-admin d-flex align-items-center text-white text-decoration-none p-3 {{ request()->routeIs('admin.categorias.*') ? 'active' : '' }}">
                            <i class="fas fa-tags me-3"></i>
                            <span>Categorías</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.banners.index') }}" 
                           class="nav-link-admin d-flex align-items-center text-white text-decoration-none p-3 {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}">
                            <i class="fas fa-images me-3"></i>
                            <span>Banners</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.opinion.index') }}" 
                           class="nav-link-admin d-flex align-items-center text-white text-decoration-none p-3 {{ request()->routeIs('admin.opinion.*') ? 'active' : '' }}">
                            <i class="fas fa-pen-nib me-3"></i>
                            <span>Opinión & Columnistas</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.comentarios.index') }}" 
                           class="nav-link-admin d-flex align-items-center text-white text-decoration-none p-3 {{ request()->routeIs('admin.comentarios.*') ? 'active' : '' }}">
                            <i class="fas fa-comments me-3"></i>
                            <span>Comentarios</span>
                            @php
                                try {
                                    $pendientesCount = \App\Models\Comentario::where('aprobado', false)->count();
                                } catch (\Throwable $e) {
                                    $pendientesCount = 0;
                                }
                            @endphp
                            @if($pendientesCount > 0)
                                <span class="badge bg-warning text-dark ms-auto" style="font-size: 0.7rem;">{{ $pendientesCount }}</span>
                            @endif
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.periodico.index') }}" 
                           class="nav-link-admin d-flex align-items-center text-white text-decoration-none p-3 {{ request()->routeIs('admin.periodico.*') ? 'active' : '' }}">
                            <i class="fas fa-file-pdf me-3"></i>
                            <span>Periódico Digital</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.newsletter.index') }}" 
                           class="nav-link-admin d-flex align-items-center text-white text-decoration-none p-3 {{ request()->routeIs('admin.newsletter.*') ? 'active' : '' }}">
                            <i class="fas fa-envelope-open-text me-3"></i>
                            <span>Boletín / Suscriptores</span>
                            @php
                                try {
                                    $newsletterCount = \App\Models\NewsletterSubscriber::where('activo', true)->count();
                                } catch (\Throwable $e) {
                                    $newsletterCount = 0;
                                }
                            @endphp
                            @if($newsletterCount > 0)
                                <span class="badge bg-info text-dark ms-auto" style="font-size: 0.7rem;">{{ $newsletterCount }}</span>
                            @endif
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.configuracion.index') }}" 
                           class="nav-link-admin d-flex align-items-center text-white text-decoration-none p-3 {{ request()->routeIs('admin.configuracion.*') ? 'active' : '' }}">
                            <i class="fas fa-sliders-h me-3"></i>
                            <span>Ajustes & Streaming</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.profile.index') }}" 
                           class="nav-link-admin d-flex align-items-center text-white text-decoration-none p-3 {{ request()->routeIs('admin.profile.*') ? 'active' : '' }}">
                            <i class="fas fa-user-cog me-3"></i>
                            <span>Mi Perfil</span>
                        </a>
                    </li>
                    <li class="mt-3 pt-3 border-top border-light border-opacity-25">
                        <a href="{{ route('portada') }}" 
                           class="nav-link-admin d-flex align-items-center text-white text-decoration-none p-3"
                           target="_blank">
                            <i class="fas fa-external-link-alt me-3"></i>
                            <span>Ver Sitio Web</span>
                        </a>
                    </li>
                </ul>
            </nav>
            
            <!-- User Info & Logout -->
            <div class="p-4 border-top border-light border-opacity-25 mt-auto">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="user-avatar rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <p class="small mb-0 fw-medium">{{ auth()->user()->name ?? 'Admin' }}</p>
                            <p class="small mb-0 opacity-75">
                                <i class="fas fa-circle text-success me-1" style="font-size: 0.5rem;"></i>
                                En línea
                            </p>
                        </div>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-link text-white p-0" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.profile.index') }}">
                                    <i class="fas fa-user me-2"></i>Mi Perfil
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}" class="d-inline w-100">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Bar -->
            <header class="admin-header shadow-sm p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <button id="sidebarToggle" class="btn btn-link d-lg-none text-muted p-0 me-3">
                            <i class="fas fa-bars fa-lg"></i>
                        </button>
                        <div>
                            <h2 class="h4 mb-0 text-dark fw-bold">
                                @yield('page-title', 'Panel de Administración')
                            </h2>
                            <p class="small text-muted mb-0">
                                <i class="fas fa-calendar me-1"></i>
                                {{ now()->format('l, d \d\e F \d\e Y') }}
                            </p>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center gap-3">
                        <!-- Quick Stats -->
                        <div class="d-none d-md-flex gap-3">
                            <div class="stat-card-mini p-2 text-center" style="min-width: 80px;">
                                <div class="small text-muted">Noticias</div>
                                <div class="fw-bold text-primary">
                                    @php
                                        try {
                                            echo auth()->user()?->noticias()->count() ?? 0;
                                        } catch (\Throwable $e) {
                                            echo 0;
                                        }
                                    @endphp
                                </div>
                            </div>
                            <div class="stat-card-mini p-2 text-center" style="min-width: 80px;">
                                <div class="small text-muted">Publicadas</div>
                                <div class="fw-bold text-success">
                                    @php
                                        try {
                                            echo auth()->user()?->noticias()->where('publicada', true)->count() ?? 0;
                                        } catch (\Throwable $e) {
                                            echo 0;
                                        }
                                    @endphp
                                </div>
                            </div>
                        </div>
                        
                        <!-- Transmisión En Vivo Status / Toggle en Cabecera -->
                        @php
                            $topLiveActive = setting('streaming_tv_active', '0') == '1';
                        @endphp
                        <form action="{{ route('admin.streaming.toggle') }}" method="POST" class="d-inline m-0">
                            @csrf
                            @if($topLiveActive)
                                <button type="submit" class="btn btn-sm btn-danger d-inline-flex align-items-center gap-2 px-3 py-1 shadow-sm rounded-pill fw-bold" style="font-size:0.75rem;" title="Al aire en el portal. Clic para detener transmisión">
                                    <span style="width:7px;height:7px;background:#fff;border-radius:50%;animation:pulse 1.2s infinite;display:inline-block"></span>
                                    <span>EN VIVO</span>
                                </button>
                            @else
                                <button type="submit" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill" style="font-size:0.75rem;" title="Transmisión inactiva. Clic para activar EN VIVO">
                                    <i class="fas fa-satellite-dish text-muted" style="font-size:0.7rem;"></i>
                                    <span class="d-none d-sm-inline text-muted fw-semibold">Off Air</span>
                                </button>
                            @endif
                        </form>

                        <!-- Toggle de modo oscuro -->
                        <button data-dark-mode-toggle class="btn btn-link text-muted p-2 me-2" type="button" aria-label="Cambiar modo oscuro">
                            <i class="fas fa-sun sun-icon fa-lg hidden"></i>
                            <i class="fas fa-moon moon-icon fa-lg"></i>
                        </button>

                        <!-- Notifications -->
                        @php
                            $notifCount = 0;
                            $notifs = [];
                            try {
                                $nNoticia = \App\Models\Noticia::where('publicada', false)->count();
                                $nComentario = \App\Models\Comentario::where('aprobado', false)->count();
                                $notifCount = $nNoticia + $nComentario;

                                if ($nComentario > 0) {
                                    $notifs[] = [
                                        'icono' => 'fas fa-comment text-info',
                                        'texto' => $nComentario . ' comentario' . ($nComentario == 1 ? '' : 's') . ' pendiente' . ($nComentario == 1 ? '' : 's') . ' de aprobacion',
                                        'url' => route('admin.comentarios.index'),
                                    ];
                                }
                                if ($nNoticia > 0) {
                                    $notifs[] = [
                                        'icono' => 'fas fa-newspaper text-primary',
                                        'texto' => $nNoticia . ' noticia' . ($nNoticia == 1 ? '' : 's') . ' sin publicar',
                                        'url' => route('admin.noticias.index'),
                                    ];
                                }
                            } catch (\Throwable $e) {
                                $notifCount = 0;
                                $notifs = [];
                            }
                        @endphp
                        <div class="dropdown">
                            <button class="btn btn-link text-muted position-relative p-2" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Notificaciones">
                                <i class="fas fa-bell fa-lg"></i>
                                @if($notifCount > 0)
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                                        {{ $notifCount > 99 ? '99+' : $notifCount }}
                                    </span>
                                @endif
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" style="min-width: 300px;">
                                <li class="dropdown-header">
                                    <i class="fas fa-bell me-2"></i>Notificaciones
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                @forelse($notifs as $n)
                                    <li>
                                        <a class="dropdown-item" href="{{ $n['url'] }}">
                                            <div class="d-flex">
                                                <div class="flex-shrink-0">
                                                    <i class="{{ $n['icono'] }}"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <div class="small">{{ $n['texto'] }}</div>
                                                    <div class="small text-muted">Requiere atencion</div>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                @empty
                                    <li>
                                        <span class="dropdown-item text-center small text-muted">No hay notificaciones pendientes</span>
                                    </li>
                                @endforelse
                            </ul>
                        </div>
                        
                        <!-- Quick Actions -->
                        <a href="{{ route('admin.noticias.create') }}" 
                           class="btn btn-latitud">
                            <i class="fas fa-plus me-2"></i>Nueva Noticia
                        </a>
                    </div>
                </div>
            </header>
            
            <!-- Content Area -->
            <main class="flex-fill overflow-auto p-4">
                <!-- Alerts -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @if(isset($errors) && $errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Errores encontrados:</strong>
                        </div>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @yield('content')
            </main>
        </div>
    </div>
    
    <!-- Mobile Sidebar Overlay -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden hidden"></div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Scripts -->
    <script>
        // Sidebar Toggle for mobile
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        
        sidebarToggle?.addEventListener('click', () => {
            sidebar.classList.toggle('show');
            sidebarOverlay.classList.toggle('d-none');
        });
        
        sidebarOverlay?.addEventListener('click', () => {
            sidebar.classList.remove('show');
            sidebarOverlay.classList.add('d-none');
        });
        
        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            document.querySelectorAll('.alert:not(.alert-permanent)').forEach(alert => {
                if (alert.querySelector('.btn-close')) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            });
        }, 5000);
        
        // Add loading states to buttons
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Procesando...';
                    submitBtn.disabled = true;
                    
                    // Re-enable after 10 seconds as fallback
                    setTimeout(() => {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }, 10000);
                }
            });
        });
        
        // Tooltips initialization
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    </script>
    
    <!-- Dark Mode Script -->
    <script src="{{ asset('js/dark-mode.js') }}"></script>
    
    @stack('scripts')
</body>
</html>