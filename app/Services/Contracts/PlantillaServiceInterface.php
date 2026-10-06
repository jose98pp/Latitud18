<?php

namespace App\Services\Contracts;

use App\Models\PeriodicoPlantilla;
use App\Models\PeriodicoPagina;

interface PlantillaServiceInterface
{
    /**
     * Aplica una plantilla a una página existente (deep copy de frames).
     * No modifica ningún campo del registro $plantilla original.
     *
     * @see Requirements 1.2, 1.4
     */
    public function aplicarAPagina(PeriodicoPlantilla $plantilla, PeriodicoPagina $pagina): void;

    /**
     * Genera un slug URL-friendly único a partir de un nombre de plantilla.
     * Si $excludeId se proporciona, ese registro se excluye de la verificación de unicidad
     * (útil al actualizar una plantilla existente sin colisionar con su propio slug).
     *
     * @see Requirements 1.6
     */
    public function generarSlugUnico(string $nombre, ?string $excludeId = null): string;

    /**
     * Crea una copia independiente de la plantilla con is_custom = true
     * y nombre con el sufijo " (Copia)".
     *
     * @see Requirements 10.2
     */
    public function duplicar(PeriodicoPlantilla $plantilla): PeriodicoPlantilla;

    /**
     * Exporta la plantilla como array listo para serializar a JSON .latitud-template.
     * Incluye los campos: id, name, category, description, preview_color, frames.
     *
     * @see Requirements 10.3
     */
    public function exportar(PeriodicoPlantilla $plantilla): array;

    /**
     * Importa una plantilla desde un array validado y persiste un nuevo registro
     * con is_custom = true.
     * Lanza \InvalidArgumentException si la validación de $data falla.
     *
     * @see Requirements 10.4, 10.5
     *
     * @throws \InvalidArgumentException
     */
    public function importar(array $data): PeriodicoPlantilla;
}
