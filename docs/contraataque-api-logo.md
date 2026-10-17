# Actualización: logo oficial y ESPN

La cabecera, el pie y los metadatos de Contraataque usan ahora `public/images/contraataque-oficial.png`, el PNG original suministrado por el usuario. El encuadre CSS de cabecera y pie oculta «OPCIÓN 1» sin modificar la imagen y conserva las proporciones del logo. En redes se comparte la imagen original completa.

ESPN queda seleccionado en `config/services.php`, incluso si el alojamiento conserva `SPORTS_DATA_PROVIDER=api-football` de una configuración anterior. No requiere clave. El adaptador alternativo de API-Football permanece disponible en el código, pero no está seleccionado para el sitio.

Después de actualizar el repositorio en el alojamiento habitual ejecutar:

```sh
php artisan config:clear
php artisan view:clear
```

Si el despliegue utiliza caché de configuración, ejecutar después `php artisan config:cache`. No requiere migraciones, compilación Node ni Vercel. Subir también el PNG nuevo.

La cobertura depende de ESPN: una competición no disponible se identifica como tal, sin inventar resultados. Los horarios se presentan en America/La_Paz; los resultados y posiciones usan la caché y el tratamiento de errores existentes.

## Historial de la integración anterior

# Contraataque: marca y datos deportivos

## Marca

`public/images/contraataque-shared-a.svg` une **CONTR + A + TAQUE** con una A roja compartida y el balón Trionda del Mundial 2026 debajo. El texto está convertido a trazados para no depender de fuentes externas. La imagen del balón está incorporada en el SVG; no requiere descargarla desde Adidas al visitar la página.

Cabecera y pie usan el mismo archivo. La versión PNG se utiliza como imagen para compartir en redes. La cabecera tiene tamaños adaptados a móvil y escritorio.

Referencia del balón: Adidas TRIONDA Pro, producto JD8021. Fuente de la imagen: [API pública del producto de Adidas](https://www.adidas.com/api/products/JD8021), imagen de producto HM1. Identificación del modelo: [presentación oficial de Trionda](https://news.adidas.com/world-cup-2026/adidas-unveils--trionda----the-official-match-ball-of-the-fifa-world-cup26-/s/27042e3a-12ba-482d-8839-8a96e056b33e).

## Fuentes de resultados

Hay dos proveedores seleccionables:

| Proveedor | Configuración | Comportamiento |
| --- | --- | --- |
| ESPN | `SPORTS_DATA_PROVIDER=espn` | Consulta sin clave. Es un servicio público sin contrato de cobertura en esta integración; una liga puede no estar disponible. |
| API-Football v3 | `SPORTS_DATA_PROVIDER=api-football` y `API_FOOTBALL_KEY` | Consulta oficial con clave del servidor: ligas, temporadas, partidos y posiciones. La cobertura depende de la cuenta y del plan contratado. |

API-Football utiliza `https://v3.football.api-sports.io`, autenticación `x-apisports-key`, y los endpoints `leagues`, `fixtures` y `standings`. Se resuelve la competición por nombre/país y la temporada por fechas: no se fija una temporada europea al año actual ni se inventan IDs de competiciones.

Documentación del proveedor: [API-Football v3](https://www.api-football.com/documentation-v3) y [guía oficial](https://www.api-football.com/news/post/how-to-get-started-with-api-football-the-complete-beginners-guide).

La comprobación real de ESPN durante este trabajo obtuvo un día sin partidos para Premier y datos no disponibles para Bolivia. Eso no valida la cobertura de Bolivia ni constituye una comprobación de API-Football con credenciales reales. Los escenarios de API-Football se verifican con respuestas simuladas.

## Instalación en latitud18.ultimahora-tv.com

Después de fusionar y actualizar el código, configurar en el `.env` privado del servidor:

```dotenv
SPORTS_DATA_PROVIDER=api-football
API_FOOTBALL_KEY=TU_CLAVE_PRIVADA
SPORTS_REFRESH_SECONDS=180
SPORTS_API_DAILY_BUDGET=90
```

La clave debe introducirse solo en el servidor; no se añade a archivos públicos, JavaScript, respuestas JSON ni Git. No hay una clave incluida en este cambio. Si se mantiene ESPN, usar `SPORTS_DATA_PROVIDER=espn` sin clave.

`SPORTS_API_DAILY_BUDGET` limita las solicitudes que hace esta aplicación a API-Football por clave y día UTC. El valor inicial es 90; debe ajustarse a la cuota real de la cuenta. No contabiliza llamadas hechas desde otras aplicaciones. Al agotar el presupuesto, se conserva la última respuesta identificada como desactualizada y no se siguen enviando solicitudes a la API ese día. Una actualización frecuente de varias ligas requiere una cuota adecuada: subir el presupuesto solo aumenta las llamadas permitidas por la aplicación, no la cuota del proveedor.

Los partidos se almacenan en caché durante el intervalo configurado (mínimo 60 segundos); las posiciones, quince minutos; las ligas/temporadas, veinticuatro horas. Las fallas tienen una pausa de treinta segundos. La última información válida se conserva hasta seis horas con fecha original y sin indicar que sigue en vivo.

```bash
php artisan config:clear
php artisan view:clear
```

No requiere migraciones, Node ni un despliegue en Vercel. Se deben subir también los nuevos archivos de `public/images` y `public/js`.

## Correcciones

- Se eliminan partidos y posiciones ficticios del respaldo boliviano.
- Un día sin partidos se distingue de una falla de conexión, un error de cuota o una respuesta inválida.
- Los horarios y la fecha visible corresponden a Bolivia. ESPN no acepta el rango de días utilizado inicialmente en la prueba real; se consultan dos días UTC en paralelo y se filtran/deduplican para obtener el día boliviano completo.
- Partidos programados muestran guiones en el marcador. Los aplazados/cancelados no se marcan como finales. No se añade dos veces el signo de minuto.
- Los partidos, la tira superior y las posiciones cambian juntos al seleccionar liga. Se puede elegir la fecha de los partidos; la tabla muestra la temporada vigente.
- Las respuestas de una selección anterior no reemplazan la liga actual. La actualización se pausa en una pestaña oculta y se informa cuando falla.
- Las cadenas de los proveedores se escapan en Blade; el navegador recibe fragmentos escapados en lugar de interpolar nombres o estadios directamente en HTML.
- Se eliminan las etiquetas fijas de Clausura/2026 y las zonas de clasificación asumidas sin datos del proveedor.
- Se limita el endpoint público a treinta solicitudes por minuto y dirección IP. Se utiliza una caché nueva para no recuperar respaldos ficticios de la versión anterior.

## Validación

```bash
php vendor/bin/phpunit --filter=SportsDataTest
node --test tests/Frontend/contraataque.test.cjs
php artisan view:cache
```

Las pruebas cubren horarios de Bolivia, días vacíos, errores de API, datos desactualizados, partidos aplazados, clave privada, temporada, presupuesto diario, HTML escapado, cambios de liga y respuestas fuera de orden. También se ejecutó la vista Blade completa en un DOM simulado y se inspeccionó la imagen del logotipo. Queda pendiente la comprobación visual de toda la página y del diseño móvil en un navegador real, así como una llamada autenticada a API-Football en el servidor.
