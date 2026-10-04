<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PeriodicoPlantilla;
use App\Models\PeriodicoEdicion;
use Illuminate\Support\Str;

class PeriodicoSeeder extends Seeder
{
    /**
     * Seed the database with the 9 core templates and migrate existing editions.
     */
    public function run(): void
    {
        $templates = $this->getDefaultTemplatesCatalog();

        foreach ($templates as $tplData) {
            PeriodicoPlantilla::updateOrCreate(
                ['id' => $tplData['id']],
                [
                    'nombre' => $tplData['nombre'],
                    'slug' => Str::slug($tplData['nombre']),
                    'categoria' => $tplData['categoria'],
                    'descripcion' => $tplData['descripcion'] ?? '',
                    'preview_color' => $tplData['preview_color'] ?? '#D71920',
                    'is_custom' => $tplData['is_custom'] ?? false,
                    'frames' => $tplData['frames'] ?? [],
                    'configuracion' => $tplData['configuracion'] ?? ['ancho' => 720, 'alto' => 1040],
                ]
            );
        }

        // Migrar ediciones desde periodicos.json si la tabla está vacía
        if (PeriodicoEdicion::count() === 0) {
            $jsonFile = storage_path('app/periodicos.json');
            if (file_exists($jsonFile)) {
                $edicionesData = json_decode(file_get_contents($jsonFile), true);
                if (is_array($edicionesData)) {
                    foreach ($edicionesData as $edData) {
                        $this->importEdicionFromJson($edData);
                    }
                }
            }

            // Si aún no hay ninguna edición creada, crear la primera edición inicial
            if (PeriodicoEdicion::count() === 0) {
                $portadaTpl = PeriodicoPlantilla::find('tpl_portada_clasica');
                if ($portadaTpl) {
                    $ed = $portadaTpl->crearEdicion([
                        'numero_edicion' => 'Edición 142',
                        'fecha' => 'Domingo, 4 de Octubre de 2026',
                        'titulo' => 'Latitud 18 — Edición Semanal',
                        'subtitulo' => 'Información Sin Ruido',
                        'slogan' => 'El Periódico Digital de Santa Cruz',
                        'ciudad' => 'Santa Cruz de la Sierra',
                        'precio' => 'Bs 7,00',
                    ]);
                    $ed->update(['publicada' => true, 'activa' => true, 'estado' => 'publicado']);
                }
            }
        }
    }

    private function importEdicionFromJson(array $data): void
    {
        $edicionId = $data['id'] ?? ('ed-' . time() . '-' . Str::random(5));

        $edicion = PeriodicoEdicion::create([
            'id' => $edicionId,
            'numero_edicion' => $data['numero_edicion'] ?? 'N° 1',
            'fecha' => $data['fecha'] ?? date('d \d\e F \d\e Y'),
            'titulo' => $data['titulo'] ?? 'Latitud 18',
            'subtitulo' => $data['subtitulo'] ?? 'Información Sin Ruido',
            'slogan' => $data['slogan'] ?? 'El Periódico Digital de Santa Cruz',
            'ciudad' => $data['ciudad'] ?? 'Santa Cruz de la Sierra',
            'precio' => $data['precio'] ?? 'Bs 7,00',
            'num_paginas' => isset($data['paginas']) ? count($data['paginas']) : 1,
            'publicada' => !empty($data['publicada']),
            'activa' => !empty($data['activa']),
            'estado' => $data['estado'] ?? (!empty($data['publicada']) ? 'publicado' : 'borrador'),
            'fecha_programada' => !empty($data['fecha_programada']) ? $data['fecha_programada'] : null,
            'fecha_publicacion' => !empty($data['fecha_publicacion']) ? $data['fecha_publicacion'] : null,
            'pdf_url' => $data['pdf_url'] ?? null,
            'plantilla_id' => 'tpl_portada_clasica',
        ]);

        if (isset($data['paginas']) && is_array($data['paginas'])) {
            foreach ($data['paginas'] as $pIdx => $pData) {
                $pagina = $edicion->paginas()->create([
                    'numero' => $pData['numero'] ?? ($pIdx + 1),
                    'nombre' => $pData['nombre'] ?? ('Página ' . ($pIdx + 1)),
                    'seccion' => $pData['seccion'] ?? ($pIdx === 0 ? 'Portada' : 'General'),
                    'ancho' => 720,
                    'alto' => 1040,
                    'fondo_color' => '#ffffff',
                ]);

                if (isset($pData['frames']) && is_array($pData['frames'])) {
                    foreach ($pData['frames'] as $fIdx => $f) {
                        $pagina->crearElementoDesdeFrame($f, $fIdx);
                    }
                }
            }
        }
    }

