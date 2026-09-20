<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error del Servidor - Latitud 18 / UHTV</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;700;800;900&family=Source+Sans+3:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Source Sans 3', sans-serif; }
        h1, h2 { font-family: 'Montserrat', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-[#0B1F3A] via-[#0E2A52] to-[#1A365D] min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full">
        <!-- Logo y Header -->
        <div class="text-center mb-6">
            <div class="flex justify-center mb-3">
                <img src="{{ asset('images/Logo.jpg') }}" alt="Latitud 18" class="w-20 h-20 rounded-full shadow-xl border-2 border-red-500 object-cover" onerror="this.onerror=null; this.src='/images/Logo.jpg'">
            </div>
            <h1 class="text-3xl font-extrabold text-white tracking-wide">
                LATITUD <span class="text-red-500">18</span>
            </h1>
            <p class="text-slate-300 text-xs tracking-widest uppercase mt-1">Información Sin Ruido • UHTV</p>
        </div>

        <!-- Tarjeta de Error -->
        <div class="bg-white rounded-2xl shadow-2xl p-8 text-center border-t-4 border-red-600">
            <div class="mx-auto w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mb-4 text-red-600">
                <i class="fas fa-server text-3xl"></i>
            </div>
            
            <h2 class="text-2xl font-black text-slate-900 mb-2">500 — Error del Servidor</h2>
            
            <p class="text-slate-600 text-sm mb-6 leading-relaxed">
                Estamos experimentando una interrupción temporal en este servicio. Nuestro equipo técnico ya fue notificado y está trabajando para restaurarlo a la brevedad.
            </p>

            <div class="space-y-3">
                <a href="{{ route('portada') }}" 
                   class="block w-full bg-[#0B1F3A] hover:bg-[#D71920] text-white py-3 px-4 rounded-lg transition-colors duration-200 font-bold text-sm tracking-wide shadow-md">
                    <i class="fas fa-home mr-2"></i> Ir a la Portada
                </a>
                <button onclick="window.location.reload()" 
                        class="block w-full bg-slate-100 hover:bg-slate-200 text-slate-700 py-2.5 px-4 rounded-lg transition-colors duration-200 font-semibold text-xs">
                    <i class="fas fa-redo-alt mr-2"></i> Reintentar ahora
                </button>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-6">
            <p class="text-slate-400 text-xs">
                © {{ date('Y') }} Latitud 18 / UHTV. Todos los derechos reservados.
            </p>
        </div>
    </div>
</body>
</html>
