<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Noticia;
use App\Models\Category;
use Illuminate\Support\Str;

class PeriodicoController extends Controller
{
    private string $storagePath;

    public function __construct()
    {
        $this->storagePath = storage_path('app/periodicos.json');
    }

    /**
     * Muestra el editor visual de periódico digital
     */
    public function index(Request $request)
    {
        $ediciones = $this->getAllEdiciones();

        // Si no hay ninguna edición creada, generamos la edición por defecto inicial
        if (empty($ediciones)) {
            $defaultEdicion = $this->getDefaultEdicionStructure();
            $ediciones = [$defaultEdicion];
            $this->saveAllEdiciones($ediciones);
        }

        // Obtener la edición seleccionada o la última activa/creada
        $selectedId = $request->query('edicion_id');
        $currentEdicion = null;

        if ($selectedId) {
            foreach ($ediciones as $ed) {
                if ($ed['id'] === $selectedId) {
                    $currentEdicion = $ed;
                    break;
                }
            }
        }

        if (!$currentEdicion) {
            // Tomar la edición activa o la primera
            foreach ($ediciones as $ed) {
                if (!empty($ed['activa'])) {
                    $currentEdicion = $ed;
                    break;
                }
            }
            if (!$currentEdicion) {
                $currentEdicion = $ediciones[0];
            }
        }

        $categorias = Category::all();
        $noticiasPublicadas = Noticia::where('publicada', true)
            ->with('category')
            ->orderBy('created_at', 'desc')
            ->limit(60)
            ->get();

        return view('admin.periodico.index', compact('ediciones', 'currentEdicion', 'categorias', 'noticiasPublicadas'));
    }

