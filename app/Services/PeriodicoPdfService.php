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
        // 1) PDF subido por el administrador, si existe
        $subido = $this->resolveUploadedPdf($edicion);
        if ($subido) {
            return response()->download($subido, $this->filename($edicion), [
                'Content-Type' => 'application/pdf',
                'Cache-Control' => 'public, max-age=600',
            ]);
        }

        // 2) Composición del PDF en el servidor
        $pdf = Pdf::loadView('periodico.pdf', ['edicion' => $edicion])
            ->setOptions([
                'isRemoteEnabled'      => true,
                'isHtml5ParserEnabled' => true,
                'defaultFont'          => 'DejaVu Serif',
            ]);

        return $pdf->download($this->filename($edicion), [
            'Content-Type' => 'application/pdf',
            'Cache-Control' => 'public, max-age=600',
        ]);
    }

    /**
     * Devuelve el PDF como string (para previsualizar o adjuntar).
     */
    public function contenido(array $edicion): string
    {
        $subido = $this->resolveUploadedPdf($edicion);
        if ($subido) {
            return (string) file_get_contents($subido);
        }

        return Pdf::loadView('periodico.pdf', ['edicion' => $edicion])
            ->setOptions([
                'isRemoteEnabled'      => true,
                'isHtml5ParserEnabled' => true,
                'defaultFont'          => 'DejaVu Serif',
            ])
            ->output();
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
        $base = 'latitud-18-' . preg_replace('/[^a-zA-Z0-9\-_]/', '', $id);
        return Str::limit($base, 60, '') . '.pdf';
    }
}
