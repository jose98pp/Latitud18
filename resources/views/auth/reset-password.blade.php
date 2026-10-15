<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - Latitud 18 Admin</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 min-h-screen flex items-center justify-center p-4">
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

        <!-- Reset Password Form -->
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <div class="mb-6 text-center">
                <div class="mx-auto w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-shield-alt text-emerald-600 text-2xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900">Nueva Contraseña</h2>
                <p class="text-gray-600 mt-2">Ingresa tu nueva contraseña segura</p>
            </div>

            <form method="POST" action="{{ route('password.store') }}" class="space-y-6">
                @csrf

                <!-- Password Reset Token -->
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <!-- Email Address -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-envelope mr-2 text-indigo-600"></i>Correo Electrónico
                    </label>
                    <input id="email" 
                           type="email" 
                           name="email" 
                           value="{{ old('email', $request->email) }}" 
                           required 
                           autofocus 
                           autocomplete="username"
                           readonly
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 text-gray-600">
                    @error('email')
                        <p class="mt-2 text-sm text-red-600 flex items-center">
                            <i class="fas fa-exclamation-circle mr-1"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock mr-2 text-indigo-600"></i>Nueva Contraseña
                    </label>
                    <div class="relative">
                        <input id="password" 
                               type="password" 
                               name="password" 
                               required 
                               autocomplete="new-password"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200"
                               placeholder="Mínimo 8 caracteres">
                        <button type="button" 
                                onclick="togglePassword('password', 'passwordIcon')" 
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                            <i id="passwordIcon" class="fas fa-eye"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-2 text-sm text-red-600 flex items-center">
                            <i class="fas fa-exclamation-circle mr-1"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-check-double mr-2 text-indigo-600"></i>Confirmar Contraseña
                    </label>
                    <div class="relative">
                        <input id="password_confirmation" 
                               type="password" 
                               name="password_confirmation" 
                               required 
                               autocomplete="new-password"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200"
                               placeholder="Repite la contraseña">
                        <button type="button" 
                                onclick="togglePassword('password_confirmation', 'confirmPasswordIcon')" 
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                            <i id="confirmPasswordIcon" class="fas fa-eye"></i>
                        </button>
                    </div>
                    @error('password_confirmation')
                        <p class="mt-2 text-sm text-red-600 flex items-center">
                            <i class="fas fa-exclamation-circle mr-1"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Password Requirements -->
                <div class="p-4 bg-indigo-50 rounded-lg border border-indigo-100">
                    <p class="text-sm font-medium text-indigo-900 mb-2">Requisitos de la contraseña:</p>
                    <ul class="text-sm text-indigo-700 space-y-1">
                        <li class="flex items-center">
                            <i class="fas fa-check text-emerald-600 mr-2"></i>
                            Mínimo 8 caracteres
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-emerald-600 mr-2"></i>
                            Al menos una letra mayúscula
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-emerald-600 mr-2"></i>
                            Al menos un número
                        </li>
                    </ul>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white py-3 px-4 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition duration-200 font-medium shadow-md shadow-indigo-600/30">
                    <i class="fas fa-save mr-2"></i>Restablecer Contraseña
                </button>
            </form>

            <!-- Back to Login -->
            <div class="mt-6 text-center">
                <a href="{{ route('admin.login') }}" 
                   class="text-indigo-600 hover:text-indigo-800 font-medium text-sm">
                    <i class="fas fa-arrow-left mr-2"></i>Volver al inicio de sesión
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-6">
            <a href="{{ route('portada') }}" 
               class="text-indigo-200 hover:text-white transition duration-200 text-sm">
                <i class="fas fa-home mr-2"></i>Volver a Latitud 18
            </a>
        </div>
    </div>

    <script>
        function togglePassword(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const passwordIcon = document.getElementById(iconId);
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordIcon.classList.remove('fa-eye');
                passwordIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                passwordIcon.classList.remove('fa-eye-slash');
                passwordIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
