<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Denegado - UHTV</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-red-600 via-red-700 to-red-800 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4">
        <!-- Logo y Header -->
        <div class="text-center mb-8">
            <div class="flex justify-center mb-4">
                <img src="{{ asset('images/Logo.jpg') }}" alt="UHTV" class="w-20 h-20 rounded-full shadow-lg">
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">UHTV</h1>
            <p class="text-red-200">Acceso Denegado</p>
        </div>

        <!-- Error Message -->
        <div class="bg-white rounded-2xl shadow-2xl p-8 text-center">
            <div class="mx-auto w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-shield-alt text-red-600 text-2xl"></i>
            </div>
            
            <h2 class="text-2xl font-bold text-gray-900 mb-4">403 - Acceso Denegado</h2>
            
            <p class="text-gray-600 mb-6">
                No tienes permisos para acceder a esta página. Solo los administradores pueden acceder al panel de control.
            </p>

            <div class="space-y-3">
                <a href="{{ route('admin.login') }}" 
                   class="block w-full bg-red-600 text-white py-3 px-4 rounded-lg hover:bg-red-700 transition duration-200 font-medium">
                    <i class="fas fa-sign-in-alt mr-2"></i>Iniciar Sesión como Admin
                </a>
                
                <a href="{{ route('portada') }}" 
                   class="block w-full bg-gray-600 text-white py-3 px-4 rounded-lg hover:bg-gray-700 transition duration-200 font-medium">
                    <i class="fas fa-home mr-2"></i>Volver al Sitio Web
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-6">
            <p class="text-red-200 text-sm">
                © {{ date('Y') }} UHTV. Todos los derechos reservados.
            </p>
        </div>
    </div>
</body>
</html>