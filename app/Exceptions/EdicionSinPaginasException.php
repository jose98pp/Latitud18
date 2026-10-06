<?php

namespace App\Exceptions;

class EdicionSinPaginasException extends \RuntimeException
{
    public function __construct(string|int $edicionId)
    {
        parent::__construct(
            "La edición '{$edicionId}' no contiene páginas. No se puede generar el PDF."
        );
    }
}
