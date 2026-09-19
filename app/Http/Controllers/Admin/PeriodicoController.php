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
    private string $templatesPath;

    public function __construct()
    {
        $this->storagePath = storage_path('app/periodicos.json');
        $this->templatesPath = storage_path('app/periodico_templates.json');
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
        $nuevaEdicion['estado'] = 'borrador';
        $nuevaEdicion['fecha_programada'] = null;
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
        if (isset($data['estado'])) $ediciones[$foundIndex]['estado'] = $data['estado'];
        if (isset($data['fecha_programada'])) $ediciones[$foundIndex]['fecha_programada'] = $data['fecha_programada'];
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
     * Actualiza el estado editorial de la edición (Flujo de trabajo de 5 estados)
     * Borrador -> En Revisión -> Aprobado -> Programado -> Publicado
     */
    public function updateEstado(Request $request, $id)
    {
        $validated = $request->validate([
            'estado' => 'required|string|in:borrador,revision,aprobado,programado,publicado',
            'fecha_programada' => 'nullable|string'
        ]);

        $ediciones = $this->getAllEdiciones();
        $found = false;
        $edicionActualizada = null;

        foreach ($ediciones as &$ed) {
            if ($ed['id'] === $id) {
                $ed['estado'] = $validated['estado'];
                $ed['fecha_programada'] = $validated['fecha_programada'] ?? ($ed['fecha_programada'] ?? null);
                $ed['updated_at'] = now()->toISOString();
                
                if ($validated['estado'] === 'publicado') {
                    $ed['publicada'] = true;
                    $ed['activa'] = true;
                    $ed['fecha_publicacion'] = now()->toISOString();
                } elseif ($validated['estado'] === 'programado') {
                    $ed['publicada'] = false;
                    $ed['activa'] = false;
                }
                $found = true;
                $edicionActualizada = $ed;
                break;
            }
        }
        unset($ed);

        if (!$found) {
            return response()->json(['success' => false, 'message' => 'Edición no encontrada.'], 404);
        }

        if ($validated['estado'] === 'publicado') {
            foreach ($ediciones as &$ed) {
                if ($ed['id'] !== $id) {
                    $ed['activa'] = false;
                }
            }
            unset($ed);
        }

        $this->saveAllEdiciones($ediciones);

        return response()->json([
            'success' => true,
            'message' => 'Estado editorial actualizado a: ' . ucfirst($validated['estado']),
            'estado' => $validated['estado'],
            'fecha_programada' => $edicionActualizada['fecha_programada'] ?? null,
            'edicion' => $edicionActualizada
        ]);
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
            
            // Crear el directorio si no existe
            $destDir = public_path('images/periodico');
            if (!file_exists($destDir)) {
                mkdir($destDir, 0755, true);
            }
            
            $file->move($destDir, $filename);

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
                    'imagen' => $n->getImageUrl(),
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
            'estado' => 'publicado',
            'fecha_programada' => null,
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

    /**
     * Obtiene el catálogo completo de plantillas InDesign
     */
    public function getTemplates()
    {
        $templates = $this->getAllTemplates();
        return response()->json([
            'success' => true,
            'templates' => $templates
        ]);
    }

    /**
     * Lee las plantillas guardadas o inicializa el catálogo maestro
     */
    public function getAllTemplates(): array
    {
        if (!file_exists($this->templatesPath)) {
            $defaults = $this->getDefaultTemplatesCatalog();
            $this->saveAllTemplates($defaults);
            return $defaults;
        }

        $content = file_get_contents($this->templatesPath);
        $data = json_decode($content, true) ?: [];
        if (empty($data)) {
            $data = $this->getDefaultTemplatesCatalog();
            $this->saveAllTemplates($data);
        }
        return $data;
    }

    /**
     * Guarda el listado de plantillas en disco
     */
    public function saveAllTemplates(array $templates): void
    {
        $dir = dirname($this->templatesPath);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($this->templatesPath, json_encode($templates, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /**
     * Guarda una nueva plantilla personalizada a partir de la página actual
     */
    public function storeTemplate(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'category' => 'required|string|max:50',
            'description' => 'nullable|string|max:500',
            'frames' => 'required|array',
            'preview_color' => 'nullable|string|max:30',
        ]);

        $templates = $this->getAllTemplates();
        $newTemplate = [
            'id' => 'tpl_' . time() . '_' . Str::random(5),
            'name' => $validated['name'],
            'category' => $validated['category'],
            'description' => $validated['description'] ?? '',
            'preview_color' => $validated['preview_color'] ?? '#1e293b',
            'frames' => $validated['frames'],
            'created_at' => now()->toISOString(),
            'is_custom' => true,
        ];

        $templates[] = $newTemplate;
        $this->saveAllTemplates($templates);

        return response()->json([
            'success' => true,
            'message' => 'Plantilla "' . $newTemplate['name'] . '" guardada exitosamente.',
            'template' => $newTemplate
        ]);
    }

    /**
     * Importa una plantilla desde archivo .latitud-template o JSON subido
     */
    public function importTemplate(Request $request)
    {
        $request->validate([
            'template_file' => 'required|file|max:10240',
        ]);

        $file = $request->file('template_file');
        $content = file_get_contents($file->getRealPath());
        $data = json_decode($content, true);

        if (!$data || (!isset($data['frames']) && !isset($data['template']['frames']))) {
            return response()->json(['success' => false, 'message' => 'El archivo no contiene una estructura válida de plantilla .latitud-template.'], 422);
        }

        $templateData = isset($data['template']) ? $data['template'] : $data;

        $templates = $this->getAllTemplates();
        $newTemplate = [
            'id' => 'tpl_imp_' . time() . '_' . Str::random(5),
            'name' => ($templateData['name'] ?? 'Plantilla Importada') . ' (Importada)',
            'category' => $templateData['category'] ?? 'general',
            'description' => $templateData['description'] ?? 'Plantilla importada desde archivo externo',
            'preview_color' => $templateData['preview_color'] ?? '#0284c7',
            'frames' => $templateData['frames'] ?? [],
            'created_at' => now()->toISOString(),
            'is_custom' => true,
        ];

        $templates[] = $newTemplate;
        $this->saveAllTemplates($templates);

        return response()->json([
            'success' => true,
            'message' => '¡Plantilla "' . $newTemplate['name'] . '" importada exitosamente!',
            'template' => $newTemplate
        ]);
    }

    /**
     * Elimina una plantilla personalizada
     */
    public function deleteTemplate($id)
    {
        $templates = $this->getAllTemplates();
        $filtered = array_values(array_filter($templates, fn($t) => $t['id'] !== $id));

        $this->saveAllTemplates($filtered);

        return response()->json([
            'success' => true,
            'message' => 'Plantilla eliminada de la biblioteca.'
        ]);
    }

    /**
     * Catálogo maestro inicial de plantillas InDesign
     */
    public function getDefaultTemplatesCatalog(): array
    {
        return [
            [
                'id' => 'tpl_portada_tradicional',
                'name' => 'Portada Tradicional (Gran Formato)',
                'category' => 'portadas',
                'description' => 'Diseño clásico de primera plana: cabecera oficial con orejas de cotización, gran titular a 4 columnas, sumario, fotonoticia principal y llamadas laterales.',
                'preview_color' => '#D71920',
                'is_custom' => false,
                'frames' => [
                    [
                        'id' => 'f-tpl-1',
                        'type' => 'masthead',
                        'x' => 20, 'y' => 20, 'w' => 680, 'h' => 112, 'z' => 10,
                        'newspaperName' => 'LA ESTRELLA',
                        'subBadge' => 'del Oriente',
                        'motto' => 'EL PRIMER PERIÓDICO DE SANTA CRUZ • FUNDADO EN 1864',
                        'editionDate' => 'Santa Cruz de la Sierra • Bolivia',
                        'editionNumber' => 'N° 11.986 • 32 páginas',
                        'price' => 'Bs 7,00',
                        'leftEar' => 'CRE 100% Tarifa Equitativa',
                        'rightEar' => 'DÓLAR: Bs 12,58'
                    ],
                    [
                        'id' => 'f-tpl-2',
                        'type' => 'headline',
                        'x' => 20, 'y' => 140, 'w' => 680, 'h' => 110, 'z' => 9,
                        'kicker' => 'SEGURIDAD NACIONAL',
                        'content' => '<p style="font-family:\'Oswald\',sans-serif;font-size:34px;font-weight:700;line-height:1.08;color:#09090b;margin:0;">Viacha: hallan booster, pólvora negra y material bélico en zona afectada por explosiones</p>'
                    ],
                    [
                        'id' => 'f-tpl-3',
                        'type' => 'article',
                        'x' => 20, 'y' => 260, 'w' => 680, 'h' => 85, 'z' => 8,
                        'columns' => 4,
                        'content' => '<p style="font-family:\'Source Sans 3\',sans-serif;font-size:11px;line-height:1.4;text-align:justify;color:#1e293b;margin:0;"><strong>RIESGO.</strong> La zona afectada por las explosiones en Viacha continúa en alto riesgo debido al hallazgo de pólvora negra y boosters de alto poder.<br><br><strong>INSPECCIÓN.</strong> El ministro de Defensa Ernesto Justiniano confirmó el uso de drones para identificar depósitos secundarios de munición militar. ► PÁG. 6</p>'
                    ],
                    [
                        'id' => 'f-tpl-4',
                        'type' => 'divider',
                        'x' => 20, 'y' => 355, 'w' => 680, 'h' => 4, 'z' => 5,
                        'color' => '#cbd5e1'
                    ],
                    [
                        'id' => 'f-tpl-5',
                        'type' => 'image',
                        'x' => 20, 'y' => 370, 'w' => 450, 'h' => 380, 'z' => 7,
                        'src' => 'https://images.unsplash.com/photo-1511578314322-379afb476865?w=1000&q=80',
                        'caption' => 'CELEBRACIÓN. Una noche de música, danza y tradición reunió a más de 600 personas en la retreta cultural organizada por la CRE en homenaje al grito libertario de 1810 frente al "Arco de la Cruceñidad". ► PÁG. 3'
                    ],
                    [
                        'id' => 'f-tpl-6',
                        'type' => 'box',
                        'x' => 485, 'y' => 370, 'w' => 215, 'h' => 185, 'z' => 6,
                        'content' => '<span style="font-size:9.5px;font-weight:800;color:#0B1F3A;text-transform:uppercase;">SEGURIDAD</span><h6 style="font-family:\'Oswald\',sans-serif;font-size:15px;font-weight:700;line-height:1.2;margin:4px 0;">SURTIDOR OCULTÓ 3.000 LITROS DE COMBUSTIBLE</h6><p style="font-size:10.5px;color:#475569;line-height:1.35;margin:0;">Un surtidor de YPFB en Cabezas habría ocultado miles de litros de gasolina. ► PÁG. 10</p>'
                    ],
                    [
                        'id' => 'f-tpl-7',
                        'type' => 'box',
                        'x' => 485, 'y' => 565, 'w' => 215, 'h' => 185, 'z' => 6,
                        'content' => '<span style="font-size:9.5px;font-weight:800;color:#D71920;text-transform:uppercase;">DEPORTES</span><h6 style="font-family:\'Oswald\',sans-serif;font-size:15px;font-weight:700;line-height:1.2;margin:4px 0;">ORIENTE PETROLERO PIERDE 3 PUNTOS POR DEUDA</h6><p style="font-size:10.5px;color:#475569;line-height:1.35;margin:0;">El tribunal falló en contra por deuda pendiente con Diego Bejarano. ► PÁG. 15</p>'
                    ],
                    [
                        'id' => 'f-tpl-8',
                        'type' => 'box',
                        'x' => 20, 'y' => 765, 'w' => 680, 'h' => 45, 'z' => 5,
                        'content' => '<div style="display:flex;align-items:center;gap:8px;"><span style="background:#D71920;color:#fff;padding:2px 6px;font-size:9px;font-weight:800;">ALERTA</span><span style="font-size:11px;font-weight:700;color:#0f172a;">MENOR ABUSADA SEXUALMENTE FALLECE POR ENFERMEDAD DE TRANSMISIÓN ► PÁG. 9</span></div>'
                    ]
                ]
            ],
            [
                'id' => 'tpl_portada_tabloide',
                'name' => 'Portada Tabloide / Moderna',
                'category' => 'portadas',
                'description' => 'Diseño contemporáneo de alto contraste: gran imagen hero a sangre, titular tipográfico audaz superpuesto y cuadrícula inferior de noticias.',
                'preview_color' => '#0284c7',
                'is_custom' => false,
                'frames' => [
                    [
                        'id' => 'f-tpl-tab-1',
                        'type' => 'masthead',
                        'x' => 20, 'y' => 20, 'w' => 680, 'h' => 90, 'z' => 10,
                        'newspaperName' => 'LATITUD 18',
                        'subBadge' => 'TABLOIDE',
                        'motto' => 'EL DIARIO DIGITAL DE BOLIVIA • EDICIÓN ESPECIAL',
                        'editionDate' => 'Santa Cruz de la Sierra',
                        'editionNumber' => 'Edición Central',
                        'price' => 'Bs 7,00',
                        'leftEar' => 'PORTAL DIGITAL',
                        'rightEar' => 'COTIZACIÓN: Bs 12,58'
                    ],
                    [
                        'id' => 'f-tpl-tab-2',
                        'type' => 'image',
                        'x' => 20, 'y' => 120, 'w' => 680, 'h' => 360, 'z' => 5,
                        'src' => 'https://images.unsplash.com/photo-1541872703-74c5e44368f9?w=1000&q=80',
                        'caption' => 'IMPACTO. Emergencia climática y sequía movilizan brigadas de rescate en la Chiquitania boliviana.'
                    ],
                    [
                        'id' => 'f-tpl-tab-3',
                        'type' => 'headline',
                        'x' => 40, 'y' => 340, 'w' => 640, 'h' => 120, 'z' => 9,
                        'kicker' => 'PRIMERA PLANA',
                        'content' => '<p style="font-family:\'Montserrat\',sans-serif;font-size:30px;font-weight:900;line-height:1.05;color:#ffffff;text-shadow:0 3px 12px rgba(0,0,0,0.85);margin:0;">ALERTA ROJA EN LA CHIQUITANIA POR INCENDIOS Y OLA DE CALOR EXTREMO</p>'
                    ],
                    [
                        'id' => 'f-tpl-tab-4',
                        'type' => 'divider',
                        'x' => 20, 'y' => 490, 'w' => 680, 'h' => 4, 'z' => 5,
                        'color' => '#D71920'
                    ],
                    [
                        'id' => 'f-tpl-tab-5',
                        'type' => 'box',
                        'x' => 20, 'y' => 510, 'w' => 215, 'h' => 250, 'z' => 6,
                        'content' => '<img src="https://images.unsplash.com/photo-1526304640581-d334cdbbf45e?w=400&q=80" style="width:100%;height:110px;object-fit:cover;border-radius:2px;margin-bottom:6px;"><span style="font-size:9px;font-weight:800;color:#0284c7;text-transform:uppercase;">ECONOMÍA</span><h6 style="font-family:\'Oswald\',sans-serif;font-size:14px;font-weight:700;line-height:1.2;margin:2px 0;">Dólar paralelo marca nuevo récord y presiona importaciones</h6><p style="font-size:10px;color:#64748b;line-height:1.3;margin:0;">Sectores comerciales piden medidas urgentes al Banco Central.</p>'
                    ],
                    [
                        'id' => 'f-tpl-tab-6',
                        'type' => 'box',
                        'x' => 250, 'y' => 510, 'w' => 220, 'h' => 250, 'z' => 6,
                        'content' => '<img src="https://images.unsplash.com/photo-1461896836934-ffe607ba8211?w=400&q=80" style="width:100%;height:110px;object-fit:cover;border-radius:2px;margin-bottom:6px;"><span style="font-size:9px;font-weight:800;color:#16a34a;text-transform:uppercase;">DEPORTES</span><h6 style="font-family:\'Oswald\',sans-serif;font-size:14px;font-weight:700;line-height:1.2;margin:2px 0;">Bolívar y The Strongest listos para el súper clásico paceño</h6><p style="font-size:10px;color:#64748b;line-height:1.3;margin:0;">El Hernando Siles espera a más de 35.000 espectadores este domingo.</p>'
                    ],
                    [
                        'id' => 'f-tpl-tab-7',
                        'type' => 'box',
                        'x' => 485, 'y' => 510, 'w' => 215, 'h' => 250, 'z' => 6,
                        'content' => '<img src="https://images.unsplash.com/photo-1517486808906-6ca8b3f04846?w=400&q=80" style="width:100%;height:110px;object-fit:cover;border-radius:2px;margin-bottom:6px;"><span style="font-size:9px;font-weight:800;color:#9333ea;text-transform:uppercase;">CULTURA</span><h6 style="font-family:\'Oswald\',sans-serif;font-size:14px;font-weight:700;line-height:1.2;margin:2px 0;">Festival Internacional de Teatro abre telón en Santa Cruz</h6><p style="font-size:10px;color:#64748b;line-height:1.3;margin:0;">Compañías de 12 países presentarán más de 40 obras gratuitas.</p>'
                    ]
                ]
            ],
            [
                'id' => 'tpl_reportaje_4col',
                'name' => 'Interior: Reportaje a 4 Columnas',
                'category' => 'interior',
                'description' => 'Maqueta editorial a 4 columnas con cintillo de sección superior, gran titular, bajada explicativa, dropcap y recuadro de infografía/datos clave.',
                'preview_color' => '#0f766e',
                'is_custom' => false,
                'frames' => [
                    [
                        'id' => 'f-tpl-rep-1',
                        'type' => 'box',
                        'x' => 20, 'y' => 20, 'w' => 680, 'h' => 32, 'z' => 5,
                        'content' => '<div style="background:#0F172A;color:#fff;padding:6px 12px;font-family:\'Anton\',sans-serif;font-size:16px;letter-spacing:1px;display:flex;justify-content:space-between;"><span>INFORME ESPECIAL // INVESTIGACIÓN</span><span>PÁGINA INTERIOR</span></div>'
                    ],
                    [
                        'id' => 'f-tpl-rep-2',
                        'type' => 'headline',
                        'x' => 20, 'y' => 65, 'w' => 680, 'h' => 85, 'z' => 8,
                        'kicker' => 'EXPANSIÓN PRODUCTIVA',
                        'content' => '<p style="font-family:\'Playfair Display\',serif;font-size:32px;font-weight:900;line-height:1.1;color:#0f172a;margin:0;">La transición energética e industrial impulsa inversiones millonarias en el oriente boliviano</p>'
                    ],
                    [
                        'id' => 'f-tpl-rep-3',
                        'type' => 'quote',
                        'x' => 20, 'y' => 160, 'w' => 680, 'h' => 55, 'z' => 7,
                        'content' => '<p style="font-family:\'Source Sans 3\',sans-serif;font-size:14px;font-style:italic;color:#475569;line-height:1.35;margin:0;">Con una inyección de capital estimada en más de $us 280 millones, proyectos de biomasa, energía solar y agroindustria transforman los corredores productivos de Santa Cruz y Beni.</p>'
                    ],
                    [
                        'id' => 'f-tpl-rep-4',
                        'type' => 'image',
                        'x' => 20, 'y' => 230, 'w' => 430, 'h' => 240, 'z' => 7,
                        'src' => 'https://images.unsplash.com/photo-1473341304170-971dccb5ac1e?w=800&q=80',
                        'caption' => 'INFRAESTRUCTURA. Planta generadora en construcción en la zona industrial cruceña.'
                    ],
                    [
                        'id' => 'f-tpl-rep-5',
                        'type' => 'box',
                        'x' => 465, 'y' => 230, 'w' => 235, 'h' => 240, 'z' => 6,
                        'content' => '<div style="background:#f1f5f9;border-left:4px solid #0284c7;padding:12px;height:100%;box-sizing:border-box;"><h6 style="font-size:12px;font-weight:900;color:#0b1f3a;margin-bottom:8px;text-transform:uppercase;">CIFRAS CLAVE DEL SECTOR</h6><ul style="font-size:10.5px;color:#334155;padding-left:14px;line-height:1.6;margin:0;"><li><strong>$us 280 MM</strong> en inversión privada ejecutada</li><li><strong>4.500 empleos directos</strong> generados en 2026</li><li><strong>35% de reducción</strong> en emisiones industriales</li><li><strong>8 parques tecnológicos</strong> en etapa de ampliación</li></ul></div>'
                    ],
                    [
                        'id' => 'f-tpl-rep-6',
                        'type' => 'article',
                        'x' => 20, 'y' => 485, 'w' => 680, 'h' => 320, 'z' => 8,
                        'columns' => 4,
                        'content' => '<p style="font-family:\'Source Sans 3\',sans-serif;font-size:11.5px;line-height:1.55;text-align:justify;color:#1e293b;"><span style="font-family:\'Anton\',sans-serif;font-size:36px;float:left;line-height:0.85;margin-right:6px;color:#0284c7;">E</span>l dinamismo económico de la región oriental continúa consolidándose como la locomotora del desarrollo nacional. Durante el presente año, una conjunción de inversiones de capital mixto ha posibilitado la puesta en marcha de complejos fabriles y de generación sostenible.<br><br>Empresarios del sector destacaron que el acceso a financiamiento verde y la apertura de mercados externos brindan condiciones favorables para la expansión sostenida durante la próxima década.</p>'
                    ]
                ]
            ],
            [
                'id' => 'tpl_opinion_editorial',
                'name' => 'Página de Opinión & Columnistas',
                'category' => 'opinion',
                'description' => 'Diseño para páginas de opinión: columna editorial con firma del director a la izquierda, tribuna de columnistas con citas y caja de indicadores.',
                'preview_color' => '#854d0e',
                'is_custom' => false,
                'frames' => [
                    [
                        'id' => 'f-tpl-op-1',
                        'type' => 'box',
                        'x' => 20, 'y' => 20, 'w' => 680, 'h' => 32, 'z' => 5,
                        'content' => '<div style="background:#0B1F3A;color:#fff;padding:6px 12px;font-family:\'Anton\',sans-serif;font-size:16px;letter-spacing:1px;display:flex;justify-content:space-between;"><span>EDITORIAL & TRIBUNA LIBRE</span><span>PÁGINA 2</span></div>'
                    ],
                    [
                        'id' => 'f-tpl-op-2',
                        'type' => 'box',
                        'x' => 20, 'y' => 65, 'w' => 240, 'h' => 450, 'z' => 6,
                        'content' => '<div style="background:#fafafa;border:1px solid #e2e8f0;padding:12px;height:100%;box-sizing:border-box;"><span style="font-size:10px;font-weight:900;color:#D71920;text-transform:uppercase;">EDITORIAL DEL DIARIO</span><h5 style="font-family:\'Playfair Display\',serif;font-size:17px;font-weight:900;color:#0f172a;margin:6px 0;">El valor irremplazable de la verdad periodística</h5><p style="font-size:11px;line-height:1.5;color:#334155;text-align:justify;"><span style="font-family:\'Anton\',sans-serif;font-size:34px;float:left;line-height:0.8;margin-right:5px;color:#D71920;">E</span>n tiempos donde la desinformación circula a la velocidad de un clic, el rigor periodístico se convierte en un bien público indispensable. Investigar, contrastar y verificar no son meras formalidades, sino el pacto ético inquebrantable con nuestros lectores.</p><div style="border-top:1px solid #cbd5e1;margin-top:14px;padding-top:8px;font-weight:800;font-size:10px;color:#0f172a;">DR. CARLOS SUBIRANA<br><span style="font-weight:500;color:#64748b;">Director General</span></div></div>'
                    ],
                    [
                        'id' => 'f-tpl-op-3',
                        'type' => 'headline',
                        'x' => 280, 'y' => 65, 'w' => 420, 'h' => 55, 'z' => 7,
                        'kicker' => 'COLUMNA DE OPINIÓN',
                        'content' => '<p style="font-family:\'Playfair Display\',serif;font-size:22px;font-weight:800;color:#0B1F3A;margin:0;">¿Hacia dónde va la educación en la era de la inteligencia artificial?</p>'
                    ],
                    [
                        'id' => 'f-tpl-op-4',
                        'type' => 'article',
                        'x' => 280, 'y' => 130, 'w' => 420, 'h' => 200, 'z' => 7,
                        'columns' => 2,
                        'content' => '<p style="font-size:11px;line-height:1.5;text-align:justify;color:#1e293b;"><strong>POR: LIC. MARCELA JUSTINIANO.</strong> La integración de herramientas inteligentes en las aulas de colegios y universidades plantea una interrogante fundamental: ¿estamos formando pensadores críticos o simples consumidores de algoritmos? La pedagogía debe reinventarse sin perder la empatía humana.</p>'
                    ],
                    [
                        'id' => 'f-tpl-op-5',
                        'type' => 'quote',
                        'x' => 280, 'y' => 340, 'w' => 420, 'h' => 85, 'z' => 7,
                        'content' => '<p style="font-size:13px;font-style:italic;color:#1e293b;line-height:1.35;margin:0;">"La tecnología multiplica las respuestas, pero la filosofía sigue enseñándonos a formular las preguntas esenciales."</p>'
                    ],
                    [
                        'id' => 'f-tpl-op-6',
                        'type' => 'box',
                        'x' => 20, 'y' => 530, 'w' => 680, 'h' => 150, 'z' => 6,
                        'content' => '<div style="background:#f8fafc;border:1px solid #e2e8f0;padding:10px;border-radius:4px;"><span style="font-size:10px;font-weight:900;color:#0284c7;text-transform:uppercase;">INDICADORES ECONÓMICOS DE BOLIVIA</span><div style="display:flex;gap:16px;margin-top:8px;font-size:11px;"><div style="flex:1;"><strong>DÓLAR OFICIAL:</strong> Bs 6,96</div><div style="flex:1;"><strong>DÓLAR PARALELO:</strong> Bs 12,58</div><div style="flex:1;"><strong>UFV:</strong> 3.34041</div><div style="flex:1;"><strong>EURO:</strong> Bs 14,60</div><div style="flex:1;"><strong>INFLACIÓN:</strong> 4,2%</div></div></div>'
                    ]
                ]
            ],
            [
                'id' => 'tpl_contraataque_deportes',
                'name' => 'Contra Ataque / Deportes',
                'category' => 'deportes',
                'description' => 'Diseño vibrante de la sección deportiva: cabecera Contra Ataque, titular de fútbol, foto de acción, tabla de posiciones y crónica.',
                'preview_color' => '#dc2626',
                'is_custom' => false,
                'frames' => [
                    [
                        'id' => 'f-tpl-dep-1',
                        'type' => 'box',
                        'x' => 20, 'y' => 20, 'w' => 680, 'h' => 45, 'z' => 10,
                        'content' => '<div style="background:linear-gradient(90deg, #D71920 0%, #111827 100%);color:#fff;padding:8px 16px;display:flex;align-items:center;justify-content:space-between;border-radius:2px;"><div style="display:flex;align-items:center;gap:10px;"><span style="font-family:\'Anton\',sans-serif;font-size:26px;letter-spacing:2px;color:#fff;">CONTRA ATAQUE</span><span style="background:#fbbf24;color:#000;font-weight:900;font-size:10px;padding:2px 6px;border-radius:2px;">DEPORTES</span></div><span style="font-family:\'Oswald\',sans-serif;font-size:12px;font-weight:700;">LIGA DEPORTIVA BOLIVIANA</span></div>'
                    ],
                    [
                        'id' => 'f-tpl-dep-2',
                        'type' => 'headline',
                        'x' => 20, 'y' => 75, 'w' => 680, 'h' => 70, 'z' => 9,
                        'kicker' => 'CLÁSICO NACIONAL',
                        'content' => '<p style="font-family:\'Anton\',sans-serif;font-size:32px;letter-spacing:0.5px;color:#0f172a;line-height:1.1;margin:0;">BOLÍVAR DERROTA A THE STRONGEST EN UN ÉPICO 3-2 EN LA PAZ</p>'
                    ],
                    [
                        'id' => 'f-tpl-dep-3',
                        'type' => 'image',
                        'x' => 20, 'y' => 155, 'w' => 450, 'h' => 280, 'z' => 7,
                        'src' => 'https://images.unsplash.com/photo-1508098682722-e99c43a406b2?w=800&q=80',
                        'caption' => 'FESTEJO. Los celestes celebraron el gol agónico al minuto 94 que definió la punta del torneo apertura 2026.'
                    ],
                    [
                        'id' => 'f-tpl-dep-4',
                        'type' => 'box',
                        'x' => 485, 'y' => 155, 'w' => 215, 'h' => 280, 'z' => 6,
                        'content' => '<div style="background:#1e293b;color:#fff;padding:10px;height:100%;box-sizing:border-box;border-radius:2px;"><h6 style="font-family:\'Oswald\',sans-serif;font-size:13px;font-weight:700;color:#fbbf24;margin-bottom:8px;text-transform:uppercase;border-bottom:1px solid #334155;padding-bottom:4px;">TABLA DE POSICIONES</h6><table style="width:100%;font-size:10px;border-collapse:collapse;color:#cbd5e1;"><tr style="color:#94a3b8;font-weight:700;"><td>#</td><td>CLUB</td><td>PJ</td><td>PTS</td></tr><tr><td>1</td><td>Bolívar</td><td>18</td><td><strong style="color:#fff;">42</strong></td></tr><tr><td>2</td><td>The Strongest</td><td>18</td><td><strong style="color:#fff;">39</strong></td></tr><tr><td>3</td><td>Always Ready</td><td>17</td><td><strong style="color:#fff;">34</strong></td></tr><tr><td>4</td><td>Oriente P.</td><td>18</td><td><strong style="color:#fff;">30</strong></td></tr><tr><td>5</td><td>Blooming</td><td>18</td><td><strong style="color:#fff;">28</strong></td></tr><tr><td>6</td><td>Wilstermann</td><td>17</td><td><strong style="color:#fff;">25</strong></td></tr></table></div>'
                    ],
                    [
                        'id' => 'f-tpl-dep-5',
                        'type' => 'article',
                        'x' => 20, 'y' => 450, 'w' => 680, 'h' => 160, 'z' => 8,
                        'columns' => 3,
                        'content' => '<p style="font-family:\'Source Sans 3\',sans-serif;font-size:11.5px;line-height:1.5;text-align:justify;color:#1e293b;">En un encuentro electrizante cargado de vértigo y dramatismo en el estadio Hernando Siles, la Academia paceña se quedó con el clásico gracias a un cabezazo letal en el tiempo de descuento. Con este triunfo, Bolívar se consolida como líder absoluto del fútbol boliviano.</p>'
                    ]
                ]
            ],
            [
                'id' => 'tpl_contraportada_cierre',
                'name' => 'Contraportada de Cierre & Cultura',
                'category' => 'contraportada',
                'description' => 'Última página del periódico: reportaje fotográfico cultural, módulo publicitario de media página y código QR de suscripción a la edición digital.',
                'preview_color' => '#475569',
                'is_custom' => false,
                'frames' => [
                    [
                        'id' => 'f-tpl-cp-1',
                        'type' => 'box',
                        'x' => 20, 'y' => 20, 'w' => 680, 'h' => 34, 'z' => 5,
                        'content' => '<div style="background:#1E293B;color:#fff;padding:6px 14px;font-family:\'Anton\',sans-serif;font-size:17px;display:flex;justify-content:space-between;"><span>CONTRAPORTADA // CULTURA & CIUDAD</span><span>ÚLTIMA PÁGINA</span></div>'
                    ],
                    [
                        'id' => 'f-tpl-cp-2',
                        'type' => 'headline',
                        'x' => 20, 'y' => 65, 'w' => 680, 'h' => 60, 'z' => 8,
                        'kicker' => 'ARTE Y PATRIMONIO CRUCEÑO',
                        'content' => '<p style="font-family:\'Playfair Display\',serif;font-size:26px;font-weight:900;color:#0f172a;margin:0;">El renacer de las misiones jesuíticas a través de la música barroca</p>'
                    ],
                    [
                        'id' => 'f-tpl-cp-3',
                        'type' => 'image',
                        'x' => 20, 'y' => 135, 'w' => 450, 'h' => 240, 'z' => 7,
                        'src' => 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?w=800&q=80',
                        'caption' => 'ARMONÍA. Músicos jóvenes interpretan partituras originales del siglo XVIII en San Javier.'
                    ],
                    [
                        'id' => 'f-tpl-cp-4',
                        'type' => 'qr',
                        'x' => 485, 'y' => 135, 'w' => 215, 'h' => 240, 'z' => 7,
                        'url' => 'https://latitud18.com/periodico',
                        'label' => 'Escanea para edición digital'
                    ],
                    [
                        'id' => 'f-tpl-cp-5',
                        'type' => 'article',
                        'x' => 20, 'y' => 390, 'w' => 680, 'h' => 120, 'z' => 8,
                        'columns' => 3,
                        'content' => '<p style="font-family:\'Source Sans 3\',sans-serif;font-size:11.5px;line-height:1.5;text-align:justify;color:#1e293b;">El festival reúne a más de tres mil personas en los templos históricos de la Chiquitania boliviana, conservando un legado musical vivo único en el continente.</p>'
                    ],
                    [
                        'id' => 'f-tpl-cp-6',
                        'type' => 'ad',
                        'x' => 20, 'y' => 525, 'w' => 680, 'h' => 240, 'z' => 6,
                        'badge' => 'ESPACIO PUBLICITARIO OFICIAL',
                        'title' => 'CRE — Cooperativa Rural de Electrificación',
                        'subtitle' => 'Iluminando el desarrollo de Santa Cruz con energía limpia y equitativa • Más de 60 años al servicio de nuestra gente',
                        'bg' => '#f0fdf4',
                        'border' => '#86efac'
                    ]
                ]
            ]
        ];
    }
}