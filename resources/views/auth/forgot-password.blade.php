<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - UHTV Admin</title>
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
            <p class="text-purple-200">Recuperación de Contraseña</p>
        </div>

        <!-- Forgot Password Form -->
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <div class="mb-6 text-center">
                <div class="mx-auto w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-key text-purple-600 text-2xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900">¿Olvidaste tu contraseña?</h2>
                <p class="text-gray-600 mt-2">No te preocupes, te enviaremos un enlace para restablecerla</p>
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
                        <i class="fas fa-envelope mr-2 text-purple-600"></i>Correo Electrónico
                    </label>
                    <input id="email" 
                           type="email" 
                           name="email" 
                           value="{{ old('email') }}" 
                           required 
                           autofocus
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition duration-200"
                           placeholder="Ingresa tu correo electrónico">
                    @error('email')
                        <p class="mt-2 text-sm text-red-600 flex items-center">
                            <i class="fas fa-exclamation-circle mr-1"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full bg-purple-600 text-white py-3 px-4 rounded-lg hover:bg-purple-700 focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition duration-200 font-medium">
                    <i class="fas fa-paper-plane mr-2"></i>Enviar Enlace de Recuperación
                </button>
            </form>

            <!-- Back to Login -->
            <div class="mt-6 text-center">
                <a href="{{ route('admin.login') }}" 
                   class="text-purple-600 hover:text-purple-800 font-medium text-sm">
                    <i class="fas fa-arrow-left mr-2"></i>Volver al inicio de sesión
                </a>
            </div>

            <!-- Help Text -->
            <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-blue-600 mt-0.5 mr-2"></i>
                    <div class="text-sm text-blue-800">
                        <p class="font-medium mb-1">¿Cómo funciona?</p>
                        <p>Te enviaremos un correo con un enlace seguro para restablecer tu contraseña. El enlace expirará en 60 minutos por seguridad.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-6">
            <a href="{{ route('portada') }}" 
               class="text-purple-200 hover:text-white transition duration-200">
                <i class="fas fa-home mr-2"></i>Volver al sitio web
            </a>
        </div>
    </div>
</body>
</html>
