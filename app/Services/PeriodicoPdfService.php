<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

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
                'Cache-Control' => 'public, max-age=600',
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
        $response->headers->set('Cache-Control', 'public, max-age=600');

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
