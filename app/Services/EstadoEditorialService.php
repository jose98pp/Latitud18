<?php

namespace App\Services;

use App\Models\PeriodicoEdicion;
use App\Services\Contracts\EstadoEditorialServiceInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Implementa la máquina de estados del ciclo de vida editorial.
 *
 * El grafo de transiciones es la fuente única de verdad para los estados permitidos.
 * No duplicar esta lógica en controladores ni en vistas.
 *
 * @see Requirements 8.1
 */
class EstadoEditorialService implements EstadoEditorialServiceInterface
{
    /**
     * Grafo inmutable de transiciones permitidas.
     * Clave: estado actual. Valor: array de estados destino permitidos.
     *
     * @see Requirements 8.1
     */
    private const TRANSICIONES = [
        'borrador'   => ['revision'],
        'revision'   => ['aprobado', 'borrador'],
        'aprobado'   => ['programado', 'revision'],
        'programado' => ['publicado', 'aprobado'],
        'publicado'  => [],
    ];

    /**
     * Retorna `true` si la transición de $estadoActual a $estadoNuevo está permitida
     * según el grafo TRANSICIONES, `false` en caso contrario.
     *
     * @see Requirements 8.1
     */
    public function puedeTransicionar(string $estadoActual, string $estadoNuevo): bool
    {
        $permitidos = self::TRANSICIONES[$estadoActual] ?? [];

        return in_array($estadoNuevo, $permitidos, true);
    }

    /**
     * Ejecuta la transición de estado sobre la edición.
     *
     * Lógica específica por estado destino:
     * - `programado`: requiere `$extra['fecha_programada']` al menos 1 minuto en el futuro.
     * - `publicado`:  dentro de una transacción, desactiva todas las demás ediciones y activa
     *                 esta como publicada con `fecha_publicacion = now()`.
     * - otros:        actualiza únicamente el campo `estado`.
     *
     * @see Requirements 8.1, 8.2, 8.3
     *
     * @throws \DomainException       Si la transición no está permitida por el grafo.
     * @throws \InvalidArgumentException Si faltan datos requeridos para el estado destino.
     */
    public function transicionar(PeriodicoEdicion $edicion, string $nuevoEstado, array $extra = []): void
    {
        $estadoActual = $edicion->estado;

        // Validate transition is allowed
        if (!$this->puedeTransicionar($estadoActual, $nuevoEstado)) {
            throw new \DomainException(
                "Transición {$estadoActual}→{$nuevoEstado} no permitida. " .
                "Transiciones válidas desde '{$estadoActual}': [" .
                implode(', ', self::TRANSICIONES[$estadoActual] ?? []) . "]"
            );
        }

        // State-specific logic
        if ($nuevoEstado === 'programado') {
            // fecha_programada must be at least 1 minute in the future
            if (empty($extra['fecha_programada'])) {
                throw new \InvalidArgumentException(
                    "Se requiere 'fecha_programada' para transicionar a estado 'programado'."
                );
            }
            $fechaProgramada = Carbon::parse($extra['fecha_programada']);
            if ($fechaProgramada->lte(now()->addMinute())) {
                throw new \InvalidArgumentException(
                    "La 'fecha_programada' debe ser al menos 1 minuto en el futuro."
                );
            }
            $edicion->update([
                'estado'           => 'programado',
                'fecha_programada' => $fechaProgramada,
            ]);
            return;
        }

        if ($nuevoEstado === 'publicado') {
            DB::transaction(function () use ($edicion) {
                // Deactivate all other editions
                PeriodicoEdicion::where('id', '!=', $edicion->id)->update(['activa' => false]);
                // Activate and publish this edition
                $edicion->update([
                    'estado'            => 'publicado',
                    'activa'            => true,
                    'publicada'         => true,
                    'fecha_publicacion' => now(),
                ]);
            });
            return;
        }

        // All other transitions: just update the estado
        $edicion->update(['estado' => $nuevoEstado]);
    }
}
