<?php

namespace Tests\Feature\Frontend;

use Tests\TestCase;

class CatalogoComponentesTest extends TestCase
{
    public function test_el_catalogo_renderiza_todos_los_componentes_con_sus_variantes(): void
    {
        $this->withoutVite();

        $catalogo = $this->view('componentes.catalogo');

        // Un encabezado por componente del RFC.
        $catalogo->assertSeeInOrder([
            'Menú lateral por rol',
            'Tokens de color',
            'Botón',
            'Campo',
            'Buscador',
            'Etiqueta de estado',
            'Tabla',
            'Tarjeta de indicador',
            'Estado vacío',
            'Indicador de paso',
        ]);

        // Variantes representativas.
        $catalogo->assertSee('Primario');
        $catalogo->assertSee('Secundario');
        $catalogo->assertSee('Terciario');
        $catalogo->assertSee('Ilegible');
        $catalogo->assertSee('Fijado para esta sesión');
        $catalogo->assertSee('Paso 2 de 4');
    }
}
