@extends('layouts.admin')

@push('styles')
<style>
    .profile-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
        color: white;
        border-radius: 1.5rem;
        padding: 3rem;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 20px 40px rgba(102, 126, 234, 0.3);
    }
    
    .profile-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200px;
        height: 200px;
        background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, transparent 70%);
        border-radius: 50%;
        animation: pulse 4s ease-in-out infinite;
    }
    
    .profile-header::after {
        content: '';
        position: absolute;
        bottom: -30%;
        left: -30%;
        width: 150px;
        height: 150px;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        border-radius: 50%;
        animation: float 6s ease-in-out infinite reverse;
    }
    
    @keyframes pulse {
        0%, 100% { transform: scale(1) rotate(0deg); opacity: 0.7; }
        50% { transform: scale(1.1) rotate(180deg); opacity: 1; }
    }
    
    .profile-avatar {
        width: 140px;
        height: 140px;
        border-radius: 50%;
        border: 6px solid rgba(255, 255, 255, 0.4);
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.3) 0%, rgba(255, 255, 255, 0.1) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 4rem;
        margin-bottom: 1rem;
        position: relative;
        z-index: 2;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        transition: all 0.3s ease;
    }
    
    .profile-avatar:hover {
        transform: scale(1.05);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
    }
    
    .stats-card {
        background: linear-gradient(145deg, #ffffff 0%, #f8f9fc 100%);
        border-radius: 1rem;
        padding: 2rem;
        box-shadow: 0 10px 30px rgba(58, 59, 69, 0.1);
        border-left: 6px solid;
        transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        position: relative;
        overflow: hidden;
    }
    
    .stats-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, transparent 0%, rgba(78, 115, 223, 0.05) 100%);
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .stats-card:hover {
        transform: translateY(-10px) scale(1.02);
        box-shadow: 0 20px 50px rgba(58, 59, 69, 0.2);
    }
    
    .stats-card:hover::before {
        opacity: 1;
    }
    
    .stats-card.primary { border-left-color: #4e73df; }
    .stats-card.success { border-left-color: #1cc88a; }
    .stats-card.info { border-left-color: #36b9cc; }
    .stats-card.warning { border-left-color: #f6c23e; }
    
    .activity-item {
        padding: 1rem;
        border-bottom: 1px solid #e3e6f0;
        transition: background-color 0.3s ease;
    }
    
    .activity-item:hover {
        background-color: #f8f9fc;
    }
    
    .activity-item:last-child {
        border-bottom: none;
    }
    
    .activity-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        margin-right: 1rem;
    }
    
    .form-section {
        background: linear-gradient(145deg, #ffffff 0%, #f8f9fc 100%);
        border-radius: 1.5rem;
        padding: 2.5rem;
        box-shadow: 0 15px 35px rgba(58, 59, 69, 0.1);
        margin-bottom: 2rem;
        border: 1px solid rgba(78, 115, 223, 0.1);
        transition: all 0.3s ease;
    }
    
    .form-section:hover {
        box-shadow: 0 20px 45px rgba(58, 59, 69, 0.15);
        border-color: rgba(78, 115, 223, 0.2);
    }
    
    .section-title {
        color: #5a5c69;
        font-weight: 700;
        margin-bottom: 1.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #e3e6f0;
    }
    
    /* Botones con gradientes */
    .btn-primary {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        border: none;
        transition: all 0.3s ease;
    }
    
    .btn-primary:hover {
        background: linear-gradient(135deg, #224abe 0%, #1a365d 100%);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(78, 115, 223, 0.3);
    }
    
    .btn-warning {
        background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%);
        border: none;
    }
    
    .btn-warning:hover {
        background: linear-gradient(135deg, #dda20a 0%, #b7791f 100%);
        transform: translateY(-2px);
    }
    
    /* Animaciones de entrada */
    .stats-card {
        animation: fadeInUp 0.6s ease-out;
        animation-fill-mode: both;
    }
    
    .stats-card:nth-child(1) { animation-delay: 0.1s; }
    .stats-card:nth-child(2) { animation-delay: 0.2s; }
    .stats-card:nth-child(3) { animation-delay: 0.3s; }
    .stats-card:nth-child(4) { animation-delay: 0.4s; }
    
    .form-section {
        animation: fadeInUp 0.8s ease-out;
        animation-fill-mode: both;
    }
    
    .form-section:nth-of-type(1) { animation-delay: 0.2s; }
    .form-section:nth-of-type(2) { animation-delay: 0.4s; }
    .form-section:nth-of-type(3) { animation-delay: 0.6s; }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Inputs con efectos */
    .form-control:focus {
        border-color: #4e73df;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        transform: translateY(-1px);
    }
    
    .form-label {
        font-weight: 600;
        color: #5a5c69;
        margin-bottom: 0.5rem;
    }
    
    @media (max-width: 768px) {
        .profile-header {
            text-align: center;
            padding: 1.5rem;
        }
        
        .profile-avatar {
            margin: 0 auto 1rem;
        }
        
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .stats-card,
        .form-section {
            animation-delay: 0s !important;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Profile Header -->
    <div class="profile-header">
        <div class="row align-items-center">
            <div class="col-md-3 text-center">
                <div class="profile-avatar mx-auto">
                    <i class="fas fa-user"></i>
                </div>
            </div>
            <div class="col-md-9">
                <h1 class="h2 mb-2">{{ auth()->user()->name }}</h1>
                <p class="mb-2">
                    <i class="fas fa-envelope me-2"></i>{{ auth()->user()->email }}
                </p>
                <p class="mb-2">
                    <i class="fas fa-shield-alt me-2"></i>
                    <span class="badge bg-light text-dark">{{ ucfirst(auth()->user()->role ?? 'Usuario') }}</span>
                </p>
                <p class="mb-0 opacity-75">
                    <i class="fas fa-calendar me-2"></i>
                    Miembro desde {{ auth()->user()->created_at ? auth()->user()->created_at->format('d/m/Y') : 'Fecha desconocida' }}
                </p>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card primary">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Noticias Creadas
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ $stats['noticias_creadas'] }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-newspaper fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card success">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Noticias Publicadas
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ $stats['noticias_publicadas'] }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card info">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Días Activo
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ $stats['dias_activo'] }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card warning">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Último Acceso
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ $stats['ultimo_acceso'] }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Profile Information Form -->
        <div class="col-lg-8">
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-user-edit text-primary"></i> Información del Perfil
                </h3>
                
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle"></i> 
                        <strong>Error:</strong> Por favor corrija los siguientes errores:
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.profile.update') }}" id="profile-form">
                    @csrf
                    @method('PATCH')
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">
                                <i class="fas fa-user text-muted"></i> Nombre Completo
                            </label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', auth()->user()->name) }}" 
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope text-muted"></i> Correo Electrónico
                            </label>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email', auth()->user()->email) }}" 
                                   required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="bio" class="form-label">
                                <i class="fas fa-info-circle text-muted"></i> Biografía (Opcional)
                            </label>
                            <textarea class="form-control" 
                                      id="bio" 
                                      name="bio" 
                                      rows="3" 
                                      placeholder="Cuéntanos un poco sobre ti...">{{ old('bio', auth()->user()->bio ?? '') }}</textarea>
                            <small class="form-text text-muted">Máximo 500 caracteres</small>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> 
                            Los cambios se guardarán automáticamente
                        </small>
                    </div>
                </form>
            </div>

            <!-- Change Password Section -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-lock text-warning"></i> Cambiar Contraseña
                </h3>
                
                <form method="POST" action="{{ route('admin.profile.password.update') }}" id="password-form">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="current_password" class="form-label">Contraseña Actual</label>
                            <input type="password" 
                                   class="form-control @error('current_password') is-invalid @enderror" 
                                   id="current_password" 
                                   name="current_password" 
                                   required>
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="password" class="form-label">Nueva Contraseña</label>
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="password_confirmation" class="form-label">Confirmar Contraseña</label>
                            <input type="password" 
                                   class="form-control" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-key"></i> Actualizar Contraseña
                    </button>
                </form>
            </div>
        </div>

        <!-- Activity Sidebar -->
        <div class="col-lg-4">
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-chart-line text-info"></i> Actividad Reciente
                </h3>
                
                <div class="activity-list">
                    @if($user->noticias && $user->noticias->count() > 0)
                        @foreach($user->noticias->sortByDesc('created_at')->take(5) as $noticia)
                            <div class="activity-item d-flex align-items-center">
                                <div class="activity-icon bg-primary text-white">
                                    <i class="fas fa-newspaper"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="font-weight-bold">{{ Str::limit($noticia->titulo, 40) }}</div>
                                    <small class="text-muted">
                                        {{ $noticia->created_at->diffForHumans() }}
                                        @if($noticia->publicada)
                                            <span class="badge bg-success ms-1">Publicada</span>
                                        @else
                                            <span class="badge bg-warning ms-1">Borrador</span>
                                        @endif
                                    </small>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-newspaper fa-3x text-gray-300 mb-3"></i>
                            <p class="text-muted">No hay actividad reciente</p>
                            <a href="{{ route('admin.noticias.create') }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus"></i> Crear Primera Noticia
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-bolt text-success"></i> Acciones Rápidas
                </h3>
                
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.noticias.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nueva Noticia
                    </a>
                    <a href="{{ route('admin.noticias.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-list"></i> Mis Noticias
                    </a>
                    <a href="{{ route('admin.categorias.index') }}" class="btn btn-outline-info">
                        <i class="fas fa-tags"></i> Gestionar Categorías
                    </a>
                    <hr>
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-save functionality for profile form
    const profileForm = document.getElementById('profile-form');
    const inputs = profileForm.querySelectorAll('input, textarea');
    
    let autoSaveTimeout;
    
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            clearTimeout(autoSaveTimeout);
            autoSaveTimeout = setTimeout(() => {
                // Show saving indicator
                const saveBtn = profileForm.querySelector('button[type="submit"]');
                const originalText = saveBtn.innerHTML;
                saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
                saveBtn.disabled = true;
                
                // Auto-submit form
                fetch(profileForm.action, {
                    method: 'POST',
                    body: new FormData(profileForm),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        saveBtn.innerHTML = '<i class="fas fa-check"></i> Guardado';
                        setTimeout(() => {
                            saveBtn.innerHTML = originalText;
                            saveBtn.disabled = false;
                        }, 2000);
                    }
                })
                .catch(error => {
                    saveBtn.innerHTML = originalText;
                    saveBtn.disabled = false;
                });
            }, 2000);
        });
    });
    
    // Password strength indicator
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            const strength = calculatePasswordStrength(password);
            
            // Remove existing strength indicator
            const existingIndicator = this.parentNode.querySelector('.password-strength');
            if (existingIndicator) {
                existingIndicator.remove();
            }
            
            // Add new strength indicator
            if (password.length > 0) {
                const indicator = document.createElement('div');
                indicator.className = 'password-strength mt-1';
                indicator.innerHTML = `
                    <div class="progress" style="height: 5px;">
                        <div class="progress-bar bg-${strength.color}" 
                             style="width: ${strength.percentage}%"></div>
                    </div>
                    <small class="text-${strength.color}">${strength.text}</small>
                `;
                this.parentNode.appendChild(indicator);
            }
        });
    }
    
    function calculatePasswordStrength(password) {
        let score = 0;
        
        if (password.length >= 8) score += 25;
        if (password.match(/[a-z]/)) score += 25;
        if (password.match(/[A-Z]/)) score += 25;
        if (password.match(/[0-9]/)) score += 25;
        if (password.match(/[^a-zA-Z0-9]/)) score += 25;
        
        if (score <= 25) return { percentage: 25, color: 'danger', text: 'Muy débil' };
        if (score <= 50) return { percentage: 50, color: 'warning', text: 'Débil' };
        if (score <= 75) return { percentage: 75, color: 'info', text: 'Buena' };
        return { percentage: 100, color: 'success', text: 'Muy fuerte' };
    }
});
</script>
@endpush

@endsection