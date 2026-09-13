<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - UHTV Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-purple-600 via-purple-700 to-purple-800 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4">
        <!-- Logo y Header -->
        <div class="text-center mb-8">
            <div class="flex justify-center mb-4">
                <img src="{{ asset('images/Logo.jpg') }}" alt="UHTV" class="w-20 h-20 rounded-full shadow-lg">
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">UHTV Admin</h1>
            <p class="text-purple-200">Panel de Administración</p>
        </div>

        <!-- Login Form -->
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-900 text-center">Iniciar Sesión</h2>
                <p class="text-gray-600 text-center mt-2">Accede a tu panel de administración</p>
            </div>

            <!-- Session Status -->
            @if (session('status'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.store') }}" class="space-y-6">
                @csrf

                <!-- Email Address -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-envelope mr-2 text-purple-600"></i>Correo Electrónico
                    </label>
                    <input id="email" 
                           type="email" 
                           name="email" 
                           value="{{ old('email') }}" 
                           required 
                           autofocus 
                           autocomplete="username"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition duration-200"
                           placeholder="admin@uhtv.com">
                    @error('email')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock mr-2 text-purple-600"></i>Contraseña
                    </label>
                    <div class="relative">
                        <input id="password" 
                               type="password" 
                               name="password" 
                               required 
                               autocomplete="current-password"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition duration-200"
                               placeholder="••••••••">
                        <button type="button" 
                                onclick="togglePassword()" 
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                            <i id="passwordIcon" class="fas fa-eye"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between">
                    <label for="remember_me" class="flex items-center">
                        <input id="remember_me" 
                               type="checkbox" 
                               name="remember"
                               class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                        <span class="ml-2 text-sm text-gray-600">Recordarme</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" 
                           class="text-sm text-purple-600 hover:text-purple-800 font-medium">
                            ¿Olvidaste tu contraseña?
                        </a>
                    @endif
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full bg-purple-600 text-white py-3 px-4 rounded-lg hover:bg-purple-700 focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition duration-200 font-medium">
                    <i class="fas fa-sign-in-alt mr-2"></i>Iniciar Sesión
                </button>
            </form>

            <!-- Footer -->
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-500">
                    © {{ date('Y') }} UHTV. Todos los derechos reservados.
                </p>
            </div>
        </div>

        <!-- Back to Site -->
        <div class="text-center mt-6">
            <a href="{{ route('portada') }}" 
               class="text-purple-200 hover:text-white transition duration-200">
                <i class="fas fa-arrow-left mr-2"></i>Volver al sitio web
            </a>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const passwordIcon = document.getElementById('passwordIcon');
            
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
