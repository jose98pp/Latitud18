<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

/**
 * Genera el PDF real (server-side) de una edición del periódico digital.
 *
 * Usado por el visor público 3D y por el panel administrativo, de modo que
 * ambos entreguen exactamente el mismo archivo: si el administrador subió un
 * PDF terminado se sirve ese archivo; si no, se compone desde la estructura
 * de la edición con DomPDF.
 */
class PeriodicoPdfService
{
    /**
     * Devuelve un response de descarga con el PDF real de la edición.
     *
     * @param  array  $edicion  Estructura de la edición
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function descarga(array $edicion)
    {
        // If pdf_url is set but file doesn't exist on disk, resolveUploadedPdf() returns null
        // and we fall through to DomPDF rendering — never returning 404 (Req 7.9)
        // 1) PDF subido por el administrador, si existe
        $subido = $this->resolveUploadedPdf($edicion);
        if ($subido) {
            return response()->download($subido, $this->filename($edicion), [
                'Content-Type' => 'application/pdf',
                'Cache-Control' => 'private, no-store',
            ]);
        }

        // 2) Composición del PDF en el servidor
        if (empty($edicion['paginas'])) {
            throw new \App\Exceptions\EdicionSinPaginasException($edicion['id'] ?? 'desconocida');
        }

        $this->prepararCacheFuentes();
        $pdf = Pdf::loadView('periodico.pdf', ['edicion' => $edicion])
            ->setOptions([
                'isRemoteEnabled'      => true,
                'isHtml5ParserEnabled' => true,
                'defaultFont'          => 'DejaVu Serif',
            ], true);

        $response = $pdf->download($this->filename($edicion));
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Cache-Control', 'private, no-store');

        return $response;
    }

    /**
     * Devuelve el PDF como string (para previsualizar o adjuntar).
     */
    public function contenido(array $edicion): string
    {
        // If pdf_url is set but file doesn't exist on disk, resolveUploadedPdf() returns null
        // and we fall through to DomPDF rendering — never returning 404 (Req 7.9)
        $subido = $this->resolveUploadedPdf($edicion);
        if ($subido) {
            return (string) file_get_contents($subido);
        }

        if (empty($edicion['paginas'])) {
            throw new \App\Exceptions\EdicionSinPaginasException($edicion['id'] ?? 'desconocida');
        }

        $this->prepararCacheFuentes();
        return Pdf::loadView('periodico.pdf', ['edicion' => $edicion])
            ->setOptions([
                'isRemoteEnabled'      => true,
                'isHtml5ParserEnabled' => true,
                'defaultFont'          => 'DejaVu Serif',
            ], true)
            ->output();
    }

    private function prepararCacheFuentes(): void
    {
        foreach (['font_dir', 'font_cache'] as $key) {
            $path = config('dompdf.options.'.$key, storage_path('fonts'));
            if ($path && !is_dir($path) && !mkdir($path, 0755, true) && !is_dir($path)) {
                throw new \RuntimeException('No se pudo crear el directorio de fuentes del PDF.');
            }
        }
    }

    /** Resuelve imágenes públicas en disco, también cuando /storage es un symlink. */
    public function imagenParaPdf(?string $url): string
    {
        $url = trim((string) $url);
        if ($url === '') return '';
        if (preg_match('#^data:image/(png|jpe?g|gif|webp);base64,[a-z0-9+/=\s]+$#i', $url)) return $url;
        if (preg_match('#^[a-z][a-z0-9+.-]*:#i', $url) && !preg_match('#^https?://#i', $url)) return '';
        $path = rawurldecode((string) parse_url($url, PHP_URL_PATH));
        if (str_contains($path, "\0")) return '';
        $relative = ltrim($path, '/');
        $roots = [public_path(), Storage::disk('public')->path('')];
        $candidates = [public_path($relative)];
        if (str_starts_with($relative, 'storage/')) {
            $candidates[] = Storage::disk('public')->path(substr($relative, 8));
        }
        foreach ($candidates as $candidate) {
            $resolved = realpath($candidate);
            if (!$resolved || !is_file($resolved)) continue;
            foreach ($roots as $root) {
                $root = realpath($root);
                if ($root && str_starts_with($resolved, $root.DIRECTORY_SEPARATOR)) return $resolved;
            }
        }
        return preg_match('#^https?://#i', $url) ? $url : '';
    }