    /**
     * Catálogo maestro de las 9 plantillas oficiales de Latitud 18
     */
    public function getDefaultTemplatesCatalog(): array
    {
        return [
            // 1. Portada clásica
            [
                'id' => 'tpl_portada_clasica',
                'nombre' => 'Portada clásica',
                'categoria' => 'portadas',
                'descripcion' => 'Diseño clásico de primera plana: cabecera oficial con orejas de cotización, gran titular a 4 columnas, sumario, fotonoticia principal y llamadas laterales.',
                'preview_color' => '#D71920',
                'is_custom' => false,
                'frames' => [
                    [
                        'id' => 'f-pc-1',
                        'type' => 'masthead',
                        'x' => 20, 'y' => 20, 'w' => 680, 'h' => 112, 'z' => 10,
                        'newspaperName' => 'LATITUD 18',
                        'subBadge' => 'INFORMACIÓN SIN RUIDO',
                        'motto' => 'EL PERIÓDICO DIGITAL DE SANTA CRUZ • FUNDADO EN 2024',
                        'editionDate' => 'Santa Cruz de la Sierra • Bolivia',
                        'editionNumber' => 'Edición Semanal',
                        'price' => 'Bs 7,00',
                        'leftEar' => 'DÓLAR: Bs 6,96',
                        'rightEar' => 'CLIMA: 28°C Soleado'
                    ],
                    [
                        'id' => 'f-pc-2',
                        'type' => 'headline',
                        'x' => 20, 'y' => 140, 'w' => 680, 'h' => 105, 'z' => 9,
                        'kicker' => 'PRIMERA PLANA',
                        'content' => '<p style="font-family:\'Oswald\',sans-serif;font-size:32px;font-weight:700;line-height:1.1;color:#09090b;margin:0;">Santa Cruz lidera la reactivación productiva y proyecta récord en exportaciones para este año</p>'
                    ],
                    [
                        'id' => 'f-pc-3',
                        'type' => 'article',
                        'x' => 20, 'y' => 255, 'w' => 680, 'h' => 80, 'z' => 8,
                        'columns' => 4,
                        'content' => '<p style="font-family:\'Source Sans 3\',sans-serif;font-size:11px;line-height:1.4;text-align:justify;color:#1e293b;margin:0;"><strong>BALANZA COMERCIAL.</strong> El sector agroindustrial y de manufacturas registró un repunte de dos dígitos durante el último trimestre.<br><br><strong>EXPECTATIVA.</strong> Representantes de la Cámara de Exportadores destacaron la apertura de nuevos mercados internacionales. ► PÁG. 4</p>'
                    ],
                    [
                        'id' => 'f-pc-4',
                        'type' => 'divider',
                        'x' => 20, 'y' => 345, 'w' => 680, 'h' => 4, 'z' => 5,
                        'color' => '#cbd5e1'
                    ],
                    [
                        'id' => 'f-pc-5',
                        'type' => 'image',
                        'x' => 20, 'y' => 360, 'w' => 440, 'h' => 280, 'z' => 7,
                        'src' => 'https://images.unsplash.com/photo-1541888946425-d0fbb18f15f6?auto=format&fit=crop&w=1200&q=80',
                        'caption' => 'PANORAMA. Zonas productivas cruceñas en plena etapa de cosecha mecanizada.'
                    ],
                    [
                        'id' => 'f-pc-6',
                        'type' => 'article',
                        'x' => 470, 'y' => 360, 'w' => 230, 'h' => 280, 'z' => 6,
                        'columns' => 1,
                        'kicker' => 'DESARROLLO REGIONAL',
                        'content' => '<p style="font-family:\'Source Sans 3\',sans-serif;font-size:11.5px;line-height:1.45;text-align:justify;color:#1e293b;margin:0;"><strong>ENERGÍAS LIMPIAS.</strong> Nuevos parques eólicos y solares se integran al sistema interconectado nacional reforzando el suministro del parque industrial.<br><br>Empresarios locales solicitan agilizar trámites y garantizar seguridad jurídica para la inversión privada. ► PÁG. 8</p>'
                    ],
                    [
                        'id' => 'f-pc-7',
                        'type' => 'divider',
                        'x' => 20, 'y' => 655, 'w' => 680, 'h' => 3, 'z' => 5,
                        'color' => '#D71920'
                    ],
                    [
                        'id' => 'f-pc-8',
                        'type' => 'article',
                        'x' => 20, 'y' => 670, 'w' => 330, 'h' => 180, 'z' => 6,
                        'columns' => 2,
                        'kicker' => 'CIUDAD & SOCIEDAD',
                        'content' => '<p style="font-family:\'Source Sans 3\',sans-serif;font-size:11px;line-height:1.4;text-align:justify;color:#1e293b;margin:0;"><strong>TRANSPORTE.</strong> Implementan plan de descongestionamiento vial en el tercer anillo y vías troncales.<br><br>Vecinos y transportistas evalúan positivamente la sincronización de semáforos inteligentes. ► PÁG. 10</p>'
                    ],
                    [
                        'id' => 'f-pc-9',
                        'type' => 'article',
                        'x' => 370, 'y' => 670, 'w' => 330, 'h' => 180, 'z' => 6,
                        'columns' => 2,
                        'kicker' => 'SALUD & PREVENCIÓN',
                        'content' => '<p style="font-family:\'Source Sans 3\',sans-serif;font-size:11px;line-height:1.4;text-align:justify;color:#1e293b;margin:0;"><strong>CAMPAÑA VITAL.</strong> Hospitales de segundo nivel refuerzan brigadas móviles de vacunación en distritos periurbanos.<br><br>Se coordinan operativos especiales de fumigación preventiva ante la temporada de lluvias. ► PÁG. 12</p>'
                    ]
                ]
            ],

            // 2. Portada deportiva
            [
                'id' => 'tpl_portada_deportiva',
                'nombre' => 'Portada deportiva',
                'categoria' => 'portadas',
                'descripcion' => 'Impactante primera plana de deportes: gran foto a sangre, titular de acción deportiva y cintillo dinámico con marcadores.',
                'preview_color' => '#00FF87',
                'is_custom' => false,
                'frames' => [
                    [
                        'id' => 'f-pd-1',
                        'type' => 'masthead',
                        'x' => 20, 'y' => 20, 'w' => 680, 'h' => 95, 'z' => 10,
                        'newspaperName' => 'LATITUD 18 • CONTRA ATAQUE',
                        'subBadge' => 'SUPLEMENTO DEPORTIVO',
                        'motto' => 'LA PASIÓN DEL FÚTBOL Y EL DEPORTE EN VIVO',
                        'editionDate' => 'Santa Cruz de la Sierra • Bolivia',
                        'editionNumber' => 'Edición Especial Deportiva',
                        'price' => 'Bs 7,00'
                    ],
                    [
                        'id' => 'f-pd-2',
                        'type' => 'headline',
                        'x' => 20, 'y' => 125, 'w' => 680, 'h' => 85, 'z' => 9,
                        'kicker' => 'FÚTBOL PROFESIONAL',
                        'content' => '<p style="font-family:\'Anton\',sans-serif;font-size:42px;letter-spacing:1px;text-transform:uppercase;color:#D71920;margin:0;line-height:1;">¡GOLEADA Y PUNTERO EN LA COPA!</p>'
                    ],
                    [
                        'id' => 'f-pd-3',
                        'type' => 'image',
                        'x' => 20, 'y' => 220, 'w' => 680, 'h' => 360, 'z' => 8,
                        'src' => 'https://images.unsplash.com/photo-1508098682722-e99c43a406b2?auto=format&fit=crop&w=1200&q=80',
                        'caption' => 'FESTEJO. Euforia total en las tribunas tras el pitazo final del clásico.'
                    ],
                    [
                        'id' => 'f-pd-4',
                        'type' => 'article',
                        'x' => 20, 'y' => 595, 'w' => 450, 'h' => 190, 'z' => 7,
                        'columns' => 2,
                        'kicker' => 'CRÓNICA DEPORTIVA',
                        'content' => '<p style="font-family:\'Source Sans 3\',sans-serif;font-size:11.5px;line-height:1.45;text-align:justify;color:#1e293b;margin:0;"><strong>NOCHE MÁGICA.</strong> Con una demostración de contundencia ofensiva y solidez en todas las líneas, el cuadro local se impuso con autoridad ante su tradicional rival.<br><br>Los goles llegaron en momentos clave del segundo tiempo, desatando la fiesta en un estadio repleto de hinchas. El director técnico felicitó el despliegue físico del plantel. ► PÁG. 16</p>'
                    ],
                    [
                        'id' => 'f-pd-5',
                        'type' => 'box',
                        'x' => 480, 'y' => 595, 'w' => 220, 'h' => 190, 'z' => 6,
                        'borderColor' => '#00FF87',
                        'bgColor' => '#090d16',
                        'content' => '<div style="color:#00FF87;font-family:\'Montserrat\',sans-serif;font-weight:900;font-size:13px;margin-bottom:8px;text-align:center;">TABLA DE POSICIONES</div><div style="color:#fff;font-size:11px;line-height:1.8;font-family:sans-serif;">1. The Strongest • 24 pts<br>2. Bolívar • 22 pts<br>3. Oriente Petrolero • 19 pts<br>4. Blooming • 18 pts<br>5. Always Ready • 16 pts</div>'
                    ]
                ]
            ],

            // 3. Política
            [
                'id' => 'tpl_politica',
                'nombre' => 'Política',
                'categoria' => 'politica',
                'descripcion' => 'Maquetación sobria y analítica para temas de Estado, leyes, asamblea legislativa, gobernación y debate político.',
                'preview_color' => '#1e3a8a',
                'is_custom' => false,
                'frames' => [
                    [
                        'id' => 'f-pol-1',
                        'type' => 'headline',
                        'x' => 20, 'y' => 30, 'w' => 680, 'h' => 90, 'z' => 10,
                        'kicker' => 'POLÍTICA & PODER',
                        'content' => '<p style="font-family:\'Oswald\',sans-serif;font-size:30px;font-weight:700;line-height:1.15;color:#0f172a;margin:0;">Asamblea debate nuevo marco fiscal y descentralización de recursos departamentales</p>'
                    ],
                    [
                        'id' => 'f-pol-2',
                        'type' => 'article',
                        'x' => 20, 'y' => 130, 'w' => 440, 'h' => 300, 'z' => 8,
                        'columns' => 2,
                        'content' => '<p style="font-family:\'Source Sans 3\',sans-serif;font-size:11px;line-height:1.5;text-align:justify;color:#334155;margin:0;"><strong>DEBATE LEGISLATIVO.</strong> Las bancadas parlamentarias iniciaron el tratamiento de la propuesta de pacto fiscal que busca redistribuir los ingresos tributarios y regalías para las regiones.<br><br>El proyecto incluye mecanismos de transparencia y control presupuestario para la ejecución de proyectos de infraestructura en educación y salud. Legisladores de diversas fuerzas manifestaron su disposición a consensuar un texto definitivo en las próximas sesiones ordinarias.</p>'
                    ],
                    [
                        'id' => 'f-pol-3',
                        'type' => 'quote',
                        'x' => 470, 'y' => 130, 'w' => 230, 'h' => 140, 'z' => 9,
                        'content' => '<blockquote style="font-family:\'Source Serif 4\',serif;font-size:15px;font-style:italic;line-height:1.35;color:#1e3a8a;border-left:4px solid #1e3a8a;padding-left:12px;margin:0;">«El país necesita certidumbre económica y autonomía de gestión real para las regiones»</blockquote>'
                    ],
                    [
                        'id' => 'f-pol-4',
                        'type' => 'image',
                        'x' => 470, 'y' => 280, 'w' => 230, 'h' => 150, 'z' => 7,
                        'src' => 'https://images.unsplash.com/photo-1541872703-74c5e44368f9?auto=format&fit=crop&w=600&q=80',
                        'caption' => 'SESIÓN. Plenario de la Asamblea Legislativa.'
                    ],
                    [
                        'id' => 'f-pol-5',
                        'type' => 'divider',
                        'x' => 20, 'y' => 445, 'w' => 680, 'h' => 2, 'z' => 5,
                        'color' => '#94a3b8'
                    ],
                    [
                        'id' => 'f-pol-6',
                        'type' => 'article',
                        'x' => 20, 'y' => 460, 'w' => 680, 'h' => 240, 'z' => 6,
                        'columns' => 3,
                        'kicker' => 'ANÁLISIS DE COYUNTURA',
                        'content' => '<p style="font-family:\'Source Sans 3\',sans-serif;font-size:11px;line-height:1.45;text-align:justify;color:#334155;margin:0;"><strong>CLAVES DEL ACUERDO.</strong> Analistas constitucionales señalan que la viabilidad del proyecto depende de la voluntad política de concertación y del equilibrio entre los niveles centrales y subnacionales.<br><br>Se prevén mesas técnicas con la participación de gobernaciones, municipios y universidades públicas para consensuar las fórmulas de asignación directa.</p>'
                    ]
                ]
            ],

            // 4. Economía
            [
                'id' => 'tpl_economia',
                'nombre' => 'Economía',
                'categoria' => 'economia',
                'descripcion' => 'Diseño financiero con tabla de indicadores macroeconómicos, divisas, comercio exterior y empresas.',
                'preview_color' => '#059669',
                'is_custom' => false,
                'frames' => [
                    [
                        'id' => 'f-eco-1',
                        'type' => 'box',
                        'x' => 20, 'y' => 25, 'w' => 680, 'h' => 65, 'z' => 10,
                        'bgColor' => '#f0fdf4',
                        'borderColor' => '#059669',
                        'content' => '<div style="display:flex;justify-content:space-around;font-family:\'Montserrat\',sans-serif;font-size:11px;font-weight:700;color:#065f46;text-align:center;padding-top:10px;"><div>DÓLAR OFICIAL: <strong>Bs 6,96</strong></div><div>DÓLAR MERCADO: <strong>Bs 12,40</strong></div><div>UFV: <strong>2,5140</strong></div><div>PETRÓLEO WTI: <strong>$78,50</strong></div><div>SOYA CBOT: <strong>$440,00</strong></div></div>'
                    ],
                    [
                        'id' => 'f-eco-2',
                        'type' => 'headline',
                        'x' => 20, 'y' => 105, 'w' => 680, 'h' => 85, 'z' => 9,
                        'kicker' => 'FINANZAS & COMERCIO',
                        'content' => '<p style="font-family:\'Oswald\',sans-serif;font-size:30px;font-weight:700;line-height:1.15;color:#064e3b;margin:0;">Exportaciones agroindustriales superan los $us 2.800 millones impulsadas por valor agregado</p>'
                    ],
                    [
                        'id' => 'f-eco-3',
                        'type' => 'article',
                        'x' => 20, 'y' => 200, 'w' => 430, 'h' => 280, 'z' => 8,
                        'columns' => 2,
                        'content' => '<p style="font-family:\'Source Sans 3\',sans-serif;font-size:11px;line-height:1.5;text-align:justify;color:#1e293b;margin:0;"><strong>BALANZA FAVORABLE.</strong> Los envíos de derivados de oleaginosas, carne bovina, chía y manufacturas alcanzaron cifras históricas durante la presente gestión.<br><br>El sector privado reiteró la necesidad de liberar de manera plena las exportaciones y facilitar el acceso a biotecnología para elevar rendimientos por hectárea sin ampliar la frontera agrícola.</p>'
                    ],
                    [
                        'id' => 'f-eco-4',
                        'type' => 'image',
                        'x' => 460, 'y' => 200, 'w' => 240, 'h' => 180, 'z' => 7,
                        'src' => 'https://images.unsplash.com/photo-1559526324-4b87b5e36e44?auto=format&fit=crop&w=600&q=80',
                        'caption' => 'LOGÍSTICA. Barcazas cargadas en la hidrovía Paraguay-Paraná.'
                    ],
                    [
                        'id' => 'f-eco-5',
                        'type' => 'article',
                        'x' => 20, 'y' => 495, 'w' => 680, 'h' => 200, 'z' => 6,
                        'columns' => 3,
                        'kicker' => 'MERCADOS & CRÉDITO',
                        'content' => '<p style="font-family:\'Source Sans 3\',sans-serif;font-size:11px;line-height:1.45;text-align:justify;color:#1e293b;margin:0;"><strong>SISTEMA BANCARIO.</strong> Los depósitos del público y la cartera de créditos mantienen ritmo de crecimiento con preferencia en préstamos productivos y de vivienda social.<br><br>Empresas medianas aceleran emisión de pagarés en la Bolsa de Valores para diversificar fuentes de financiamiento.</p>'
                    ]
                ]
            ],

            // 5. Página de entrevista
            [
                'id' => 'tpl_entrevista',
                'nombre' => 'Página de entrevista',
                'categoria' => 'entrevistas',
                'descripcion' => 'Estructura periodística para entrevistas a fondo: retrato de autor, cita textual destacada y formato Pregunta / Respuesta.',
                'preview_color' => '#7c3aed',
                'is_custom' => false,
                'frames' => [
                    [
                        'id' => 'f-ent-1',
                        'type' => 'headline',
                        'x' => 20, 'y' => 30, 'w' => 680, 'h' => 95, 'z' => 10,
                        'kicker' => 'LA GRAN ENTREVISTA',
                        'content' => '<p style="font-family:\'Source Serif 4\',serif;font-size:32px;font-weight:700;font-style:italic;line-height:1.15;color:#1e1b4b;margin:0;">«La innovación y la educación técnica son el verdadero motor del siglo XXI»</p>'
                    ],
                    [
                        'id' => 'f-ent-2',
                        'type' => 'image',
                        'x' => 20, 'y' => 135, 'w' => 260, 'h' => 280, 'z' => 8,
                        'src' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=600&q=80',
                        'caption' => 'PERFIL. Especialista en tecnología y desarrollo educativo.'
                    ],
                    [
                        'id' => 'f-ent-3',
                        'type' => 'box',
                        'x' => 20, 'y' => 425, 'w' => 260, 'h' => 160, 'z' => 7,
                        'bgColor' => '#f5f3ff',
                        'borderColor' => '#7c3aed',
                        'content' => '<div style="font-family:\'Montserrat\',sans-serif;font-size:11px;color:#4c1d95;padding:4px;"><strong style="font-size:12px;">FICHA BIOGRÁFICA</strong><br><br><strong>Cargo:</strong> Director de Innovación Tecnológica.<br><strong>Formación:</strong> Máster en Inteligencia Artificial y Economía Digital.<br><strong>Trayectoria:</strong> Más de 18 años asesorando a ecosistemas de startups en América Latina.</div>'
                    ],
                    [
                        'id' => 'f-ent-4',
                        'type' => 'article',
                        'x' => 295, 'y' => 135, 'w' => 405, 'h' => 450, 'z' => 9,
                        'columns' => 2,
                        'content' => '<p style="font-family:\'Source Sans 3\',sans-serif;font-size:11px;line-height:1.55;text-align:justify;color:#1e293b;margin:0;"><strong>— ¿Cuál es el diagnóstico actual del ecosistema productivo cruceño?</strong><br>Estamos en un punto de inflexión. El modelo tradicional ha demostrado ser exitoso, pero el futuro exige incorporar automatización, datos en tiempo real y bioeconomía.<br><br><strong>— ¿Cómo evalúa el talento joven de nuestras universidades?</strong><br>Existe un potencial extraordinario. Los jóvenes tienen hambre de emprender y resolver desafíos reales, pero requieren mayor vinculación entre academia y empresas.<br><br><strong>— ¿Qué rol deben jugar las políticas públicas?</strong><br>Facilitar, no obstaculizar. Desburocratizar la apertura de emprendimientos y otorgar incentivos a quienes inviertan en investigación y desarrollo local.</p>'
                    ]
                ]
            ],

            // 6. Página fotográfica
            [
                'id' => 'tpl_fotografica',
                'nombre' => 'Página fotográfica',
                'categoria' => 'fotografia',
                'descripcion' => 'Diseño visualmente dominante para fotorreportajes: 1 gran foto panorámica y 3 fotos de detalle con epígrafes explicativos.',
                'preview_color' => '#0284c7',
                'is_custom' => false,
                'frames' => [
                    [
                        'id' => 'f-fot-1',
                        'type' => 'headline',
                        'x' => 20, 'y' => 25, 'w' => 680, 'h' => 60, 'z' => 10,
                        'kicker' => 'FOTORREPORTAJE • EL LENTE DE LATITUD 18',
                        'content' => '<p style="font-family:\'Oswald\',sans-serif;font-size:26px;font-weight:700;color:#0f172a;margin:0;">Los guardianes de la Chiquitania: Tradición, fe y naturaleza viva</p>'
                    ],
                    [
                        'id' => 'f-fot-2',
                        'type' => 'image',
                        'x' => 20, 'y' => 95, 'w' => 680, 'h' => 330, 'z' => 9,
                        'src' => 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=1200&q=80',
                        'caption' => 'PANORAMA. Atardecer dorado sobre las serranías chiquitanas en vísperas de las fiestas patronales.'
                    ],
                    [
                        'id' => 'f-fot-3',
                        'type' => 'image',
                        'x' => 20, 'y' => 440, 'w' => 215, 'h' => 210, 'z' => 8,
                        'src' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=600&q=80',
                        'caption' => 'ROSTROS. Tejedoras transmiten saberes ancestrales.'
                    ],
                    [
                        'id' => 'f-fot-4',
                        'type' => 'image',
                        'x' => 250, 'y' => 440, 'w' => 215, 'h' => 210, 'z' => 8,
                        'src' => 'https://images.unsplash.com/photo-1513836279014-a89f7a76ae86?auto=format&fit=crop&w=600&q=80',
                        'caption' => 'ARQUITECTURA. Detalles tallados en madera misional.'
                    ],
                    [
                        'id' => 'f-fot-5',
                        'type' => 'image',
                        'x' => 480, 'y' => 440, 'w' => 220, 'h' => 210, 'z' => 8,
                        'src' => 'https://images.unsplash.com/photo-1518495973542-4542c06a5843?auto=format&fit=crop&w=600&q=80',
                        'caption' => 'FLORA & FAUNA. Especies protegidas en su hábitat natural.'
                    ],
                    [
                        'id' => 'f-fot-6',
                        'type' => 'article',
                        'x' => 20, 'y' => 665, 'w' => 680, 'h' => 85, 'z' => 7,
                        'columns' => 3,
                        'content' => '<p style="font-family:\'Source Sans 3\',sans-serif;font-size:11px;line-height:1.45;color:#475569;margin:0;"><strong>CRÓNICA VISUAL.</strong> Un recorrido de más de 800 kilómetros documentando la resiliencia de las comunidades y la riqueza cultural que define la identidad del oriente boliviano. Fotografías exclusivas de nuestro enviado especial.</p>'
                    ]
                ]
            ],

            // 7. Contra Ataque
            [
                'id' => 'tpl_contraataque',
                'nombre' => 'Contra Ataque',
                'categoria' => 'deportes',
                'descripcion' => 'Página deportiva oficial con marca Contra Ataque, ficha técnica de partido, fotografía de gol y columna de opinión.',
                'preview_color' => '#dc2626',
                'is_custom' => false,
                'frames' => [
                    [
                        'id' => 'f-ca-1',
                        'type' => 'masthead',
                        'x' => 20, 'y' => 20, 'w' => 680, 'h' => 75, 'z' => 10,
                        'newspaperName' => 'CONTRA ATAQUE',
                        'subBadge' => 'PORTAL DEPORTIVO OFICIAL',
                        'motto' => 'INFORMACIÓN DEPORTIVA SIN RUIDO • FÚTBOL & PASIÓN',
                        'editionDate' => 'Edición Especial',
                        'editionNumber' => 'PÁG. DEPORTES'
                    ],
                    [
                        'id' => 'f-ca-2',
                        'type' => 'headline',
                        'x' => 20, 'y' => 105, 'w' => 680, 'h' => 85, 'z' => 9,
                        'kicker' => 'LIGA PROFESIONAL BOLIVIANA',
                        'content' => '<p style="font-family:\'Anton\',sans-serif;font-size:38px;letter-spacing:1px;text-transform:uppercase;color:#09090b;margin:0;line-height:1.05;">¡TRIUNFO AGÓNICO CON GOL AL MINUTO 94!</p>'
                    ],
                    [
                        'id' => 'f-ca-3',
                        'type' => 'image',
                        'x' => 20, 'y' => 200, 'w' => 450, 'h' => 260, 'z' => 8,
                        'src' => 'https://images.unsplash.com/photo-1574629810360-7efbbe195018?auto=format&fit=crop&w=1200&q=80',
                        'caption' => 'DEFINICIÓN. Remate cruzado imparable que desató el delirio de la hinchada.'
                    ],
                    [
                        'id' => 'f-ca-4',
                        'type' => 'box',
                        'x' => 480, 'y' => 200, 'w' => 220, 'h' => 260, 'z' => 7,
                        'bgColor' => '#0f172a',
                        'borderColor' => '#dc2626',
                        'content' => '<div style="color:#00FF87;font-family:\'Montserrat\',sans-serif;font-weight:900;font-size:12px;margin-bottom:8px;text-align:center;">FICHA DEL ENCUENTRO</div><div style="color:#fff;font-size:11px;line-height:1.7;font-family:sans-serif;"><strong>Estadio:</strong> Ramón Tahuichi Aguilera<br><strong>Público:</strong> 28.500 espectadores<br><strong>Árbitro:</strong> Ivo Méndez<br><br><span style="color:#00FF87;">GOLES:</span><br>42\' G. Álvarez (1-0)<br>68\' M. Suárez (1-1)<br>94\' J. Vaca (2-1)<br><br><span style="color:#f87171;">TARJETAS:</span> 3 amarillas, 1 roja</div>'
                    ],
                    [
                        'id' => 'f-ca-5',
                        'type' => 'article',
                        'x' => 20, 'y' => 475, 'w' => 680, 'h' => 220, 'z' => 6,
                        'columns' => 3,
                        'kicker' => 'CRÓNICA & VESTUARIO',
                        'content' => '<p style="font-family:\'Source Sans 3\',sans-serif;font-size:11px;line-height:1.5;text-align:justify;color:#1e293b;margin:0;"><strong>DRAMATISMO HASTA EL FINAL.</strong> Cuando todo indicaba que el reparto de puntos estaba sellado, una triangulación precisa en tres cuartos de cancha rompió el cerrojo defensivo y sentenció la victoria.<br><br>En vestuarios, el estratega destacó el carácter y la convicción del grupo para pelear cada pelota hasta el pitazo final del juez.</p>'
                    ]
                ]
            ],

            // 8. Publicidad
            [
                'id' => 'tpl_publicidad',
                'nombre' => 'Publicidad',
                'categoria' => 'publicidad',
                'descripcion' => 'Página para pauta comercial e institucional de página entera: diseño limpio, llamada a la acción y espacios de marca.',
                'preview_color' => '#d97706',
                'is_custom' => false,
                'frames' => [
                    [
                        'id' => 'f-pub-1',
                        'type' => 'box',
                        'x' => 20, 'y' => 20, 'w' => 680, 'h' => 700, 'z' => 10,
                        'bgColor' => '#0b1329',
                        'borderColor' => '#d97706',
                        'content' => '<div style="padding:40px 24px;text-align:center;color:#fff;font-family:\'Montserrat\',sans-serif;"><div style="font-size:13px;letter-spacing:3px;text-transform:uppercase;color:#fbbf24;font-weight:800;margin-bottom:12px;">ESPACIO EXCLUSIVO DE MARCA</div><h2 style="font-family:\'Oswald\',sans-serif;font-size:38px;font-weight:700;line-height:1.15;color:#fff;max-width:540px;margin:0 auto 20px;">LLEGA A MILES DE LECTORES CADA SEMANA EN LATITUD 18</h2><p style="font-size:14px;color:#94a3b8;max-width:500px;margin:0 auto 30px;line-height:1.6;">El periódico digital más innovador de Santa Cruz. Diseños editoriales interactivos, lecturas en alta fidelidad y alcance regional.</p><div style="display:inline-block;background:#D71920;color:#fff;font-weight:900;padding:12px 28px;border-radius:4px;font-size:14px;letter-spacing:1px;">CONTACTA A NUESTRO EQUIPO COMERCIAL</div><div style="margin-top:35px;font-size:12px;color:#cbd5e1;">WhatsApp: +591 70000000 • publicidad@latitud18.com<br>Santa Cruz de la Sierra • Bolivia</div></div>'
                    ]
                ]
            ],

            // 9. Contraportada
            [
                'id' => 'tpl_contraportada',
                'nombre' => 'Contraportada',
                'categoria' => 'contraportada',
                'descripcion' => 'Diseño de cierre: cartelera cultural, servicios útiles del fin de semana, clima extendido y créditos del staff directivo.',
                'preview_color' => '#0f172a',
                'is_custom' => false,
                'frames' => [
                    [
                        'id' => 'f-cp-1',
                        'type' => 'masthead',
                        'x' => 20, 'y' => 20, 'w' => 680, 'h' => 70, 'z' => 10,
                        'newspaperName' => 'LATITUD 18 • CONTRAPORTADA',
                        'subBadge' => 'CULTURA & SERVICIOS',
                        'motto' => 'EDICIÓN SEMANAL DIGITAL',
                        'editionDate' => 'Santa Cruz de la Sierra',
                        'editionNumber' => 'PÁGINA FINAL'
                    ],
                    [
                        'id' => 'f-cp-2',
                        'type' => 'headline',
                        'x' => 20, 'y' => 100, 'w' => 680, 'h' => 70, 'z' => 9,
                        'kicker' => 'AGENDA CULTURAL & CIUDAD',
                        'content' => '<p style="font-family:\'Oswald\',sans-serif;font-size:26px;font-weight:700;color:#0f172a;margin:0;">Festivales de teatro, conciertos al aire libre y muestras de arte cruceño</p>'
                    ],
                    [
                        'id' => 'f-cp-3',
                        'type' => 'image',
                        'x' => 20, 'y' => 180, 'w' => 360, 'h' => 230, 'z' => 8,
                        'src' => 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?auto=format&fit=crop&w=800&q=80',
                        'caption' => 'ESCENARIO. Temporada cultural en centros históricos de la ciudad.'
                    ],
                    [
                        'id' => 'f-cp-4',
                        'type' => 'box',
                        'x' => 395, 'y' => 180, 'w' => 305, 'h' => 230, 'z' => 7,
                        'bgColor' => '#f8fafc',
                        'borderColor' => '#0f172a',
                        'content' => '<div style="font-family:\'Montserrat\',sans-serif;font-size:11px;padding:6px;"><strong style="color:#D71920;font-size:12px;">CLIMA SEMANAL (SCZ)</strong><br><br><strong>Lunes:</strong> 21°C / 30°C • Despejado<br><strong>Martes:</strong> 22°C / 32°C • Caluroso<br><strong>Miércoles:</strong> 20°C / 27°C • Lluvia aislada<br><strong>Jueves:</strong> 19°C / 26°C • Parcialmente nublado<br><strong>Viernes:</strong> 21°C / 31°C • Soleado</div>'
                    ],
                    [
                        'id' => 'f-cp-5',
                        'type' => 'divider',
                        'x' => 20, 'y' => 425, 'w' => 680, 'h' => 2, 'z' => 5,
                        'color' => '#cbd5e1'
                    ],
                    [
                        'id' => 'f-cp-6',
                        'type' => 'box',
                        'x' => 20, 'y' => 440, 'w' => 680, 'h' => 140, 'z' => 6,
                        'bgColor' => '#0f172a',
                        'borderColor' => '#D71920',
                        'content' => '<div style="color:#fff;font-family:\'Montserrat\',sans-serif;font-size:11px;line-height:1.6;padding:12px;text-align:center;"><strong style="font-size:13px;letter-spacing:1px;color:#D71920;">LATITUD 18 • INFORMACIÓN SIN RUIDO</strong><br>Periódico Digital Semanal y Medio Multiplataforma • Santa Cruz de la Sierra, Bolivia.<br><br><strong>Dirección General:</strong> Redacción Latitud 18 • <strong>Edición Digital:</strong> Equipo Editorial<br>Registro de Propiedad Intelectual en Trámite • Todos los derechos reservados © ' . date('Y') . '</div>'
                    ]
                ]
            ]
        ];
    }
}
