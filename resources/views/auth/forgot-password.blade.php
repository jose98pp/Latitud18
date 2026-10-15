<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - Latitud 18 Admin</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4">
        <!-- Logo y Header -->
        <div class="text-center mb-8">
            <div class="flex justify-center mb-4">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-500 to-amber-500 flex items-center justify-center text-white font-black text-2xl shadow-xl shadow-indigo-500/20">
                    L18
                </div>
            </div>
            <h1 class="text-3xl font-black text-white tracking-tight mb-1">LATITUD <span class="text-amber-400">18</span></h1>
            <p class="text-indigo-200 text-sm font-medium">Panel Editorial & Administración</p>
        </div>

        <!-- Forgot Password Form -->
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <div class="mb-6 text-center">
                <div class="mx-auto w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-key text-indigo-600 text-2xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900">¿Olvidaste tu contraseña?</h2>
                <p class="text-gray-600 mt-2">Te enviaremos un enlace seguro para restablecerla</p>
            </div>

            <!-- Session Status -->
            @if (session('status'))
                <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        <span>{{ session('status') }}</span>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                @csrf

                <!-- Email Address -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-envelope mr-2 text-indigo-600"></i>Correo Electrónico
                    </label>
                    <input id="email" 
                           type="email" 
                           name="email" 
                           value="{{ old('email') }}" 
                           required 
                           autofocus
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200"
                           placeholder="admin@latitud18.com">
                    @error('email')
                        <p class="mt-2 text-sm text-red-600 flex items-center">
                            <i class="fas fa-exclamation-circle mr-1"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 px-4 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition duration-200 font-medium shadow-md shadow-indigo-600/30">
                    <i class="fas fa-paper-plane mr-2"></i>Enviar Enlace de Recuperación
                </button>
            </form>

            <!-- Back to Login -->
            <div class="mt-6 text-center">
                <a href="{{ route('admin.login') }}" 
                   class="text-indigo-600 hover:text-indigo-800 font-medium text-sm">
                    <i class="fas fa-arrow-left mr-2"></i>Volver al Inicio de Sesión
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-6">
            <p class="text-indigo-300/70 text-sm">
                &copy; {{ date('Y') }} Latitud 18. Todos los derechos reservados.
            </p>
        </div>
    </div>
</body>
</html>