    /**
     * Crea una nueva edición semanal
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'numero_edicion' => 'required|string|max:100',
            'fecha' => 'required|string|max:100',
            'titulo' => 'nullable|string|max:255',
            'precio' => 'nullable|string|max:50',
            'paginas' => 'nullable|array',
            'clonar_de' => 'nullable|string',
        ]);

        $ediciones = $this->getAllEdiciones();
        $nuevaEdicion = $this->getDefaultEdicionStructure();

        $nuevaEdicion['id'] = 'ed-' . time() . '-' . Str::random(5);
        $nuevaEdicion['numero_edicion'] = $validated['numero_edicion'];
        $nuevaEdicion['fecha'] = $validated['fecha'];
        if (!empty($validated['titulo'])) $nuevaEdicion['titulo'] = $validated['titulo'];
        if (!empty($validated['precio'])) $nuevaEdicion['precio'] = $validated['precio'];
        $nuevaEdicion['publicada'] = false;
        $nuevaEdicion['activa'] = false;
        $nuevaEdicion['created_at'] = now()->toISOString();
        $nuevaEdicion['updated_at'] = now()->toISOString();

        // Si se pidió clonar de una existente
        if (!empty($validated['clonar_de'])) {
            foreach ($ediciones as $orig) {
                if ($orig['id'] === $validated['clonar_de']) {
                    $nuevaEdicion['paginas'] = $orig['paginas'];
                    $nuevaEdicion['titulo'] = $orig['titulo'];
                    $nuevaEdicion['subtitulo'] = $orig['subtitulo'] ?? '';
                    $nuevaEdicion['precio'] = $orig['precio'] ?? 'Bs 7,00';
                    break;
                }
            }
        } elseif (!empty($validated['paginas'])) {
            $nuevaEdicion['paginas'] = $validated['paginas'];
        }

        array_unshift($ediciones, $nuevaEdicion);
        $this->saveAllEdiciones($ediciones);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Nueva edición creada exitosamente.',
                'edicion' => $nuevaEdicion,
                'redirect_url' => route('admin.periodico.index', ['edicion_id' => $nuevaEdicion['id']])
            ]);
        }

        return redirect()->route('admin.periodico.index', ['edicion_id' => $nuevaEdicion['id']])
            ->with('success', 'Nueva edición semanal creada con éxito.');
    }

    /**
     * Actualiza el contenido completo de una edición (páginas, textos, widgets, imágenes)
     */
    public function update(Request $request, $id)
    {
        $ediciones = $this->getAllEdiciones();
        $foundIndex = -1;

        foreach ($ediciones as $idx => $ed) {
            if ($ed['id'] === $id) {
                $foundIndex = $idx;
                break;
            }
        }

        if ($foundIndex === -1) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Edición no encontrada.'], 404);
            }
            return redirect()->route('admin.periodico.index')->with('error', 'Edición no encontrada.');
        }

        $data = $request->json()->all() ?: $request->all();

        // Actualizar campos básicos
        if (isset($data['numero_edicion'])) $ediciones[$foundIndex]['numero_edicion'] = $data['numero_edicion'];
        if (isset($data['fecha'])) $ediciones[$foundIndex]['fecha'] = $data['fecha'];
        if (isset($data['titulo'])) $ediciones[$foundIndex]['titulo'] = $data['titulo'];
        if (isset($data['subtitulo'])) $ediciones[$foundIndex]['subtitulo'] = $data['subtitulo'];
        if (isset($data['precio'])) $ediciones[$foundIndex]['precio'] = $data['precio'];
        if (isset($data['ciudad'])) $ediciones[$foundIndex]['ciudad'] = $data['ciudad'];
        if (isset($data['slogan'])) $ediciones[$foundIndex]['slogan'] = $data['slogan'];
        if (isset($data['publicada'])) $ediciones[$foundIndex]['publicada'] = (bool)$data['publicada'];
        if (isset($data['pdf_url'])) $ediciones[$foundIndex]['pdf_url'] = $data['pdf_url'];

        // Actualizar estructura de páginas
        if (isset($data['paginas'])) {
            $ediciones[$foundIndex]['paginas'] = is_string($data['paginas']) ? json_decode($data['paginas'], true) : $data['paginas'];
            $ediciones[$foundIndex]['num_paginas'] = count($ediciones[$foundIndex]['paginas']);
        }

        $ediciones[$foundIndex]['updated_at'] = now()->toISOString();
        $this->saveAllEdiciones($ediciones);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Edición guardada correctamente.',
                'edicion' => $ediciones[$foundIndex]
            ]);
        }

        return redirect()->route('admin.periodico.index', ['edicion_id' => $id])
            ->with('success', 'Edición guardada exitosamente.');
    }

    /**
     * Publica una edición y la marca como la edición semanal activa en el portal
     */
    public function publish(Request $request, $id)
    {
        $ediciones = $this->getAllEdiciones();
        $found = false;

        foreach ($ediciones as &$ed) {
            if ($ed['id'] === $id) {
                $ed['publicada'] = true;
                $ed['activa'] = true;
                $ed['fecha_publicacion'] = now()->toISOString();
                $found = true;
            } else {
                $ed['activa'] = false; // Solo una activa a la vez
            }
        }
        unset($ed);

        if (!$found) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Edición no encontrada.'], 404);
            }
            return redirect()->route('admin.periodico.index')->with('error', 'Edición no encontrada.');
        }

        $this->saveAllEdiciones($ediciones);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => '¡Edición semanal publicada con éxito en la página web!'
            ]);
        }

        return redirect()->route('admin.periodico.index', ['edicion_id' => $id])
            ->with('success', '¡Edición semanal publicada con éxito en la página web!');
    }

    /**
     * Elimina una edición
     */
    public function destroy($id)
    {
        $ediciones = $this->getAllEdiciones();
        $nuevas = array_values(array_filter($ediciones, fn($e) => $e['id'] !== $id));

        $this->saveAllEdiciones($nuevas);

        return redirect()->route('admin.periodico.index')
            ->with('success', 'Edición eliminada correctamente.');
    }

    /**
     * Subida de imágenes para cualquier bloque del periódico
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp,gif|max:10240', // hasta 10MB
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = 'periodico_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/periodico'), $filename);

            $publicUrl = asset('images/periodico/' . $filename);

            return response()->json([
                'success' => true,
                'url' => $publicUrl,
                'filename' => $filename,
                'message' => 'Imagen subida exitosamente.'
            ]);
        }

        return response()->json(['success' => false, 'message' => 'No se recibió ningún archivo de imagen válido.'], 400);
    }

    /**
     * Subida de archivo PDF completo para la edición
     */
    public function uploadPdf(Request $request, $id)
    {
        $request->validate([
            'pdf_file' => 'required|mimes:pdf|max:51200', // hasta 50MB
        ]);

        if ($request->hasFile('pdf_file')) {
            $file = $request->file('pdf_file');
            $filename = 'edicion_' . $id . '_' . time() . '.pdf';
            
            $destDir = public_path('storage/ediciones_pdf');
            if (!file_exists($destDir)) {
                mkdir($destDir, 0755, true);
            }
            
            $file->move($destDir, $filename);
            $publicUrl = asset('storage/ediciones_pdf/' . $filename);

            $ediciones = $this->getAllEdiciones();
            foreach ($ediciones as &$ed) {
                if ($ed['id'] === $id) {
                    $ed['pdf_url'] = $publicUrl;
                    $ed['updated_at'] = now()->toISOString();
                    break;
                }
            }
            unset($ed);
            $this->saveAllEdiciones($ediciones);

            return response()->json([
                'success' => true,
                'url' => $publicUrl,
                'message' => 'PDF de la edición subido correctamente.'
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Archivo PDF inválido.'], 400);
    }

    /**
     * Genera la vista imprimible / PDF de alta fidelidad
     */
    public function generatePdf($id)
    {
        $ediciones = $this->getAllEdiciones();
        $edicion = null;

        foreach ($ediciones as $ed) {
            if ($ed['id'] === $id) {
                $edicion = $ed;
                break;
            }
        }

        if (!$edicion) {
            return redirect()->route('admin.periodico.index')
                ->with('error', 'Edición no encontrada.');
        }

        return view('admin.periodico.pdf-preview', compact('edicion'));
    }

    /**
     * Obtiene noticias de una categoría específica para autocompletar
     */
    public function getNoticiasByCategory($categoryId)
    {
        $query = Noticia::where('publicada', true)->with('category');

        if ($categoryId !== 'all') {
            $query->where('category_id', $categoryId);
        }

        $noticias = $query->orderBy('created_at', 'desc')
            ->limit(30)
            ->get()
            ->map(function ($n) {
                return [
                    'id' => $n->id,
                    'titulo' => $n->titulo,
                    'bajada' => Str::limit(strip_tags($n->contenido), 180),
                    'contenido' => strip_tags($n->contenido),
                    'categoria' => $n->category->name ?? 'General',
                    'imagen' => $n->imagenUrl,
                    'autor' => $n->autor ?? 'REDACCIÓN',
                    'fecha' => $n->created_at->format('d/m/Y'),
                ];
            });

        return response()->json($noticias);
    }

    /**
     * Obtiene todas las ediciones guardadas
     */
    public function getAllEdiciones(): array
    {
        // Migrar de periodico.json anterior si existe y periodicos.json no existe aún
        $oldPath = storage_path('app/periodico.json');
        if (!file_exists($this->storagePath) && file_exists($oldPath)) {
            $oldData = json_decode(file_get_contents($oldPath), true) ?: [];
            if (!empty($oldData)) {
                file_put_contents($this->storagePath, json_encode($oldData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
        }

        if (!file_exists($this->storagePath)) {
            return [];
        }

        $content = file_get_contents($this->storagePath);
        return json_decode($content, true) ?: [];
    }

    /**
     * Guarda todas las ediciones en el archivo JSON
     */
    public function saveAllEdiciones(array $ediciones): void
    {
        $dir = dirname($this->storagePath);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($this->storagePath, json_encode($ediciones, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /**
     * Estructura inicial por defecto completa y fiel a las 4 páginas del periódico físico de referencia
     */
    public function getDefaultEdicionStructure(): array
    {
        return [
            'id' => 'ed-' . time(),
            'numero_edicion' => 'N° 11.986',
            'fecha' => 'Domingo 6 de septiembre de 2026',
            'titulo' => 'La Estrella',
            'subtitulo' => 'del Oriente',
            'slogan' => 'El 100% de los hogares atendidos por CRE pagan la misma tarifa equitativa',
            'ciudad' => 'Santa Cruz de la Sierra',
            'precio' => 'Bs 7,00',
            'num_paginas' => 4,
            'publicada' => true,
            'activa' => true,
            'pdf_url' => null,
            'created_at' => now()->toISOString(),
            'updated_at' => now()->toISOString(),
            'paginas' => [
                // PÁGINA 1: PORTADA
                [
                    'numero' => 1,
                    'tipo' => 'portada',
                    'nombre' => 'Portada',
                    'dolar_compra' => '12,58',
                    'dolar_venta' => '12,58',
                    'titular_principal' => [
                        'antetitulo' => 'SEGURIDAD NACIONAL',
                        'titulo' => 'Viacha: hallan booster, pólvora negra y material bélico en zona afectada por explosiones',
                        'columnas' => [
                            [
                                'destacado' => 'RIESGO.',
                                'texto' => 'La zona afectada por las explosiones en Viacha continúa en alto riesgo debido al hallazgo de pólvora negra, boosters (explosivos multiplicadores que pueden incrementar la detonación).'
                            ],
                            [
                                'destacado' => 'ALCANCE.',
                                'texto' => 'Cuando entran en contacto con otro explosivo y material bélico, informó el ministro de Defensa, Ernesto Justiniano Urenda, quien advirtió que no existen condiciones seguras.'
                            ],
                            [
                                'destacado' => 'EQUIPOS.',
                                'texto' => 'Advirtió que todavía no existen condiciones seguras para el ingreso de maquinaria pesada. La inspección realizada mediante drones permitió identificar que un edificio ubicado detrás.'
                            ],
                            [
                                'destacado' => 'VÍCTIMAS.',
                                'texto' => 'Detrás del área de la explosión alberga material bélico y una gran cantidad de granadas. El soldado Wilson Copa Mamani, fue la tercera víctima fatal.',
                                'pagina_ref' => 'PÁG. 6'
                            ]
                        ]
                    ],
                    'noticia_central' => [
                        'imagen' => 'https://images.unsplash.com/photo-1511578314322-379afb476865?w=1000&q=80',
                        'titulo_sobre_foto' => 'La CRE celebró grito libertario de 1810 con retreta cultural',
                        'epigrafe' => 'CELEBRACIÓN. Una noche de música, danza y tradición reunió a más de 600 personas en la retreta cultural, organizada por la Cooperativa Rural de Electrificación (CRE), como parte de los homenajes al grito libertario de 1810. La actividad se desarrolló frente a la oficina central de la institución, junto al paseo del "Arco de la Cruceñidad".',
                        'pagina_ref' => 'PÁG. 3'
                    ],
                    'lateral_noticias' => [
                        [
                            'categoria' => 'SEGURIDAD',
                            'color_tag' => '#0B1F3A',
                            'imagen' => 'https://images.unsplash.com/photo-1589829545856-d10d557cf95f?w=400&q=80',
                            'titulo' => 'SURTIDOR OCULTÓ COMBUSTIBLE',
                            'texto' => 'Un surtidor de YPFB, ubicado en el municipio de Cabezas, provincia Cordillera, habría ocultado alrededor de 3.000 litros de gasolina.',
                            'pagina_ref' => 'PÁG. 10'
                        ],
                        [
                            'categoria' => 'DEPORTES',
                            'color_tag' => '#D71920',
                            'imagen' => 'https://images.unsplash.com/photo-1508098682722-e99c43a406b2?w=400&q=80',
                            'titulo' => 'ORIENTE HABRÍA PERDIDO 3 PUNTOS',
                            'texto' => 'Debido a que no pagó la deuda que tenía con Diego Bejarano, Oriente Petrolero habría perdido tres puntos, denunció Víctor Hugo Pérez.',
                            'pagina_ref' => 'PÁG. 15'
                        ]
                    ],
                    'cintillo_inferior' => [
                        'categoria' => 'SEGURIDAD',
                        'texto' => 'MENOR ABUSADA SEXUALMENTE FALLECE POR UNA ENFERMEDAD DE TRANSMISIÓN SEXUAL',
                        'pagina_ref' => 'PÁG. 9'
                    ]
                ],

                // PÁGINA 2: EDITORIAL & OPINIÓN & SERVICIOS
                [
                    'numero' => 2,
                    'tipo' => 'editorial',
                    'nombre' => 'Editorial / Opinión',
                    'seccion_titulo' => 'EDITORIAL',
                    'editorial' => [
                        'titulo' => 'Preocupante inseguridad',
                        'parrafos_col1' => [
                            'En los últimos meses, se registraron cuatro incidentes relacionados con la seguridad en instalaciones de las Fuerzas Armadas. El último ocurrió este viernes en Viacha, encendiendo las alarmas sobre las condiciones de seguridad y control dentro de los recintos militares del país.',
                            'Anteriormente, se denunció la sustracción de 33.608 cartuchos de munición calibre 5,56 × 45 milímetros de un depósito del Regimiento de Satinadores de Selva 12 "Coronel Francisco Manchego", en Montero, Santa Cruz; un robo de armamento de guerra del cuartel VI Independencia, ubicado en Chua Cocani, La Paz y un incendio en instalaciones antiguas del Regimiento de Infantería Mecanizada 23 "Max Toledo", en Viacha.',
                            'Estos hechos son por demás de preocupantes, puesto que, supuestamente, los cuarteles son lugares seguros para aquellos que circunstancialmente viven en los mismos e, hipotéticamente, tendrían que dar una sensación de seguridad para sus vecinos.'
                        ],
                        'cita_destacada' => 'No solo se debe aclarar por qué sucedieron las explosiones: sino que también se debe informar sobre los recaudos adoptados para que no se repitan estos hechos.',
                        'parrafos_col2' => [
                            'Sin embargo, por lo menos en dos de los cuatro casos mencionados, se convirtieron en generadores de inseguridad total para los vivientes en dichos cuarteles y para los vecinos.',
                            'Sin lugar a dudas que el peor de los cuatro ha sido el que se produjo el viernes pasado, cuando se detonaron dos explosiones en un cuartel de Viacha, que, hasta el momento, han dejado 3 soldados muertos, 84 heridos y 14 desaparecidos, que aún continúan siendo buscados.',
                            'El Ministerio de Defensa y los investigadores tienen la palabra en el caso de las explosiones en Viacha, porque se tiene que determinar qué fue lo que sucedió exactamente y quiénes son los responsables para aplicar las sanciones correspondientes.'
                        ]
                    ],
                    'principios' => [
                        'titulo' => 'TODOS SOMOS IGUALES',
                        'imagen' => 'https://images.unsplash.com/photo-1582213782179-e0d53f98f2ca?w=400&q=80',
                        'texto' => 'La Estrella del Oriente, el primer periódico de Santa Cruz, fundado en 1864, reconoce como valores supremos la Libertad, Igualdad y Solidaridad, como base de la Justicia y el Desarrollo de los pueblos. Su política editorial se sustenta en la Libertad de Expresión y repudia todo acto racista y discriminatorio.'
                    ],
                    'servicios' => [
                        'clima' => [
                            'ciudad' => 'Santa Cruz de la Sierra',
                            'hoy' => ['dia' => 'DOMINGO 6 sep', 'min' => '17 °C', 'max' => '22 °C', 'icono' => '☀️', 'viento' => 'Sur 31 km/h'],
                            'manana' => ['dia' => 'LUNES 7 sep', 'min' => '16 °C', 'max' => '26 °C', 'icono' => '⛅', 'viento' => 'Sur 32 km/h']
                        ],
                        'cotizaciones' => [
                            'dolar_compra' => '12,58',
                            'dolar_venta' => '12,58',
                            'ufv' => '3.34041',
                            'real' => '2.45272',
                            'peso_arg' => '0.00834',
                            'euro' => '14.60922'
                        ]
                    ],
                    'opinion' => [
                        'autor' => 'Garina Cabo, INFOBAE',
                        'titulo' => 'Leer y escribir no alcanza: el desafío de comprender el mundo',
                        'dropcap' => 'E',
                        'columnas' => [
                            'En una sociedad saturada de información, aprender a leer y escribir no alcanza. Quien no puede comprender un texto, distinguir una opinión de un hecho o construir una voz propia queda nuevamente del lado de la exclusión.',
                            'Históricamente, entendimos la alfabetización como la simple capacidad de decodificar letras, escribir palabras o resolver cuentas básicas. Hoy, esa definición es sumamente reduccionista en una sociedad atravesada por la información constante.',
                            'Para desarmar esta realidad, resulta interesante retomar a un referente de la pedagogía latinoamericana, Paulo Freire, quien planteó que alfabetizar va mucho más allá del mero deletreo; exige una comprensión crítica del mundo.',
                            'Alfabetizar, hoy, nos exige reconocer la singularidad y la trayectoria de cada alumno, diseñando alternativas de enseñanza que respondan a las problemáticas reales de sus propios contextos.'
                        ]
                    ],
                    'staff' => [
                        'fundado' => 'Tristán Roca Suárez en 1864',
                        'director' => 'Dr. Carlos Subirana',
                        'subdirectora' => 'Lic. Ximena Suárez Melgar',
                        'gerente' => 'Dr. Pedro Alberto Subirana Gianella',
                        'seguridad' => 'Carol Suárez',
                        'comunidad' => 'Antonella Justiniano',
                        'central' => 'Calle Celso Castedo Nro. 46 • Tel: 332-9011',
                        'editorial_imprenta' => 'Editorial CSS Ltda. • Matrícula de Comercio 00102409'
                    ]
                ],

                // PÁGINA 3: COMUNIDAD
                [
                    'numero' => 3,
                    'tipo' => 'comunidad',
                    'nombre' => 'Comunidad',
                    'seccion_titulo' => 'COMUNIDAD',
                    'articulo_principal' => [
                        'antetitulo' => 'En el "Arco de la Cruceñidad"',
                        'titulo' => 'La CRE celebró el grito libertario de 1810 con una retreta cultural',
                        'bajada' => 'El espectáculo comenzó pasadas las 19:00 y permitió a más de 600 personas disfrutar de una jornada inspirada en la Santa Cruz de antaño, con interpretaciones de taquiraris, chobenas y brincaos.',
                        'imagen' => 'https://images.unsplash.com/photo-1511578314322-379afb476865?w=1000&q=80',
                        'pie_foto' => 'CRE. Más de 600 personas participaron de la retreta cultural, organizada por la Cooperativa, en homenaje al grito libertario.',
                        'autor' => 'TEXTO: ANTONELLA JUSTINIANO',
                        'email_autor' => 'antonella.justiniano91@gmail.com',
                        'dropcap' => 'U',
                        'columnas' => [
                            'Una noche de música, danza y tradición reunió a más de 600 personas en la retreta cultural, organizada por la Cooperativa Rural de Electrificación (CRE), como parte de los homenajes al grito libertario de 1810. La actividad se desarrolló frente a la oficina central de la institución, junto al paseo del "Arco de la Cruceñidad".',
                            'El espectáculo comenzó pasadas las 19:00 y permitió a las familias disfrutar de una jornada inspirada en la Santa Cruz de antaño. La actividad fue gratuita y convocó a ciudadanos que llegaron para compartir y revalorizar las expresiones culturales de la región.',
                            'El presidente del Consejo de Administración de CRE, José Alejandro Durán Rek, dio inicio al programa y destacó el compromiso de la cooperativa con las tradiciones y la identidad cruceña. El escenario recibió a Darwin Olmos, Sebas Soliz, Tiziana Zankyz y el Coro Santa Cruz.',
                            'Con la retreta, la CRE volvió a reunir a las familias en torno a la música y las costumbres regionales, fortaleciendo la valoración de la identidad cultural cruceña durante las celebraciones de septiembre.'
                        ]
                    ],
                    'lateral_noticias' => [
                        [
                            'antetitulo' => 'CAMBIO CLIMÁTICO',
                            'titulo' => 'Santa Cruz se prepara para un fuerte descenso térmico',
                            'texto' => 'La Gobernación de Santa Cruz emitió una alerta naranja por el brusco descenso de temperaturas previsto para este domingo 6 y lunes 7 de septiembre.',
                            'imagen' => 'https://images.unsplash.com/photo-1534088568595-a066f410bcda?w=400&q=80'
                        ],
                        [
                            'antetitulo' => 'EN LA PLAZA 24 DE SEPTIEMBRE',
                            'titulo' => 'Peatones y ciclistas tomarán las calles este domingo',
                            'texto' => 'Santa Cruz de la Sierra se prepara para vivir este domingo el Día Nacional del Peatón y del Ciclista con actividades deportivas.',
                            'imagen' => 'https://images.unsplash.com/photo-1485965120184-e220f721d03e?w=400&q=80'
                        ],
                        [
                            'antetitulo' => 'ACCESO A SERVICIOS',
                            'titulo' => 'Un 20% de personas ciegas buscan acreditarse en Montero',
                            'texto' => 'Cerca de un 20% de las personas con discapacidad visual registradas en Santa Cruz viven en el Norte Integrado.',
                            'imagen' => 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?w=400&q=80'
                        ]
                    ]
                ],

                // PÁGINA 4: NEGOCIOS & ECONOMÍA
                [
                    'numero' => 4,
                    'tipo' => 'negocios',
                    'nombre' => 'Negocios / Economía',
                    'seccion_titulo' => 'NEGOCIOS',
                    'articulo_superior' => [
                        'antetitulo' => 'Rueda de Negocios 2026 en Concepción',
                        'titulo' => 'Encuentro cierra con $us 3,5 MM en intenciones de negocio',
                        'bajada' => 'Durante cuatro horas se desarrollaron 195 citas presenciales y virtuales, en las que participaron pequeños, medianos y grandes operadores forestales. Las reuniones permitieron establecer contactos entre compradores y vendedores.',
                        'imagen' => 'https://images.unsplash.com/photo-1556761175-5973dc0f32e7?w=1000&q=80',
                        'pie_foto' => 'ENCUENTRO. Forestal reunió a empresas y operadores del sector, donde se concretaron 195 citas para explorar nuevos mercados.',
                        'autor' => 'TEXTO: ANTONELLA JUSTINIANO',
                        'dropcap' => 'C',
                        'columnas' => [
                            'Con resultados positivos y nuevas oportunidades comerciales, Concepción cerró la sexta versión del Encuentro Social de Negocios de la Industria Forestal 2026 con $us 3,5 millones en intenciones de negocio, resultado de 195 citas comerciales.',
                            'La actividad se desarrolló en el marco de la sexta versión de la Feria Forestal y fue organizada por la Cámara Forestal de Bolivia (CFB) y el Gobierno Autónomo Municipal de Concepción.',
                            'El gerente general de la CFB, Walter Rioja, destacó la participación de operadores de distintos mercados. "El encuentro cerró con $us 3,5 millones en intenciones de negocios, lo que representa un incremento del 25% frente al año anterior", afirmó.',
                            'Por su parte, el presidente de la Cámara Forestal resaltó el valor de estos espacios para fortalecer los vínculos comerciales y dinamizar la economía regional en el oriente boliviano.'
                        ]
                    ],
                    'articulo_inferior' => [
                        'antetitulo' => 'PROINPA',
                        'titulo' => 'Proponen nuevas estrategias para proteger la producción de girasol',
                        'imagen' => 'https://images.unsplash.com/photo-1597848212624-a19eb35e2651?w=800&q=80',
                        'pie_foto' => 'PROINPA. Impulsa control biológico para mejorar el cultivo de girasol.',
                        'autor' => 'Oscar Navia, investigador',
                        'columnas' => [
                            'La Fundación PROINPA presentó nuevas alternativas para enfrentar a la Orobanche cumana, conocida como jopo, una maleza parásita que afecta la producción de girasol en Santa Cruz.',
                            'La propuesta fue expuesta durante el Segundo Simposio Internacional realizado en Santa Cruz, donde se recomendaron combinaciones de híbridos con métodos de control biológico.',
                            'Navia explicó que los microorganismos benéficos del suelo pueden ser identificados y multiplicados para elaborar bioinsumos que protejan las raíces y mejoren el rendimiento.'
                        ]
                    ]
                ]
            ]
        ];
    }
}