<?php

namespace App\Services\Contracts;

use App\Models\PeriodicoEdicion;

interface EdicionServiceInterface
{
    /**
     * Creates a complete 12-page edition from a mapping, wrapped in a DB transaction.
     *
     * @param array $mapping  12 entries like [{"slot":1,"plantilla_id":"tpl-portada"|null}, ...]
     * @param array $atributos  Edition attributes: numero_edicion, fecha, titulo, etc.
     * @return PeriodicoEdicion  The created edition with pages and elements loaded
     */
    public function crearDesdeMapping(array $mapping, array $atributos): PeriodicoEdicion;
}
