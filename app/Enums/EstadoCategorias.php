<?php

namespace App\Enums;

enum EstadoCategorias : string
{
    case ACTIVO = 'Activo';
    case INACTIVO = 'Inactivo';

    public function toString(): string
    {
        return match($this)
        {
            self::ACTIVO => 'Activo',
            self::INACTIVO => 'Inactivo',
        };
    }
}
