<?php

namespace Tests\Models;

use App\Enums\EstadoCategorias;
use App\Models\Categorias;
use PHPUnit\Framework\TestCase;

class CategoriasTest extends TestCase
{

    public function testInsert()
    {
        $Categoria = new Categorias(
            ['id' => null,
             'nombres' => 'Bebidas',
             'orden' => 1,
             'estado' => 'Activo']
        );
        $Categoria->insert();
        $this->assertSame(true, $Categoria->categoriaRegistrada('Bebidas'));
    }

}
