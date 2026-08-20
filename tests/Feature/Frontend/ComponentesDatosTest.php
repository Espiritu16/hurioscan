<?php

namespace Tests\Feature\Frontend;

use Tests\TestCase;

class ComponentesDatosTest extends TestCase
{
    public function test_la_etiqueta_de_estado_usa_los_cuatro_colores_semanticos(): void
    {
        $this->blade('<x-etiqueta-estado tipo="exito">Digitalizado</x-etiqueta-estado>')
            ->assertSee('text-exito');
        $this->blade('<x-etiqueta-estado tipo="advertencia">Parcial</x-etiqueta-estado>')
            ->assertSee('text-advertencia');
        $this->blade('<x-etiqueta-estado tipo="peligro">Ilegible</x-etiqueta-estado>')
            ->assertSee('text-peligro');
        $this->blade('<x-etiqueta-estado>Corregida</x-etiqueta-estado>')
            ->assertSee('text-tinta-suave');
    }

    public function test_la_etiqueta_de_estado_no_usa_el_color_de_acento(): void
    {
        foreach (['exito', 'advertencia', 'peligro', 'neutro'] as $tipo) {
            $this->blade('<x-etiqueta-estado :tipo="$tipo">Estado</x-etiqueta-estado>', ['tipo' => $tipo])
                ->assertDontSee('text-acento');
        }
    }

    public function test_la_tabla_se_desplaza_dentro_de_su_propio_contenedor(): void
    {
        $tabla = $this->blade(<<<'BLADE'
            <x-tabla>
                <x-slot:cabecera><x-tabla.encabezado>Paciente</x-tabla.encabezado></x-slot:cabecera>
                <tr><td>Mamani Choque</td></tr>
            </x-tabla>
        BLADE);

        $tabla->assertSee('overflow-x-auto');
        $tabla->assertSee('Paciente');
        $tabla->assertSee('Mamani Choque');
    }

    public function test_el_encabezado_ordenable_expone_aria_sort(): void
    {
        $this->blade('<x-tabla.encabezado url="#" orden="asc">H.C.</x-tabla.encabezado>')
            ->assertSee('aria-sort="ascending"', false);
        $this->blade('<x-tabla.encabezado url="#" orden="desc">H.C.</x-tabla.encabezado>')
            ->assertSee('aria-sort="descending"', false);
        $this->blade('<x-tabla.encabezado>H.C.</x-tabla.encabezado>')
            ->assertDontSee('aria-sort', false);
    }

    public function test_la_tarjeta_de_indicador_muestra_titulo_valor_y_detalle(): void
    {
        $tarjeta = $this->blade(
            '<x-tarjeta-indicador titulo="Folders digitalizados" valor="1 248" unidad="/ 3 410">faltan 2 162</x-tarjeta-indicador>'
        );

        $tarjeta->assertSeeInOrder(['Folders digitalizados', '1 248', '/ 3 410', 'faltan 2 162']);
    }

    public function test_el_estado_vacio_ofrece_una_accion(): void
    {
        $vacio = $this->blade(<<<'BLADE'
            <x-estado-vacio titulo="Sin resultados" descripcion="No hay coincidencias para el término buscado.">
                <x-boton variante="secundario">Registrar paciente</x-boton>
            </x-estado-vacio>
        BLADE);

        $vacio->assertSee('Sin resultados');
        $vacio->assertSee('Registrar paciente');
    }
}
