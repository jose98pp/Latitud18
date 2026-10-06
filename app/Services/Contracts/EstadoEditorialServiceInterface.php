<?php

namespace App\Services\Contracts;

use App\Models\PeriodicoEdicion;

interface EstadoEditorialServiceInterface
{
    /**
     * Determina si una transición de estado es válida según el grafo editorial.
     *
     * @see Requirements 8.1
     */
    public function puedeTransicionar(string $estadoActual, string $estadoNuevo): bool;

    /**
     * Ejecuta la transición de estado sobre la edición dada.
     * Lanza \DomainException si la transición no está permitida.
     *
     * @see Requirements 8.1, 8.2, 8.3
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     */
    public function transicionar(PeriodicoEdicion $edicion, string $nuevoEstado, array $extra = []): void;
}
