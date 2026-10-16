<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página No Encontrada - Latitud 18</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
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
                <img src="{{ asset('images/Logo.jpg') }}?v=2" alt="Latitud 18" class="w-20 h-20 rounded-full shadow-xl border-2 border-amber-400 object-cover" onerror="this.onerror=null; this.src='/favicon.svg'">
            </div>
            <h1 class="text-3xl font-extrabold text-white tracking-wide">
                LATITUD <span class="text-amber-400">18</span>
            </h1>
            <p class="text-slate-300 text-xs tracking-widest uppercase mt-1">Información Sin Ruido</p>
        </div>

        <!-- Tarjeta de Error -->
        <div class="bg-white rounded-2xl shadow-2xl p-8 text-center border-t-4 border-amber-500">
            <div class="mx-auto w-16 h-16 bg-amber-50 rounded-full flex items-center justify-center mb-4 text-amber-600">
                <i class="fas fa-search text-2xl"></i>
            </div>
            
            <h2 class="text-2xl font-black text-slate-900 mb-2">404 — Página No Encontrada</h2>
            
            <p class="text-slate-600 text-sm mb-6 leading-relaxed">
                La página que buscas no existe o ha sido movida. Verifica la URL o navega a una de nuestras páginas principales.
            </p>

            <div class="space-y-3">
                <a href="{{ route('portada') }}" 
                   class="block w-full bg-[#0B1F3A] hover:bg-[#D71920] text-white py-3 px-4 rounded-lg transition-colors duration-200 font-bold text-sm tracking-wide shadow-md">
                    <i class="fas fa-home mr-2"></i> Ir a la Portada
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-6">
            <p class="text-slate-400 text-xs">
                © {{ date('Y') }} Latitud 18. Todos los derechos reservados.
            </p>
        </div>
    </div>
</body>
</html>