    public function estiloTextoParaPdf(array $styles, float $scale): string
    {
        $allowed = ['fontFamily', 'fontSize', 'lineHeight', 'color', 'textAlign', 'backgroundColor', 'fontWeight', 'fontStyle', 'textDecoration', 'textTransform', 'letterSpacing'];
        $css = '';
        foreach ($allowed as $property) {
            $value = (string) ($styles[$property] ?? '');
            if ($value === '' || !preg_match('/^[a-z0-9#.,%()\'"\s-]+$/i', $value)) continue;
            $name = strtolower(preg_replace('/[A-Z]/', '-$0', $property));
            $value = preg_replace_callback('/([0-9]+(?:\.[0-9]+)?)px\b/', fn($m) => ((float)$m[1]*$scale).'mm', $value);
            $css .= $name.':'.$value.';';
        }
        return $css;
    }

    /** DomPDF no soporta column-count: repartir texto conservando sus etiquetas. */
    public function columnasParaPdf(string $html, int $count): array
    {
        $count = max(1, min(6, $count));
        if ($count === 1 || trim(strip_tags($html)) === '') return [$html];
        $document = new \DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        try {
            $document->loadHTML('<?xml encoding="UTF-8"><div id="pdf-columns">'.$html.'</div>');
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
        $source = $document->getElementById('pdf-columns');
        if (!$source) return [$html];
        $words = preg_match_all('/\S+/u', $source->textContent);
        $perColumn = max(1, (int) ceil($words / $count));
        $roots = [];
        for ($i = 0; $i < $count; $i++) $roots[] = $document->createElement('div');
        $column = 0;
        $written = 0;
        $parents = [];
        $append = function (\DOMNode $node, array $path) use (&$parents, &$column, $roots) {
            $parent = $roots[$column];
            foreach ($path as $ancestor) {
                $key = $ancestor->getNodePath();
                if (!isset($parents[$key])) {
                    $parents[$key] = $ancestor->cloneNode(false);
                    $parent->appendChild($parents[$key]);
                }
                $parent = $parents[$key];
            }
            $parent->appendChild($node);
        };
        $visit = function (\DOMNode $node, array $path) use (&$visit, &$column, &$written, &$parents, $perColumn, $count, $append, $document) {
            if ($node instanceof \DOMText) {
                foreach (preg_split('/(\s+)/u', $node->textContent, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY) as $token) {
                    if (trim($token) !== '') {
                        if ($written >= $perColumn && $column < $count - 1) { $column++; $written = 0; $parents = []; }
                        $written++;
                    }
                    $append($document->createTextNode($token), $path);
                }
            } elseif ($node instanceof \DOMElement) {
                if (!$node->hasChildNodes()) $append($node->cloneNode(true), $path);
                else foreach ($node->childNodes as $child) $visit($child, [...$path, $node]);
            }
        };
        foreach ($source->childNodes as $node) $visit($node, []);
        return array_map(function ($root) use ($document) {
            $result = '';
            foreach ($root->childNodes as $child) $result .= $document->saveHTML($child);
            return $result;
        }, $roots);
    }

    /**
     * Localiza en disco el PDF subido por el administrador, si existe.
     */
    public function resolveUploadedPdf(array $edicion): ?string
    {
        $url = $edicion['pdf_url'] ?? null;
        if (empty($url)) {
            return null;
        }

        $filename = basename((string) parse_url($url, PHP_URL_PATH));
        if ($filename === '') {
            return null;
        }

        $candidatos = [
            storage_path('app/public/ediciones_pdf/' . $filename),
            public_path('storage/ediciones_pdf/' . $filename),
            public_path('ediciones_pdf/' . $filename),
        ];

        foreach ($candidatos as $ruta) {
            if (is_file($ruta)) {
                return $ruta;
            }
        }

        return null;
    }

    /**
     * Indica si la edición ya tiene un PDF definitivo subido por el administrador.
     */
    public function tienePdfSubido(array $edicion): bool
    {
        return $this->resolveUploadedPdf($edicion) !== null;
    }

    /**
     * Nombre de archivo limpio: latitud-18-ed-123.pdf
     */
    public function filename(array $edicion): string
    {
        $id = (string) ($edicion['id'] ?? 'edicion');
        $base = 'latitud-18-ed-' . preg_replace('/[^a-zA-Z0-9\-_]/', '', $id);
        return Str::limit($base, 60, '') . '.pdf';
    }
}
